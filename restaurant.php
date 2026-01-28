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
    <title><?php echo h($restaurant['name']); ?> - 双鸭山美食</title>
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
                <a href="/discover.php">发现</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <a href="javascript:history.back()" style="display: inline-block; margin-bottom: 16px; color: #005826; font-size: 14px;">返回</a>

        <div class="restaurant-detail">
            <div class="detail-section">
                <h3>基本信息</h3>
                
                <div class="restaurant-header">
                    <span class="campus"><?php echo h($restaurant['campus']); ?></span>
                    <h1 style="font-size: 28px; font-weight: 600; color: #333; margin-bottom: 12px; margin-top: 8px;"><?php echo h($restaurant['name']); ?></h1>
                    
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="score-badge" style="font-size: 24px; padding: 8px 16px;"><?php echo $restaurant['overall_score']; ?></span>
                            <span style="font-size: 14px; color: #666;">综合评分</span>
                        </div>
                    </div>
                </div>

                <?php if ($restaurant['image_url']): ?>
                    <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" style="width: 100%; height: 300px; object-fit: cover; border-radius: 3px; margin-bottom: 24px;">
                <?php endif; ?>

                <?php if ($restaurant['location']): ?>
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 4px;">位置</div>
                        <div style="font-size: 15px; color: #333;"><?php echo h($restaurant['location']); ?></div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 16px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 8px;">推荐点单方式</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php if ($platforms['dine_in'] ?? false): ?>
                            <span style="background: #e8f5e9; color: #005826; padding: 6px 12px; font-size: 13px; border-radius: 3px;">堂食</span>
                        <?php endif; ?>
                        <?php if ($platforms['jd'] ?? false): ?>
                            <span style="background: #e8f5e9; color: #005826; padding: 6px 12px; font-size: 13px; border-radius: 3px;">京东</span>
                        <?php endif; ?>
                        <?php if ($platforms['meituan'] ?? false): ?>
                            <span style="background: #e8f5e9; color: #005826; padding: 6px 12px; font-size: 13px; border-radius: 3px;">美团</span>
                        <?php endif; ?>
                        <?php if ($platforms['taobao'] ?? false): ?>
                            <span style="background: #e8f5e9; color: #005826; padding: 6px 12px; font-size: 13px; border-radius: 3px;">淘宝</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($platforms['phone'])): ?>
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 4px;">联系电话</div>
                        <div style="font-size: 15px; color: #333;"><?php echo h($platforms['phone']); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($restaurant['description']): ?>
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 8px;">介绍</div>
                        <div style="font-size: 15px; color: #333; line-height: 1.6;"><?php echo h($restaurant['description']); ?></div>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <a href="/discover.php" class="btn">发现更多美食</a>
                    <a href="/ranking.php?campus=<?php echo urlencode($restaurant['campus']); ?>" class="btn btn-secondary">查看该校区排行</a>
                    <?php if ($isOwner): ?>
                        <a href="/user/edit-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-secondary">编辑</a>
                        <a href="/user/delete-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-danger" onclick="return confirm('确定要删除这个商家吗？');">删除</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-section">
                    <h3>评分详情</h3>
                    
                    <div class="radar-container" style="margin-bottom: 24px;">
                        <canvas class="radar-chart radar-detail" data-scores='<?php echo json_encode($radarData['data']); ?>'></canvas>
                    </div>

                    <div class="score-item">
                        <span class="score-name">口味</span>
                        <span class="score-value"><?php echo $restaurant['taste_score']; ?></span>
                    </div>
                    <div class="score-item">
                        <span class="score-name">价格</span>
                        <span class="score-value"><?php echo $restaurant['price_score']; ?></span>
                    </div>
                    <div class="score-item">
                        <span class="score-name">包装</span>
                        <span class="score-value"><?php echo $restaurant['packaging_score']; ?></span>
                    </div>
                    <div class="score-item">
                        <span class="score-name">速度</span>
                        <span class="score-value"><?php echo $restaurant['speed_score']; ?></span>
                    </div>
                </div>
            </div>
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
