<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查用户登录状态
if (!isUserLoggedIn()) {
    header('Location: /user/login.php');
    exit;
}

$success = '';
$error = '';

// 获取当前用户创建的商家
$currentUser = getCurrentUser();
$myRestaurants = getRestaurantsByUser($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/jpeg" href="<?php echo defined('SITE_ICON') ? SITE_ICON : '/favicon.ico'; ?>">
    <title>我的商家 - 双鸭山大学美食分享</title>
    <style>
        .form-container {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 28px;
        }
        .form-header h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 6px;
        }
        .form-header p {
            color: #999;
            font-size: 13px;
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
        }
        .btn-edit {
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .btn-edit:hover {
            background: #bae6fd;
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
                <a href="/submit.php">上传商家</a>
                <a href="/user/my-restaurants.php" class="active">我的商家</a>
                <a href="/user/user-logout.php">退出</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>📋 我的商家</h1>
                <p>欢迎，<?php echo h($currentUser['username']); ?>！您共创建了 <?php echo count($myRestaurants); ?> 个商家</p>
            </div>

            <div class="btn-group" style="margin-bottom: 20px;">
                <a href="/submit.php" class="btn btn-primary">+ 上传新商家</a>
                <a href="/" class="btn btn-secondary">返回首页</a>
            </div>

            <div class="table-container">
                <?php if (count($myRestaurants) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>商家</th>
                                <th>校区</th>
                                <th>评分</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myRestaurants as $restaurant): ?>
                                <?php
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
                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                            <?php echo $restaurant['overall_score']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($restaurant['created_at'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="/restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-edit" target="_blank">查看</a>
                                            <a href="/user/edit-my-restaurant.php?id=<?php echo $restaurant['id']; ?>" class="btn-sm btn-edit">编辑</a>
                                            <a href="/user/delete-my-restaurant.php?id=<?php echo $restaurant['id']; ?>"
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
                        <p>您还没有创建任何商家</p>
                        <a href="/submit.php" class="btn btn-primary" style="margin-top: 16px;">上传第一个商家</a>
                    </div>
                <?php endif; ?>
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
</body>
</html>
