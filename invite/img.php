<?php
error_reporting(E_ERROR);
$s = intval($_GET['s']);

$file_url = "cache.php";
$fp = fopen($file_url,'r');//读 
if(flock($fp , LOCK_EX)){ 
	$cfg = fread($fp,filesize($file_url));
	$cfg = str_replace("<?php exit;?>",'',$cfg);
	$cfg = unserialize($cfg);
	flock($fp , LOCK_UN);
} else {
	$cfg = array();
}

if ($s == -1) {
	$name = '';
} else {
	if (empty($cfg[$s])) {
		$name = '';
	} else {
		$name = $cfg[$s];
		
		$match = preg_match('/^(?!_|\s\')[\x80-\xff\s\']+$/',$name); 
		/*
		if ($match) {
			if (strlen($name) == 6) {
				$showX = 178;
			} else if (strlen($name) == 7) {
				$showX = 265;
			} else if (strlen($name) == 3) {
				$showX = 210;
			} else if (strlen($name) == 12) {
				$showX = 215;
			} else if (strlen($name) == 15) {
				$showX = 185;
			}
		}*/
	}
}

//使用  
new showChinaText($name,82,200);  


/* 
param $image   图象资源 
param size     字体大小 
param angle    字体输出角度 
param showX    输出位置x坐标 
param showY    输出位置y坐标 
param font    字体文件位置 
param content 要在图片里显示的内容 
*/  
class showChinaText {  
    var $text = '图象资源';  
    var $font = 'fonts/SIMSUN.TTC'; //如果没有要自己加载到相应的目录下（本地www）  
    var $angle = 0;  
    var $size = 18;  
    var $showX = 160;  
    var $showY = 200;  
      

      
    function showChinaText($showText = '',$showX ,$showY) {  
    	//echo $showText;exit;
        $this->text = isset ( $showText ) ? $showText : $this->text;  
        
        $this->showX = isset ( $showX ) ? $showX : $this->showX;  
        $this->showY = isset ( $showY ) ? $showY : $this->showY;  
       // echo $this->text;exit;
        $this->show ();  
    }  
    function createText($instring) {  
        $outstring = "";  
        $max = strlen ( $instring );  
        for($i = 0; $i < $max; $i ++) {  
            $h = ord ( $instring [$i] );  
            if ($h >= 160 && $i < $max - 1) {  
                $outstring .= substr ( $instring, $i, 2 );  
                $i ++;  
            } else {  
                $outstring .= $instring [$i];  
            }  
        }  
        return $outstring;  
    }  
    function show() {  
        //输出头内容  
        Header ( "Content-type: image/png" );  
        //建立图象  
        //$image = imagecreate(400,300);  
        $image = imagecreatefromjpeg ( "images/1.jpg" ); //这里的图片，换成你的图片路径  
        //定义颜色    
        $color = ImageColorAllocate ( $image, 94, 82, 164 );  
        //填充颜色  
        //ImageFilledRectangle($image,0,0,200,200,$red);  
        //显示文字  
        $txt = $this->createText ( $this->text );  
        //$txt0 = $this->createText ( $this->text0 );  
       // $txt1 = $this->createText ( $this->text1 );  
       // $txt2 = $this->createText ( $this->text2 );  
       // $txt3 = $this->createText ( $this->text3 );  
      //  $txt4 = $this->createText ( $this->text4 );  
        //写入文字  
        imagettftext ( $image, $this->size, $this->angle, $this->showX, $this->showY, $color, $this->font, $txt );  
     //   imagettftext ( $image, $this->size, $this->angle0, $this->showX0, $this->showY0, $color, $this->font, $txt0 );  
      //  imagettftext ( $image, $this->size, $this->angle1, $this->showX1, $this->showY1, $color, $this->font, $txt1 );  
     //   imagettftext ( $image, $this->size, $this->angle2, $this->showX2, $this->showY2, $color, $this->font, $txt2 );  
      //  imagettftext ( $image, $this->size, $this->angle3, $this->showX3, $this->showY3, $color, $this->font, $txt3 );  
     //   imagettftext ( $image, $this->size, $this->angle4, $this->showX4, $this->showY4, $color, $this->font, $txt4 );  
        //ImageString($image,5,50,10,$txt,$white);  
        //显示图形  
        imagejpeg ( $image,'',100 );  
        imagegif ( $image, "images/x.jpg" );  
        ImageDestroy ( $image );  
    }  
}  