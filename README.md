# 中山大学美食分享网站 🍜

一个用于分享中山大学校园周边美食的PHP网站，支持商家评分、排行榜和随机发现功能。

## 功能特性

### 管理员后台
- 🔐 安全的登录系统
- ➕ 添加/编辑/删除商家
- 📷 上传商家图片到对象存储（支持 AWS S3、Cloudflare R2、多吉云等）
- 📊 设定多维评分（口味、价格、服务、健康）
- 📈 自动计算综合评分（加权平均）
- 📱 设定推荐点单平台（电话、堂食、京东、美团、淘宝）
- 🏫 支持五大校区分类（南校区、北校区、东校区、珠海校区、深圳校区）

### 前台展示
- 🏆 评价排行榜（可按校区/评分维度筛选）
- 🎲 随机发现美食
- 📊 n维雷达图展示各项评分
- 🏰 按校区浏览商家
- 📱 响应式设计，支持手机访问

## 技术栈

- **后端**: PHP 7.4+
- **数据库**: MySQL/MariaDB
- **存储**: AWS S3 / Cloudflare R2 / 多吉云 / MinIO（S3 API 兼容）
- **前端**: 原生HTML/CSS/JavaScript
- **图表**: Chart.js（雷达图）
- **依赖管理**: Composer

## 安装步骤

### 🚀 快速安装（推荐）

项目提供了图形化安装向导，可以在浏览器中完成所有配置：

#### 1. 环境要求

- PHP 7.4 或更高版本
- MySQL/MariaDB 5.7 或更高版本
- Web服务器（Apache/Nginx）
- 文件写入权限（用于生成config.php）
- 对象存储服务（AWS S3、Cloudflare R2 等，可选）

#### 2. 克隆/下载项目

```bash
cd /path/to/your/web/directory
# 将项目文件放在此目录下
```

#### 3. 安装依赖

```bash
composer install
```

#### 4. 运行安装向导

1. 访问 `http://your-domain.com/install.php`
2. 按照向导步骤完成安装：
   - **步骤1**: 环境检查（自动检测PHP版本、扩展等）
   - **步骤2**: 配置数据库连接
   - **步骤3**: 创建数据表（自动创建数据库和表结构）
   - **步骤4**: 设置管理员账户
   - **步骤5**: 配置对象存储（AWS S3、Cloudflare R2、多吉云等，可选，可跳过）
   - **步骤6**: 确认配置并完成安装

3. 安装完成后，访问网站即可使用

#### 5. 配置Web服务器

##### Apache

确保启用了 `mod_rewrite` 模块：

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

项目已包含 `.htaccess` 文件，应该可以直接工作。

##### Nginx

添加以下配置到你的Nginx配置文件：

```nginx
location / {
    try_files $uri $uri/ /index.php?q=$uri&$args;
}
```

#### 6. 访问网站

- 前台: `http://your-domain.com/` 或 `http://localhost/`
- 后台: `http://your-domain.com/admin/login.php`

---

### 🔧 手动安装

如果你想手动配置，可以按照以下步骤：

#### 1. 环境要求

- PHP 7.4 或更高版本
- MySQL/MariaDB 5.7 或更高版本
- Web服务器（Apache/Nginx）
- 对象存储服务（AWS S3、Cloudflare R2、多吉云等，可选）

#### 2. 安装依赖

```bash
composer install
```

#### 3. 配置数据库

创建数据库并导入表结构：

```bash
mysql -u root -p < database.sql
```

或手动执行 `database.sql` 文件中的SQL语句。

#### 4. 配置文件

复制 `config.example.php` 为 `config.php`，然后编辑：

```bash
cp config.example.php config.php
```

编辑 `config.php`，填写数据库和对象存储配置：

