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

try {
    // 获取12个随机商家
    $randomRestaurants = getRandomRestaurants(12);

    // 生成HTML
    $html = '';

    if (count($randomRestaurants) > 0) {
        foreach ($randomRestaurants as $restaurant) {
            $radarData = generateRadarChartData($restaurant);
            $radarDataJson = json_encode($radarData['data']);
            // 使用 Base64 编码 JSON 数据，避免特殊字符问题
            $radarDataBase64 = base64_encode($radarDataJson);

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
            $html .= '<div class="radar-chart-container">';
            $html .= '<canvas class="radar-chart" data-scores="' . $radarDataBase64 . '"></canvas>';
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
