<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'eatsysu');

// AWS S3 配置
define('AWS_ACCESS_KEY_ID', 'your_aws_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_aws_secret_key');
define('AWS_REGION', 'ap-guangzhou');
define('AWS_BUCKET', 'your-bucket-name');

// 会话配置
define('SESSION_NAME', 'EATSYSU_SESSION');

// 管理员凭证（实际使用时应使用哈希密码）
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'your_secure_password_here');

// 时区设置
date_default_timezone_set('Asia/Shanghai');
