<?php
require_once __DIR__ . '/../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /user/my-restaurants.php');
    exit;
}

$restaurant = getRestaurantById($id);
if (!$restaurant) {
    header('Location: /user/my-restaurants.php');
    exit;
}

if (!isRestaurantOwnedByUser($id, getCurrentUser()['id'])) {
    header('Location: /user/my-restaurants.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

        $imageUrl = $restaurant['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = uploadFile($_FILES['image']);
        }

        $data = [
            'name' => trim($_POST['name']),
            'campus' => $_POST['campus'],
            'location' => trim($_POST['location'] ?? ''),
            'platforms' => [
                'phone' => $_POST['phone'] ?? '',
                'dine_in' => isset($_POST['dine_in']),
                'jd' => isset($_POST['platform_jd']),
                'meituan' => isset($_POST['platform_meituan']),
                'taobao' => isset($_POST['platform_taobao']),
            ],
            'description' => trim($_POST['description'] ?? ''),
            'image_url' => $imageUrl,
            'taste_score' => floatval($_POST['taste_score']),
            'price_score' => floatval($_POST['price_score']),
            'packaging_score' => floatval($_POST['packaging_score']),
            'speed_score' => floatval($_POST['speed_score'])
        ];

        updateRestaurant($id, $data);
        $success = '商家更新成功';
        $restaurant = getRestaurantById($id);

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$currentUser = getCurrentUser();
$campuses = getCampusList();
$platforms = json_decode($restaurant['platforms'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>编辑商家 - 双鸭山美食</title>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span class="logo-icon">■</span>
                <h1>双鸭山美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php">发现</a>
                <a href="/submit.php">上传</a>
                <a href="/user/my-restaurants.php" class="active">我的商家</a>
                <a href="/user/user-logout.php">退出</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <a href="/user/my-restaurants.php" class="back-link">返回</a>

        <div class="form-container">
            <div class="form-header">
                <h1>编辑商家</h1>
                <p>修改商家信息</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo h($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo h($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">商家名称 <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required value="<?php echo h($restaurant['name']); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="campus">校区 <span class="required">*</span></label>
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
                        <input type="text" id="phone" name="phone" value="<?php echo h($platforms['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">位置</label>
                    <input type="text" id="location" name="location" value="<?php echo h($restaurant['location'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>推荐点单平台</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="dine_in" id="dine_in" <?php echo ($platforms['dine_in'] ?? false) ? 'checked' : ''; ?>>
                            <span>堂食</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platform_jd" id="platform_jd" <?php echo ($platforms['jd'] ?? false) ? 'checked' : ''; ?>>
                            <span>京东</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platform_meituan" id="platform_meituan" <?php echo ($platforms['meituan'] ?? false) ? 'checked' : ''; ?>>
                            <span>美团</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platform_taobao" id="platform_taobao" <?php echo ($platforms['taobao'] ?? false) ? 'checked' : ''; ?>>
                            <span>淘宝</span>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="taste_score">口味评分 <span class="required">*</span> (0-10)</label>
                        <input type="number" id="taste_score" name="taste_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['taste_score']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="price_score">价格评分 <span class="required">*</span> (0-10)</label>
                        <input type="number" id="price_score" name="price_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['price_score']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="packaging_score">包装评分 <span class="required">*</span> (0-10)</label>
                        <input type="number" id="packaging_score" name="packaging_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['packaging_score']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="speed_score">速度评分 <span class="required">*</span> (0-10)</label>
                        <input type="number" id="speed_score" name="speed_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['speed_score']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">图片上传</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <?php if ($restaurant['image_url']): ?>
                        <div style="margin-top: 12px; padding: 12px; background: var(--bg-light); border-radius: 4px;">
                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="当前图片" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                            <p style="margin-top: 8px; color: var(--text-secondary); font-size: 12px;">当前图片（上传新图片将替换）</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">介绍</label>
                    <textarea id="description" name="description"><?php echo h($restaurant['description'] ?? ''); ?></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="/user/my-restaurants.php" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener">
                <img src="https://beian.mps.gov.cn/img/logo01.dd7ff50e.png" alt="公安备案" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>
</body>
</html>
