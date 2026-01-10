# 双鸭山大学美食分享网站 🍜

一个用于分享双鸭山大学校园周边美食的PHP网站，支持用户自主管理商家、评分、排行榜和随机发现功能。

## 功能特性

### 用户系统
- 👤 用户注册/登录
- 🏪 用户自主添加商家
- ✏️ 用户编辑自己创建的商家
- 🗑️ 用户删除自己创建的商家
- 📋 用户查看自己管理的商家列表
- 🔒 权限验证：用户只能操作自己创建的商家

### 管理员后台
- 🔐 独立的管理员登录系统
- 👥 管理普通用户账户
- ➕ 管理员添加/编辑/删除所有商家
- 📷 上传商家图片到对象存储（支持 AWS S3、Cloudflare R2、多吉云等）
- 📊 设定多维评分（口味、价格、包装、速度）
- 📈 自动计算综合评分（加权平均）
- 📱 设定推荐点单平台（电话、堂食、京东、美团、淘宝）
- 🏫 支持五大校区分类（南校区、北校区、东校区、珠海校区、深圳校区）

### 前台展示
- 🏆 评价排行榜（可按校区/评分维度筛选）
- 🎲 随机发现美食
- 📊 n维雷达图展示各项评分
- 🏰 按校区浏览商家
- 📱 响应式设计，支持手机访问
- 👤 商家详情页显示创建者信息

## 技术栈

- **后端**: PHP 7.4+
- **数据库**: MySQL/MariaDB
- **存储**: AWS S3 / Cloudflare R2 / 多吉云 / MinIO（S3 API 兼容）
- **前端**: 原生HTML/CSS/JavaScript
- **图表**: Chart.js（雷达图）
- **依赖管理**: Composer

## 安装步骤

### 快速安装（推荐）

项目提供了图形化安装向导，可以在浏览器中完成所有配置。

#### 1. 环境要求

- PHP 7.4 或更高版本
- MySQL/MariaDB 5.7 或更高版本
- Web服务器（Apache/Nginx）
- 文件写入权限（用于生成config.php）
- 对象存储服务（可选）

#### 2. 安装依赖

```bash
composer install
```

#### 3. 运行安装向导

1. 访问 `http://your-domain.com/install.php`
2. 按照向导步骤完成安装：
   - 步骤1: 环境检查
   - 步骤2: 配置数据库连接
   - 步骤3: 创建数据表
   - 步骤4: 设置管理员账户
   - 步骤5: 配置对象存储（可选）
   - 步骤6: 确认配置

3. 安装完成后即可使用

#### 4. 访问网站

- 前台: `http://your-domain.com/`
- 后台: `http://your-domain.com/admin/login.php`

### 手动安装

如果你想手动配置：

1. 创建数据库并导入 `database.sql`
2. 复制 `config.example.php` 为 `config.php`，填写配置信息
3. 设置管理员密码哈希并插入数据库
4. 创建 `install.lock` 文件

详细配置参考 `config.example.php` 文件中的注释说明。

## 使用指南

### 用户使用

1. 访问 `/user/login.php` 登录（需管理员先创建账户）
2. 点击"上传商家"，填写商家信息
3. 上传商家图片（需要配置对象存储）
4. 填写多维评分（0-10分），系统自动计算综合评分
5. 在"我的商家"页面查看、编辑或删除自己创建的商家
6. 商家详情页会显示"编辑"和"删除"按钮（仅限创建者）

### 管理员使用

1. 访问 `/user/login.php`，切换到"管理员登录"标签
2. 点击"管理用户"，可以创建普通用户账户
3. 在后台管理所有商家（不受用户权限限制）
4. 可随时编辑或删除任何商家

### 前台使用

- **排行榜**: 访问首页或 `/ranking.php`，可按校区、评分维度筛选
- **随机发现**: 访问 `/discover.php`，点击"换一批"随机显示商家
- **商家详情**: 点击商家名称查看详细信息和雷达图
- **用户管理**: 用户登录后可查看自己创建的商家列表

