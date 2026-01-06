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

// 上传图片到对象存储（支持 AWS S3、Cloudflare R2 等 S3 API 兼容服务）
function uploadToS3($file, $folder = 'restaurants') {
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception("AWS SDK未安装，请运行: composer install");
    }

    require_once __DIR__ . '/../vendor/autoload.php';

    // S3 客户端配置
    $s3Config = [
        'version' => 'latest',
        'region' => AWS_REGION,
        'credentials' => [
            'key' => AWS_ACCESS_KEY_ID,
            'secret' => AWS_SECRET_ACCESS_KEY,
        ],
    ];

    // 如果配置了自定义端点（如 Cloudflare R2、MinIO 等）
    if (defined('S3_ENDPOINT') && !empty(S3_ENDPOINT)) {
        $s3Config['endpoint'] = S3_ENDPOINT;
        $s3Config['use_path_style_endpoint'] = defined('S3_USE_PATH_STYLE') ? S3_USE_PATH_STYLE : true;
    }

    $s3 = new Aws\S3\S3Client($s3Config);

    $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $key = $folder . '/' . $fileName;

    try {
        $uploadArgs = [
            'Bucket' => AWS_BUCKET,
            'Key' => $key,
            'SourceFile' => $file['tmp_name'],
            'ContentType' => $file['type'],
        ];

        // 仅 AWS S3 支持 ACL，其他服务不需要
        if (!defined('S3_ENDPOINT') || empty(S3_ENDPOINT)) {
            $uploadArgs['ACL'] = 'public-read';
        }

        $result = $s3->putObject($uploadArgs);

        // 如果配置了自定义域名，使用自定义域名
        if (defined('S3_CUSTOM_DOMAIN') && !empty(S3_CUSTOM_DOMAIN)) {
            return 'https://' . S3_CUSTOM_DOMAIN . '/' . $key;
        }

        // Cloudflare R2 等服务可能不返回 ObjectURL
        $objectUrl = $result->get('ObjectURL');
        if (empty($objectUrl) && defined('S3_ENDPOINT')) {
            // 手动构建 URL
            $endpoint = rtrim(S3_ENDPOINT, '/');
            return $endpoint . '/' . AWS_BUCKET . '/' . $key;
        }

        return $objectUrl;
    } catch (Aws\Exception\AwsException $e) {
        throw new Exception("对象存储上传失败: " . $e->getMessage());
    }
}

/**
 * 调用多吉云 API
 *
 * @param string    $apiPath    调用的 API 接口地址，包含 URL 请求参数 QueryString，例如：/auth/tmp_token.json
 * @param array     $data       POST 的数据，关联数组，例如 array('a' => 1, 'b' => 2)，传递此参数表示不是 GET 请求而是 POST 请求
 * @param boolean   $jsonMode   数据 data 是否以 JSON 格式请求，默认为 false 则使用表单形式（a=1&b=2）
 * @return array 返回的数据
 */
function dogecloudApi($apiPath, $data = array(), $jsonMode = false) {
    $accessKey = DOGE_ACCESS_KEY;
    $secretKey = DOGE_SECRET_KEY;

    $body = $jsonMode ? json_encode($data) : http_build_query($data);
    $signStr = $apiPath . "\n" . $body;
    $sign = hash_hmac('sha1', $signStr, $secretKey);
    $authorization = "TOKEN " . $accessKey . ":" . $sign;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, DOGE_API_URL . $apiPath);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    if(isset($data) && $data){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: ' . ($jsonMode ? 'application/json' : 'application/x-www-form-urlencoded'),
        'Authorization: ' . $authorization
    ));
    
    $ret = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("多吉云 API 请求失败: " . $error);
    }
    
    $result = json_decode($ret, true);
    
    if (!$result) {
        throw new Exception("多吉云 API 响应解析失败");
    }
    
    if ($result['code'] != 200) {
        throw new Exception("多吉云 API 错误: " . ($result['msg'] ?? '未知错误') . " (错误代码: " . ($result['err_code'] ?? '未知') . ")");
    }
    
    return $result;
}

/**
 * 获取多吉云临时密钥（用于客户端上传）
 *
 * @param string    $type       密钥类型：VOD_UPLOAD(视频云上传), OSS_UPLOAD(云存储上传), OSS_FULL(云存储全功能)
 * @param array     $scopes     授权范围，例如 ['mybucket:abc/123.jpg'] 或 ['mybucket:abc/def/*']
 * @param int       $ttl        有效期（秒），默认 7200
 * @return array    包含临时密钥和 S3 配置信息
 */
function getDogecloudTmpToken($type, $scopes = [], $ttl = null) {
    if ($ttl === null) {
        $ttl = defined('DOGE_TMP_TOKEN_TTL') ? DOGE_TMP_TOKEN_TTL : 7200;
    }
    
    $data = [
        'channel' => $type,
        'ttl' => $ttl,
    ];
    
    if (!empty($scopes)) {
        $data['scopes'] = $scopes;
    }
    
    return dogecloudApi('/auth/tmp_token.json', $data, true);
}

/**
 * 上传文件到多吉云云存储（使用临时密钥）
 *
 * @param string    $filePath    本地文件路径
 * @param string    $objectKey   对象键名，例如 'restaurants/image.jpg'
 * @param string    $contentType 内容类型，例如 'image/jpeg'
 * @return string   文件访问 URL
 */
