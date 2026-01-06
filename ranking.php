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
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>美食排行榜 - 中山大学美食分享</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
        }
        .logo h1 {
            font-size: 20px;
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 24px;
        }
        .nav-links a {
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #667eea;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .hero h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .hero p {
            font-size: 16px;
            opacity: 0.9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .filters {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }
        .filters select, .filters label {
            padding: 10px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            background: white;
        }
        .filters label {
            cursor: default;
            border: none;
            padding: 0;
            font-weight: 500;
            color: #333;
        }
        .restaurant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        .restaurant-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            position: relative;
            height: auto;
            width: 100%;
        }
        .restaurant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }
        .rank-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            z-index: 10;
        }
        .rank-1 { background: #ffd700; color: #333; }
        .rank-2 { background: #c0c0c0; color: #333; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-other { background: rgba(102, 126, 234, 0.9); color: white; }
        .restaurant-image {
            width: 100%;
            height: 200px;
            min-height: 200px;
            object-fit: cover;
            background: #e5e7eb;
        }
        .restaurant-content {
            padding: 20px;
        }
        .restaurant-campus {
            font-size: 12px;
            color: #667eea;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .restaurant-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .restaurant-score {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .score-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .score-label {
            color: #999;
            font-size: 12px;
        }
        .score-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;
        }
        .score-details.three-cols {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }
        .score-item {
            font-size: 12px;
            color: #666;
        }
        .score-item strong {
            color: #333;
        }
        .radar-chart-container {
            width: 100%;
            height: 120px;
            margin: 12px 0;
        }
        .radar-chart {
            width: 100%;
            height: 100%;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }
        .empty-state .emoji {
            font-size: 64px;
            margin-bottom: 16px;
        }
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 40px 20px;
            margin-top: 40px;
        }
        footer p {
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span style="font-size: 28px;">🍜</span>
                <h1>中山大学美食</h1>
            </a>
            <nav class="nav-links">
                <a href="/">首页</a>
                <a href="/ranking.php" class="active">排行榜</a>
                <a href="/discover.php">发现</a>
            </nav>
        </div>
    </header>
    
    <section class="hero">
        <h1>🏆 美食排行榜</h1>
        <p>探索中大校园周边的最佳餐厅</p>
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
                            <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px; background: #e5e7eb; color: #999;">🍜</div>
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
                <div class="emoji">🍽️</div>
                <p>没有找到商家</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // 初始化雷达图
        document.querySelectorAll('.radar-chart').forEach(canvas => {
            const scores = JSON.parse(canvas.dataset.scores);
            new Chart(canvas, {
                type: 'radar',
                data: {
                    labels: ['口味', '价格', '包装', '速度'],
                    datasets: [{
                        data: scores,
                        backgroundColor: 'rgba(102, 126, 234, 0.2)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(102, 126, 234, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 10,
                            ticks: {
                                display: false
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            pointLabels: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
    <footer>
        <p>© 2024 中山大学美食分享 | 用心分享每一道美食<?php echo defined('SITE_ICP_NUMBER') && SITE_ICP_NUMBER ? ' | ' . SITE_ICP_NUMBER : ''; ?></p>
    </footer>
</body>
</html>
