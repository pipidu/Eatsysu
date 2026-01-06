<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'eatsysu');

// 对象存储配置（支持 AWS S3、Cloudflare R2、MinIO 等 S3 API 兼容服务）
define('AWS_ACCESS_KEY_ID', 'your_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key');
define('AWS_REGION', 'auto');  // AWS S3 使用实际区域（如 ap-guangzhou），其他服务通常使用 auto
define('AWS_BUCKET', 'your-bucket-name');

// 自定义对象存储端点（可选）
// 用于 Cloudflare R2、MinIO、阿里云OSS等 S3 API 兼容服务
// Cloudflare R2 示例: https://<account-id>.r2.cloudflarestorage.com
// MinIO 示例: https://minio.example.com
// 留空则使用 AWS S3
define('S3_ENDPOINT', '');

// 是否使用路径风格端点（某些自建 S3 服务需要设置为 true）
// Cloudflare R2 需要设置为 true
define('S3_USE_PATH_STYLE', true);

// 自定义域名（可选）
// 如果为对象存储配置了自定义域名（如 CDN 域名），在此填写
// 示例: cdn.example.com
define('S3_CUSTOM_DOMAIN', '');

// 会话配置
define('SESSION_NAME', 'EATSYSU_SESSION');

// 时区设置
date_default_timezone_set('Asia/Shanghai');
