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
            margin-bottom: 32px;
        }
        .login-header h1 {
            font-size: 24px;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .login-tabs {
            display: flex;
            border-bottom: 2px solid #e5e5e5;
            margin-bottom: 24px;
        }
        .login-tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            font-weight: 500;
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
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #005826;
        }
        .captcha-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .captcha-input {
            flex: 1;
        }
        .captcha-display {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 6px;
            color: #005826;
            padding: 8px 12px;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
            cursor: pointer;
            user-select: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #005826;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #00441c;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 3px;
            margin-bottom: 16px;
            font-size: 14px;
            border-left: 3px solid #c00;
        }
        .alert-error {
            background: #fef2f2;
            color: #c00;
            border-left-color: #c00;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: #005826;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
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
