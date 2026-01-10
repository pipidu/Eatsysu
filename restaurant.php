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

$currentUser = getCurrentUser();
$isOwner = false;
if ($currentUser) {
    $isOwner = ($restaurant['user_id'] === $currentUser['id']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title><?php echo h($restaurant['name']); ?> - 双鸭山大学美食分享</title>
    <style>
        body {
            background: #fff;
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
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        .image-section .restaurant-image {
            height: 350px;
        }
        .info-section {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 28px;
        }
        .contact-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding: 14px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .campus-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #005826;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .restaurant-detail .restaurant-name {
            font-size: 28px;
            font-weight: 500;
            color: #333;
            margin-bottom: 20px;
        }
        .overall-score {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
            padding: 16px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .score-display {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #005826;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 500;
            color: white;
        }
        .radar-container {
            margin: 20px 0;
        }
        .radar-container .radar-chart {
            height: 260px;
        }
        .score-breakdown {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .score-breakdown .score-item {
            background: #f5f5f5;
            padding: 14px;
            border-radius: 4px;
        }
        .description-box {
            background: #f5f5f5;
            padding: 18px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .actions {
            margin-top: 28px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-delete {
            background: #fef2f2;
            color: #c00;
            border: 1px solid #fee2e2;
        }
        .btn-delete:hover {
            background: #fecaca;
            border-color: #fecaca;
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
                    <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; font-size: 80px; background: #f5f5f5; color: #999;">🍜</div>
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
                    <?php if ($isOwner): ?>
                        <a href="/edit-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-secondary">✏️ 编辑</a>
                        <a href="/delete-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-delete" onclick="return confirm('确定要删除这个商家吗？');">🗑️ 删除</a>
                    <?php endif; ?>
                </div>
            </div>
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
