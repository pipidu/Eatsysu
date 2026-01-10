<?php
require_once __DIR__ . '/includes/functions.php';

// 检查用户登录状态
if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /my-restaurants.php');
    exit;
}

$currentUser = getCurrentUser();

// 验证用户是否拥有该商家
if (!isRestaurantOwnedByUser($id, $currentUser['id'])) {
    header('Location: /my-restaurants.php');
    exit;
}

// 删除商家
if (deleteRestaurant($id)) {
    header('Location: /my-restaurants.php');
    exit;
} else {
    header('Location: /my-restaurants.php');
    exit;
}
