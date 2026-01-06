<?php
session_start();

// 安装检查 - 如果未安装，跳转到安装页面
$installLockFile = __DIR__ . '/../install.lock';
if (!file_exists($installLockFile)) {
    $currentPath = $_SERVER['PHP_SELF'] ?? '';
    if (basename($currentPath) !== 'install.php') {
        header('Location: /install.php');
        exit;
    }
}

require_once __DIR__ . '/../config.php';

// 数据库连接
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("数据库连接失败: " . $e->getMessage());
    }
}

// 检查管理员登录状态
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// 管理员登录
function adminLogin($username, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    return false;
}

// 管理员登出
function adminLogout() {
    session_unset();
    session_destroy();
}

// 上传图片到S3
function uploadToS3($file, $folder = 'restaurants') {
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception("AWS SDK未安装，请运行: composer install");
    }
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => AWS_REGION,
        'credentials' => [
            'key' => AWS_ACCESS_KEY_ID,
            'secret' => AWS_SECRET_ACCESS_KEY,
        ],
    ]);
    
    $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $key = $folder . '/' . $fileName;
    
    try {
        $result = $s3->putObject([
            'Bucket' => AWS_BUCKET,
            'Key' => $key,
            'SourceFile' => $file['tmp_name'],
            'ContentType' => $file['type'],
            'ACL' => 'public-read'
        ]);
        
        return $result->get('ObjectURL');
    } catch (Aws\Exception\AwsException $e) {
        throw new Exception("S3上传失败: " . $e->getMessage());
    }
}

// 计算综合评分（n维图评分）
function calculateOverallScore($scores) {
    // 使用加权平均计算综合评分
    // 权重：口味 35%, 价格 20%, 服务 20%, 健康 25%
    $weights = [
        'taste' => 0.35,
        'price' => 0.20,
        'service' => 0.20,
        'health' => 0.25
    ];
    
    $overall = 0;
    foreach ($scores as $key => $value) {
        if (isset($weights[$key])) {
            $overall += $value * $weights[$key];
        }
    }
    
    return round($overall, 1);
}

// 获取所有商家（按综合评分排序）
function getAllRestaurants($sort = 'overall_score', $order = 'DESC', $limit = null) {
    $pdo = getDB();
    $allowedSort = ['overall_score', 'taste_score', 'price_score', 'service_score', 'health_score', 'created_at'];
    $sort = in_array($sort, $allowedSort) ? $sort : 'overall_score';
    $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
    
    $sql = "SELECT * FROM restaurants ORDER BY {$sort} {$order}";
    if ($limit) {
        $sql .= " LIMIT {$limit}";
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// 获取随机商家
function getRandomRestaurants($count = 6) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM restaurants ORDER BY RAND() LIMIT ?");
    $stmt->execute([$count]);
    return $stmt->fetchAll();
}

// 根据ID获取商家
function getRestaurantById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 添加商家
function addRestaurant($data) {
    $pdo = getDB();
    
    // 计算综合评分
    $overall = calculateOverallScore([
        'taste' => $data['taste_score'],
        'price' => $data['price_score'],
        'service' => $data['service_score'],
        'health' => $data['health_score']
    ]);
    
    $sql = "INSERT INTO restaurants (name, campus, location, platforms, description, image_url, 
            taste_score, price_score, service_score, health_score, overall_score) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['name'],
        $data['campus'],
        $data['location'] ?? null,
        json_encode($data['platforms']),
        $data['description'],
        $data['image_url'],
        $data['taste_score'],
        $data['price_score'],
        $data['service_score'],
        $data['health_score'],
        $overall
    ]);
    
    return $pdo->lastInsertId();
}

// 更新商家
function updateRestaurant($id, $data) {
    $pdo = getDB();
    
    // 计算综合评分
    $overall = calculateOverallScore([
        'taste' => $data['taste_score'],
        'price' => $data['price_score'],
        'service' => $data['service_score'],
        'health' => $data['health_score']
    ]);
    
    $sql = "UPDATE restaurants SET name = ?, campus = ?, location = ?, platforms = ?, 
            description = ?, image_url = ?, taste_score = ?, price_score = ?, 
            service_score = ?, health_score = ?, overall_score = ? WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['name'],
        $data['campus'],
        $data['location'] ?? null,
        json_encode($data['platforms']),
        $data['description'],
        $data['image_url'],
        $data['taste_score'],
        $data['price_score'],
        $data['service_score'],
        $data['health_score'],
        $overall,
        $id
    ]);
}

// 删除商家
function deleteRestaurant($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
    return $stmt->execute([$id]);
}

// 获取校区列表
function getCampusList() {
    return ['南校区', '北校区', '东校区', '珠海校区', '深圳校区'];
}

// 获取所有校区及其商家数量
function getCampusStats() {
    $pdo = getDB();
    $campuses = getCampusList();
    $stats = [];
    
    foreach ($campuses as $campus) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM restaurants WHERE campus = ?");
        $stmt->execute([$campus]);
        $result = $stmt->fetch();
        $stats[$campus] = $result['count'];
    }
    
    return $stats;
}

// 记录浏览
function recordView($restaurantId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO views (restaurant_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([
        $restaurantId,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

// HTML转义
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 生成n维图数据（用于前端展示）
function generateRadarChartData($restaurant) {
    return [
        'labels' => ['口味', '价格', '服务', '健康'],
        'data' => [
            $restaurant['taste_score'],
            $restaurant['price_score'],
            $restaurant['service_score'],
            $restaurant['health_score']
        ]
    ];
}
