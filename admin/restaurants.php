<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$sort = $_GET['sort'] ?? 'overall_score';
$order = $_GET['order'] ?? 'DESC';
$campusFilter = $_GET['campus'] ?? '';

$restaurants = getAllRestaurants($sort, $order);
$campuses = getCampusList();

// 校区过滤
if ($campusFilter) {
    $restaurants = array_filter($restaurants, function($r) use ($campusFilter) {
        return $r['campus'] === $campusFilter;
    });
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商家管理 - 双鸭山大学美食分享</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: #fff;
            min-height: 100vh;
        }
        .header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 18px;
            font-weight: 500;
        }
        .header .back-link {
            color: #005826;
            text-decoration: none;
            font-size: 13px;
        }
        .header .back-link:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        .filters .btn-add {
            padding: 8px 16px;
            background: #005826;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .filters .btn-add:hover {
            background: #00441e;
        }
        .table-container {
            background: white;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f5f5f5;
            color: #666;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
        }
        td {
            color: #333;
            font-size: 13px;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .restaurant-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .restaurant-image {
            width: 48px;
            height: 48px;
            border-radius: 4px;
            object-fit: cover;
            background: #f5f5f5;
        }
        .restaurant-name {
            font-weight: 500;
            font-size: 13px;
        }
        .score-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 12px;
        }
        .score-high {
            background: #f0f9f0;
            color: #005826;
        }
        .score-medium {
            background: #fef3c7;
            color: #d97706;
        }
        .score-low {
            background: #fef2f2;
            color: #c00;
        }
        .actions-cell {
            display: flex;
            gap: 6px;
        }
        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .btn-edit {
            background: #e8f5e9;
            color: #005826;
            border: 1px solid #d4e8e3;
        }
        .btn-edit:hover {
            background: #d4e8e3;
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
        .btn-view {
            background: #e0e7ff;
            color: #4f46e5;
            border: 1px solid #bae6fd;
        }
        .btn-view:hover {
            background: #c7d2fe;
        }
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #999;
        }
        .sort-link {
            color: #005826;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
        }
        .sort-link:hover {
            text-decoration: underline;
        }
        .platforms-display {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .platform-tag {
            font-size: 11px;
            padding: 2px 8px;
            background: #f5f5f5;
            border-radius: 4px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>商家管理</h1>
        <a href="/admin/dashboard.php" class="back-link">← 返回控制台</a>
    </div>

    <div class="container">
        <div class="filters">
            <select onchange="location.href='?campus='+this.value+'&sort=<?php echo h($sort); ?>&order=<?php echo h($order); ?>'" style="margin-right: auto;">
                <option value="">全部校区</option>
                <?php foreach ($campuses as $campus): ?>
                    <option value="<?php echo h($campus); ?>" <?php echo $campusFilter === $campus ? 'selected' : ''; ?>>
                        <?php echo h($campus); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select onchange="location.href='?campus=<?php echo h($campusFilter); ?>&sort=overall_score&order='+this.value">
                <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>评分从高到低</option>
                <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>评分从低到高</option>
            </select>

            <a href="/admin/add-restaurant.php" class="btn-add">+ 添加商家</a>
        </div>

        <div class="table-container">
            <?php if (count($restaurants) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>商家</th>
                            <th>校区</th>
                            <th>点单平台</th>
                            <th>评分</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <?php
                                $platforms = json_decode($restaurant['platforms'], true) ?: [];
                                $scoreClass = $restaurant['overall_score'] >= 8 ? 'score-high' :
                                             ($restaurant['overall_score'] >= 6 ? 'score-medium' : 'score-low');
                            ?>
                            <tr>
                                <td>
                                    <div class="restaurant-info">
                                        <?php if ($restaurant['image_url']): ?>
                                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                                        <?php else: ?>
                                            <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; background: #f5f5f5; color: #999;">🍜</div>
                                        <?php endif; ?>
                                        <span class="restaurant-name"><?php echo h($restaurant['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo h($restaurant['campus']); ?></td>
                                <td>
                                    <div class="platforms-display">
                                        <?php if ($platforms['dine_in'] ?? false): ?><span class="platform-tag">堂食</span><?php endif; ?>
                                        <?php if ($platforms['jd'] ?? false): ?><span class="platform-tag">京东</span><?php endif; ?>
                                        <?php if ($platforms['meituan'] ?? false): ?><span class="platform-tag">美团</span><?php endif; ?>
                                        <?php if ($platforms['taobao'] ?? false): ?><span class="platform-tag">淘宝</span><?php endif; ?>
                                        <?php if (!empty($platforms['phone'])): ?><span class="platform-tag">电话</span><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        <?php echo $restaurant['overall_score']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($restaurant['created_at'])); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-view" target="_blank">查看</a>
                                        <a href="/admin/edit-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-edit">编辑</a>
                                        <a href="/admin/delete-restaurant.php?id=<?php echo $restaurant['id']; ?>"
                                           class="btn-sm btn-delete"
                                           onclick="return confirm('确定要删除这个商家吗？');">删除</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 40px;">🍽️</div>
                    <p>没有找到商家</p>
                    <a href="/admin/add-restaurant.php" class="btn-add" style="margin-top: 16px; display: inline-block;">添加第一个商家</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