function uploadToDogecloud($filePath, $objectKey, $contentType = 'application/octet-stream') {
    if (!defined('DOGE_ENABLED') || !DOGE_ENABLED) {
        throw new Exception("多吉云未启用，请在配置文件中设置 DOGE_ENABLED = true");
    }
    
    if (!defined('DOGE_BUCKET')) {
        throw new Exception("未配置多吉云存储空间名称");
    }
    
    // 获取临时上传密钥
    $tokenResult = getDogecloudTmpToken('OSS_UPLOAD', [DOGE_BUCKET . ':' . $objectKey]);
    
    $credentials = $tokenResult['data']['Credentials'];
    $bucketInfo = $tokenResult['data']['Buckets'][0];
    
    // 使用 AWS S3 SDK 上传文件（多吉云兼容 S3 API）
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception("AWS SDK未安装，请运行: composer install");
    }

    require_once __DIR__ . '/../vendor/autoload.php';

    // 多吉云底层使用腾讯云 COS，需要使用虚拟主机风格的域名
    $s3Endpoint = $bucketInfo['s3Endpoint'];
    $s3Bucket = $bucketInfo['s3Bucket'];

    // 检测是否是腾讯云 COS
    $isTencentCOS = (strpos($s3Endpoint, 'myqcloud.com') !== false);

    // 检查是否配置了自定义域名
    $customDomain = defined('S3_CUSTOM_DOMAIN') && !empty(S3_CUSTOM_DOMAIN) ? S3_CUSTOM_DOMAIN : null;

    if ($isTencentCOS) {
        // 腾讯云 COS：直接使用 endpoint（SDK 会自动处理 bucket）
        $s3ClientEndpoint = $s3Endpoint;
        $usePathStyle = false;
    } else {
        // 其他服务使用路径风格
        $s3ClientEndpoint = $s3Endpoint;
        $usePathStyle = true;
    }

    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'auto',
        'credentials' => [
            'key' => $credentials['accessKeyId'],
            'secret' => $credentials['secretAccessKey'],
            'token' => $credentials['sessionToken'],
        ],
        'endpoint' => $s3ClientEndpoint,
        'use_path_style_endpoint' => $usePathStyle,
    ]);

    try {
        $result = $s3->putObject([
            'Bucket' => $s3Bucket,
            'Key' => $objectKey,
            'SourceFile' => $filePath,
            'ContentType' => $contentType,
        ]);

        // 构建访问 URL
        if ($customDomain) {
            // 使用自定义 CDN 域名：cdn.domain.com/key
            return 'https://' . rtrim($customDomain, '/') . '/' . $objectKey;
        } elseif ($isTencentCOS) {
            // 腾讯云 COS：bucket.endpoint/key（SDK 已自动处理）
            $endpoint = parse_url($s3Endpoint, PHP_URL_HOST);
            return 'https://' . $s3Bucket . '.' . $endpoint . '/' . $objectKey;
        } else {
            // 其他服务使用路径风格：endpoint/bucket/key
            $endpoint = rtrim($s3Endpoint, '/');
            return $endpoint . '/' . $s3Bucket . '/' . $objectKey;
        }
    } catch (Aws\Exception\AwsException $e) {
        throw new Exception("多吉云上传失败: " . $e->getMessage());
    }
}

/**
 * 统一的文件上传函数（优先使用多吉云，未启用则使用 S3）
 *
 * @param array     $file       $_FILES 数组中的文件项
 * @param string    $folder     上传文件夹，例如 'restaurants'
 * @return string   文件访问 URL
 */
function uploadFile($file, $folder = 'restaurants') {
    // 验证文件
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception("无效的上传文件");
    }
    
    // 验证文件类型
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $file['type'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("不支持的文件类型: " . $fileType);
    }
    
    // 验证文件大小（10MB 限制）
    $maxSize = 10 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception("文件大小超过限制（最大 10MB）");
    }
    
    // 生成文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $objectKey = $folder . '/' . $fileName;
    
    // 优先使用多吉云
    if (defined('DOGE_ENABLED') && DOGE_ENABLED) {
        return uploadToDogecloud($file['tmp_name'], $objectKey, $file['type']);
    }
    
    // 回退到 S3
    return uploadToS3($file, $folder);
}

// 计算综合评分（n维图评分）
function calculateOverallScore($scores) {
    // 使用加权平均计算综合评分
    // 权重：口味 30%, 价格 15%, 服务 15%, 速度 15%, 健康 25%
    $weights = [
        'taste' => 0.30,
        'price' => 0.15,
        'service' => 0.15,
        'speed' => 0.15,
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
    $allowedSort = ['overall_score', 'taste_score', 'price_score', 'service_score', 'speed_score', 'health_score', 'created_at'];
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
        'speed' => $data['speed_score'],
        'health' => $data['health_score']
    ]);
    
    $sql = "INSERT INTO restaurants (name, campus, location, platforms, description, image_url, 
            taste_score, price_score, service_score, speed_score, health_score, overall_score) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
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
        $data['speed_score'],
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
        'speed' => $data['speed_score'],
        'health' => $data['health_score']
    ]);
    
    $sql = "UPDATE restaurants SET name = ?, campus = ?, location = ?, platforms = ?, 
            description = ?, image_url = ?, taste_score = ?, price_score = ?, 
            service_score = ?, speed_score = ?, health_score = ?, overall_score = ? WHERE id = ?";
    
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
        $data['speed_score'],
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
        'labels' => ['口味', '价格', '服务', '速度', '健康'],
        'data' => [
            $restaurant['taste_score'],
            $restaurant['price_score'],
            $restaurant['service_score'],
            $restaurant['speed_score'],
            $restaurant['health_score']
        ]
    ];
}
