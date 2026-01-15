<?php
// ck.php - 照片查看页面
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');

// 获取查询ID
$id = $_GET['id'] ?? '';

if (empty($id)) {
    // 如果没有ID，显示查询表单
    showQueryForm();
    exit;
}

// 查询照片
$photos = findPhotos($id);

// 显示结果
showResults($id, $photos);

// ====================================
// 函数定义部分
// ====================================

/**
 * 显示查询表单
 */
function showQueryForm() {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>照片查询 - 冰嘉网恋照妖镜</title>
    <style>
        body {
            background: linear-gradient(135deg, #f0f5ff 0%, #e6f0ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(30, 41, 59, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        h1 {
            color: #1e3b8c;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .input-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #3b66f6;
            box-shadow: 0 0 0 3px rgba(59, 102, 246, 0.1);
        }
        
        button {
            background: linear-gradient(135deg, #3b66f6 0%, #1e3b8c 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 102, 246, 0.25);
        }
        
        .tips {
            margin-top: 25px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 14px;
            color: #64748b;
            text-align: left;
            line-height: 1.6;
        }
        
        .tips h3 {
            color: #3b66f6;
            margin-bottom: 10px;
        }
        
        .error {
            color: #ef4444;
            background: #fef2f2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 照片查询系统</h1>
        
        <?php if(isset($_GET['error'])): ?>
        <div class="error">
            <strong>错误：</strong> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>
        
        <form action="" method="GET">
            <div class="input-group">
                <label for="id">请输入查询ID：</label>
                <input type="text" id="id" name="id" required 
                       placeholder="输入生成链接时使用的独立密码" 
                       value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
            </div>
            <button type="submit">查询照片</button>
        </form>
        
        <div class="tips">
            <h3>📝 使用说明：</h3>
            <p>1. 输入生成链接时使用的独立密码</p>
            <p>2. 点击查询按钮查看照片</p>
            <p>3. 系统会自动查找该ID下的所有照片</p>
            <p>4. 如果查询失败，请检查ID是否正确</p>
        </div>
    </div>
</body>
</html>
<?php
}

/**
 * 查找照片文件
 */
function findPhotos($id) {
    $photos = [];
    
    // 查找 images 目录下的照片
    if (is_dir('images')) {
        // 1. 查找直接以ID命名的文件
        $directFile = "{$id}.jpg";
        if (file_exists($directFile)) {
            $photos[] = [
                'file' => $directFile,
                'time' => filemtime($directFile),
                'size' => filesize($directFile)
            ];
        }
        
        // 2. 查找带时间戳的文件
        $pattern = "{$id}_*.jpg";
        $files = glob($pattern);
        
        foreach ($files as $file) {
            $photos[] = [
                'file' => $file,
                'time' => filemtime($file),
                'size' => filesize($file)
            ];
        }
        
        // 3. 查找所有包含ID的文件
        $allFiles = glob("*.jpg");
        foreach ($allFiles as $file) {
            $filename = basename($file);
            if (strpos($filename, $id) !== false) {
                // 避免重复添加
                $alreadyExists = false;
                foreach ($photos as $photo) {
                    if ($photo['file'] === $file) {
                        $alreadyExists = true;
                        break;
                    }
                }
                
                if (!$alreadyExists) {
                    $photos[] = [
                        'file' => $file,
                        'time' => filemtime($file),
                        'size' => filesize($file)
                    ];
                }
            }
        }
    }
    
    // 按时间排序（最新的在前面）
    usort($photos, function($a, $b) {
        return $b['time'] - $a['time'];
    });
    
    return $photos;
}

/**
 * 显示查询结果
 */
function showResults($id, $photos) {
    // 处理页码
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $total = count($photos);
    $totalPages = ceil($total / $perPage);
    
    // 分页处理
    $offset = ($page - 1) * $perPage;
    $currentPhotos = array_slice($photos, $offset, $perPage);
    
    // 读取您的HTML模板并动态插入内容
    $html = file_get_contents('ck_template.html');
    
    if (!$html) {
        // 如果模板文件不存在，使用备用HTML
        showBackupResults($id, $photos, $page, $totalPages, $total);
        return;
    }
    
    // 替换变量
    $replacements = [
        '{{ID}}' => htmlspecialchars($id),
        '{{TOTAL_PHOTOS}}' => $total,
        '{{CURRENT_PAGE}}' => $page,
        '{{TOTAL_PAGES}}' => $totalPages,
    ];
    
    // 替换状态消息
    if ($total > 0) {
        $statusClass = 'success';
        $statusIcon = 'fa-check-circle';
        $statusText = "找到 {$total} 张照片";
    } else {
        $statusClass = 'warning';
        $statusIcon = 'fa-folder-open';
        $statusText = "ID为 <strong>{$id}</strong> 的目录下没有找到任何图片";
    }
    
    $html = str_replace('status-message warning', "status-message {$statusClass}", $html);
    $html = str_replace('fa-folder-open', $statusIcon, $html);
    $html = str_replace('ID为 <strong></strong> 的目录下没有找到任何图片', $statusText, $html);
    
    // 生成图片HTML
    $imagesHtml = '';
    if (!empty($currentPhotos)) {
        foreach ($currentPhotos as $index => $photo) {
            $imageName = basename($photo['file']);
            $imageTime = date('Y-m-d H:i:s', $photo['time']);
            $imageSize = formatBytes($photo['size']);
            $imageNumber = $offset + $index + 1;
            
            $imagesHtml .= '
            <div class="image-frame-vertical">
                <img src="' . htmlspecialchars($photo['file']) . '" 
                     alt="照片 ' . $imageNumber . '" 
                     onerror="this.onerror=null; this.src=\'data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 800 500\"><rect width=\"100%\" height=\"100%\" fill=\"#f8fafc\"/><text x=\"50%\" y=\"50%\" font-family=\"Segoe UI\" font-size=\"24\" fill=\"#94a3b8\" text-anchor=\"middle\" dominant-baseline=\"middle\">图片已删除或损坏</text></svg>\'">
                <div class="image-info-vertical">
                    <div class="image-name-vertical">
                        <i class="fas fa-camera"></i> 照片 #' . $imageNumber . '
                    </div>
                    <div class="image-time-vertical">
                        <i class="far fa-clock"></i> ' . $imageTime . '
                        <span style="margin: 0 10px">•</span>
                        <i class="fas fa-weight-hanging"></i> ' . $imageSize . '
                        <span style="margin: 0 10px">•</span>
                        <i class="fas fa-hashtag"></i> ' . $imageName . '
                    </div>
                </div>
            </div>';
        }
    } else {
        $imagesHtml = '
        <div class="no-images">
            <i class="far fa-images"></i>
            <p>暂无图片可显示</p>
            <p style="font-size: 15px; margin-top: 12px; color: #64748b;">请检查ID是否正确，或上传新的图片</p>
        </div>';
    }
    
    // 替换图片区域
    $html = preg_replace('/<div class="no-images">.*?<\/div>/s', $imagesHtml, $html);
    
    // 生成分页HTML
    $paginationHtml = generatePagination($page, $totalPages, $id);
    $html = preg_replace('/<div class="pagination">.*?<\/div>/s', $paginationHtml, $html);
    
    // 输出最终HTML
    echo $html;
}

/**
 * 备用结果显示（当模板文件不存在时）
 */
function showBackupResults($id, $photos, $page, $totalPages, $total) {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>照片查看 - 冰嘉网恋照妖镜</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f5ff; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 15px; max-width: 1000px; margin: 0 auto; }
        h1 { color: #1e3b8c; text-align: center; margin-bottom: 30px; }
        .status { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .success { background: #d1fae5; color: #065f46; }
        .warning { background: #fef3c7; color: #92400e; }
        .photos { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .photo { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
        .photo img { width: 100%; height: auto; display: block; }
        .info { padding: 15px; background: #f8fafc; font-size: 14px; color: #64748b; }
        .pagination { margin-top: 30px; text-align: center; }
        .pagination a, .pagination span { display: inline-block; padding: 8px 16px; margin: 0 5px; border-radius: 6px; text-decoration: none; }
        .pagination a { background: #3b66f6; color: white; }
        .pagination .current { background: #1e3b8c; color: white; }
        .empty { text-align: center; padding: 50px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 照片查看系统 - ID: <?php echo htmlspecialchars($id); ?></h1>
        
        <div class="status <?php echo $total > 0 ? 'success' : 'warning'; ?>">
            <?php if($total > 0): ?>
                ✅ 找到 <?php echo $total; ?> 张照片
            <?php else: ?>
                ⚠️ 未找到照片，请检查ID是否正确
            <?php endif; ?>
        </div>
        
        <?php if($total > 0): ?>
        <div class="photos">
            <?php foreach($photos as $index => $photo): ?>
            <div class="photo">
                <img src="<?php echo htmlspecialchars($photo['file']); ?>" 
                     alt="照片 <?php echo $index + 1; ?>"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="info" style="display:none; background:#fef2f2; color:#dc2626;">
                    图片加载失败
                </div>
                <div class="info">
                    时间: <?php echo date('Y-m-d H:i:s', $photo['time']); ?><br>
                    大小: <?php echo formatBytes($photo['size']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?id=<?php echo urlencode($id); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty">
            <h2>📭 没有照片</h2>
            <p>可能的原因：</p>
            <ul style="text-align: left; display: inline-block; margin: 20px 0;">
                <li>ID输入错误</li>
                <li>照片尚未上传</li>
                <li>照片已过期被删除</li>
                <li>系统文件存储错误</li>
            </ul>
            <p><a href="?">重新查询</a></p>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="index.html">返回首页</a> | 
            <a href="?">重新查询</a>
        </div>
    </div>
</body>
</html>
<?php
}

/**
 * 生成分页HTML
 */
function generatePagination($currentPage, $totalPages, $id) {
    if ($totalPages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // 上一页
    if ($currentPage > 1) {
        $html .= sprintf('<a href="?id=%s&page=%d"><i class="fas fa-chevron-left"></i> 上一页</a>', 
                        urlencode($id), $currentPage - 1);
    } else {
        $html .= '<span class="disabled"><i class="fas fa-chevron-left"></i> 上一页</span>';
    }
    
    // 页码
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<a href="?id=' . urlencode($id) . '&page=1">1</a>';
        if ($start > 2) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $html .= '<a href="?id=' . urlencode($id) . '&page=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
        $html .= '<a href="?id=' . urlencode($id) . '&page=' . $totalPages . '">' . $totalPages . '</a>';
    }
    
    // 下一页
    if ($currentPage < $totalPages) {
        $html .= sprintf('<a href="?id=%s&page=%d">下一页 <i class="fas fa-chevron-right"></i></a>', 
                        urlencode($id), $currentPage + 1);
    } else {
        $html .= '<span class="disabled">下一页 <i class="fas fa-chevron-right"></i></span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 格式化文件大小
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>