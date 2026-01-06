<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if ($id && deleteRestaurant($id)) {
    header('Location: /admin/dashboard.php');
    exit;
} else {
    header('Location: /admin/dashboard.php');
    exit;
}
