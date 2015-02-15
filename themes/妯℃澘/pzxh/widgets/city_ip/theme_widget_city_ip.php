<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
function theme_widget_city_ip(&$setting,&$render){
       // $limit = ($setting['limit'])?$setting['limit']:6;
	   // $brand_list = app::get('b2c')->model('brand')->getList('*',array(),0,$limit,'ordernum desc');
       //static $realip;
       //获取数据库城市地区
       $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
       $ret["area"]=$obj_regions_op->getRegionById();
       
	   if (isset($_SERVER)){
		 if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		 } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip = $_SERVER["HTTP_CLIENT_IP"];
		 } else {
			$realip = $_SERVER["REMOTE_ADDR"];
		 }
       } else {
		if (getenv("HTTP_X_FORWARDED_FOR")){
			$realip = getenv("HTTP_X_FORWARDED_FOR");
		} else if (getenv("HTTP_CLIENT_IP")) {
			$realip = getenv("HTTP_CLIENT_IP");
		} else {
			$realip = getenv("REMOTE_ADDR");
	   }
	 }
	 
	 $getServerURL="http://ip.taobao.com/service/getIpInfo.php";
     $params["ip"]=$realip;
     //$params["ip"]="60.30.131.255";
	//根据IP地址获取所在城市信息,默认深圳市
     $ret["region"]="广东省";
     $ret["realcity"]="广州市";
     $ipresultobj = json_decode(make_request($getServerURL,$params,1));
     if($ipresultobj) {
	    if($ipresultobj->code ==1){
	        $ret["region"]="广东省";
	        $ret["realcity"]="广州市";
	      }else{
	        $ret["region"]=$ipresultobj->data->region;
	        $ret["realcity"] = $ipresultobj->data->city;
	     }
	     if($ipresultobj->data->region==="")
	     {
	       	$ret["region"]="广东省";
	     	$ret["realcity"]="广州市";
	     }
     }
     
     //setcookie("region",$ret["region"],"3600*24");
	 return $ret;
}
/*
 * 根据URL 获取数据
 * 
 */
function make_request($url, $params , $timeout =30){
	set_time_limit(0);
	$str="";
	if($params!="")
	{
		foreach ($params as $k=>$v) {
			if (is_array($v)) {
				foreach ($v as $kv => $vv) {
					$str .= '&' . $k . '[' . $kv  . ']=' . urlencode($vv);
				}
			} else {
				$str .= '&' . $k . '=' . urlencode($v);
			}
		}
	}
	if (function_exists('curl_init')) {
		// Use CURL if installed...
		$ch = curl_init();
		$header=array(
				'Accept-Language: zh-cn',
				'Connection: Keep-Alive',
				'Cache-Control: no-cache'
		);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		if($timeout > 0)curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	} else {
		$context = array(
				'http' => array(
						'method' => 'POST',
						'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
						'Content-length: ' . strlen($str),
						'content' => $str));
		if($timeout > 0)$context['http']['timeout'] = $timeout;
		$contextid = stream_context_create($context);
		$sock = @fopen($url, 'r', false, $contextid);
		if ($sock) {
			$result = '';
			while (!feof($sock)) {
				$result .= fgets($sock, 8192);
			}
			fclose($sock);
		}
		else{
			return 'TimeOut';
		}
	}
	return $result;
}
//根据地区id获取地区
function getRegionById()
{
	$obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
	$ret["realregion"]=$obj_regions_op->getRegionById($_POST['regionId']);
	$ret["status"]=1;
    echo json_encode($ret);
}
   
?>






