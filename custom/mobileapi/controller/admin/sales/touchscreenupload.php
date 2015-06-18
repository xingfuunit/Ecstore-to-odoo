<?php
/**
 * plupload upload.php
 * 
 * date:	2015-06-03
 *
 */
error_reporting(E_ALL);
if (__FILE__ == '') { die('Fatal error code: __FILE__');}
define('ROOT_PATH', str_replace('/custom/mobileapi/controller/admin/sales/touchscreenupload.php', '', str_replace('\\', '/', __FILE__)));

//-------------------------------------------
//检查权限
$key = isset($_REQUEST["key"]) ? $_REQUEST["key"] : '';
if(strlen($key)<32){
	SendMsg('100.您还未登陆或登陆超时，请刷新。');
	exit();	
}

$rnd  = substr($key,32);
if( $key != md5( $rnd .'视频上传').$rnd ){
	SendMsg('100.您还未登陆或登陆超时，请刷新。');
	exit();	
}


//-------------------------------------------
// 5 minutes execution time
@set_time_limit(5 * 60);

$opluploader = new pluploader();

class pluploader{

	//可以上传的格式
	var $allowedExt 	= array('mp4','jpg');

	//出错编号
    var $errCode		= '';

	//出错信息
    var $errMsg			= '';

	//保存的路径
	var $saveDir		= '/public/vod/';

	//充许上传的文件大小(单位:KB)，默认:100000KB = 100m
    var $maxSize		= 100000;
	
	// Remove old files
	var $cleanupTargetDir = true;
	
	//Temp file age in seconds 24 * 3600 = 1天
	var $maxFileAge 	= 86400;
	
	//上传 post name
	var $filePostName = 'file';
	
	public function __construct(){
		
		//------------------------------------------
		
		$newFile = isset($_REQUEST["newfile"]) ? $_REQUEST["newfile"] : '';
		
		//------------------------------------------
			
		static $tmpUploadDir = NULL;
		if($tmpUploadDir === NULL) {
			$tmpUploadDir = $this->get_temp_dir();
		}
	
		// Create tmpUploadDir
		if (!file_exists($tmpUploadDir)) {
			if(@mkdir($tmpUploadDir,0755)){
				@chmod($tmpUploadDir,0755);
			};
		}

		//------------------------------------------
		//检查暂时目录是否有权限
		if (@is_writable($tmpUploadDir) === false){
			SendMsg('101.暂时目录没有写权限!'.$tmpUploadDir);
			exit();
		}

		//------------------------------------------
		//检查目录写权限
		$saveDirAll = ROOT_PATH . $this->saveDir;
		// Create saveDir
		if (!file_exists($saveDirAll)) {
			if(@mkdir($saveDirAll,0755)){
				@chmod($saveDirAll,0755);
			};
		}

		if (@is_writable($saveDirAll) === false){
			SendMsg('102.上传目录没有写权限！').$saveDirAll;
			exit();
		}


		//------------------------------------------
		//是否有上传文件

		if ( !(isset( $_FILES[$this->filePostName] ) && !is_null( $_FILES[$this->filePostName]['tmp_name'] ) && $_FILES[$this->filePostName]['name'] != '' )){
			SendMsg('103.缺少上传文件');
			exit();
		}

		if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
			SendMsg('104.未能将上传的文件移动。');
			exit();
		}
		//------------------------------------------
		//文件大小
		$fileSize = $_FILES[$this->filePostName]['size'];
		
		if($fileSize<10)
		{   
			SendMsg('103.缺少上传文件.');
			exit();
		}

		if($fileSize > ($this->maxSize * 1024) ){   
			SendMsg('105.上传文件大小超过了限制.最多上传('.$this->maxSize .'KB).');
			exit();
		}
		//$fileSize = round($fileSize/1024,0);
		
		//------------------------------------------
		//服务器上临时文件名
		//原始文件名称
		if (isset($_REQUEST["name"])) {
			$srcName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$srcName = $_FILES["file"]["name"];
		} else {
			$srcName = uniqid("file_");
		}
		
		$fileExt = strtolower(substr( $srcName, strrpos( $srcName, '.' ) + 1 ));

		if (!in_array($fileExt, $this->allowedExt)) {
			SendMsg('106.不支持该文件格式.,只支持以下格式:('. join(',', $this->allowedExt) . ').');
			exit();
		}

		//新的文件名
		if($newFile == ''){
			$newFile =  date('ymdHis',time()).sprintf('%03d', mt_rand(0, 999)). '.' .$fileExt;
		}
		
		$newPath  = $this->saveDir . $newFile;
		$newPathAll = $saveDirAll . $newFile;
		
		$filePath = $tmpUploadDir . DIRECTORY_SEPARATOR . $newFile;

		//------------------------------------------
		// Remove old temp files	
		if ($this->cleanupTargetDir) {
			if (!is_dir($tmpUploadDir) || !$dir = opendir($tmpUploadDir)) {
				SendMsg('101.暂时目录没有写权限!');
				exit;
			}

			while (($file = readdir($dir)) !== false) {
				$tmpPath = $tmpUploadDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				//如果是当前上传的将跳过，不删除
				if ($tmpPath == "{$filePath}.part") {
					continue;
				}
				/*
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpPath) < time() - $this->maxFileAge)) {
					@unlink($tmpPath);
				}
				*/
				
				//从暂时文件夹中删除超过2天的上传文件
				if ((filemtime($tmpPath) < time() - $this->maxFileAge)) {
					@unlink($tmpPath);
				}
			}
			closedir($dir);
		}
		
		//------------------------------------------
		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			SendMsg('107.未能打开输出流.'."{$filePath}.part");
			exit();
		}

		// Read binary input stream and append it to temp file
		if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
			SendMsg('108.未能打开输入的流。');
			exit();
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			
			/*
			* 保存后再移动，这里不移动
			if (move_uploaded_file($filePath, $newPathAll)){
				
			} else if (copy($filePath, $newPathAll)) {
				@unlink($filePath);
			}else{
				SendMsg('109.上传后，移动出错！');
				exit();
			}
			*/
		}
		
		//上传成功，返回新路径和原始名称
		SendMsg('', $newFile,$newPath, $srcName);
	}
	

	
	function get_temp_dir(){
		$tmpDir = ''.ini_get("upload_tmp_dir");
		if($tmpDir==''){
			$tmpDir = sys_get_temp_dir();	//	sys_get_temp_dir() 必须php 5.2.1 后才有的函数			
		}
		$tmpDir .= DIRECTORY_SEPARATOR . 'ecstore_plupload';
		return $tmpDir;
	}
}

/*
 * 向页面输出
 * */
function SendMsg($msg, $newfile = '', $newpath = '',$srcfile = ''){
		
	// Make sure file is not cached (as it happens for example on iOS devices)
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('Content-type: text/html; charset=UTF-8');
	
	/* 
	// Support CORS
	header("Access-Control-Allow-Origin: *");
	// other CORS headers if any...
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		exit; // finish preflight CORS requests here
	}
	*/

	$hash = array(
		'err' => 1,			//1 = error ,0 = yes
		'msg' => $msg,
		'newfile' => $newfile,	//新文件名，不包含路径
		'newpath' => $newpath,	//新文件名，包括路径
		'srcfile' => $srcfile	//原文件名，不包含路径
	);
	
	if($msg == ''){
		$hash["err"] = 0;
	}
	echo json_encode($hash);
	exit();
}
?>