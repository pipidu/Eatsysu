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
    <title>用户管理 - 双鸭山美食</title>
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
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header .user-info span {
            font-size: 14px;
            opacity: 0.9;
        }
        .header .btn-logout {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            font-size: 13px;
        }
        .header .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 24px;
        }
        .section-title {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 3px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #005826;
            border-left: 3px solid #005826;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc3545;
            border-left: 3px solid #dc3545;
        }
        .form-container {
            background: #fff;
            border: 1px solid #e5e5e5;
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
            border: 1px solid #e5e5e5;
            border-radius: 3px;
            font-size: 13px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #005826;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            background: #005826;
            color: white;
        }
        .btn-primary:hover {
            background: #00441c;
        }
        .btn-secondary {
            background: #fff;
            color: #333;
            border: 1px solid #e5e5e5;
        }
        .btn-secondary:hover {
            background: #fafafa;
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
        .actions-cell {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
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
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }
        .empty-state p {
            margin-top: 10px;
            font-size: 14px;
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
        <h1>双鸭山美食管理</h1>
        <div class="user-info">
            <span>欢迎，<?php echo h($_SESSION['admin_username']); ?></span>
            <a href="/admin/logout.php" class="btn-logout">退出登录</a>
        </div>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo h($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo h($error); ?>
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
                                           onclick="return confirm('确定要删除这个用户吗？');">删除</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>还没有添加任何用户</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
