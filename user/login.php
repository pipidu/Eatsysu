<?php
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';

// 启动 Session
session_start();

// 生成验证码
function generateCaptcha() {
    $code = '';
    for ($i = 0; $i < 4; $i++) {
        $code .= rand(0, 9);
    }
    $_SESSION['captcha_code'] = $code;
    $_SESSION['captcha_time'] = time();
    return $code;
}

// 验证验证码
function verifyCaptcha($inputCode) {
    if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
        return false;
    }
    // 验证码5分钟内有效
    if (time() - $_SESSION['captcha_time'] > 300) {
        unset($_SESSION['captcha_code']);
        unset($_SESSION['captcha_time']);
        return false;
    }
    return $inputCode === $_SESSION['captcha_code'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $loginType = $_POST['login_type'] ?? 'user';

    // 验证验证码
    if (!verifyCaptcha($captcha)) {
        $error = '验证码错误或已过期';
    } else {
        // 验证成功后清除验证码
        unset($_SESSION['captcha_code']);
        unset($_SESSION['captcha_time']);

        if ($loginType === 'admin') {
            if (adminLogin($username, $password)) {
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = '管理员用户名或密码错误';
            }
        } else {
            if (userLogin($username, $password)) {
                header('Location: /');
                exit;
            } else {
                $error = '用户名或密码错误';
            }
        }
    }
}

// 生成新的验证码（每次刷新页面）
$captchaCode = generateCaptcha();

// 如果已登录，直接跳转
if (isAdminLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

if (isUserLoggedIn()) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 双鸭山大学美食分享</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-wrapper {
            width: 100%;
            max-width: 360px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            font-size: 36px;
            margin-bottom: 12px;
        }
        .login-header h1 {
            font-size: 18px;
            color: #333;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .login-header p {
            color: #999;
            font-size: 13px;
        }
        .login-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 24px;
        }
        .login-tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
        }
        .login-tab:hover {
            color: #005826;
        }
        .login-tab.active {
            color: #005826;
            border-bottom-color: #005826;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 13px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #005826;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: #005826;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #00441e;
        }
        .error {
            background: #fef2f2;
            color: #c00;
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
            border-left: 3px solid #c00;
        }
        .success {
            background: #f0f9f0;
            color: #005826;
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
            border-left: 3px solid #005826;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link a:hover {
            color: #005826;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="logo">🍜</div>
            <h1>双鸭山美食</h1>
            <p>登录</p>
        </div>

        <div class="login-tabs">
            <div class="login-tab active" onclick="switchTab('user')">用户登录</div>
            <div class="login-tab" onclick="switchTab('admin')">管理员登录</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <input type="hidden" name="login_type" id="loginType" value="user">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label for="captcha">验证码</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="captcha" name="captcha" required placeholder="请输入验证码" style="width: 120px;">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4d2x4IiB4bWxucz0iaHR0cDovL3d3dy53My5vZy8yMDAwL3N2ZyIj48dGV4dCB4d2x4IiB4bWxucz0iaHR0cDovL3d3dy53My5vZy8yMDAwL3N2ZyIiB0eGg9IjAgMTAwIDEwMCIgd2VydG9yPSJyZyIgZmlsbD0iI2ZmZmZmZiIHN0cm9rZS13aWR0aD0iMS4xIiBzdHJva2Utb3BhY2l0eT0ibWl0dGVybWl0IiBzdHJva2Utb3Zhc2l0eT0i3Ij48dGV4dCB4c2x4IjE0IE1EMCIgZmlsbD0iI2ZmZmZmZiIHN0cm9rZS13aWR0aD0iMS4xIiBzdHJva2Utb3BhY2l0eT0ibWl0dGVybWl0IiBzdHJva2Utb3Zhc2l0eT0iMyI+PC90ZXh0Pjwvc3ZnPg==" 
                         style="width: 80px; height: 32px; cursor: pointer; border-radius: 4px;" 
                         onclick="location.reload()" 
                         alt="验证码：<?php echo $captchaCode; ?>" 
                         title="点击刷新验证码">
                    <span style="font-size: 18px; font-weight: bold; letter-spacing: 4px; color: #005826;">
                        <?php echo $captchaCode; ?>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn" id="submitBtn">登录</button>
        </form>

        <div class="back-link">
            <a href="/">← 返回首页</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            const tabs = document.querySelectorAll('.login-tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            event.target.classList.add('active');
            document.getElementById('loginType').value = type;
            document.getElementById('submitBtn').textContent = type === 'admin' ? '管理员登录' : '登录';

            // 聚焦到用户名输入框
            document.getElementById('username').focus();
        }
    </script>
</body>
</html>
