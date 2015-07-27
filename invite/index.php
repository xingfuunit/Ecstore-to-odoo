<?php

error_reporting(E_ERROR);
include ("common.php");
$s = isset($_GET['s']) ? $_GET['s'] : -1;

$isMobile = isMobile();
$isMobile = true;
function isMobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 

$this_url =  ROOT_URL.'/?s='.$s;

$file_url = "cache.php";
$fp = fopen($file_url,'r');//读 
if(flock($fp , LOCK_EX)){ 
	$cfg = fread($fp,filesize($file_url));
	$cfg = str_replace("<?php exit;?>",'',$cfg);
	$cfg = unserialize($cfg);
	$name = $cfg[$s];
	flock($fp , LOCK_UN);
} else {
	$name = '';
}
?>
<html class="ks-webkit537 ks-webkit ks-chrome21 ks-chrome ks-webkit537 ks-webkit ks-chrome38 ks-chrome">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<title>品珍鲜活</title>
<meta charset="utf-8">
<?php if ($isMobile) : ?>
<link rel="stylesheet" href="<?php echo ROOT_URL;?>/images/mobi.css">
<?php else : ?>
<link rel="stylesheet" href="<?php echo ROOT_URL;?>/images/pc.css">
<?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no">
<meta http-equiv="Cache-Control" content="max-age=0">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="description" content="品珍鲜活邀请函" />
<script>
      var imgUrl = '<?php echo ROOT_URL;?>/images/logo.jpg';  // 分享后展示的一张图片
        var lineLink = '<?php echo $this_url;?>'; // 点击分享后跳转的页面地址
        var descContent = "2014年11月22日下午2点30分，国通物流园总部大楼，敬待光临！";  // 分享后的描述信息
        var shareTitle = '品珍发给<?php echo $name;?>的邀请函';  // 分享后的标题
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

<table id="loading"><tr><td style="text-align:center;background-color:#1A1A1A;"><img src="<?php echo ROOT_URL;?>/images/loaderc.gif" class="loading_img"></td></tr></table>  
<img src="<?php echo ROOT_URL;?>/img.php?s=<?php echo $s;?>" id="box_bg" style="display:none">
<div class="box" id="bb" style='background-size: 100% 100%;'>
	<canvas id="cas" ></canvas>
</div>


<section class="p-index sectionh1" style="display: block;">
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/img.php?s=<?php echo $s;?>" style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; display: none;">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
	
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/2.jpg" style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; display: none;">
  <a href="javascript:;" class="help-up"></a> </div>
</section>

<section class="m-page m-page2 hide sectionh1" style=" top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/3.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>

<!--
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/04.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>


<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/05.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>

<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/06.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
	
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/07.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
	
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/08.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
	
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/09.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
	
<section class="m-page m-page2 hide sectionh1" style="top: 0px;">
  <div class="m-img m-img01" style="background-size:100% 100%;">
  <img class="animated fadeInDown J_fTxt" src="<?php echo ROOT_URL;?>/images/10.jpg" style="position:absolute; left:0px; top:0px; width: 100%; height:100%; display: none; ">
  <a href="javascript:;" class="help-up"></a> </div>
</section>
-->
	
<audio src=""  autoplay="autoplay" id="video1" loop="loop"></audio>


<a href="javascript:;" class="player-button" style="display:none;"> <span class="player-tip" style="display:none;">点击开启/关闭音乐</span> </a>
</section>


<!-- 引入脚本 --> 


<script src="<?php echo ROOT_URL;?>/images/jquery-1.8.2.min.js"></script> 
<script src="<?php echo ROOT_URL;?>/images/jquery.easing.1.3.js"></script> 
<script src="<?php echo ROOT_URL;?>/images/hd_main.js"></script> 

