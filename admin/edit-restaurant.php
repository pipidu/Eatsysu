<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /admin/dashboard.php');
    exit;
}

$restaurant = getRestaurantById($id);
if (!$restaurant) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 处理图片上传
        $imageUrl = $restaurant['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = uploadToS3($_FILES['image']);
        }
        
        // 准备平台数据
        $platforms = [
            'phone' => $_POST['phone'] ?? '',
            'dine_in' => isset($_POST['platforms']) && in_array('dine_in', $_POST['platforms']),
            'jd' => isset($_POST['platforms']) && in_array('jd', $_POST['platforms']),
            'meituan' => isset($_POST['platforms']) && in_array('meituan', $_POST['platforms']),
            'taobao' => isset($_POST['platforms']) && in_array('taobao', $_POST['platforms'])
        ];
        
        // 准备商家数据
        $data = [
            'name' => trim($_POST['name']),
            'campus' => $_POST['campus'],
            'location' => trim($_POST['location'] ?? ''),
            'platforms' => $platforms,
            'description' => trim($_POST['description'] ?? ''),
            'image_url' => $imageUrl,
            'taste_score' => floatval($_POST['taste_score']),
            'price_score' => floatval($_POST['price_score']),
            'service_score' => floatval($_POST['service_score']),
            'health_score' => floatval($_POST['health_score'])
        ];
        
        // 更新商家
        updateRestaurant($id, $data);
        
        $success = '商家更新成功！';
        $restaurant = getRestaurantById($id); // 重新获取数据
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$campuses = getCampusList();
$platforms = json_decode($restaurant['platforms'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑商家 - 中山大学美食分享</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 20px;
        }
        .header .back-link {
            color: #667eea;
            text-decoration: none;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .form-title {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-group label span.required {
            color: #dc2626;
        }
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        .score-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .score-input {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
        }
        .score-input label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .score-input input {
            width: 100%;
        }
        .score-input .hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #e1e1e1;
        }
        .btn-secondary:hover {
            background: #f9f9f9;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d1fae5;
            color: #059669;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
        }
        .current-image {
            margin-top: 12px;
        }
        .current-image img {
            max-width: 200px;
            border-radius: 8px;
        }
        .current-image p {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>编辑商家: <?php echo h($restaurant['name']); ?></h1>
        <a href="/admin/dashboard.php" class="back-link">← 返回控制台</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2 class="form-title">商家信息</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo h($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo h($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><span class="required">*</span>商家名称</label>
                    <input type="text" name="name" required value="<?php echo h($restaurant['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label><span class="required">*</span>所在校区</label>
                    <select name="campus" required>
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?php echo h($campus); ?>" <?php echo $restaurant['campus'] === $campus ? 'selected' : ''; ?>>
                                <?php echo h($campus); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>具体位置</label>
                    <input type="text" name="location" value="<?php echo h($restaurant['location'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>商家图片</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if ($restaurant['image_url']): ?>
                        <div class="current-image">
                            <img src="<?php echo h($restaurant['image_url']); ?>" alt="商家图片">
                            <p>当前图片（上传新图片将替换）</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>推荐点单平台</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="dine_in" <?php echo ($platforms['dine_in'] ?? false) ? 'checked' : ''; ?>>
                            堂食
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="jd" <?php echo ($platforms['jd'] ?? false) ? 'checked' : ''; ?>>
                            京东
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="meituan" <?php echo ($platforms['meituan'] ?? false) ? 'checked' : ''; ?>>
                            美团
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="taobao" <?php echo ($platforms['taobao'] ?? false) ? 'checked' : ''; ?>>
                            淘宝
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>联系电话</label>
                    <input type="tel" name="phone" value="<?php echo h($platforms['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>商家介绍</label>
                    <textarea name="description"><?php echo h($restaurant['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><span class="required">*</span>多维评分 (0-10分)</label>
                    <div class="score-inputs">
                        <div class="score-input">
                            <label>口味评分</label>
                            <input type="number" name="taste_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['taste_score']); ?>">
                            <div class="hint">食物的味道和品质</div>
                        </div>
                        <div class="score-input">
                            <label>价格评分</label>
                            <input type="number" name="price_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['price_score']); ?>">
                            <div class="hint">价格合理性和性价比</div>
                        </div>
                        <div class="score-input">
                            <label>服务评分</label>
                            <input type="number" name="service_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['service_score']); ?>">
                            <div class="hint">服务态度和效率</div>
                        </div>
                        <div class="score-input">
                            <label>健康评分</label>
                            <input type="number" name="health_score" min="0" max="10" step="0.1" required value="<?php echo h($restaurant['health_score']); ?>">
                            <div class="hint">食材新鲜和健康程度</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/admin/dashboard.php" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
