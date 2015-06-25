<?php
/**
 * @author iegss@126.com
 * @version 0.1
 * @package messenger
 * @description 企业信通-吉信通
 */

class b2c_messenger_smsgxt{

    
    private $sendUrl = 'http://service.winic.org/sys_port/gateway/'; //吉信通发送地址
    
    //private $sms_user_name = "pzfresh@pzfresh.com"; //帐号
//     private $sms_user_name = "it-service"; //帐号
    
    private $sms_user_name = MESSAGE_SMS_ACCOUNT;
    private $sms_password = MESSAGE_SMS_PASSWORD; //密码
    
    private $statusStr = array(
    		"000"  => "成送成功",
    		"-01"  => "当前账号余额不足",
    		"-02"  => "当前用户ID错误",
    		"-03"  => "当前密码错误",
    		"-04"  => "参数不够或参数内容的类型错误",
    		"-05" => "手机号码格式不对",
    		"-06" => "短信内容编码不对",
    		"-07" => "短信内容含有敏感字符",
    		"-08" => "无接收数据",
    		"-09" => "系统维护中..",
    		"-10" => "手机号码数量超长",
    		"-11" => "短信内容超长",
    		"body_null" => "无数据",
    		"-12" => "其它错误",
    		"-111 ip block" => "IP限制"
    );

    public function __construct() {
        $this->httpClient = kernel::single('base_httpclient');
        $this->httpClient->set_timeout(6);

        //启用测试数据
        //$this->testInit();
    }

    /**
     * @description 短信发送
     * @access public
     * @param void
     * @return void
     */
    public function send($contents,$config,&$msg) {
    	
    	$mobile_number = $contents[0]['phones'];
    	$content = $contents[0]['content'];
    	
    	if(!$mobile_number)return false;
    	
    	if(is_array($mobile_number))
    	{
    		$mobile_number = implode(",",$mobile_number);
    	}
    	
    	$params = array(
				"id"=>$this->sms_user_name,
				"pwd"=>$this->sms_password,
				"to"=>$mobile_number,
				"content"=>iconv("utf-8","GB2312",$content),
				"time"=>''
			);
    	
    	logger::info("messenger_sms:".print_r($params,1));
    	
    	$result = $this->httpClient->post($this->sendUrl,$params);
    	
    	logger::info("messenger_sms:".$result);
    	
    	//000/Send:1/Consumption:.07/Tmoney:999.72/sid:0106215942807529
		$smsStatus = explode('/', isset($result['body'])?$result['body']:$result);
		$code = isset($smsStatus[0])?$smsStatus[0]:'body_null';
		
		$msg = isset($this->statusStr[$code])?$this->statusStr[$code]:'返回错误';
		
		if($code=='000'){
			return true;
		}
		
		return false;
    }


}
?>