<script type="text/javascript" charset="utf-8">

		function play_audio () {
			$("#video1").attr("src","<?php echo ROOT_URL;?>/images/music.mp3");
			$(".player-button").show();
			var myVideo=document.getElementById("video1");
			var status_bool=true;
			myVideo.play();
			$(".player-button").click(function () {

					if(status_bool==true){
						myVideo.pause();
						$(this).addClass('player-button-stop');
						status_bool=false;
					}else{
						 myVideo.play();
						$(this).removeClass('player-button-stop');
						status_bool=true;
					}
			})
		}


	
		
		var canvas = document.getElementById("cas"),ctx = canvas.getContext("2d");
		var x1,y1,a=40,timeout,totimes = 100,jiange = 30;
		canvas.width = document.getElementById("bb").clientWidth;
		canvas.height = document.getElementById("bb").clientHeight;
		var load_img_num = 0;
		var img = new Image();
		img.src = "<?php echo ROOT_URL;?>/images/0.jpg";
		img.onload = function(){
			load_img_num++;
		}
		$("#box_bg").load(function() {
			load_img_num++;

		});
		var time_add = 0;
		setTimeout("start_can()",1000);
		function start_can() {
			time_add++;
			if (load_img_num >= 2 || time_add>4) {
				$(".box").css("background",'url("<?php echo ROOT_URL;?>/img.php?s=<?php echo $s;?>") no-repeat');
				$(".box").css("background-size",'100% 100%');
				
				$("#loading").remove();
				ctx.drawImage(img,0,0,canvas.width,canvas.height);
				tapClip();
				$(".box").addClass('box_bg');
				play_audio();
			} else {
				setTimeout("start_can()",1000);
			}
		}
		
		//通过修改globalCompositeOperation来达到擦除的效果
		function tapClip(){
			var hastouch = "ontouchstart" in window?true:false,
				tapstart = hastouch?"touchstart":"mousedown",
				tapmove = hastouch?"touchmove":"mousemove",
				tapend = hastouch?"touchend":"mouseup";
				
			ctx.lineCap = "round";
			ctx.lineJoin = "round";
			ctx.lineWidth = a*2;
			ctx.globalCompositeOperation = "destination-out";
			
			canvas.addEventListener(tapstart , function(e){
				clearTimeout(timeout)
				e.preventDefault();
				
				x1 = hastouch?e.targetTouches[0].pageX:e.clientX-canvas.offsetLeft;
				y1 = hastouch?e.targetTouches[0].pageY:e.clientY-canvas.offsetTop;
				
				ctx.save();
				ctx.beginPath()
				ctx.arc(x1,y1,1,0,2*Math.PI);
				ctx.fill();
				ctx.restore();
				
				canvas.addEventListener(tapmove , tapmoveHandler);
				canvas.addEventListener(tapend , function(){
					canvas.removeEventListener(tapmove , tapmoveHandler);
					
					timeout = setTimeout(function(){
					
						var imgData = ctx.getImageData(0,0,canvas.width,canvas.height);
						var dd = 0;
						
						for(var x=0;x<imgData.width;x+=jiange){
							for(var y=0;y<imgData.height;y+=jiange){
								var i = (y*imgData.width + x)*4;
								if(imgData.data[i+3] >0){
									dd++
								}
							}
						}
						
						if(dd/(imgData.width*imgData.height/(jiange*jiange))<0.6){
							initPage();
							canvas.className = "noOp";
							$(".box").remove();
						}
					},totimes)
				});
				function tapmoveHandler(e){
					clearTimeout(timeout)
					e.preventDefault()
					x2 = hastouch?e.targetTouches[0].pageX:e.clientX-canvas.offsetLeft;
					y2 = hastouch?e.targetTouches[0].pageY:e.clientY-canvas.offsetTop;
					
					ctx.save();
					ctx.moveTo(x1,y1);
					ctx.lineTo(x2,y2);
					ctx.stroke();
					ctx.restore()
					
					x1 = x2;
					y1 = y2;
				}
			})
		}
	
	</script>



</body></html>