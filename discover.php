<?php
require_once __DIR__ . '/includes/functions.php';

// 获取随机商家
$randomRestaurants = getRandomRestaurants(12);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>发现美食 - 双鸭山大学美食分享</title>
    <style>
        body {
            background: #fff;
        }
        .hero.discover {
            background: #005826;
            padding: 40px 20px;
        }
        .hero.discover h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .hero.discover p {
            font-size: 14px;
            margin-bottom: 20px;
        }
        .hero.discover .btn {
            background: #fff;
            color: #005826;
            border: none;
        }
        .hero.discover .btn:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span style="font-size: 24px;">🍜</span>
                <h1>双鸭山美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php" class="active">发现</a>
            </nav>
        </div>
    </header>

    <section class="hero discover">
        <h1>发现美食</h1>
        <p>随机探索校园周边美食</p>
        <button class="btn" onclick="refreshRestaurants()">
            换一批
        </button>
    </section>

    <div class="container">
        <div id="loading" class="loading">
            <p>正在加载...</p>
        </div>

        <div id="restaurantsGrid" class="restaurant-grid">
            <?php if (count($randomRestaurants) > 0): ?>
                <?php foreach ($randomRestaurants as $restaurant): ?>
                    <?php $radarData = generateRadarChartData($restaurant); ?>
                    <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
                        <?php if ($restaurant['image_url']): ?>
                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                        <?php else: ?>
                            <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px; background: #f5f5f5; color: #ddd;">+</div>
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
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="icon">+</div>
                    <p>还没有添加任何商家</p>
                </div>
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

    <script src="https://doges3bucket2.img.shygo.cn/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script>
        function refreshRestaurants() {
            const grid = document.getElementById('restaurantsGrid');
            const loading = document.getElementById('loading');

            // 显示加载状态
            grid.style.display = 'none';
            loading.style.display = 'block';

            // 重新加载页面
            setTimeout(() => {
                location.reload();
            }, 500);
        }
    </script>
</body>
</html>
