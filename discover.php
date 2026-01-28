<?php
require_once __DIR__ . '/includes/functions.php';

$randomRestaurants = getRandomRestaurants(12);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>发现美食 - 双鸭山美食</title>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <img src="https://doges3.img.shygo.cn/2026/01/06/42ac7f56a69e3b866e19c6ecb6dc62f8.jpg/720x1080" alt="" style="width: 40px; height: 40px; object-fit: contain; margin-right: 8px;">
                <h1>双鸭山美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php" class="active">发现</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <h1>发现美食</h1>
        <p>随机探索校园周边美食</p>
        <button class="btn btn-secondary" onclick="refreshRestaurants()">换一批</button>
    </section>

    <div class="container">
        <div id="loading" class="loading" style="display: none; text-align: center; padding: 40px;">
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
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="icon">+</div>
                    <p>还没有添加任何商家</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; font-size: 14px;">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 4px; font-size: 14px;">
                <img src="https://doges3.img.shygo.cn/2025/12/30/d0289dc0a46fc5b15b3363ffa78cf6c7.png/720x1080" alt="" style="width: 14px; height: 14px;">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>

    <script src="https://doges3bucket2.img.shygo.cn/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script>
        function refreshRestaurants() {
            const loading = document.getElementById('loading');
            const grid = document.getElementById('restaurantsGrid');

            loading.style.display = 'block';
            grid.style.opacity = '0.5';

            fetch('/api/random-restaurants.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络响应异常: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        grid.innerHTML = data.html;
                        loading.style.display = 'none';
                        grid.style.opacity = '1';
                        // 使用 setTimeout 确保 DOM 更新后再初始化图表
                        setTimeout(function() {
                            try {
                                if (typeof initRadarCharts === 'function') {
                                    initRadarCharts();
                                }
                            } catch (e) {
                                console.error('初始化雷达图失败:', e);
                            }
                        }, 100);
                    } else {
                        throw new Error(data.error || '未知错误');
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    grid.style.opacity = '1';
                    console.error('刷新失败:', error);
                });
        }
    </script>
</body>
</html>