```php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'eatsysu');

// 对象存储配置（支持 AWS S3、Cloudflare R2、MinIO 等）
define('AWS_ACCESS_KEY_ID', 'your_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key');
define('AWS_REGION', 'auto');  // AWS S3 使用实际区域（如 ap-guangzhou），其他服务通常使用 auto
define('AWS_BUCKET', 'your-bucket-name');

// 自定义对象存储端点（可选）
// 用于 Cloudflare R2、MinIO、阿里云OSS等 S3 API 兼容服务
define('S3_ENDPOINT', '');  // Cloudflare R2 示例: https://<account-id>.r2.cloudflarestorage.com

// 是否使用路径风格端点
define('S3_USE_PATH_STYLE', true);  // Cloudflare R2 需要设置为 true

// 自定义域名（可选）
define('S3_CUSTOM_DOMAIN', '');  // 如果配置了 CDN 域名可填写

// 多吉云配置（可选）
// 多吉云是国内的对象存储服务提供商，提供 CDN 加速
// 官网: https://www.dogecloud.com/
define('DOGE_ACCESS_KEY', 'your_doge_access_key'); // 在用户中心-密钥管理中查看
define('DOGE_SECRET_KEY', 'your_doge_secret_key'); // 请勿在客户端暴露密钥
define('DOGE_ENABLED', false); // 是否启用多吉云（设置为 true 时会优先使用多吉云上传）
define('DOGE_BUCKET', 'your_doge_bucket_name'); // 多吉云存储空间名称
define('DOGE_API_URL', 'https://api.dogecloud.com'); // 多吉云 API 地址
define('DOGE_TMP_TOKEN_TTL', 7200); // 临时密钥有效期（秒），范围 0-7200
```

#### 5. 设置管理员密码

使用以下方式生成密码哈希：

```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

将生成的哈希值插入到数据库 `admins` 表：

```sql
INSERT INTO admins (username, password) VALUES ('admin', 'your_hashed_password');
```

#### 6. 创建安装锁文件

手动安装完成后，创建安装锁文件：

```bash
touch install.lock
```

#### 7. 访问网站

- 前台: `http://your-domain.com/` 或 `http://localhost/`
- 后台: `http://your-domain.com/admin/login.php`

---

### 💡 安装提示

- **自动跳转**: 如果未安装，访问任何页面都会自动跳转到安装向导
- **重新安装**: 访问 `install.php?force=1` 可以重新运行安装（仅用于开发调试）
- **安全性**: 生产环境安装完成后，建议删除 `install.php` 文件
- **S3可选**: 对象存储配置是可选的，不配置也可以使用网站（但不能上传图片）

## 使用指南

### 管理员登录

1. 访问 `/admin/login.php`
2. 输入用户名和密码
3. 登录后可以管理商家

### 添加商家

1. 登录后台
2. 点击"添加商家"
3. 填写商家信息：
   - 商家名称
   - 所在校区
   - 具体位置
   - 商家图片（可选）
   - 推荐点单平台
   - 商家介绍
   - 多维评分（0-10分）
4. 保存后系统自动计算综合评分

### 查看排行榜

- 访问首页查看推荐排行榜
- 访问 `/ranking.php` 查看完整排行榜
- 可按校区、评分维度筛选

### 随机发现

- 访问 `/discover.php`
- 点击"换一批"按钮随机显示新的商家

## 数据库结构

### admins 表
管理员账户信息

### restaurants 表
商家信息，包括：
- 基本信息（名称、校区、位置、介绍）
- 点单平台（JSON格式）
- 多维评分（口味、价格、服务、健康）
- 综合评分（自动计算）
- 图片URL
- 创建/更新时间

### views 表
用户浏览记录（用于统计）

## 评分算法

综合评分采用加权平均算法：

```
综合评分 = 口味 × 35% + 价格 × 20% + 服务 × 20% + 健康 × 25%
```

## 对象存储配置说明

### AWS S3 配置

1. 登录AWS控制台
2. 转到S3服务
3. 创建新的存储桶
4. 设置存储桶为公开访问（或配置CloudFront）
5. 配置CORS策略（如需要）

#### IAM权限

