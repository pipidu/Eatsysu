<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// 获取统计数据
$pdo = getDB();
$totalRestaurants = $pdo->query("SELECT COUNT(*) as count FROM restaurants")->fetch()['count'];
$totalViews = $pdo->query("SELECT COUNT(*) as count FROM views")->fetch()['count'];
$avgScore = $pdo->query("SELECT AVG(overall_score) as avg FROM restaurants")->fetch()['avg'];

// 最近添加的商家
$recentRestaurants = $pdo->query("SELECT r.*, u.username as created_by_user FROM restaurants r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理控制台 - 双鸭山大学美食分享</title>
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
            background: #fff;
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
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header .user-info span {
            color: #666;
            font-size: 13px;
        }
        .header .btn-logout {
            padding: 6px 14px;
            background: #005826;
            color: white;
            text-decoration: none;
            border: 1px solid #005826;
            border-radius: 4px;
            font-size: 13px;
            transition: background 0.2s;
        }
        .header .btn-logout:hover {
            background: #00441e;
            border-color: #00441e;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 20px;
        }
        .stat-card .label {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .stat-card .value {
            color: #333;
            font-size: 32px;
            font-weight: 500;
        }
        .section-title {
            color: #333;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #005826;
            color: white;
            border: 1px solid #005826;
        }
        .btn-primary:hover {
            background: #00441e;
            border-color: #00441e;
        }
        .btn-secondary {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #f5f5f5;
            border-color: #bbb;
        }
        .table-container {
            background: #fff;
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
        }
        .btn-edit {
            background: #f5f5f5;
            color: #005826;
            border: 1px solid #005826;
        }
        .btn-edit:hover {
            background: #e8f5e9;
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
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #999;
        }
        .empty-state p {
            margin-top: 10px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍜 双鸭山大学美食管理</h1>
        <div class="user-info">
            <span>欢迎, <?php echo h($_SESSION['admin_username']); ?></span>
            <a href="/admin/logout.php" class="btn-logout">退出登录</a>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="label">商家总数</div>
                <div class="value"><?php echo $totalRestaurants; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">总浏览量</div>
                <div class="value"><?php echo $totalViews; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">平均评分</div>
                <div class="value"><?php echo $avgScore ? round($avgScore, 1) : '0.0'; ?></div>
            </div>
        </div>

        <h2 class="section-title">快速操作</h2>
        <div class="actions">
            <a href="/admin/add-restaurant.php" class="btn btn-primary">+ 添加商家</a>
            <a href="/admin/restaurants.php" class="btn btn-secondary">管理所有商家</a>
            <a href="/admin/users.php" class="btn btn-secondary">管理用户</a>
            <a href="/" class="btn btn-secondary" target="_blank">查看网站</a>
        </div>

        <h2 class="section-title">最近添加</h2>
        <div class="table-container">
            <?php if (count($recentRestaurants) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>商家名称</th>
                            <th>校区</th>
                            <th>综合评分</th>
                            <th>创建者</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRestaurants as $restaurant): ?>
                            <tr>
                                <td><?php echo h($restaurant['name']); ?></td>
                                <td><?php echo h($restaurant['campus']); ?></td>
                                <td>
                                    <?php
                                        $scoreClass = $restaurant['overall_score'] >= 8 ? 'score-high' :
                                                     ($restaurant['overall_score'] >= 6 ? 'score-medium' : 'score-low');
                                    ?>
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        <?php echo $restaurant['overall_score']; ?>
                                    </span>
                                </td>
                                <td><?php echo h($restaurant['created_by_user'] ?? '管理员'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($restaurant['created_at'])); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="/admin/edit-restaurant.php?id=<?php echo $restaurant['id']; ?>"
                                           class="btn btn-sm btn-edit">编辑</a>
                                        <a href="/admin/delete-restaurant.php?id=<?php echo $restaurant['id']; ?>"
                                           class="btn btn-sm btn-delete"
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
                    <p>还没有添加任何商家</p>
                    <a href="/admin/add-restaurant.php" class="btn btn-primary">添加第一个商家</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
