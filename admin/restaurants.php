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

$restaurants = getAllRestaurantsWithUser($sort, $order);
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
    <title>商家管理 - 双鸭山美食</title>
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
            background: #005826;
            color: #fff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 600;
        }
        .header .back-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 13px;
        }
        .header .back-link:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 24px;
        }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters select {
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
            font-size: 13px;
            cursor: pointer;
        }
        .filters select:focus {
            outline: none;
            border-color: #005826;
        }
        .table-container {
            background: #fff;
            border: 1px solid #e5e5e5;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }
        th {
            background: #f8f8f8;
            color: #666;
            font-weight: 500;
            font-size: 13px;
        }
        td {
            color: #333;
            font-size: 14px;
        }
        tr:hover {
            background: #fafafa;
        }
        .restaurant-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .restaurant-image {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            object-fit: cover;
            background: #f5f5f5;
        }
        .restaurant-name {
            font-weight: 500;
            font-size: 14px;
        }
        .score-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 12px;
            background: #005826;
            color: #fff;
        }
        .actions-cell {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
            text-decoration: none;
            border-radius: 3px;
        }
        .btn-edit {
            background: #fff;
            color: #005826;
            border: 1px solid #e5e5e5;
        }
        .btn-edit:hover {
            background: #e8f5e9;
            border-color: #005826;
        }
        .btn-view {
            background: #fff;
            color: #005826;
            border: 1px solid #e5e5e5;
        }
        .btn-view:hover {
            background: #e8f5e9;
            border-color: #005826;
        }
        .btn-delete {
            background: #fff;
            color: #dc3545;
            border: 1px solid #e5e5e5;
        }
        .btn-delete:hover {
            background: #fef2f2;
            border-color: #dc3545;
        }
        .btn-add {
            padding: 8px 16px;
            background: #005826;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
        }
        .btn-add:hover {
            background: #00441c;
        }
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
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
            border-radius: 3px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>商家管理</h1>
        <a href="/admin/dashboard.php" class="back-link">返回控制台</a>
    </div>

    <div class="container">
        <div class="filters">
            <select onchange="location.href='?campus='+this.value+'&sort=<?php echo h($sort); ?>&order=<?php echo h($order); ?>'">
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

            <a href="/admin/add-restaurant.php" class="btn-add">添加商家</a>
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
                            <th>创建者</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <?php
                                $platforms = json_decode($restaurant['platforms'], true) ?: [];
                            ?>
                            <tr>
                                <td>
                                    <div class="restaurant-info">
                                        <?php if ($restaurant['image_url']): ?>
                                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="<?php echo h($restaurant['name']); ?>" class="restaurant-image">
                                        <?php else: ?>
                                            <div class="restaurant-image" style="display: flex; align-items: center; justify-content: center; background: #f5f5f5; color: #ccc; font-size: 20px;">+</div>
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
                                    <span class="score-badge">
                                        <?php echo $restaurant['overall_score']; ?>
                                    </span>
                                </td>
                                <td><?php echo h($restaurant['created_by_user'] ?? '管理员'); ?></td>
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
                    <p>没有找到商家</p>
                    <a href="/admin/add-restaurant.php" class="btn-add" style="margin-top: 16px; display: inline-block;">添加第一个商家</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