创建一个IAM用户，分配以下权限：

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject"
            ],
            "Resource": "arn:aws:s3:::your-bucket-name/*"
        }
    ]
}
```

### Cloudflare R2 配置

1. 登录 Cloudflare 控制台
2. 转到 R2 > 创建存储桶
3. 获取存储桶名称和账户 ID
4. 创建 API Token（Access Key ID 和 Secret Access Key）
5. 配置 `config.php`：

```php
define('AWS_ACCESS_KEY_ID', 'your_r2_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_r2_secret_key');
define('AWS_REGION', 'auto');  // R2 使用 auto
define('AWS_BUCKET', 'your-bucket-name');
define('S3_ENDPOINT', 'https://<account-id>.r2.cloudflarestorage.com');
define('S3_USE_PATH_STYLE', true);
```

#### 配置自定义域名（可选）

如果需要通过自定义域名访问 R2 上的图片：

1. 在 Cloudflare 中创建 R2 自定义域名
2. 将域名配置到 `config.php`：
```php
define('S3_CUSTOM_DOMAIN', 'cdn.example.com');
```

### MinIO 配置

对于自建的 MinIO 服务：

```php
define('AWS_ACCESS_KEY_ID', 'your_minio_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_minio_secret_key');
define('AWS_REGION', 'auto');
define('AWS_BUCKET', 'your-bucket-name');
define('S3_ENDPOINT', 'https://minio.example.com');
define('S3_USE_PATH_STYLE', true);
```

### 多吉云配置

多吉云是国内的对象存储服务提供商，提供 CDN 加速，非常适合国内使用。

1. 注册并登录 [多吉云控制台](https://www.dogecloud.com/)
2. 进入「用户中心」->「密钥管理」，获取 AccessKey 和 SecretKey
3. 创建存储空间（Bucket）
4. 配置 `config.php`：

```php
define('DOGE_ACCESS_KEY', 'your_doge_access_key');
define('DOGE_SECRET_KEY', 'your_doge_secret_key');
define('DOGE_ENABLED', true); // 启用多吉云
define('DOGE_BUCKET', 'your_bucket_name');
```

**优势：**
- 国内 CDN 加速，访问速度快
- 兼容 S3 API，无需修改代码
- 按需付费，成本更低
- 支持 HTTPS 自定义域名

### 其他 S3 API 兼容服务

任何支持 S3 API 的对象存储服务都可以使用，只需配置正确的端点：

```php
define('S3_ENDPOINT', 'https://your-storage-endpoint.com');
define('S3_USE_PATH_STYLE', true);
```

## 多吉云 API 说明

### API 验证机制

多吉云 API 使用 HMAC-SHA1 签名进行身份验证：

1. 将 API 路径和请求体用换行符 `\n` 连接
2. 使用 SecretKey 对连接后的字符串进行 HMAC-SHA1 加密
3. 将加密结果转换为十六进制字符串
4. 将 AccessKey 和签名用冒号连接
5. 在请求头中添加 `Authorization: TOKEN {accessKey}:{sign}`

### 可用的函数

系统提供以下函数使用多吉云 API：

#### 1. `dogecloudApi($apiPath, $data, $jsonMode)`

通用 API 调用函数。

```php
$result = dogecloudApi('/oss/bucket/list.json');
```

#### 2. `getDogecloudTmpToken($type, $scopes, $ttl)`

获取临时上传密钥。

```php
$token = getDogecloudTmpToken('OSS_UPLOAD', [DOGE_BUCKET . ':test.jpg']);
echo $token['data']['Credentials']['accessKeyId'];
```

#### 3. `uploadToDogecloud($filePath, $objectKey, $contentType)`

上传文件到多吉云。

```php
$url = uploadToDogecloud('/tmp/photo.jpg', 'restaurants/photo.jpg', 'image/jpeg');
echo $url;
```

#### 4. `uploadFile($file, $folder)`

**推荐的统一上传函数**

自动选择多吉云（如果启用）或 S3 作为存储后端。

```php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageUrl = uploadFile($_FILES['image'], 'restaurants');
    echo "上传成功: " . $imageUrl;
}
```

### 临时密钥类型

- `OSS_UPLOAD` - 云存储上传权限（适合客户端使用）
- `OSS_FULL` - 云存储完全权限（仅服务端使用）
- `VOD_UPLOAD` - 视频云上传权限

### 授权范围格式

- `*` - 所有空间的所有文件（仅 OSS_FULL）
- `bucket` - 某个存储空间
- `bucket:prefix/*` - 某个前缀下的所有文件
- `bucket:path/file.jpg` - 特定文件

### 错误处理

API 错误会返回非 200 的 code，常见错误代码：

- `ERROR_UNAUTHORIZED` - 未授权
- `ERROR_AUTHORIZATION_FAILED` - 密钥验证失败
- `ERROR_PERMISSION_DENIED` - 没有权限
- `ERROR_TOO_FREQUENTLY` - 请求过于频繁

### 安全注意事项

⚠️ **重要：**

1. 永远不要在客户端（浏览器、移动 App）暴露密钥
2. 所有 API 调用应在服务端进行
3. 遵循最小权限原则，只授予必要的权限
4. 定期更换密钥
5. 限制临时密钥的有效期

## 常见问题

### 1. 图片上传失败

- 检查对象存储配置是否正确（端点、密钥等）
- 检查用户权限和存储桶访问权限
- 检查存储桶是否公开访问（如需要）
- 检查PHP文件上传大小限制
- 对于 Cloudflare R2，确保使用了路径风格端点（S3_USE_PATH_STYLE = true）
- 对于多吉云，确保存储空间已创建且名称正确

### 2. 多吉云配置失败

- 确认 AccessKey 和 SecretKey 正确
- 检查存储空间是否存在
- 确保 `DOGE_ENABLED` 设置为 `true`
- 验证网络连接（需要访问 api.dogecloud.com）

### 3. 如何从 S3 迁移到多吉云？

- 渐进式迁移：保持 S3 配置，启用多吉云，新文件使用多吉云
- 完整迁移：将所有文件上传到多吉云，更新数据库，禁用 S3
- 两种方式都可以，系统会自动处理多种存储后端

### 4. 数据库连接失败

- 检查数据库配置信息
- 确保数据库服务正在运行
- 检查用户权限

### 5. 重定向问题

- 确保 `.htaccess` 文件存在
- 检查 `mod_rewrite` 是否启用
- 检查Apache配置

### 6. 雷达图不显示

- 检查网络连接（需要加载Chart.js CDN）
- 查看浏览器控制台错误信息

## 安全建议

1. **修改默认密码**: 生产环境务必使用强密码
2. **HTTPS**: 配置SSL证书启用HTTPS
3. **文件权限**: 设置适当的文件和目录权限
4. **定期备份**: 定期备份数据库
5. **更新依赖**: 定期更新PHP和Composer依赖
6. **防火墙**: 配置Web应用防火墙

## 目录结构

```
eatsysu/
├── admin/                  # 后台管理目录
│   ├── login.php          # 登录页面
│   ├── logout.php         # 退出登录
│   ├── dashboard.php      # 控制台
│   ├── add-restaurant.php # 添加商家
│   ├── edit-restaurant.php # 编辑商家
│   ├── delete-restaurant.php # 删除商家
│   └── restaurants.php    # 商家管理列表
├── includes/              # 公共函数目录
│   └── functions.php      # 核心函数库
├── config.php             # 配置文件
├── database.sql           # 数据库结构
├── composer.json          # Composer配置
├── .htaccess             # Apache重写规则
├── index.php             # 首页
├── ranking.php           # 排行榜
├── discover.php          # 发现页面
├── restaurant.php        # 商家详情
└── README.md             # 本文件
```

## 自定义开发

### 添加新的评分维度

1. 修改 `database.sql`，在 `restaurants` 表添加新字段
2. 修改 `includes/functions.php` 中的 `calculateOverallScore()` 函数
3. 修改 `admin/add-restaurant.php` 和 `edit-restaurant.php` 表单
4. 更新前端页面以显示新维度

### 修改评分权重

编辑 `includes/functions.php` 中的 `$weights` 数组：

```php
$weights = [
    'taste' => 0.35,      // 35%
    'price' => 0.20,      // 20%
    'service' => 0.20,    // 20%
    'health' => 0.25      // 25%
];
```

### 添加新校区

编辑 `includes/functions.php` 中的 `getCampusList()` 函数：

```php
return ['南校区', '北校区', '东校区', '珠海校区', '深圳校区'];
```

## 许可证

MIT License

## 联系方式

如有问题或建议，欢迎提交Issue或Pull Request。

## 相关链接

- 多吉云官网：https://www.dogecloud.com/
- 多吉云文档：https://www.dogecloud.com/doc
- PHP 文档：https://www.php.net/docs.php
- AWS SDK for PHP：https://docs.aws.amazon.com/aws-sdk-php/v3/api/

---

祝你使用愉快！🍜✨
