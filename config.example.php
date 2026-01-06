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
// 注意：多吉云也可使用自定义域名，在多吉云控制台配置后在此填写
define('S3_CUSTOM_DOMAIN', '');

// 多吉云配置（可选）
// 多吉云是国内的对象存储服务提供商，提供 CDN 加速
// 官网: https://www.dogecloud.com/
define('DOGE_ACCESS_KEY', 'your_doge_access_key'); // 在用户中心-密钥管理中查看
define('DOGE_SECRET_KEY', 'your_doge_secret_key'); // 请勿在客户端暴露密钥
define('DOGE_ENABLED', false); // 是否启用多吉云（设置为 true 时会优先使用多吉云上传）
define('DOGE_BUCKET', 'your_doge_bucket_name'); // 多吉云存储空间名称
define('DOGE_API_URL', 'https://api.dogecloud.com'); // 多吉云 API 地址
define('DOGE_TMP_TOKEN_TTL', 7200); // 临时密钥有效期（秒），范围 0-7200

// 网站配置
define('SITE_ICON', 'https://doges3.img.shygo.cn/2026/01/06/42ac7f56a69e3b866e19c6ecb6dc62f8.jpg/720x1080'); // 网站图标
define('SITE_ICP_NUMBER', ''); // 备案号，如需要填写

// 平台图标配置
define('PLATFORM_ICONS', [
    'phone' => '📞',
    'dine_in' => '🏢',
    'jd' => 'https://doges3.img.shygo.cn/2026/01/06/d2d2439d19cbb03207b53ace32279b01.jpg/720x1080',
    'meituan' => 'https://doges3.img.shygo.cn/2026/01/06/71b72d9229c9f9d0a843fe527d20540b.png/720x1080',
    'taobao' => 'https://doges3.img.shygo.cn/2026/01/06/ad8095ff1dfa687f275fbc0459dbdf22.jpg/720x1080'
]);

// 会话配置
define('SESSION_NAME', 'EATSYSU_SESSION');

// 时区设置
date_default_timezone_set('Asia/Shanghai');
