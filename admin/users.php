<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /admin/login.php');
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
        .section-title {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #059669;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
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
        .actions-cell {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
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
                    <div style="font-size: 48px;">👥</div>
                    <p>还没有添加任何用户</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
