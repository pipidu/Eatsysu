<?php
require_once __DIR__ . '/includes/functions.php';

// 获取参数
$campusFilter = $_GET['campus'] ?? '';
$sortBy = $_GET['sort'] ?? 'overall_score';
$orderBy = $_GET['order'] ?? 'DESC';

// 获取商家数据
$restaurants = getAllRestaurants($sortBy, $orderBy);

// 校区过滤
if ($campusFilter) {
    $restaurants = array_filter($restaurants, function($r) use ($campusFilter) {
        return $r['campus'] === $campusFilter;
    });
}

$campuses = getCampusList();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>美食排行榜 - 双鸭山大学美食分享</title>
    <style>
        body {
            background: #fff;
        }
        .hero {
            background: #005826;
        }
        .filters select {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
        }
        .filters select:focus {
            outline: none;
            border-color: #005826;
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
                <a href="/ranking.php" class="active">排行榜</a>
                <a href="/discover.php">发现</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <h1>美食排行榜</h1>
        <p>探索校园周边的最佳餐厅</p>
    </section>

    <div class="container">
        <div class="filters">
            <label>筛选:</label>
            <select onchange="location.href='?campus='+this.value+'&sort=<?php echo h($sortBy); ?>&order=<?php echo h($orderBy); ?>'">
                <option value="">全部校区</option>
                <?php foreach ($campuses as $campus): ?>
                    <option value="<?php echo h($campus); ?>" <?php echo $campusFilter === $campus ? 'selected' : ''; ?>>
                        <?php echo h($campus); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=overall_score&order='+this.value">
                <option value="DESC" <?php echo $orderBy === 'DESC' ? 'selected' : ''; ?>>综合评分从高到低</option>
                <option value="ASC" <?php echo $orderBy === 'ASC' ? 'selected' : ''; ?>>综合评分从低到高</option>
            </select>

            <select onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=taste_score&order=DESC'">
                <option value="" disabled selected>按口味排序</option>
                <option value="taste_score" <?php echo $sortBy === 'taste_score' ? 'selected' : ''; ?>>口味评分</option>
            </select>

            <select onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=price_score&order=DESC'">
                <option value="" disabled selected>按价格排序</option>
                <option value="price_score" <?php echo $sortBy === 'price_score' ? 'selected' : ''; ?>>价格评分</option>
            </select>

            <select onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=packaging_score&order=DESC'">
                <option value="" disabled selected>按包装排序</option>
                <option value="packaging_score" <?php echo $sortBy === 'packaging_score' ? 'selected' : ''; ?>>包装评分</option>
            </select>
        </div>

        <?php if (count($restaurants) > 0): ?>
            <div class="restaurant-grid">
                <?php foreach (array_values($restaurants) as $index => $restaurant): ?>
                    <?php
                        $rankClass = $index < 3 ? 'rank-' . ($index + 1) : 'rank-other';
                        $radarData = generateRadarChartData($restaurant);
                        $platforms = json_decode($restaurant['platforms'], true) ?: [];
                    ?>
                    <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
                        <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $index + 1; ?></span>
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
                            <div class="score-details">
                                <div class="score-item">口味: <strong><?php echo $restaurant['taste_score']; ?></strong></div>
                                <div class="score-item">价格: <strong><?php echo $restaurant['price_score']; ?></strong></div>
                                <div class="score-item">包装: <strong><?php echo $restaurant['packaging_score']; ?></strong></div>
                                <div class="score-item">速度: <strong><?php echo $restaurant['speed_score']; ?></strong></div>
                            </div>
                            <div class="radar-chart-container">
                                <canvas class="radar-chart" data-scores='<?php echo json_encode($radarData['data']); ?>'></canvas>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">+</div>
                <p>没有找到商家</p>
            </div>
        <?php endif; ?>
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
