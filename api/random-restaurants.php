<?php
// 检查安装锁
$installLockFile = __DIR__ . '/../install.lock';
if (!file_exists($installLockFile)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => '系统未安装'
    ]);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
// 禁用缓存
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

try {
    // 获取12个随机商家
    $randomRestaurants = getRandomRestaurants(12);

    // 生成HTML
    $html = '';

    if (count($randomRestaurants) > 0) {
        foreach ($randomRestaurants as $restaurant) {
            $html .= '<a href="/restaurant.php?id=' . h($restaurant['id']) . '" class="restaurant-card">';

            if ($restaurant['image_url']) {
                $html .= '<img src="' . h($restaurant['image_url']) . '" alt="' . h($restaurant['name']) . '" class="restaurant-image">';
            } else {
                $html .= '<div class="restaurant-image-placeholder">+</div>';
            }

            $html .= '<div class="restaurant-content">';
            $html .= '<div class="restaurant-campus">' . h($restaurant['campus']) . '</div>';
            $html .= '<h3 class="restaurant-name">' . h($restaurant['name']) . '</h3>';
            $html .= '<div class="restaurant-score">';
            $html .= '<span class="score-badge">' . $restaurant['overall_score'] . '</span>';
            $html .= '<span class="score-label">综合评分</span>';
            $html .= '</div>';
            $html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; font-size: 13px; color: #666;">';
            $html .= '<div>口味: <strong style="color: #005826;">' . $restaurant['taste_score'] . '</strong></div>';
            $html .= '<div>价格: <strong style="color: #005826;">' . $restaurant['price_score'] . '</strong></div>';
            $html .= '<div>包装: <strong style="color: #005826;">' . $restaurant['packaging_score'] . '</strong></div>';
            $html .= '<div>速度: <strong style="color: #005826;">' . $restaurant['speed_score'] . '</strong></div>';
            $html .= '</div>';
            $html .= '<p class="restaurant-description">' . h($restaurant['description'] ?? '暂无介绍') . '</p>';
            $html .= '</div>';
            $html .= '</a>';
        }
    } else {
        $html = '<div class="empty-state" style="grid-column: 1 / -1;">';
        $html .= '<div class="icon">+</div>';
        $html .= '<p>还没有添加任何商家</p>';
        $html .= '</div>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
