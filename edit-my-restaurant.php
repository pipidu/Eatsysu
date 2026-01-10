<?php
require_once __DIR__ . '/includes/functions.php';

// 检查用户登录状态
if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /my-restaurants.php');
    exit;
}

$currentUser = getCurrentUser();

// 验证用户是否拥有该商家
if (!isRestaurantOwnedByUser($id, $currentUser['id'])) {
    header('Location: /my-restaurants.php');
    exit;
}

$restaurant = getRestaurantById($id);
if (!$restaurant) {
    header('Location: /my-restaurants.php');
    exit;
}

// 解析平台数据
$platforms = json_decode($restaurant['platforms'], true) ?: [];
$campuses = getCampusList();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 处理图片上传
        $imageUrl = $restaurant['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = uploadFile($_FILES['image'], 'restaurants');
        }
        
        // 准备平台数据
        $newPlatforms = [
            'phone' => $_POST['phone'] ?? '',
            'dine_in' => isset($_POST['platforms']) && in_array('dine_in', $_POST['platforms']),
            'jd' => isset($_POST['platforms']) && in_array('jd', $_POST['platforms']),
            'meituan' => isset($_POST['platforms']) && in_array('meituan', $_POST['platforms']),
            'taobao' => isset($_POST['platforms']) && in_array('taobao', $_POST['platforms'])
        ];
        
        // 准备商家数据
        $data = [
            'name' => trim($_POST['name']),
            'campus' => $_POST['campus'],
            'location' => trim($_POST['location'] ?? ''),
            'platforms' => $newPlatforms,
            'description' => trim($_POST['description'] ?? ''),
            'image_url' => $imageUrl,
            'taste_score' => floatval($_POST['taste_score']),
            'price_score' => floatval($_POST['price_score']),
            'packaging_score' => floatval($_POST['packaging_score']),
            'speed_score' => floatval($_POST['speed_score'])
        ];
        
        // 更新商家
        updateRestaurant($id, $data);
        
        $success = '商家更新成功！';
        $restaurant = getRestaurantById($id); // 重新获取数据
        $platforms = $newPlatforms; // 更新平台数据用于表单显示
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>编辑商家 - 双鸭山大学美食分享</title>
    <style>
        .form-container {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 28px;
        }
        .form-header h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 6px;
        }
        .form-header p {
            color: #999;
            font-size: 13px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .checkbox-item input[type="checkbox"] {
            cursor: pointer;
        }
        .current-image {
            margin-top: 12px;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 4px;
            object-fit: cover;
        }
        .current-image p {
            color: #666;
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span style="font-size: 24px;">🍜</span>
                <h1>双鸭山大学美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php">发现</a>
                <a href="/submit.php">上传商家</a>
                <a href="/my-restaurants.php" class="active">我的商家</a>
                <a href="/user-logout.php">退出</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>✏️ 编辑商家</h1>
                <p>欢迎，<?php echo h($currentUser['username']); ?>！请修改商家信息</p>
            </div>

            <?php if ($error): ?>
                <div class="error">
                    ❌ <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success">
                    ✅ <?php echo h($success); ?>
                    <br><br>
                    <a href="/my-restaurants.php" style="color: #005826;">返回我的商家</a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">商家名称 *</label>
                        <input type="text" id="name" name="name" required value="<?php echo h($restaurant['name']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="campus">校区 *</label>
                            <select id="campus" name="campus" required>
                                <?php foreach ($campuses as $campus): ?>
                                    <option value="<?php echo h($campus); ?>" <?php echo $restaurant['campus'] === $campus ? 'selected' : ''; ?>>
                                        <?php echo h($campus); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">联系电话</label>
                            <input type="text" id="phone" name="phone" placeholder="如：13800000000" value="<?php echo h($platforms['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">位置</label>
                        <input type="text" id="location" name="location" placeholder="如：南校区东区食堂2楼" value="<?php echo h($restaurant['location'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>推荐点单平台</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="platforms[]" value="dine_in" <?php echo ($platforms['dine_in'] ?? false) ? 'checked' : ''; ?>>
                                堂食
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platforms[]" value="jd" <?php echo ($platforms['jd'] ?? false) ? 'checked' : ''; ?>>
                                京东
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platforms[]" value="meituan" <?php echo ($platforms['meituan'] ?? false) ? 'checked' : ''; ?>>
                                美团
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="platforms[]" value="taobao" <?php echo ($platforms['taobao'] ?? false) ? 'checked' : ''; ?>>
                                淘宝
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="taste_score">口味评分 * (0-10)</label>
                            <input type="number" id="taste_score" name="taste_score" min="0" max="10" step="0.1" required value="<?php echo $restaurant['taste_score']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="price_score">价格评分 * (0-10)</label>
                            <input type="number" id="price_score" name="price_score" min="0" max="10" step="0.1" required value="<?php echo $restaurant['price_score']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="packaging_score">包装评分 * (0-10)</label>
                            <input type="number" id="packaging_score" name="packaging_score" min="0" max="10" step="0.1" required value="<?php echo $restaurant['packaging_score']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="speed_score">速度评分 * (0-10)</label>
                            <input type="number" id="speed_score" name="speed_score" min="0" max="10" step="0.1" required value="<?php echo $restaurant['speed_score']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">图片上传</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if ($restaurant['image_url']): ?>
                            <div class="current-image">
                                <img src="<?php echo h($restaurant['image_url']); ?>" alt="商家图片">
                                <p>当前图片（上传新图片将替换）</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">介绍</label>
                        <textarea id="description" name="description" placeholder="请简要介绍这家商家..."><?php echo h($restaurant['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">保存修改</button>
                        <a href="/my-restaurants.php" class="btn btn-secondary">取消</a>
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
