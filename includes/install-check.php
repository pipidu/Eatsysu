<?php
// 安装检查 - 所有页面都应该包含此文件
$installLockFile = dirname(__DIR__) . '/install.lock';

if (!file_exists($installLockFile)) {
    // 如果访问的是安装页面，则不跳转
    $currentPath = $_SERVER['PHP_SELF'] ?? '';
    if (basename($currentPath) !== 'install.php') {
        header('Location: /install.php');
        exit;
    }
}
