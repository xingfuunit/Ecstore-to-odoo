<?php
class weixin_wechat{

    public function __construct($app){
        $this->weixinObject = kernel::single('weixin_object');
        $this->weixinMsg = kernel::single('weixin_message');
    }


    public function checkSignature($signature, $timestamp, $nonce, $token){
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( strtoupper($tmpStr) == strtoupper($signature) ){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  发消息
     */
    public function send_msg($postData,$paramsData){
        $data = array();
        if( !empty($paramsData) ){
            $commData['ToUserName'] = $postData['FromUserName'];
            $commData['FromUserName'] = $postData['ToUserName'];
            $commData['CreateTime'] = time();
            $data = array_merge($commData,$paramsData);
        }
        $this->weixinObject->send($data);
        
        
       
    }
    
    /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function create($demo_name,$result_weixin,$openid,$http = ''){
        
        
        $_POST['pam_account']['login_name'] = $demo_name;
        $_POST['pam_account']['login_password'] = '123456';
        $this->userObject = kernel::single('b2c_user_object');
        $this->userPassport = kernel::single('b2c_user_passport');

        $_POST['vcode'] = $_POST['pam_account']['login_password'];
        $_POST['pam_account']['psw_confirm'] = $_POST['pam_account']['login_password'];
       
        $saveData = $this->userPassport->pre_signup_process($_POST);
        $saveData['b2c_members']['source'] = 'weixin';
        
        if( $member_id = $this->userPassport->save_members($saveData) ){
            $this->userObject->set_member_session($member_id);
            foreach(kernel::servicelist('b2c_save_post_om') as $object) {
                $object->set_arr($member_id, 'member');
                $refer_url = $object->get_arr($member_id, 'member');
            }
            
            /*注册完成后做某些操作! begin*/
            foreach(kernel::servicelist('b2c_register_after') as $object) {
                $object->registerActive($member_id);
            }
            //增加会员同步 2012-5-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->createActive($member_id);
            }
            
            /*end*/
            $data['member_id'] = $member_id;
            $data['uname'] = $saveData['pam_account']['login_account'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['refer_url'] = $refer_url ? $refer_url : '';
            $data['is_frontend'] = true;
            app::get('b2c')->model('member_account')->fireEvent('register',$data,$member_id);
            $this->bind_weixin($member_id, $result_weixin,$openid,$http);
            return true;
        }

    }
    
    public function bind_weixin($member_id,$result,$openid,$http = ''){
        $bindOpenId = app::get('pam')->model('bind_tag')->getRow('member_id',array('open_id'=>$openid));
        $bindMember = app::get('pam')->model('bind_tag')->getRow('member_id',array('member_id'=>$member_id));
        //生成日志文件 json_encode($bindWeixinData)
       // file_put_contents('test.txt',$member_id);
        if( $result['openid'] && !$bindOpenId && !$bindMember ){
            $bindWeixinData = array(
                'member_id' => $member_id,
                'open_id' => $result['openid'],
                'tag_name' => $result['nickname'],
                'createtime' => time()
            );
            //print_r($http);exit;
            app::get('pam')->model('bind_tag')->save($bindWeixinData);
            header("Location: ".$http);
        }
    }


    /**
     * 订阅事件处理
     **/
    public function subscribe($postData,$is_send = 0,$openid ='',$http = ''){
       
        if($is_send == 0){
        		
            //发送关注自动回复
            $messageData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'reply_type'=>'attention') );
            $paramsData = $this->get_message($messageData[0]['message_id'], $messageData[0]['message_type'], $postData);
            $this->send_msg($postData,$paramsData);
            
            
            
            //统计关注数据
            $qrcode_model = app::get('weixin')->model('qrcode_log');
            $key =  explode('_',$postData['EventKey']);
            $qrcode_model->add_qrcode_log('follow',$key[1],$postData['ToUserName'],$postData['FromUserName'],$postData['CreateTime']);
        }
        
        //关注注册    
        
        $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$postData['bind_id']));
        #error_log($postData['bind_id'],3,'e.log'); 
        if(!$access_token = $this->get_basic_accesstoken($postData['bind_id'])){
                return false;
        }
        
        //$paramsData['Content'] = $access_token;
        if($openid == ''){
          $openid  = $postData['FromUserName'];  
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($url, $post_menu);
        $result = json_decode($response, true);
        $paramsData['Content'] = $result['nickname'];
        $pam_members_model = app::get('pam')->model('members');
        $flag = $pam_members_model->getList('member_id',array('login_account'=>trim($openid)));
        if($result['errcode']==40001){
        				$bind_id   =  $postData['bind_id'];
        
                if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
                    $this->subscribe($postData,$is_send = 0,$openid ='',$http = '');
                    return true;
                }
        }
        
        
        if( $result['errcode']==0 ){
        	   if($flag[0]['member_id'] == ''){
			            $this->create($openid,$result,$openid,$http);
			        }
            return true;
        }else{
            return false;
        }
        
        //end 注册
    }

    /**
     * 自定义菜单消息回复
     */
    public function click($postData){
    	//error_log($postData['EventKey'],3,'e.log');
    	 
        $EventKey = explode('_',$postData['EventKey']);
        $paramsData = $this->get_message($EventKey[1], $EventKey[0], $postData);
        $this->send_msg($postData,$paramsData);
    }
    /**
     * 自定义菜单接入多客服
     */
    public function personalService($postData){
              
    	  $transferData = array();
    	  $transferData['ToUserName'] = $postData['FromUserName'];
    	  $transferData['FromUserName'] = $postData['ToUserName'];
    	  $transferData['CreateTime'] = time();
    	  $transferData['MsgType'] = 'transfer_customer_service';
          $this->weixinObject->send($transferData);
         
    }

    /**
     * 普通消息公共调用
     * MsgType ： text image voice video 等
     */
    public function commonMsg($postData){
    	  
        $msgData = array();
        if( $postData['MsgType'] == 'text' && !empty($postData['Content']) ){
        	  $msgData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'keywords'=>$postData['Content']));
        }
        if( empty($msgData) ){
              $this->personalService($postData);
	    	  	return ;
            //$msgData = app::get('weixin')->model('message')->getList('message_id,message_type',array('bind_id'=>$postData['bind_id'],'reply_type'=>'message'));
        }
        $paramsData = $this->get_message($msgData[0]['message_id'], $msgData[0]['message_type'], $postData);
        $this->send_msg($postData,$paramsData);
    }

    /**
     * 获取回复消息 文字|图文
     *
     * @params $msg_id   int    消息ID
     * @params $msg_type string 消息内容
     * @params $postData array  微信POST数据
     */
    public function get_message($msg_id, $msg_type, $postData){
        
        $urlParams = $this->weixinObject->set_wechat_sign($postData);
        if( $msg_type == 'text' ){
            $messageData = app::get('weixin')->model('message_text')->getList('is_check_bind',array('id'=>$msg_id) );
        }else{
            $messageData = app::get('weixin')->model('message_image')->getList('is_check_bind',array('id'=>$msg_id));
        }
        $shopBindWeixin = app::get('pam')->model('bind_tag')->getList('id',array('open_id'=>$postData['FromUserName']));
        if( $messageData[0]['is_check_bind'] == 'true' && empty($shopBindWeixin) ){
            $content = app::get('weixin')->getConf('weixin_sso_setting');
            $arrUrl = preg_match_all("/href[\s]*?=[\s]*?[\'|\"](.+?)[\'|\"]/",$content['noBindText'],$match);
            foreach((array)$match[1] as $url){
                if( stristr($url, '?' ) ){
                    $tmp_url = $url.'&'.$urlParams;
                }else{
                    $tmp_url = $url.'?'.$urlParams;
                }
                $content = str_replace( $url, $tmp_url, $content['noBindText']);
            }
            $paramsData['Content'] = $content;
            $paramsData['MsgType'] = 'text';
        }else{
            $paramsData = $this->weixinMsg->get_message($msg_id, $msg_type, $urlParams);
        }
        //file_put_contents('test.txt',$paramsData['Content']);
        return $paramsData;
    }

    // 获取微信基础信息ACCESS_TOKEN
    public function get_basic_accesstoken($bind_id){
    	
    	$token_obj = base_kvstore::instance('weixin')->fetch('basic_accesstoken_'.$bind_id, $access_token);
        if($token_obj !== false && $access_token != ''){
     //   if(base_kvstore::instance('weixin')->fetch('basic_accesstoken_'.$bind_id, $access_token) !== false){
            
            logger::info('kv获取ACCESS_TOKEN'.$access_token);
            return $access_token;
        }else{
            $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$bind_id));
            if( $bindinfo['appid'] && $bindinfo['appsecret']){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$bindinfo['appid']}&secret={$bindinfo['appsecret']}";
                $httpclient = kernel::single('base_httpclient');
                $response = $httpclient->set_timeout(6)->get($url);
                $result = json_decode($response, true);
                if( $result['errcode']==0 ){
                    if( !base_kvstore::instance('weixin')->store('basic_accesstoken_'.$bind_id, $result['access_token'], $result['expires_in']) ){ // 微信ACCESS_TOKEN的有效期,单位为秒
                        logger::info("KVSTORE写入公众账号登录邮箱为 {$bindinfo['email']} 的ACCESS_TOKEN错误");
                    }
                    logger::info('远程获取ACCESS_TOKEN'.$result['access_token']);
                    return $result['access_token'];
                }else{
                    logger::info("获取公众账号登录邮箱为 {$bindinfo['email']} 的ACCESS_TOKEN错误,微信返回的错误码为 {$result['errcode']}");
                    return false;
                }
            }else{
                logger::info("没有取到公众账号ID为 {$bind_id} 的 appid 或者 appsecret 的信息");
                return false;
            }
        }
    }

    // 获取微信OAUTH2的ACCESS_TOKEN
    public function get_oauth2_accesstoken($bind_id, $code, &$result){
         if( base_kvstore::instance('weixin')->fetch('oauth2_accesstoken_'.$bind_id.'_'.$result['openid'], $oauth2_access_token) !== false ){
             return $oauth2_access_token;
         }else{
            $bindinfo = app::get('weixin')->model('bind')->getRow('appid, appsecret, email',array('id'=>$bind_id));
            if( $bindinfo['appid'] && $bindinfo['appsecret']){
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$bindinfo['appid']}&secret={$bindinfo['appsecret']}&code={$code}&grant_type=authorization_code";
                $httpclient = kernel::single('base_httpclient');
                $response = $httpclient->set_timeout(6)->get($url);
                $result = json_decode($response, true);
                if( $result['errcode']==0 ){
                    #if( !base_kvstore::instance('weixin')->store('oauth2_accesstoken_'.$bind_id.'_'.$result['openid'], $result['access_token'], $result['expires_in']) ){ // 微信ACCESS_TOKEN的有效期,单位为秒
                    #    logger::info("KVSTORE写入公众账号登录邮箱为 {$bindinfo['email']} 的OAUTH2的ACCESS_TOKEN错误");
                    #}
                    logger::info('远程获取OAUTH2_ACCESS_TOKEN'.$result['access_token']);
                    return $result['access_token'];
                }else{
                    logger::info("获取公众账号登录邮箱为 {$bindinfo['email']} 的OAUTH2认证的ACCESS_TOKEN错误,微信返回的错误码为 {$result['errcode']}");
                    return false;
                }
            }else{
                logger::info("没有取到公众账号ID为 {$bind_id} 的 appid 或者 appsecret 的信息");
                return false;
            }
        }
    }

    // 发送微信自定义菜单
    public function createMenu($bind_id, $menu_data, &$msg){
        if(!$access_token = $this->get_basic_accesstoken($bind_id)){
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $data = json_encode ( $menu_data );
        // 由于微信不直接认json_encode处理过的带中文数据的信息，这里做个转换
        $post_menu = preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->post($url, $post_menu);
        $result = json_decode($response, true);
        if($result['errcode']==40001){
        	
                if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
                    $this->createMenu($bind_id, $menu_data, &$msg);
                }
                return true;
            }
        if( $result['errcode']==0 ){
            logger::info('更新微信菜单数据成功:'.print_r($data,1));
            return true;
        }else{
            $msg = "创建微信自定义菜单错误,微信返回的错误码为 {$result['errcode']}";
            logger::info($msg);
            return false;
        }
    }

    // 非OAUTH2网页授权方式获取用户基本信息
    public function get_basic_userinfo($bind_id, $openid){
        if(!$access_token = $this->get_basic_accesstoken($bind_id)){
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($url, $post_menu);
        $result = json_decode($response, true);
        if($result['errcode']==40001){
        	
                if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
                   return $this->get_basic_userinfo($bind_id, $openid);
                }
                return false;
        }
        if( $result['errcode']==0 ){
            return $result;
        }else{
            $msg = "微信基本获取用户信息错误(非OAUTH2方式),微信返回的错误码为 {$result['errcode']}";
            logger::info($msg);
            return false;
        }
    }
    
    /**
     * oauth2 方式获取用户信息 
     * 调用方式： 1、用微信端口连接 先获取 code  2、调用 父类 $accesstoken_oauth2，$openid
     */
    public function get_basic_userinfo_oauth2($accesstoken_oauth2,$openid){
    	$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken_oauth2}&openid={$openid}&lang=zh_CN";
    	$httpclient = kernel::single('base_httpclient');
    	$response = $httpclient->set_timeout(10)->get($url);
    	$result = json_decode($response, true);
    	
    	return $result;
    }
    
    
    // 生成微信需授权页面链接
    public function gen_auth_link($appid, $eid, $redirect_uri){
        $url = sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect',$appid,$redirect_uri,$eid);
        return $url;
    }

    // 获取微信分享自定义信息
    public function get_weixin_share_page(){
        return app::get('weixin')->getConf('weixin_basic_setting.share_page');
    }

    // 随机获取一个可以创建自定义菜单的微信的APPID
    public function get_a_appid(){
        $bindinfo = app::get('weixin')->model('bind')->getRow('appid',array('appid|noequal'=>''));
        return $bindinfo['appid'];
    }

    /**
     * 接受维权通知到微信
     */
    public function updatefeedback($bind_id, $openid, $feedbackid){
        $access_token = $this->get_basic_accesstoken($bind_id);
        $url = "https://api.weixin.qq.com/payfeedback/update?access_token={$access_token}&openid={$openid}&feedbackid={$feedbackid}"; 
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($url);
        $result = json_decode($response, true);
        if($result['errcode']==40001){
        	
                if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
                    $this->updatefeedback($bind_id, $openid, $feedbackid);
                }
                return true;
        }
        if( $result['errcode'] == 0){
            return true;
        } else {
            
            return $result;
        }
    }

    //发货通知微信
    public function delivernotify($postData){
        $payData = app::get('ectools')->getConf('weixin_payment_plugin_wxpay');
        $payData = unserialize($payData);
        $postData['appid'] = trim($payData['setting']['appId']);
        $bindData = app::get('weixin')->model('bind')->getRow('id',array('appid'=>$postData['appid'])); 
        $access_token = $this->get_basic_accesstoken($bindData['id']);
        $url = "https://api.weixin.qq.com/pay/delivernotify?access_token={$access_token}";

        $paySignKey = trim($payData['setting']['paySignKey']); // 财付通商户权限密钥 Key
        $sign = weixin_util::sign_sha1($postData,weixin_util::trimString($paySignKey));

        $postData['app_signature'] = $sign;
        $postData['sign_method'] = 'sha1';
        $httpclient = kernel::single('base_httpclient');
        $postData = json_encode($postData);
        $response = $httpclient->set_timeout(6)->post($url,$postData);
        $result = json_decode($response, true);
        if($result['errcode']==40001){
        	$bind_id   =  $bindData['id'];
        
                if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
                    $this->delivernotify($postData);
                }
                return true;
            }
        if( $result['errcode'] == 0){
            return true;
        } else {
            
            $msg = "发货通知到微信,微信返回的错误码为 {$result['errcode']}\n,错误信息：{$result['errmsg']}";
            logger::info($msg);
            return false;
        }
    }

    public function get_code($appid, $redirect_uri, $response_type='code', $scope='snsapi_base', $state='STATE')
    {
        $api_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $api_url = $api_url.'appid='.$appid.'&redirect_uri='.urlencode($redirect_uri).'&respense_type='.$respense_type.'&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header('Location:'.$api_url);
        exit;
    }

    public function get_openid_by_code($appid, $secret, $code, $grant_type='authorization_code')
    {
        $result = $this->get_access_token($appid, $secret, $code, $grant_type);
        return $result['openid'];
    }

    public function get_access_token($appid, $secret, $code, $grant_type='authorization_code')
    {
        $api_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $api_url = $api_url.'appid='.$appid;
        $api_url = $api_url.'&secret='.$secret;
        $api_url = $api_url.'&code='.$code;
        $api_url = $api_url.'&grant_type='.$grant_type;
        $httpclient = kernel::single('base_httpclient');
        $response = $httpclient->set_timeout(6)->get($api_url);
        $result = json_decode($response, true);
        //logger::info('微信获取access_token返回的数据：'.var_export($result));
        return $result;
    }

    // 判断是否来自微信浏览器
    function from_weixin() {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }
    
    /**
     * 客服接口-发消息 （当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单、订阅事件、扫描二维码事件、支付成功事件、用户维权） ，48小时内可回复）
     * 发送微信 发消息
     *  {
 	 *	"touser":"OPENID",
 	 *	"msgtype":"text",
 	 *	"text":
 	 * 	{
 	 *		"content":"Hello World"
 	 *	}
 	 *}
     */
    function send_message($bind_id,$openid,$content){
    	
    	if(!$access_token = $this->get_basic_accesstoken($bind_id)){
    		return false;
    	}
    	
    	$data=array(
    			'touser'=>$openid,
    			'msgtype'=>'text',
    			'text'=>array('content'=>$content),
    	);
    	
    	$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
    	$data = json_encode ( $data );
    	// 由于微信不直接认json_encode处理过的带中文数据的信息，这里做个转换
    	$post_message = preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
    	$httpclient = kernel::single('base_httpclient');
    	
    	$response = $httpclient->set_timeout(6)->post($url, $post_message);
    	$result = json_decode($response, true);
    	if($result['errcode']==40001){
    		 
    		if(base_kvstore::instance('weixin')->delete('basic_accesstoken_'.$bind_id)){
    			$this->createMenu($bind_id, $menu_data, &$msg);
    		}
    		return true;
    	}
    	if( $result['errcode']==0 ){
    		logger::info('推送消息失败:'.print_r($data,1));
    		return true;
    	}else{
    		$msg = "推送消息失败,微信返回的错误码为 {$result['errcode']}";
    		logger::info($msg);
    		return false;
    	}
    	
    }
    
    /**
     * 根据 bind_id 获得需要 的微信 oauth2 url
     * @param unknown_type $bind_id
     * @param unknown_type $url
     * @param unknown_type $scope
     * @return string
     */
    function  build_wx_oauth2_url($bind_id,$url,$scope='snsapi_base'){
		$bind = app::get('weixin')->model('bind')->getRow('*',array('id'=>$bind_id));
		$path1 = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$bind['appid']}&redirect_uri=";
		$path2 = "?response_type=code&scope={$scope}&state={$bind['eid']}&connect_redirect=1#wechat_redirect";
		
		return $url = $path1.$url.$path2;
    }
    
    /**
     * 
     * @param unknown_type $bind_id
     * @param $scene_id 生成二维码识别id 不要重复
     * @param unknown_type $forever 是否永久二维码 
     */
    function create_qr_code($bind_id,&$scene_id,$forever=true){
    	if(!$access_token = $this->get_basic_accesstoken($bind_id)){
    		return false;
    	}
    	
    	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
    	
    	if($forever){
	    	$qrcode_data = array(
	    				'action_name'=> 'QR_LIMIT_SCENE',
	    				'action_info'=> array(
	    							'scene' => array('scene_id'=>$scene_id)
	    								),
	    		);
    	}else{
    		$qrcode_data = array(
    					'expire_seconds'=>1800,
	    				'action_name'	=> 'QR_SCENE',
	    				'action_info'	=> array(
	    								'scene' => array('scene_id'=>$scene_id)
	    							),
	    		);
    	}
    	$data = json_encode ( $qrcode_data );
    	// 由于微信不直接认json_encode处理过的带中文数据的信息，这里做个转换
    	$post_code = preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
    	$httpclient = kernel::single('base_httpclient');
    	$response = $httpclient->set_timeout(6)->post($url, $post_code);
    	$result = json_decode($response, true);

    	if( !$result['errcode']){
    		logger::info('生成微信二维码成功:'.print_r($data,1));
    		return $result;
    	}else{
    		$msg = "生成微信二维码失败,微信返回的错误码为 {$result['errcode']}";
    		logger::info($msg);
    		return false;
    	}
    }
    
    
    /**
     *  自定义 二维码 扫描事件 处理
     */
    public function scan($postdata){
    	$qrcode_model = app::get('weixin')->model('qrcode_log');
    	if($qrcode_model->add_qrcode_log('scan',$postdata['EventKey'],$postdata['ToUserName'],$postdata['FromUserName'],$postdata['CreateTime'])){
    		echo 'success';exit;
    	}
    }
    
    

}
