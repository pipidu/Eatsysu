<?php
require_once __DIR__ . '/../includes/functions.php';

// 检查登录状态
if (!isAdminLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 验证必填字段
        $required = ['name', 'campus', 'taste_score', 'price_score', 'packaging_score', 'speed_score'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                throw new Exception('请填写所有必填字段');
            }
        }

        // 处理图片上传
        $imageUrl = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = uploadFile($_FILES['image'], 'restaurants');
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
            'packaging_score' => floatval($_POST['packaging_score']),
            'speed_score' => floatval($_POST['speed_score'])
        ];

        // 添加商家
        $restaurantId = addRestaurant($data);

        $success = '商家添加成功！';
        // 重置表单
        $_POST = [];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$campuses = getCampusList();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加商家 - 双鸭山大学美食分享</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: #fff;
            min-height: 100vh;
        }
        .header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 18px;
            font-weight: 500;
        }
        .header .back-link {
            color: #005826;
            text-decoration: none;
            font-size: 13px;
        }
        .header .back-link:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .form-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 28px;
        }
        .form-title {
            color: #333;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .form-group label span.required {
            color: #c00;
        }
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #005826;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .checkbox-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
        .score-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .score-inputs.three-cols {
            grid-template-columns: repeat(3, 1fr);
        }
        .score-input {
            background: #f5f5f5;
            padding: 14px;
            border-radius: 4px;
        }
        .score-input label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .score-input input {
            width: 100%;
        }
        .score-input .hint {
            font-size: 11px;
            color: #999;
            margin-top: 4px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #005826;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #00441e;
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #f5f5f5;
        }
        .alert {
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .alert-success {
            background: #f0f9f0;
            color: #005826;
            border-left: 3px solid #005826;
        }
        .alert-error {
            background: #fef2f2;
            color: #c00;
            border-left: 3px solid #c00;
        }
        .upload-area {
            border: 1px dashed #ddd;
            border-radius: 4px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .upload-area:hover {
            border-color: #005826;
        }
        .upload-area input[type="file"] {
            display: none;
        }
        .upload-area-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }
        .upload-area-text {
            color: #666;
            font-size: 13px;
        }
        .upload-area-hint {
            color: #999;
            font-size: 11px;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>添加商家</h1>
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
                    <input type="text" name="name" required placeholder="例如：南校区东门奶茶店" value="<?php echo h($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><span class="required">*</span>所在校区</label>
                    <select name="campus" required>
                        <option value="">请选择校区</option>
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?php echo h($campus); ?>" <?php echo (isset($_POST['campus']) && $_POST['campus'] === $campus) ? 'selected' : ''; ?>>
                                <?php echo h($campus); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>具体位置</label>
                    <input type="text" name="location" placeholder="例如：东门外100米" value="<?php echo h($_POST['location'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>商家图片</label>
                    <label class="upload-area">
                        <input type="file" name="image" accept="image/*">
                        <div class="upload-area-icon">📷</div>
                        <div class="upload-area-text">点击上传图片</div>
                        <div class="upload-area-hint">支持 JPG、PNG 格式，建议尺寸 800x600</div>
                    </label>
                </div>

                <div class="form-group">
                    <label>推荐点单平台</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="dine_in" <?php echo (isset($_POST['platforms']) && in_array('dine_in', $_POST['platforms'])) ? 'checked' : ''; ?>>
                            堂食
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="jd" <?php echo (isset($_POST['platforms']) && in_array('jd', $_POST['platforms'])) ? 'checked' : ''; ?>>
                            京东
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="meituan" <?php echo (isset($_POST['platforms']) && in_array('meituan', $_POST['platforms'])) ? 'checked' : ''; ?>>
                            美团
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="platforms[]" value="taobao" <?php echo (isset($_POST['platforms']) && in_array('taobao', $_POST['platforms'])) ? 'checked' : ''; ?>>
                            淘宝
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>联系电话</label>
                    <input type="tel" name="phone" placeholder="选填" value="<?php echo h($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>商家介绍</label>
                    <textarea name="description" placeholder="介绍一下这家店的特点、推荐菜品等..."><?php echo h($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label><span class="required">*</span>多维评分 (0-10分)</label>
                    <div class="score-inputs">
                        <div class="score-input">
                            <label>口味评分</label>
                            <input type="number" name="taste_score" min="0" max="10" step="0.1" required value="<?php echo h($_POST['taste_score'] ?? ''); ?>">
                            <div class="hint">食物的味道和品质</div>
                        </div>
                        <div class="score-input">
                            <label>价格评分</label>
                            <input type="number" name="price_score" min="0" max="10" step="0.1" required value="<?php echo h($_POST['price_score'] ?? ''); ?>">
                            <div class="hint">价格合理性和性价比</div>
                        </div>
                        <div class="score-input">
                            <label>包装评分</label>
                            <input type="number" name="packaging_score" min="0" max="10" step="0.1" required value="<?php echo h($_POST['packaging_score'] ?? ''); ?>">
                            <div class="hint">包装美观和完整性</div>
                        </div>
                        <div class="score-input">
                            <label>速度评分</label>
                            <input type="number" name="speed_score" min="0" max="10" step="0.1" required value="<?php echo h($_POST['speed_score'] ?? ''); ?>">
                            <div class="hint">出餐速度和等待时间</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存商家</button>
                    <a href="/admin/dashboard.php" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
