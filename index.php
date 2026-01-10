<?php
// 检查是否已安装
$installLockFile = __DIR__ . '/install.lock';

if (!file_exists($installLockFile)) {
    // 如果访问的是安装页面，则不跳转
    if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
        header('Location: /install.php');
        exit;
    }
}

require_once __DIR__ . '/includes/functions.php';

// 获取商家数据
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
    <title>双鸭山大学美食分享</title>
    <style>
        body {
            background: #fff;
        }
        .hero {
            background: #005826;
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
                <a href="#" class="active">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php">发现</a>
                <?php if ($currentUser): ?>
                    <a href="/submit.php">上传商家</a>
                    <a href="/user-logout.php">退出</a>
                <?php else: ?>
                    <a href="/login.php" style="color: #005826;">登录</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="hero">
        <h1>双鸭山大学美食地图</h1>
        <p>分享双鸭山大学校园周边的美食，帮你找到最好吃的餐厅</p>
    </section>

    <div class="container">
        <h2 class="section-title">🏆 推荐排行榜</h2>
        <p class="section-subtitle">综合评分最高的餐厅</p>

        <?php if (count($topRestaurants) > 0): ?>
            <div class="restaurant-grid">
                <?php foreach ($topRestaurants as $index => $restaurant): ?>
                    <?php $radarData = generateRadarChartData($restaurant); ?>
                    <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
                        <?php if ($restaurant['image_url']): ?>
                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                        <?php else: ?>
                            <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px; background: #f5f5f5; color: #999;">🍜</div>
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
                <div class="emoji">🍽️</div>
                <p>还没有添加任何商家</p>
            </div>
        <?php endif; ?>

        <div class="discover-btn">
            <a href="/discover.php" class="btn">🎲 随机发现美食</a>
        </div>

        <h2 class="section-title">🏫 按校区探索</h2>
        <p class="section-subtitle">选择你想探索的校区</p>

        <div class="campus-grid">
            <?php foreach (getCampusList() as $campus): ?>
                <a href="/ranking.php?campus=<?php echo urlencode($campus); ?>" class="campus-card">
                    <div class="emoji">🏫</div>
                    <h3><?php echo h($campus); ?></h3>
                    <div class="count"><?php echo $campusStats[$campus] ?? 0; ?> 家商家</div>
                </a>
            <?php endforeach; ?>
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

    <script src="https://doges3bucket2.img.shygo.cn/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
