<?php
error_reporting(E_ERROR);
include ("common.php");
$alert_msg = '';
$goto = '';
if ($_POST['act']) {
	$username = $_POST['username'];
	$match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/',$username); 
	if (empty($username)) {
		$alert_msg = '请输入被邀请人姓名';
	} else if (empty($match)) {
		$alert_msg = '被邀请人姓名只允许输入中文，英文，数字，下划线';
	} else {
		$file_url = "cache.php";
		$fp = fopen($file_url,'r');//读 
		if(flock($fp , LOCK_EX)){  //锁文件
			$cfg = fread($fp,filesize($file_url));
			$cfg = str_replace("<?php exit;?>",'',$cfg);
			$cfg = unserialize($cfg);
			if (empty($cfg)) {
			$cfg = array(0=>$username);
			} else {
			$cfg[] = $username;
			}
		  
			 //把数据存入数组             
			file_put_contents($file_url,'<?php exit;?>'.serialize($cfg)); 
			end($cfg);
			$go_id = key($cfg);
		  	
		  	$goto =ROOT_URL.'/?s='.$go_id;
		  	flock($fp , LOCK_UN); //解锁
	  	}
	  	fclose($fp);  
	}
}



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>品珍邀请函生成系统</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="resource/css/bootstrap.min.css">
<link rel="stylesheet" href="resource/css/bootstrap-theme.min.css">
<script src="resource/js/jquery.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<?php if ($alert_msg) : ?>
	<script>
		alert("<?php echo $alert_msg;?>");
		history.back();
	</script>
<?php endif; ?>
		
<?php if ($goto) : ?>
	<script>
		alert("生成成功");
		window.location.href="<?php echo $goto;?>";
	</script>
<?php endif; ?>
<meta name="description" content="品珍鲜活邀请函生成系统" />
<script>
      var imgUrl = '<?php echo ROOT_URL;?>/images/logo.jpg';  // 分享后展示的一张图片
        var lineLink = '<?php echo ROOT_URL;?>/create.php'; // 点击分享后跳转的页面地址
        var descContent = "品珍鲜活邀请函生成系统！";  // 分享后的描述信息
        var shareTitle = '品珍鲜活';  // 分享后的标题
        var appid = '';  // 应用id,如果有可以填，没有就留空
        
        function shareFriend() {
            WeixinJSBridge.invoke('sendAppMessage',{
                "appid": appid,
                "img_url": imgUrl,
                "img_width": "200",
                "img_height": "200",
                "link": lineLink,
                "desc": descContent,
                "title": shareTitle
            }, function(res) {
                //_report('send_msg', res.err_msg);  // 这是回调函数，必须注释掉
            })
        }
        function shareTimeline() {
            WeixinJSBridge.invoke('shareTimeline',{
                "img_url": imgUrl,
                "img_width": "200",
                "img_height": "200",
                "link": lineLink,
                "desc": descContent,
                "title": shareTitle
            }, function(res) {
                   //_report('timeline', res.err_msg); // 这是回调函数，必须注释掉
            });
        }
        function shareWeibo() {
            WeixinJSBridge.invoke('shareWeibo',{
                "content": descContent,
                "url": lineLink,
            }, function(res) {
                //_report('weibo', res.err_msg);
            });
        }
        // 当微信内置浏览器完成内部初始化后会触发WeixinJSBridgeReady事件。
        document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
            // 发送给好友
            WeixinJSBridge.on('menu:share:appmessage', function(argv){
                shareFriend();
            });
            // 分享到朋友圈
            WeixinJSBridge.on('menu:share:timeline', function(argv){
                shareTimeline();
            });
            // 分享到微博
            WeixinJSBridge.on('menu:share:weibo', function(argv){
                shareWeibo();
            });
        }, false);
       
</script>
</head>

<body>
<form action="" method="post">
<input type="hidden" value="true" name="act">
<ul class="list-group">
  <li class="list-group-item active">邀请函生成系统</li>
  <li class="list-group-item">请输入被邀请人姓名</li>
  <li class="list-group-item"><input type="text" name="username" class="form-control" placeholder="中文"></li>
  <li class="list-group-item"><button type="submit" class="btn btn-primary">生成邀请函</button></li>
  <li class="list-group-item">注：姓名请勿输入英文，生成邀请函之后，请点击浏览器右上角图标，将邀请函通过微信或短信"发送给朋友"</li>
</ul>
</form>
</body>
</html>
