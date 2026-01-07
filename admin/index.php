<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查管理员登录状态
if (!isAdminLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

header('Location: /admin/dashboard.php');
exit;
