<?php
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /');
    exit;
}

$restaurant = getRestaurantById($id);
if (!$restaurant) {
    header('Location: /');
    exit;
}

// 记录浏览
recordView($id);

$platforms = json_decode($restaurant['platforms'], true) ?: [];
$radarData = generateRadarChartData($restaurant);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title><?php echo h($restaurant['name']); ?> - 中山大学美食分享</title>
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
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #5568d3;
        }
        .restaurant-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
        @media (max-width: 768px) {
            .restaurant-detail {
                grid-template-columns: 1fr;
            }
        }
        .image-section {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .restaurant-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background: #e5e7eb;
        }
        .info-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .contact-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .contact-info-icon {
            font-size: 24px;
        }
        .contact-info-text {
            flex: 1;
        }
        .contact-info-label {
            font-weight: 500;
            color: #666;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .contact-info-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .campus-badge {
            display: inline-block;
            background: #dbeafe;
            color: #2563eb;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .restaurant-name {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 24px;
        }
        .overall-score {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 12px;
        }
        .score-display {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: white;
        }
        .score-label {
            flex: 1;
        }
        .score-label h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 4px;
        }
        .score-label p {
            color: #666;
            font-size: 14px;
        }
        .info-group {
            margin-bottom: 24px;
        }
        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .info-value {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .location-info {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .platform-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .platform-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }
        .platform-tag:hover {
            background: #e5e7eb;
        }
        .radar-container {
            margin: 24px 0;
        }
        .radar-chart {
            width: 100%;
            height: 300px;
        }
        .score-breakdown {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .score-item {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
        }
        .score-item-name {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .score-item-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            margin-top: 8px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            transition: width 0.5s;
        }
        .description-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            margin-top: 24px;
        }
        .description-box p {
            color: #666;
            line-height: 1.8;
            font-size: 15px;
        }
        .actions {
            margin-top: 32px;
            display: flex;
            gap: 12px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #e1e1e1;
        }
        .btn-secondary:hover {
            background: #f9f9f9;
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
                <a href="/discover.php">发现</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <a href="javascript:history.back()" class="back-link">← 返回</a>
        
        <div class="restaurant-detail">
            <div class="image-section">
                <?php if ($restaurant['image_url']): ?>
                    <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                <?php else: ?>
                    <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; font-size: 80px; background: #e5e7eb; color: #999;">🍜</div>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <span class="campus-badge"><?php echo h($restaurant['campus']); ?></span>
                <h1 class="restaurant-name"><?php echo h($restaurant['name']); ?></h1>
                
                <div class="overall-score">
                    <div class="score-display"><?php echo $restaurant['overall_score']; ?></div>
                    <div class="score-label">
                        <h3>综合评分</h3>
                        <p>基于口味、价格、包装、速度的综合评价</p>
                    </div>
                </div>
                
                <?php if ($restaurant['location']): ?>
                    <div class="info-group">
                        <div class="info-label">📍 位置</div>
                        <div class="info-value">
                            <div class="location-info">
                                <?php echo h($restaurant['location']); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <div class="info-label">📱 推荐点单方式</div>
                    <div class="platform-tags">
                        <?php if ($platforms['dine_in'] ?? false): ?>
                            <span class="platform-tag"><?php echo defined('PLATFORM_ICONS') ? PLATFORM_ICONS['dine_in'] : '🏢'; ?> 堂食</span>
                        <?php endif; ?>
                        <?php if ($platforms['jd'] ?? false): ?>
                            <span class="platform-tag">
                                <?php if (defined('PLATFORM_ICONS') && !empty(PLATFORM_ICONS['jd'])): ?>
                                    <img src="<?php echo PLATFORM_ICONS['jd']; ?>" alt="京东" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
                                <?php else: ?>
                                    <?php echo defined('PLATFORM_ICONS') ? PLATFORM_ICONS['jd'] : '📦'; ?>
                                <?php endif; ?>
                                京东
                            </span>
                        <?php endif; ?>
                        <?php if ($platforms['meituan'] ?? false): ?>
                            <span class="platform-tag">
                                <?php if (defined('PLATFORM_ICONS') && !empty(PLATFORM_ICONS['meituan'])): ?>
                                    <img src="<?php echo PLATFORM_ICONS['meituan']; ?>" alt="美团" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
                                <?php else: ?>
                                    <?php echo defined('PLATFORM_ICONS') ? PLATFORM_ICONS['meituan'] : '🦐'; ?>
                                <?php endif; ?>
                                美团
                            </span>
                        <?php endif; ?>
                        <?php if ($platforms['taobao'] ?? false): ?>
                            <span class="platform-tag">
                                <?php if (defined('PLATFORM_ICONS') && !empty(PLATFORM_ICONS['taobao'])): ?>
                                    <img src="<?php echo PLATFORM_ICONS['taobao']; ?>" alt="淘宝" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
                                <?php else: ?>
                                    <?php echo defined('PLATFORM_ICONS') ? PLATFORM_ICONS['taobao'] : '🛒'; ?>
                                <?php endif; ?>
                                淘宝
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($platforms['phone'])): ?>
                    <div class="contact-info">
                        <div class="contact-info-icon">📞</div>
                        <div class="contact-info-text">
                            <div class="contact-info-label">联系电话</div>
                            <div class="contact-info-value"><?php echo h($platforms['phone']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <div class="info-label">📊 多维评分</div>
                    <div class="radar-container">
                        <canvas class="radar-chart" data-scores='<?php echo json_encode($radarData['data']); ?>'></canvas>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">📈 评分详情</div>
                    <div class="score-breakdown">
                        <div class="score-item">
                            <div class="score-item-name">😋 口味</div>
                            <div class="score-item-value"><?php echo $restaurant['taste_score']; ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $restaurant['taste_score'] * 10; ?>%"></div>
                            </div>
                        </div>
                        <div class="score-item">
                            <div class="score-item-name">💰 价格</div>
                            <div class="score-item-value"><?php echo $restaurant['price_score']; ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $restaurant['price_score'] * 10; ?>%"></div>
                            </div>
                        </div>
                        <div class="score-item">
                            <div class="score-item-name">📦 包装</div>
                            <div class="score-item-value"><?php echo $restaurant['packaging_score']; ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $restaurant['packaging_score'] * 10; ?>%"></div>
                            </div>
                        </div>
                        <div class="score-item">
                            <div class="score-item-name">🚀 速度</div>
                            <div class="score-item-value"><?php echo $restaurant['speed_score']; ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $restaurant['speed_score'] * 10; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($restaurant['description']): ?>
                    <div class="description-box">
                        <div class="info-label">📝 介绍</div>
                        <p><?php echo nl2br(h($restaurant['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="actions">
                    <a href="/discover.php" class="btn btn-primary">🎲 发现更多美食</a>
                    <a href="/ranking.php?campus=<?php echo urlencode($restaurant['campus']); ?>" class="btn btn-secondary">🏆 查看该校区排行</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
