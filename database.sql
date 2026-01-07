-- 创建数据库
CREATE DATABASE IF NOT EXISTS eatsysu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eatsysu;

-- 管理员表
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 商家表
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    campus VARCHAR(50) NOT NULL,
    location VARCHAR(200),
    platforms JSON COMMENT '推荐点单平台：{"phone": "电话号码", "dine_in": true, "jd": true, "meituan": true, "taobao": true}',
    description TEXT,
    image_url VARCHAR(500),
    taste_score DECIMAL(3,1) DEFAULT 0 COMMENT '口味评分',
    price_score DECIMAL(3,1) DEFAULT 0 COMMENT '价格评分',
    packaging_score DECIMAL(3,1) DEFAULT 0 COMMENT '包装评分',
    speed_score DECIMAL(3,1) DEFAULT 0 COMMENT '速度评分',
    overall_score DECIMAL(3,1) DEFAULT 0 COMMENT '综合评分',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campus (campus),
    INDEX idx_overall_score (overall_score DESC)
);

-- 用户浏览记录表（可选，用于统计）
CREATE TABLE IF NOT EXISTS views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_by INT COMMENT '创建该用户的管理员ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- 插入默认管理员（密码需用 password_hash 处理，这里先用明文占位）
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
