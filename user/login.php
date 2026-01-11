<?php
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';

session_start();

function generateCaptcha() {
    $code = '';
    for ($i = 0; $i < 4; $i++) {
        $code .= rand(0, 9);
    }
    $_SESSION['captcha_code'] = $code;
    $_SESSION['captcha_time'] = time();
    return $code;
}

function verifyCaptcha($inputCode) {
    if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
        return false;
    }
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

    if (!verifyCaptcha($captcha)) {
        $error = '验证码错误或已过期';
    } else {
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

$captchaCode = generateCaptcha();

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
    <title>登录 - 双鸭山美食</title>
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
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .login-header .logo {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--primary-color);
        }
        .login-header h1 {
            font-size: 24px;
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .login-header p {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .login-tabs {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 32px;
        }
        .login-tab {
            flex: 1;
            padding: 16px 0;
            text-align: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 14px;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .login-tab:hover {
            color: var(--primary-color);
        }
        .login-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .captcha-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .captcha-input {
            flex: 1;
        }
        .captcha-display {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 8px;
            color: var(--primary-color);
            padding: 8px 16px;
            background: var(--bg-light);
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #00441e;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c00;
        }
        .alert-error {
            background: #fef2f2;
            color: #c00;
            border-left-color: #c00;
        }
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: var(--primary-color);
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
    <div class="login-wrapper">
        <div class="login-header">
            <div class="logo">■</div>
            <h1>双鸭山美食</h1>
            <p>登录</p>
        </div>

        <div class="login-tabs">
            <div class="login-tab active" onclick="switchTab('user')">用户登录</div>
            <div class="login-tab" onclick="switchTab('admin')">管理员登录</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
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
                <div class="captcha-group">
                    <input type="text" id="captcha" name="captcha" required placeholder="请输入验证码" class="captcha-input">
                    <div class="captcha-display" onclick="location.reload()" title="点击刷新">
                        <?php echo $captchaCode; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn">登录</button>
        </form>

        <div class="back-link">
            <a href="/">返回首页</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.getElementById('loginType').value = type;
            const tabs = document.querySelectorAll('.login-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
