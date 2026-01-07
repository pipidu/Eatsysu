<?php
// 禁用安装程序后的保护标记
$installLockFile = __DIR__ . '/install.lock';

// 如果已经安装过，且没有强制重新安装，则跳转
if (file_exists($installLockFile) && !isset($_GET['force'])) {
    header('Location: /');
    exit;
}

session_start();

// HTML转义函数
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// 保存上一步的配置
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['install_config'] = array_merge($_SESSION['install_config'] ?? [], $_POST);
}

$config = $_SESSION['install_config'] ?? [];

// 步骤处理
switch ($step) {
    case 1:
        // 环境检查
        break;
        
    case 2:
        // 数据库配置
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 测试数据库连接
            try {
                $pdo = new PDO(
                    "mysql:host=" . $_POST['db_host'] . ";charset=utf8mb4",
                    $_POST['db_user'],
                    $_POST['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                header('Location: install.php?step=3');
                exit;
            } catch (PDOException $e) {
                $error = '数据库连接失败: ' . $e->getMessage();
            }
        }
        break;
        
    case 3:
        // 创建数据库和表
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = new PDO(
                    "mysql:host=" . $config['db_host'] . ";charset=utf8mb4",
                    $config['db_user'],
                    $config['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // 创建数据库
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$config['db_name']}`");
                
                // 创建管理员表
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS admins (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                
                // 创建商家表
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS restaurants (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        campus VARCHAR(50) NOT NULL,
                        location VARCHAR(200),
                        platforms JSON COMMENT '推荐点单平台',
                        description TEXT,
                        image_url VARCHAR(500),
                        taste_score DECIMAL(3,1) DEFAULT 0 COMMENT '口味评分',
                        price_score DECIMAL(3,1) DEFAULT 0 COMMENT '价格评分',
                        packaging_score DECIMAL(3,1) DEFAULT 0 COMMENT '包装评分',
                        speed_score DECIMAL(3,1) DEFAULT 0 COMMENT '速度评分',
                        overall_score DECIMAL(3,1) DEFAULT 0 COMMENT '综合评分',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_campus (campus),
                        INDEX idx_overall_score (overall_score DESC)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                
                // 创建浏览记录表
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS views (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        restaurant_id INT NOT NULL,
                        ip_address VARCHAR(45),
                        user_agent VARCHAR(500),
                        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");

                // 创建用户表
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        created_by INT COMMENT '创建该用户的管理员ID',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                
                header('Location: install.php?step=4');
                exit;
                
            } catch (PDOException $e) {
                $error = '创建数据库表失败: ' . $e->getMessage();
            }
        }
        break;
        
    case 4:
        // 管理员账户
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = new PDO(
                    "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'] . ";charset=utf8mb4",
                    $config['db_user'],
                    $config['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // 创建管理员账户
                $username = trim($_POST['admin_username']);
                $password = $_POST['admin_password'];
                
                if (strlen($username) < 3) {
                    throw new Exception('用户名至少需要3个字符');
                }
                
                if (strlen($password) < 6) {
                    throw new Exception('密码至少需要6个字符');
                }
                
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $passwordHash]);
                
                header('Location: install.php?step=5');
                exit;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;
        
    case 5:
        // S3配置（可选）
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Location: install.php?step=6');
            exit;
        }
        break;
        
    case 6:
        // 完成安装
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 生成配置文件
                $configContent = "<?php\n";
                $configContent .= "/**\n";
                $configContent .= " * 配置文件\n";
                $configContent .= " * 如果此文件不存在或格式不正确，请运行安装程序 install.php\n";
                $configContent .= " */\n\n";
                
                $configContent .= "// 数据库配置\n";
                $configContent .= "define('DB_HOST', '{$config['db_host']}');\n";
                $configContent .= "define('DB_USER', '{$config['db_user']}');\n";
                $configContent .= "define('DB_PASS', '{$config['db_pass']}');\n";
                $configContent .= "define('DB_NAME', '{$config['db_name']}');\n\n";
                
                $configContent .= "// 对象存储配置（支持 AWS S3、Cloudflare R2、MinIO 等 S3 API 兼容服务）\n";
                $configContent .= "define('AWS_ACCESS_KEY_ID', '{$config['aws_key']}');\n";
                $configContent .= "define('AWS_SECRET_ACCESS_KEY', '{$config['aws_secret']}');\n";
                $configContent .= "define('AWS_REGION', '{$config['aws_region']}');\n";
                $configContent .= "define('AWS_BUCKET', '{$config['aws_bucket']}');\n\n";
                
                $configContent .= "// 自定义对象存储端点（可选）\n";
                $configContent .= "// 用于 Cloudflare R2、MinIO、阿里云OSS等 S3 API 兼容服务\n";
                $configContent .= "// 留空则使用 AWS S3\n";
                $configContent .= "define('S3_ENDPOINT', '{$config['s3_endpoint']}');\n\n";
                
                $configContent .= "// 是否使用路径风格端点（某些自建 S3 服务需要设置为 true）\n";
                $configContent .= "// Cloudflare R2 需要设置为 true\n";
                $configContent .= "define('S3_USE_PATH_STYLE', true);\n\n";
                
                $configContent .= "// 自定义域名（可选）\n";
                $configContent .= "// 如果为对象存储配置了自定义域名（如 CDN 域名），在此填写\n";
                $configContent .= "// 可用于所有存储服务（AWS S3、Cloudflare R2、多吉云等）的 CDN 加速\n";
                $configContent .= "// 示例: cdn.example.com\n";
                $configContent .= "// 注意：多吉云需要在控制台绑定域名到存储空间后，在此填写\n";
                $configContent .= "define('S3_CUSTOM_DOMAIN', '{$config['s3_custom_domain']}');\n\n";

                $configContent .= "// 多吉云配置（可选）\n";
                $configContent .= "// 多吉云是国内的对象存储服务提供商，提供 CDN 加速\n";
                $configContent .= "// 官网: https://www.dogecloud.com/\n";
                $configContent .= "define('DOGE_ACCESS_KEY', '{$config['doge_access_key']}');\n";
                $configContent .= "define('DOGE_SECRET_KEY', '{$config['doge_secret_key']}');\n";
                $configContent .= "define('DOGE_ENABLED', " . (empty($config['doge_bucket']) ? 'false' : 'true') . "); // 是否启用多吉云\n";
                $configContent .= "define('DOGE_BUCKET', '{$config['doge_bucket']}');\n";
                $configContent .= "define('DOGE_API_URL', 'https://api.dogecloud.com');\n";
                $configContent .= "define('DOGE_TMP_TOKEN_TTL', 7200); // 临时密钥有效期（秒），范围 0-7200\n\n";

                $configContent .= "// 会话配置\n";
                $configContent .= "define('SESSION_NAME', 'EATSYSU_SESSION');\n\n";

                $configContent .= "// 网站配置\n";
                $configContent .= "define('SITE_ICON', 'https://doges3.img.shygo.cn/2026/01/06/42ac7f56a69e3b866e19c6ecb6dc62f8.jpg/720x1080'); // 网站图标\n";
                $configContent .= "define('SITE_ICP_NUMBER', '" . (isset($config['site_icp_number']) ? addslashes($config['site_icp_number']) : '') . "'); // ICP备案号\n";
                $configContent .= "define('SITE_PSB_NUMBER', '" . (isset($config['site_psb_number']) ? addslashes($config['site_psb_number']) : '') . "'); // 公安备案号\n\n";

                $configContent .= "// 平台图标配置\n";
                $configContent .= "define('PLATFORM_ICONS', [\n";
                $configContent .= "    'phone' => '📞',\n";
                $configContent .= "    'dine_in' => '🏢',\n";
                $configContent .= "    'jd' => 'https://doges3.img.shygo.cn/2026/01/06/d2d2439d19cbb03207b53ace32279b01.jpg/720x1080',\n";
                $configContent .= "    'meituan' => 'https://doges3.img.shygo.cn/2026/01/06/71b72d9229c9f9d0a843fe527d20540b.png/720x1080',\n";
                $configContent .= "    'taobao' => 'https://doges3.img.shygo.cn/2026/01/06/ad8095ff1dfa687f275fbc0459dbdf22.jpg/720x1080'\n";
                $configContent .= "]);\n\n";

                $configContent .= "// 时区设置\n";
                $configContent .= "date_default_timezone_set('Asia/Shanghai');\n";
                
                // 写入配置文件
                file_put_contents(__DIR__ . '/config.php', $configContent);
                
                // 创建安装锁
                file_put_contents($installLockFile, date('Y-m-d H:i:s'));
                
                header('Location: install.php?step=7');
                exit;
                
            } catch (Exception $e) {
                $error = '安装失败: ' . $e->getMessage();
            }
        }
        break;
        
    case 7:
        // 安装完成
        break;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - 双鸭山大学美食分享</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .steps {
            display: flex;
            padding: 20px 32px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .step-dot {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            color: #9ca3af;
        }
        .step-dot.active {
            color: #667eea;
            font-weight: 600;
        }
        .step-dot.completed {
            color: #10b981;
        }
        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }
        .step-dot.active .step-number {
            background: #667eea;
            color: white;
        }
        .step-dot.completed .step-number {
            background: #10b981;
            color: white;
        }
        .content {
            padding: 32px;
        }
        .step-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
            text-align: center;
        }
        .step-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
            text-align: center;
            line-height: 1.6;
        }
        .check-list {
            margin-bottom: 24px;
        }
        .check-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-icon {
            font-size: 20px;
        }
        .check-text {
            flex: 1;
            color: #333;
            font-size: 14px;
        }
        .check-icon.success {
            color: #10b981;
        }
        .check-icon.error {
            color: #ef4444;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group .hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #e1e1e1;
        }
        .btn-secondary:hover {
            background: #f9f9f9;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
        }
        .alert-success {
            background: #d1fae5;
            color: #059669;
        }
        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .info-box p {
            color: #1e40af;
            font-size: 14px;
            line-height: 1.6;
        }
        .success-animation {
            text-align: center;
            padding: 40px 0;
        }
        .success-icon {
            font-size: 80px;
            margin-bottom: 24px;
            animation: bounce 1s ease;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .success-title {
            font-size: 28px;
            font-weight: 600;
            color: #10b981;
            margin-bottom: 16px;
        }
        .success-message {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 32px;
        }
        .next-steps {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin-bottom: 24px;
        }
        .next-steps h4 {
            color: #333;
            font-size: 16px;
            margin-bottom: 12px;
        }
        .next-steps ul {
            color: #666;
            font-size: 14px;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 8px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        .checkbox-group label {
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="header">
            <h1>🍜 双鸭山大学美食分享</h1>
            <p>安装向导</p>
        </div>
        
        <div class="steps">
            <div class="step-dot <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">1</span>
                环境检查
            </div>
            <div class="step-dot <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">2</span>
                数据库
            </div>
            <div class="step-dot <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">3</span>
                创建表
            </div>
            <div class="step-dot <?php echo $step >= 4 ? ($step > 4 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">4</span>
                管理员
            </div>
            <div class="step-dot <?php echo $step >= 5 ? ($step > 5 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">5</span>
                对象存储
            </div>
            <div class="step-dot <?php echo $step >= 6 ? ($step > 6 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number">6</span>
                完成
            </div>
        </div>
        
        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo h($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo h($success); ?></div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <h2 class="step-title">环境检查</h2>
                <p class="step-description">检查您的服务器环境是否满足安装要求</p>
                
                <div class="check-list">
                    <?php
                        $checks = [];
                        
                        // PHP版本
                        $phpVersion = phpversion();
                        $checks[] = [
                            'name' => 'PHP版本',
                            'value' => $phpVersion . ' (要求: 7.4+)',
                            'success' => version_compare($phpVersion, '7.4.0', '>=')
                        ];
                        
                        // PDO
                        $checks[] = [
                            'name' => 'PDO扩展',
                            'value' => extension_loaded('pdo') ? '已安装' : '未安装',
                            'success' => extension_loaded('pdo')
                        ];
                        
                        // PDO MySQL
                        $checks[] = [
                            'name' => 'PDO MySQL',
                            'value' => extension_loaded('pdo_mysql') ? '已安装' : '未安装',
                            'success' => extension_loaded('pdo_mysql')
                        ];
                        
                        // JSON
                        $checks[] = [
                            'name' => 'JSON扩展',
                            'value' => extension_loaded('json') ? '已安装' : '未安装',
                            'success' => extension_loaded('json')
                        ];
                        
                        // cURL
                        $checks[] = [
                            'name' => 'cURL扩展',
                            'value' => extension_loaded('curl') ? '已安装' : '未安装',
                            'success' => extension_loaded('curl')
                        ];
                        
                        // 文件写入权限
                        $configWritable = is_writable(__DIR__);
                        $checks[] = [
                            'name' => 'config.php 写入权限',
                            'value' => $configWritable ? '可写' : '不可写',
                            'success' => $configWritable
                        ];
                        
                        // Composer依赖
                        $vendorExists = is_dir(__DIR__ . '/vendor');
                        $checks[] = [
                            'name' => 'Composer依赖',
                            'value' => $vendorExists ? '已安装' : '需要运行 composer install',
                            'success' => $vendorExists
                        ];
                    ?>
                    
                    <?php foreach ($checks as $check): ?>
                        <div class="check-item">
                            <span class="check-icon <?php echo $check['success'] ? 'success' : 'error'; ?>">
                                <?php echo $check['success'] ? '✓' : '✗'; ?>
                            </span>
                            <span class="check-text">
                                <strong><?php echo h($check['name']); ?>:</strong> 
                                <?php echo h($check['value']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php
                    $allPassed = true;
                    foreach ($checks as $check) {
                        if (!$check['success']) {
                            $allPassed = false;
                            break;
                        }
                    }
                ?>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" onclick="location.reload()">重新检查</button>
                    <?php if ($allPassed): ?>
                        <button type="button" class="btn btn-primary" onclick="location.href='?step=2'">下一步</button>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($step == 2): ?>
                <h2 class="step-title">数据库配置</h2>
                <p class="step-description">请输入您的数据库连接信息</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>数据库主机</label>
                        <input type="text" name="db_host" value="<?php echo h($config['db_host'] ?? 'localhost'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>数据库名称</label>
                        <input type="text" name="db_name" value="<?php echo h($config['db_name'] ?? 'eatsysu'); ?>" required>
                        <p class="hint">如果数据库不存在，安装程序会自动创建</p>
                    </div>
                    <div class="form-group">
                        <label>数据库用户名</label>
                        <input type="text" name="db_user" value="<?php echo h($config['db_user'] ?? 'root'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>数据库密码</label>
                        <input type="password" name="db_pass" value="<?php echo h($config['db_pass'] ?? ''); ?>">
                    </div>
                    <div class="form-actions">
                        <a href="?step=1" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary">测试连接并继续</button>
                    </div>
                </form>
                
            <?php elseif ($step == 3): ?>
                <h2 class="step-title">创建数据表</h2>
                <p class="step-description">安装程序将自动创建所需的数据表</p>
                
                <div class="info-box">
                    <p>将创建以下数据表：</p>
                    <ul style="margin-top: 8px; padding-left: 20px;">
                        <li><strong>admins</strong> - 管理员账户表</li>
                        <li><strong>restaurants</strong> - 商家信息表</li>
                        <li><strong>views</strong> - 浏览记录表</li>
                        <li><strong>users</strong> - 用户账户表</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <div class="form-actions">
                        <a href="?step=2" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary">创建数据表</button>
                    </div>
                </form>
                
            <?php elseif ($step == 4): ?>
                <h2 class="step-title">创建管理员账户</h2>
                <p class="step-description">设置后台管理员账户，用于登录管理系统</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>管理员用户名</label>
                        <input type="text" name="admin_username" value="<?php echo h($config['admin_username'] ?? 'admin'); ?>" required>
                        <p class="hint">至少3个字符</p>
                    </div>
                    <div class="form-group">
                        <label>管理员密码</label>
                        <input type="password" name="admin_password" required>
                        <p class="hint">至少6个字符</p>
                    </div>
                    <div class="form-group">
                        <label>确认密码</label>
                        <input type="password" name="admin_password_confirm" required>
                    </div>
                    <div class="form-actions">
                        <a href="?step=3" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary" onclick="return validatePassword()">创建管理员</button>
                    </div>
                </form>
                
                <script>
                function validatePassword() {
                    const password = document.querySelector('input[name="admin_password"]').value;
                    const confirm = document.querySelector('input[name="admin_password_confirm"]').value;
                    
                    if (password !== confirm) {
                        alert('两次输入的密码不一致');
                        return false;
                    }
                    return true;
                }
                </script>
                
            <?php elseif ($step == 5): ?>
                <h2 class="step-title">对象存储配置</h2>
                <p class="step-description">配置图片存储（支持 AWS S3、Cloudflare R2 等，可选）</p>
                
                <div class="info-box">
                    <p><strong>支持的服务：</strong></p>
                    <ul style="margin-top: 8px; padding-left: 20px;">
                        <li>AWS S3（留空端点）</li>
                        <li>Cloudflare R2（填写 R2 端点）</li>
                        <li>MinIO、阿里云OSS等 S3 API 兼容服务</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <p><strong>提示：</strong>如果暂时没有对象存储账户，可以留空跳过此步骤。之后可以在 config.php 中配置。</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Access Key ID</label>
                        <input type="text" name="aws_key" value="<?php echo h($config['aws_key'] ?? ''); ?>" placeholder="留空跳过">
                    </div>
                    <div class="form-group">
                        <label>Secret Access Key</label>
                        <input type="password" name="aws_secret" value="<?php echo h($config['aws_secret'] ?? ''); ?>" placeholder="留空跳过">
                    </div>
                    <div class="form-group">
                        <label>区域</label>
                        <input type="text" name="aws_region" value="<?php echo h($config['aws_region'] ?? 'auto'); ?>" placeholder="AWS S3 用区域（如 ap-guangzhou），其他用 auto">
                    </div>
                    <div class="form-group">
                        <label>存储桶名称</label>
                        <input type="text" name="aws_bucket" value="<?php echo h($config['aws_bucket'] ?? ''); ?>" placeholder="留空跳过">
                    </div>
                    <div class="form-group">
                        <label>自定义端点（可选）</label>
                        <input type="text" name="s3_endpoint" value="<?php echo h($config['s3_endpoint'] ?? ''); ?>" placeholder="Cloudflare R2: https://xxx.r2.cloudflarestorage.com">
                        <p class="hint">用于 Cloudflare R2、MinIO 等服务，AWS S3 留空</p>
                    </div>
                    <div class="form-group">
                        <label>自定义域名（可选）</label>
                        <input type="text" name="s3_custom_domain" value="<?php echo h($config['s3_custom_domain'] ?? ''); ?>" placeholder="例如: cdn.example.com">
                        <p class="hint">如果配置了 CDN 域名可填写（多吉云控制台绑定域名后在此填写）</p>
                    </div>

                    <hr style="margin: 30px 0; border: none; border-top: 2px solid #e0e0e0;">

                    <h3 style="margin-bottom: 15px;">多吉云配置（可选）</h3>
                    <div class="info-box" style="margin-bottom: 20px;">
                        <p><strong>多吉云优势：</strong>国内 CDN 加速、兼容 S3 API、按需付费、支持 HTTPS 自定义域名</p>
                        <p style="margin-top: 8px;"><strong>提示：</strong>多吉云也可使用自定义域名，请在多吉云控制台绑定域名后，在上面的"自定义域名"框中填写。</p>
                    </div>

                    <div class="form-group">
                        <label>Access Key（多吉云）</label>
                        <input type="text" name="doge_access_key" value="<?php echo h($config['doge_access_key'] ?? ''); ?>" placeholder="在用户中心-密钥管理中查看">
                    </div>
                    <div class="form-group">
                        <label>Secret Key（多吉云）</label>
                        <input type="password" name="doge_secret_key" value="<?php echo h($config['doge_secret_key'] ?? ''); ?>" placeholder="请勿泄露">
                    </div>
                    <div class="form-group">
                        <label>存储空间名称（多吉云）</label>
                        <input type="text" name="doge_bucket" value="<?php echo h($config['doge_bucket'] ?? ''); ?>" placeholder="例如: my-bucket-name">
                        <p class="hint">填写后将自动启用多吉云（可手动在配置文件中关闭）</p>
                    </div>
                    <div class="form-group">
                        <label>ICP备案号（可选）</label>
                        <input type="text" name="site_icp_number" value="<?php echo h($config['site_icp_number'] ?? ''); ?>" placeholder="例如：粤ICP备XXXXXXXX号">
                        <p class="hint">工信部ICP备案号，将显示在网站底部</p>
                    </div>
                    <div class="form-group">
                        <label>公安备案号（可选）</label>
                        <input type="text" name="site_psb_number" value="<?php echo h($config['site_psb_number'] ?? ''); ?>" placeholder="例如：京公网安备XXXXXXXX号">
                        <p class="hint">公安部备案号，将显示在网站底部</p>
                    </div>
                    <div class="form-actions">
                        <a href="?step=4" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary">下一步</button>
                    </div>
                </form>
                
            <?php elseif ($step == 6): ?>
                <h2 class="step-title">确认安装</h2>
                <p class="step-description">确认配置信息，完成安装</p>
                
                <div class="info-box">
                    <p><strong>即将创建配置文件：</strong> config.php</p>
                </div>
                
                <form method="POST">
                    <div class="checkbox-group">
                        <input type="checkbox" id="confirm" required>
                        <label for="confirm">我确认以上配置信息正确，点击完成后将自动创建配置文件</label>
                    </div>
                    <div class="form-actions">
                        <a href="?step=5" class="btn btn-secondary">上一步</a>
                        <button type="submit" class="btn btn-primary">完成安装</button>
                    </div>
                </form>
                
            <?php elseif ($step == 7): ?>
                <div class="success-animation">
                    <div class="success-icon">🎉</div>
                    <h2 class="success-title">安装成功！</h2>
                    <p class="success-message">双鸭山大学美食分享网站已成功安装！</p>
                    
                    <div class="next-steps">
                        <h4>接下来的步骤：</h4>
                        <ul>
                            <li>访问 <a href="/admin/login.php" style="color: #667eea;">/admin/login.php</a> 登录后台管理系统</li>
                            <li>在后台管理用户，用户可以上传商家信息</li>
                            <li>开始添加您喜爱的美食商家</li>
                            <li>如果需要使用图片上传功能，请配置AWS S3</li>
                            <li>查看 <a href="/README.md" style="color: #667eea;" target="_blank">README.md</a> 了解更多功能</li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/" class="btn btn-primary">访问网站首页</a>
                        <a href="/admin/login.php" class="btn btn-secondary">进入管理后台</a>
                    </div>
                    
                    <div class="info-box" style="margin-top: 24px;">
                        <p><strong>安全提示：</strong>安装完成后，建议删除 install.php 文件以确保安全。</p>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function h(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    </script>
</body>
</html>
