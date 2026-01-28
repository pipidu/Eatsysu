<?php
// 检查是否已安装
$installLockFile = __DIR__ . '/install.lock';

if (!file_exists($installLockFile)) {
    if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
        header('Location: /install.php');
        exit;
    }
}

require_once __DIR__ . '/includes/functions.php';

$topRestaurants = getAllRestaurants('overall_score', 'DESC', 10);
$randomRestaurants = getRandomRestaurants(8);
$campusStats = getCampusStats();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>双鸭山美食</title>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <img src="https://doges3.img.shygo.cn/2026/01/06/42ac7f56a69e3b866e19c6ecb6dc62f8.jpg/720x1080" alt="" style="width: 40px; height: 40px; object-fit: contain; margin-right: 8px;">
                <h1>双鸭山美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/" class="active">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php">发现</a>
                <?php if ($currentUser): ?>
                    <a href="/submit.php">上传</a>
                    <a href="/user/my-restaurants.php">我的商家</a>
                    <a href="/user/user-logout.php">退出</a>
                <?php else: ?>
                    <a href="/user/login.php">登录</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="hero">
        <h1>双鸭山美食地图</h1>
        <p>分享校园周边美食，发现好吃餐厅</p>
    </section>

    <div class="container">
        <h2 class="section-title">推荐商家</h2>
        <p class="section-subtitle">综合评分最高的餐厅</p>

        <?php if (count($topRestaurants) > 0): ?>
            <div class="restaurant-grid">
                <?php foreach ($topRestaurants as $index => $restaurant): ?>
                    <?php $radarData = generateRadarChartData($restaurant); ?>
                    <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
                        <?php if ($restaurant['image_url']): ?>
                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                        <?php else: ?>
                            <div class="restaurant-image-placeholder">+</div>
                        <?php endif; ?>
                        <div class="restaurant-content">
                            <div class="restaurant-campus"><?php echo h($restaurant['campus']); ?></div>
                            <h3 class="restaurant-name"><?php echo h($restaurant['name']); ?></h3>
                            <div class="restaurant-score">
                                <span class="score-badge"><?php echo $restaurant['overall_score']; ?></span>
                                <span class="score-label">综合评分</span>
                            </div>
                            <div class="radar-chart-container">
                                <canvas class="radar-chart" data-scores='<?php echo json_encode($radarData['data']); ?>'></canvas>
                            </div>
                            <p class="restaurant-description"><?php echo h($restaurant['description'] ?? '暂无介绍'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">+</div>
                <p>还没有添加任何商家</p>
            </div>
        <?php endif; ?>

        <div class="discover-btn">
            <a href="/discover.php" class="btn">发现美食</a>
        </div>

        <h2 class="section-title">按校区探索</h2>
        <p class="section-subtitle">选择想探索的校区</p>

        <div class="campus-grid">
            <?php foreach (getCampusList() as $campus): ?>
                <a href="/ranking.php?campus=<?php echo urlencode($campus); ?>" class="campus-card">
                    <h3><?php echo h($campus); ?></h3>
                    <div class="count"><?php echo $campusStats[$campus] ?? 0; ?> 家商家</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer">
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 4px;">
                <img src="https://doges3.img.shygo.cn/2025/12/30/d0289dc0a46fc5b15b3363ffa78cf6c7.png/720x1080" alt="" style="width: 20px; height: 20px;">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>

    <script src="https://doges3bucket2.img.shygo.cn/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
