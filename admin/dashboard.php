<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$pdo = getDB();
$totalRestaurants = $pdo->query("SELECT COUNT(*) as count FROM restaurants")->fetch()['count'];
$totalViews = $pdo->query("SELECT COUNT(*) as count FROM views")->fetch()['count'];
$avgScore = $pdo->query("SELECT AVG(overall_score) as avg FROM restaurants")->fetch()['avg'];

$recentRestaurants = $pdo->query("SELECT r.*, u.username as created_by_user FROM restaurants r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理控制台 - 双鸭山美食</title>
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
            border-bottom: 2px solid var(--primary-color);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: var(--primary-color);
            font-size: 20px;
            font-weight: 600;
        }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header .user-info span {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .header .btn-logout {
            padding: 8px 20px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .header .btn-logout:hover {
            background: #00441e;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 28px;
        }
        .stat-card .label {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .stat-card .value {
            color: var(--text-main);
            font-size: 40px;
            font-weight: 600;
        }
        .section-title {
            color: var(--text-main);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background: #00441e;
        }
        .btn-secondary {
            background: #fff;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-color);
        }
        .table-container {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background: var(--bg-light);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 13px;
        }
        td {
            color: var(--text-main);
            font-size: 14px;
        }
        tr:hover {
            background: var(--bg-light);
        }
        .score-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
        }
        .score-high {
            background: var(--primary-light);
            color: var(--primary-color);
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
            gap: 8px;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-edit {
            background: var(--bg-light);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }
        .btn-edit:hover {
            background: var(--primary-light);
            border-color: var(--primary-color);
        }
        .btn-view {
            background: var(--bg-light);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }
        .btn-view:hover {
            background: var(--primary-light);
            border-color: var(--primary-color);
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
            padding: 60px 20px;
            text-align: center;
            color: var(--text-secondary);
        }
        :root {
            --primary-color: #005826;
            --primary-light: #e8f5e9;
            --text-main: #333;
            --text-secondary: #666;
            --border-color: #e5e5e5;
            --bg-light: #fafafa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>双鸭山美食管理</h1>
        <div class="user-info">
            <span>欢迎，<?php echo h($_SESSION['admin_username']); ?></span>
            <a href="/admin/logout.php" class="btn-logout">退出</a>
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
            <a href="/admin/add-restaurant.php" class="btn btn-primary">添加商家</a>
            <a href="/admin/restaurants.php" class="btn btn-secondary">管理商家</a>
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
                            <?php
                                $scoreClass = $restaurant['overall_score'] >= 8 ? 'score-high' :
                                                ($restaurant['overall_score'] >= 6 ? 'score-medium' : 'score-low');
                                ?>
                                <tr>
                                    <td><?php echo h($restaurant['name']); ?></td>
                                    <td><?php echo h($restaurant['campus']); ?></td>
                                    <td>
                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                            <?php echo $restaurant['overall_score']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($restaurant['created_by_user'] ?? '管理员'); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($restaurant['created_at'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-view" target="_blank">查看</a>
                                            <a href="/admin/edit-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-edit">编辑</a>
                                            <a href="/admin/delete-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('确定要删除这个商家吗？');">删除</a>
                                        </div>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>还没有商家</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