## 对象存储配置

支持多种对象存储服务，在安装向导中配置或手动编辑 `config.php`。

### AWS S3

创建 IAM 用户，授予 `s3:PutObject` 和 `s3:GetObject` 权限。

### Cloudflare R2

获取账户 ID 和 API Token，配置端点：`https://<account-id>.r2.cloudflarestorage.com`

### 多吉云

国内对象存储服务，提供 CDN 加速，访问速度快。

1. 注册 [多吉云](https://www.dogecloud.com/)
2. 获取 AccessKey 和 SecretKey
3. 创建存储空间
4. 启用多吉云配置：`DOGE_ENABLED = true`

**自定义域名**：在多吉云控制台绑定域名到存储空间，然后在配置中填写 `S3_CUSTOM_DOMAIN`。

### MinIO / 其他 S3 兼容服务

配置正确的端点地址即可使用。

## 常见问题

### 1. 图片上传失败

- 检查对象存储配置是否正确
- 检查存储桶是否公开访问
- 确认PHP文件上传大小限制
- 多吉云：确保存储空间已创建且名称正确

### 2. 数据库连接失败

- 检查数据库配置信息
- 确保数据库服务正在运行
- 检查用户权限

### 3. 重定向问题

- 确保 `.htaccess` 文件存在
- 检查 `mod_rewrite` 是否启用
- 检查 Apache 配置

### 4. 雷达图不显示

- 检查网络连接（需要加载 Chart.js CDN）
- 查看浏览器控制台错误信息

## 安全建议

1. 修改默认密码，使用强密码
2. 配置 SSL 证书启用 HTTPS
3. 设置适当的文件和目录权限
4. 定期备份数据库
5. 定期更新 PHP 和 Composer 依赖
6. 生产环境安装完成后，删除 `install.php` 文件

## 目录结构

```
eatsysu/
├── admin/                  # 后台管理目录
│   ├── login.php           # 管理员登录（重定向到用户登录页）
│   ├── dashboard.php       # 管理员仪表板
│   ├── add-restaurant.php  # 添加商家
│   ├── edit-restaurant.php  # 编辑商家
│   ├── delete-restaurant.php # 删除商家
│   ├── restaurants.php      # 商家列表
│   ├── users.php           # 用户管理
│   └── logout.php          # 管理员登出
├── user/                   # 用户系统目录
│   ├── login.php           # 用户/管理员登录页面
│   ├── my-restaurants.php   # 我的商家列表
│   ├── edit-my-restaurant.php # 编辑我的商家
│   ├── delete-my-restaurant.php # 删除我的商家
│   └── user-logout.php     # 用户登出
├── includes/
│   ├── functions.php       # 核心函数库
│   └── install-check.php   # 安装检查
├── assets/
│   ├── css/
│   │   └── style.css       # 样式文件
│   └── js/
│       └── main.js         # JavaScript文件
├── config.php              # 配置文件（安装后生成）
├── config.example.php      # 配置文件示例
├── database.sql            # 数据库结构
├── composer.json           # Composer依赖配置
├── install.php             # 安装向导
├── .htaccess               # Apache配置
├── index.php              # 首页
├── ranking.php            # 排行榜
├── discover.php           # 发现美食
├── submit.php             # 上传商家
└── restaurant.php          # 商家详情
```

## 自定义开发

### 修改评分权重

编辑 `includes/functions.php` 中的 `$weights` 数组调整各维度权重。

### 添加新校区

编辑 `includes/functions.php` 中的 `getCampusList()` 函数。

### 数据库表结构

- `admins` - 管理员账户表
- `users` - 普通用户账户表（由管理员创建）
- `restaurants` - 商家信息表（包含 `user_id` 字段关联用户）
- `views` - 浏览记录表

**权限说明**:
- 用户只能查看、编辑、删除自己创建的商家（通过 `user_id` 字段关联）
- 管理员可以管理所有商家和所有用户

## 许可证

MIT License

祝你使用愉快！🍜✨