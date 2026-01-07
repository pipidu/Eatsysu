<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

// 获取统计数据
$pdo = getDB();
$totalRestaurants = $pdo->query("SELECT COUNT(*) as count FROM restaurants")->fetch()['count'];
$totalViews = $pdo->query("SELECT COUNT(*) as count FROM views")->fetch()['count'];
$avgScore = $pdo->query("SELECT AVG(overall_score) as avg FROM restaurants")->fetch()['avg'];

// 最近添加的商家
$recentRestaurants = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC LIMIT 5")->fetchAll();
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
            background: #f5f7fa;
            min-height: 100vh;
        }
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header .user-info span {
            color: #666;
        }
        .header .btn-logout {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .header .btn-logout:hover {
            background: #5568d3;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .stat-card .label {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .stat-card .value {
            color: #333;
            font-size: 36px;
            font-weight: bold;
        }
        .section-title {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #e1e1e1;
        }
        .btn-secondary:hover {
            background: #f9f9f9;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background: #f9fafb;
            color: #666;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        td {
            color: #333;
            font-size: 14px;
        }
        tr:hover {
            background: #f9fafb;
        }
        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .score-high {
            background: #d1fae5;
            color: #059669;
        }
        .score-medium {
            background: #fef3c7;
            color: #d97706;
        }
        .score-low {
            background: #fee2e2;
            color: #dc2626;
        }
        .actions-cell {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .btn-edit {
            background: #dbeafe;
            color: #2563eb;
        }
        .btn-edit:hover {
            background: #bfdbfe;
        }
        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-delete:hover {
            background: #fecaca;
        }
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }
        .empty-state p {
            margin-top: 12px;
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
                    <div style="font-size: 48px;">🍽️</div>
                    <p>还没有添加任何商家</p>
                    <a href="/admin/add-restaurant.php" class="btn btn-primary">添加第一个商家</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
