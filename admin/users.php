<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$success = '';
$error = '';

// 处理添加用户
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception('用户名和密码不能为空');
        }

        if (strlen($password) < 6) {
            throw new Exception('密码长度至少为6位');
        }

        if (addUser($username, $password, $_SESSION['admin_id'])) {
            $success = '用户添加成功！';
        } else {
            throw new Exception('用户添加失败，可能是用户名已存在');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// 处理删除用户
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    if (deleteUser($userId)) {
        $success = '用户删除成功！';
    } else {
        $error = '用户删除失败！';
    }
}

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 双鸭山大学美食管理</title>
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
            border-radius: 4px;
            font-size: 13px;
            transition: background 0.2s;
        }
        .header .btn-logout:hover {
            background: #00441e;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .section-title {
            color: #333;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .alert {
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .alert-success {
            background: #f0f9f0;
            color: #005826;
            border-left: 3px solid #005826;
        }
        .alert-error {
            background: #fef2f2;
            color: #c00;
            border-left: 3px solid #c00;
        }
        .form-container {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 32px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 500;
            font-size: 13px;
        }
        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #005826;
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
        }
        .btn-primary:hover {
            background: #00441e;
        }
        .btn-secondary {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #f5f5f5;
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
        .actions-cell {
            display: flex;
            gap: 6px;
        }
        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
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
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?php echo h($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">添加用户</h2>
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">用户名</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">密码</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">添加用户</button>
                    </div>
                    <div class="form-group">
                        <a href="/admin/dashboard.php" class="btn btn-secondary">返回控制台</a>
                    </div>
                </div>
            </form>
        </div>

        <h2 class="section-title">用户列表</h2>
        <div class="table-container">
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>创建者</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo h($user['username']); ?></td>
                                <td><?php echo h($user['created_by_admin'] ?? '-'); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="/admin/users.php?delete=<?php echo $user['id']; ?>"
                                           class="btn btn-sm btn-delete"
                                           onclick="return confirm('确定要删除这个用户吗？')">删除</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 40px;">👥</div>
                    <p>还没有添加任何用户</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
