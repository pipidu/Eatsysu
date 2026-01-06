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
    <title>发现美食 - 中山大学美食分享</title>
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
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .btn-icon {
            margin-right: 8px;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .section-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 32px;
            text-align: center;
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
        }
        .restaurant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }
        .restaurant-image {
            width: 100%;
            height: 200px;
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
        .radar-chart {
            width: 100%;
            height: 120px;
            margin: 12px 0;
        }
        .restaurant-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
            display: none;
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
                <a href="/ranking.php">排行榜</a>
                <a href="/discover.php" class="active">发现</a>
            </nav>
        </div>
    </header>
    
    <section class="hero">
        <h1>🎲 发现美食</h1>
        <p>随机探索中山大学周边的美食</p>
        <button class="btn" onclick="refreshRestaurants()">
            <span class="btn-icon">🔄</span>换一批
        </button>
    </section>
    
    <div class="container">
        <div id="loading" class="loading">
            <div style="font-size: 32px; margin-bottom: 16px;">⏳</div>
            <p>正在为你发现美食...</p>
        </div>
        
        <div id="restaurantsGrid" class="restaurant-grid">
            <?php if (count($randomRestaurants) > 0): ?>
                <?php foreach ($randomRestaurants as $restaurant): ?>
                    <?php $radarData = generateRadarChartData($restaurant); ?>
                    <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="restaurant-card">
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
                            <canvas class="radar-chart" data-scores='<?php echo json_encode($radarData['data']); ?>'></canvas>
                            <p class="restaurant-description"><?php echo h($restaurant['description'] ?? '暂无介绍'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="emoji">🍽️</div>
                    <p>还没有添加任何商家</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let charts = [];
        
        function initCharts() {
            // 销毁旧图表
            charts.forEach(chart => chart.destroy());
            charts = [];
            
            // 初始化新图表
            document.querySelectorAll('.radar-chart').forEach(canvas => {
                const scores = JSON.parse(canvas.dataset.scores);
                const chart = new Chart(canvas, {
                    type: 'radar',
                    data: {
                        labels: ['口味', '价格', '服务', '健康'],
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
                charts.push(chart);
            });
        }
        
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
        
        // 页面加载完成后初始化图表
        document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>
