<?php
require_once __DIR__ . '/includes/functions.php';

$campusFilter = $_GET['campus'] ?? '';
$sortBy = $_GET['sort'] ?? 'overall_score';
$orderBy = $_GET['order'] ?? 'DESC';

$restaurants = getAllRestaurants($sortBy, $orderBy);

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
    <title>美食排行榜 - 双鸭山美食</title>
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
        <div class="filters" style="margin-bottom: 32px; display: flex; gap: 16px; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="margin-bottom: 4px;">校区</label>
                <select class="form-control" onchange="location.href='?campus='+this.value+'&sort=<?php echo h($sortBy); ?>&order=<?php echo h($orderBy); ?>'">
                    <option value="">全部</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?php echo h($campus); ?>" <?php echo $campusFilter === $campus ? 'selected' : ''; ?>>
                            <?php echo h($campus); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label style="margin-bottom: 4px;">排序方式</label>
                <select class="form-control" onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort='+this.value+'&order=<?php echo h($orderBy); ?>'">
                    <option value="overall_score" <?php echo $sortBy === 'overall_score' ? 'selected' : ''; ?>>综合评分</option>
                    <option value="taste_score" <?php echo $sortBy === 'taste_score' ? 'selected' : ''; ?>>口味</option>
                    <option value="price_score" <?php echo $sortBy === 'price_score' ? 'selected' : ''; ?>>价格</option>
                    <option value="packaging_score" <?php echo $sortBy === 'packaging_score' ? 'selected' : ''; ?>>包装</option>
                    <option value="speed_score" <?php echo $sortBy === 'speed_score' ? 'selected' : ''; ?>>速度</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label style="margin-bottom: 4px;">顺序</label>
                <select class="form-control" onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=<?php echo h($sortBy); ?>&order='+this.value">
                    <option value="DESC" <?php echo $orderBy === 'DESC' ? 'selected' : ''; ?>>从高到低</option>
                    <option value="ASC" <?php echo $orderBy === 'ASC' ? 'selected' : ''; ?>>从低到高</option>
                </select>
            </div>
        </div>

        <?php if (count($restaurants) > 0): ?>
            <div class="restaurant-grid">
                <?php foreach (array_values($restaurants) as $index => $restaurant): ?>
                    <?php
                        $radarData = generateRadarChartData($restaurant);
                        $platforms = json_decode($restaurant['platforms'], true) ?: [];
                    ?>
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
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; font-size: 12px; color: #666;">
                                <div>口味: <strong style="color: #005826;"><?php echo $restaurant['taste_score']; ?></strong></div>
                                <div>价格: <strong style="color: #005826;"><?php echo $restaurant['price_score']; ?></strong></div>
                                <div>包装: <strong style="color: #005826;"><?php echo $restaurant['packaging_score']; ?></strong></div>
                                <div>速度: <strong style="color: #005826;"><?php echo $restaurant['speed_score']; ?></strong></div>
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

    <footer class="footer">
        <?php if (defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER): ?>
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; font-size: 14px;">
                <?php echo h(SITE_ICP_NUMBER); ?>
            </a>
        <?php endif; ?>
        <?php if (defined('SITE_PSB_NUMBER') && SITE_PSB_NUMBER): ?>
            <a href="http://www.beian.gov.cn/portal/registerSystemInfo" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 4px; font-size: 14px;">
                <img src="https://doges3.img.shygo.cn/2025/12/30/d0289dc0a46fc5b15b3363ffa78cf6c7.png/720x1080" alt="" style="width: 20px; height: 20px;">
                <?php echo h(SITE_PSB_NUMBER); ?>
            </a>
        <?php endif; ?>
    </footer>

    <script src="https://doges3bucket2.img.shygo.cn/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
