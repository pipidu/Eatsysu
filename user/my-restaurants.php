<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: /user/login.php');
    exit;
}

$success = '';
$error = '';

$currentUser = getCurrentUser();
$myRestaurants = getRestaurantsByUser($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>我的商家 - 双鸭山美食</title>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
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
        <h2 class="section-title">我的商家</h2>
        <p class="section-subtitle">共创建了 <?php echo count($myRestaurants); ?> 个商家</p>

        <div style="margin-bottom: 24px; display: flex; gap: 12px;">
            <a href="/submit.php" class="btn">添加商家</a>
            <a href="/" class="btn btn-secondary">返回首页</a>
        </div>

        <div class="table-container">
                <?php if (count($myRestaurants) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>商家</th>
                                <th>校区</th>
                                <th>评分</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myRestaurants as $restaurant): ?>
                                <?php
                                    $scoreClass = $restaurant['overall_score'] >= 8 ? 'score-high' :
                                                ($restaurant['overall_score'] >= 6 ? 'score-medium' : 'score-low');
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <?php if ($restaurant['image_url']): ?>
                                                <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 3px;">
                                            <?php else: ?>
                                                <div style="width: 48px; height: 48px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #ccc; border-radius: 3px;">+</div>
                                            <?php endif; ?>
                                            <span style="font-size: 14px; color: #333;"><?php echo h($restaurant['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo h($restaurant['campus']); ?></td>
                                    <td>
                                        <span style="background: #005826; color: #fff; padding: 4px 10px; border-radius: 3px; font-size: 14px; font-weight: 600;">
                                            <?php echo $restaurant['overall_score']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($restaurant['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" style="color: #005826; font-size: 13px;" target="_blank">查看</a>
                                            <a href="/user/edit-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" style="color: #005826; font-size: 13px;">编辑</a>
                                            <a href="/user/delete-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" style="color: #dc3545; font-size: 13px;" onclick="return confirm('确定要删除这个商家吗？');">删除</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">+</div>
                        <p>还没有添加任何商家</p>
                    </div>
            </div>
        </div>
    </div>
                <?php endif; ?>
            </div>
    <footer class="footer">
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>
</body>
</html>
