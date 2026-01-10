<?php
require_once __DIR__ . '/includes/functions.php';

// 检查用户登录状态
if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 验证必填字段
        if (empty($_POST['name'])) {
            throw new Exception('商家名称不能为空');
        }
        if (empty($_POST['campus'])) {
            throw new Exception('校区不能为空');
        }
        if (empty($_POST['taste_score']) || !is_numeric($_POST['taste_score']) || $_POST['taste_score'] < 0 || $_POST['taste_score'] > 10) {
            throw new Exception('口味评分必须在0-10之间');
        }
        if (empty($_POST['price_score']) || !is_numeric($_POST['price_score']) || $_POST['price_score'] < 0 || $_POST['price_score'] > 10) {
            throw new Exception('价格评分必须在0-10之间');
        }
        if (empty($_POST['packaging_score']) || !is_numeric($_POST['packaging_score']) || $_POST['packaging_score'] < 0 || $_POST['packaging_score'] > 10) {
            throw new Exception('包装评分必须在0-10之间');
        }
        if (empty($_POST['speed_score']) || !is_numeric($_POST['speed_score']) || $_POST['speed_score'] < 0 || $_POST['speed_score'] > 10) {
            throw new Exception('速度评分必须在0-10之间');
        }

        // 准备数据
        $data = [
            'name' => $_POST['name'],
            'campus' => $_POST['campus'],
            'location' => $_POST['location'] ?? '',
            'description' => $_POST['description'] ?? '',
            'taste_score' => floatval($_POST['taste_score']),
            'price_score' => floatval($_POST['price_score']),
            'packaging_score' => floatval($_POST['packaging_score']),
            'speed_score' => floatval($_POST['speed_score']),
            'platforms' => [
                'phone' => $_POST['phone'] ?? '',
                'dine_in' => isset($_POST['dine_in']),
                'jd' => isset($_POST['platform_jd']),
                'meituan' => isset($_POST['platform_meituan']),
                'taobao' => isset($_POST['platform_taobao']),
            ],
            'image_url' => ''
        ];

        // 处理图片上传
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image_url'] = uploadFile($_FILES['image']);
        } elseif (!empty($_POST['image_url'])) {
            $data['image_url'] = $_POST['image_url'];
        }

        // 添加商家
        addRestaurant($data);
        $success = true;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>上传商家 - 双鸭山大学美食分享</title>
    <style>
        .form-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 60px;
        }
        .form-header {
            margin-bottom: 32px;
        }
        .form-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }
        .form-header p {
            color: #666;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .checkbox-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #059669;
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #dc2626;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
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
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span style="font-size: 28px;">🍜</span>
                <h1>双鸭山大学美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php">发现</a>
                <a href="/submit.php" class="active">上传商家</a>
                <a href="/user-logout.php">退出</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>📝 上传商家</h1>
                <p>欢迎，<?php echo h($currentUser['username']); ?>！请填写商家信息</p>
            </div>

            <?php if ($success): ?>
                <div class="success">
                    ✅ 商家上传成功！等待管理员审核后将在网站上显示。
                    <br><br>
                    <a href="/" style="color: #667eea;">返回首页</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error">
                    ❌ <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">商家名称 *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="campus">校区 *</label>
                            <select id="campus" name="campus" required>
                                <?php foreach (getCampusList() as $campus): ?>
                                    <option value="<?php echo h($campus); ?>"><?php echo h($campus); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">联系电话</label>
                            <input type="text" id="phone" name="phone" placeholder="如：13800000000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">位置</label>
                        <input type="text" id="location" name="location" placeholder="如：南校区东区食堂2楼">
                    </div>

                    <div class="form-group">
                        <label>推荐点单平台</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="dine_in" id="dine_in">
                                <span>堂食</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platform_jd" id="platform_jd">
                                <span>京东</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platform_meituan" id="platform_meituan">
                                <span>美团</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platform_taobao" id="platform_taobao">
                                <span>淘宝</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="taste_score">口味评分 * (0-10)</label>
                            <input type="number" id="taste_score" name="taste_score" min="0" max="10" step="0.1" required>
                        </div>
                        <div class="form-group">
                            <label for="price_score">价格评分 * (0-10)</label>
                            <input type="number" id="price_score" name="price_score" min="0" max="10" step="0.1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="packaging_score">包装评分 * (0-10)</label>
                            <input type="number" id="packaging_score" name="packaging_score" min="0" max="10" step="0.1" required>
                        </div>
                        <div class="form-group">
                            <label for="speed_score">速度评分 * (0-10)</label>
                            <input type="number" id="speed_score" name="speed_score" min="0" max="10" step="0.1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">图片上传</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <div class="form-group">
                        <label for="description">介绍</label>
                        <textarea id="description" name="description" placeholder="请简要介绍这家商家..."></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">提交</button>
                        <a href="/" class="btn btn-secondary">取消</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" style="color: #999; text-decoration: none; margin: 0 10px;">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener" style="color: #999; text-decoration: none; margin: 0 10px;">
                <img src="https://beian.mps.gov.cn/img/logo01.dd7ff50e.png" alt="公安备案" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>
</body>
</html>
