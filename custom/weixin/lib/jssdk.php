<?php
/**
 * 微信 js_jdk 调用生成接口
 *
 */
class weixin_jssdk {

	public function __construct($app) {
  	
    	$this->weixinObject = kernel::single('weixin_object');
    	$this->weixinMsg = kernel::single('weixin_message');
  	}

  	public function getSignPackage($bind_id) {
    	$jsapiTicket = $this->getJsApiTicket($bind_id);
    
	    $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$bind_id));
	    
	    // 注意 URL 一定要动态获取，不能 hardcode.
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	    $timestamp = time();
	    $nonceStr = $this->createNonceStr();
	
	    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
	    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
	    
	    $signature = sha1($string);
	    
	    $signPackage = array(
	      "appId"     => $bindinfo['appid'],
	      "nonceStr"  => $nonceStr,
	      "timestamp" => $timestamp,
	      "url"       => $url,
	      "signature" => $signature,
	      "rawString" => $string
    	);
    	return $signPackage; 
  	}

	 private function createNonceStr($length = 16) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str = "";
	    for ($i = 0; $i < $length; $i++) {
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	  }

  /**
   * 获得jsapi_ticket  应该全局存储与更新
   * @return string
   */
	private function getJsApiTicket($bind_id) {
	  	if( base_kvstore::instance('weixin')->fetch('basic_jsapi_ticket_'.$bind_id, $jsapi_ticket) !== false ){
	  		logger::info('kv获取jsapi_ticket'.$jsapi_ticket);
	  		return $jsapi_ticket;
	  	}else{
		      $accessToken = kernel::single('weixin_wechat')->get_basic_accesstoken($bind_id);
		      // 如果是企业号用以下 URL 获取 ticket
		      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
		      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		      $httpclient = kernel::single('base_httpclient');
		  	  $response = $httpclient->set_timeout(6)->get($url);
		  	  $result = json_decode($response, true);
		      $jsapi_ticket = $result['ticket'];
		      
		      if ($jsapi_ticket) {
		      	if( !base_kvstore::instance('weixin')->store('basic_jsapi_ticket_'.$bind_id, $jsapi_ticket, $result['expires_in']) ){ // 微信jsapi_ticket的有效期,单位为秒
		      		logger::info("KVSTORE写入公众账号绑定id为: {$bind_id} 的jsapi_ticket错误");
		      	}
		      	
		      	logger::info('远程获取jsapi_ticket'.$jsapi_ticket);
		      	return $jsapi_ticket;
		      }
		      else{
		      	//todo : 错误提示
		      }
	    } 
	  }

}

