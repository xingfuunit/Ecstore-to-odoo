<?php
/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_passport  extends mobileapi_frontpage {
	
	/**
	 * 
	 * @var base_rpc_service
	 */
	var $rpcService;
	
	/**
	 */
	function __construct($app)
	{
		$this->app = $app;
		
		$_POST['site_autologin'] = 'on';
		
		//$this->_response->set_header('Cache-Control', 'no-store');
		kernel::single('base_session')->start();
		$this->userObject = kernel::single('b2c_user_object');
		$this->userPassport = kernel::single('b2c_user_passport');
		
		$this->rpcService = kernel::single('base_rpc_service');
	}

	 /*
     * 登录验证
     * */
    public function post_login(){
        //_POST过滤
        $userData = array(
            'login_account' => $_POST['uname'],
            'login_password' => $_POST['password']
        );

        

        if(kernel::single('b2c_service_vcode')->status() && empty($_POST['verifycode'])){
            //$msg = app::get('b2c')->_('请输入验证码!');
        	$data['needVcode'] = 1;
        	$data['msg'] = '请输入验证码!';
            $this->rpcService->send_user_error('login_error', $data);
        }

        $member_id = kernel::single('pam_passport_site_basic')->login($userData,$_POST['verifycode'],$msg);
        if(!$member_id){
            //设置登陆失败错误次数 一个小时三次错误后需要自动开启验证码
            kernel::single('b2c_service_vcode')->set_error_count();
            $data['needVcode'] = kernel::single('b2c_service_vcode')->status();
            //$this->splash('failed',null,$msg,true,$data);exit;
            $data['msg'] = $msg;
            $this->rpcService->send_user_error('login_error', $data);
        }
        
        

        //记录登录信息
        $b2c_members_model = kernel::single('b2c_mdl_members');
        $member_point_model = kernel::single('b2c_mdl_member_point');

        $member_data = $b2c_members_model->getList( '*', array('member_id'=>$member_id) );
        if(!$member_data){
            kernel::single('b2c_service_vcode')->set_error_count();
            $data['needVcode'] = kernel::single('b2c_service_vcode')->status();
            //在登录认证表中存在记录，但是在会员信息表中不存在记录
            //$msg = $this->app->_('登录失败：会员数据存在问题,请联系商家或客服');
            //$this->splash('failed',null,$msg,true,$data);exit;
            $data['msg'] = '登录失败：会员数据存在问题,请联系商家或客服';
            $this->rpcService->send_user_error('login_error', $data);
        }

        $member_data = $member_data[0];
        $member_data['order_num'] = kernel::single('b2c_mdl_orders')->count( array('member_id'=>$member_id) );

        /*
        if($this->app->getConf('site.level_switch')==1)
        {
            $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
        }
        if($this->app->getConf('site.level_switch')==0)
        {
            $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
        }
        */

        $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        $this->userObject->set_member_session($member_id);
        $this->bind_member($member_id);
        $this->set_cookie('loginName',$_POST['uname'],time()+31536000);//用于记住密码
        @kernel::single('b2c_mdl_cart_objects')->setCartNum();
        
        $member_data['session'] = $_SESSION;

        // 微店
        //$member_data['microshop_info']  = array();
        if ( $member_data['member_type'] == 2 ) {
            $mdl    = app::get('microshop')->model('shop');
            $filter = array (
                'member_id' => $member_data['member_id']
            );
            
            if($mic_info = $mdl->getDetail($filter)) $member_data['microshop_info'] = $mic_info;
        }

        $member_data['avatar']  = $member_data['avatar'] ? kernel::single('base_storager')->image_path($member_data['avatar']) : $this->app->res_url.'/images/top-bg.png';
        $member_data['cover']   = $member_data['cover'] ? kernel::single('base_storager')->image_path($member_data['cover']) : $this->app->res_url.'/images/top-bg.png';

        return $member_data;
    }//end function
    
    
    public function logout(){
    	
    	$this->unset_member();
    	@kernel::single('b2c_mdl_cart_objects')->setCartNum($arr);
    	return true;
    }
    
    public function unset_member(){
    	$auth = pam_auth::instance(pam_account::get_account_type('b2c'));
    	foreach(kernel::servicelist('passport') as $k=>$passport){
    		$passport->loginout($auth);
    	}
    	//$this->app->member_id = 0;
    	$this->cookie_path = kernel::base_url().'/';
    	$this->set_cookie('MEMBER',null,time()-3600);
    	$this->set_cookie('UNAME','',time()-3600);
    	$this->set_cookie('MLV','',time()-3600);
    	$this->set_cookie('CUR','',time()-3600);
    	$this->set_cookie('LANG','',time()-3600);
    	$this->set_cookie('S[MEMBER]','',time()-3600);
    	foreach(kernel::servicelist('member_logout') as $service){
    		$service->logout();
    	}
    }
    
    public function send_vcode_email()
    {
    	$email = $_POST['uname'];
    	$type = trim($_POST['type'])?$_POST['type']:'signup'; //激活activation
    
    	if( !$type || !$email ){
    		$msg = app::get('b2c')->_('请填写正确的邮箱');
    		$this->rpcService->send_user_error('email_error', $msg);
    	}
    
    	$login_type = $this->userPassport->get_login_account_type($email);
    	if($login_type != 'email'){
    		$msg = app::get('b2c')->_('请填写正确的邮箱');
    		$this->rpcService->send_user_error('email_error', $msg);
    	}
    
    	if($type == 'reset' && !$this->userPassport->check_signup_account( trim($email),$msg )){
    		$this->rpcService->send_user_error('email_error', $msg);
    	}
    
    	$userVcode = kernel::single('b2c_user_vcode');
    	if($email){
    		$vcode = $userVcode->set_vcode($email,$type,$msg);
    	}
    	if($vcode){
    		//发送邮箱验证码
    		$data['vcode'] = $vcode;
    		$data['uname'] = $_POST['uname'];
    		$data['msg'] = '邮件已发送';
    		if( !$userVcode->send_email($type,(string)$email,$data) ){
    			$msg = app::get('b2c')->_('参数错误');
    			$this->rpcService->send_user_error('email_p_error', $msg);
    		}
    		
    		return $data;
    	}else{
    		$this->rpcService->send_user_error('email_vcode_error', $msg);
    	}
    }
    
    public function send_vcode_sms(){
    	$mobile = trim($_POST['uname']);
    	$type = trim($_POST['type'])?$_POST['type']:'signup'; //激活activation signup forgot
    
    	if( !$type || !$mobile ){
    		$msg = app::get('b2c')->_('用户名不能为空');
    		//$this->splash('failed',null,$msg,true);
    		$this->rpcService->send_user_error('send_vcode_sms_error', $msg);
    	}
    
    	$login_type = $this->userPassport->get_login_account_type($mobile);
    	if($login_type != 'mobile'){
    		$msg = app::get('b2c')->_('请填写正确的手机号码');
    		//$this->splash('failed',null,$msg,true);
    		$this->rpcService->send_user_error('send_vcode_sms_error', $msg);
    	}
    
    	if($type == 'reset' && !$this->userPassport->check_signup_account( trim($mobile),$msg )){
    		//$this->splash('failed',null,$msg,true);
    		$this->rpcService->send_user_error('send_vcode_sms_error', $msg);
    	}
    
    	$userVcode = kernel::single('b2c_user_vcode');
    	if($mobile){
    		$vcode = $userVcode->set_vcode($mobile,$type,$msg);
    	}
    	if($vcode){
    		//发送验证码 发送短信
    		$data['vcode'] = $vcode;
    		$data['uname'] = $_POST['uname'];
    		$data['msg'] = '短信已发送';
    		if( !$userVcode->send_sms($type,(string)$mobile,$data) ){
    			$msg = app::get('b2c')->_('发送失败');
    			//$this->splash('failed',null,$msg,true);
    			$this->rpcService->send_user_error('send_vcode_sms_error', $msg);
    		}
    		
    		return $data;
    		
    	}else{
    		//$this->splash('failed',null,$msg,true);
    		$this->rpcService->send_user_error('send_vcode_sms_error', $msg);
    	}
    }
    
    
    /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function member_create(){
    	$in_post['response_json'] = $_POST['response_json'] = 'true';
    	$in_post['license'] = $_POST['license'] = 'on';
    	
    	$in_post['pam_account']['login_name'] = trim($_POST['uname']);
    	$in_post['pam_account']['psw_confirm'] = $_POST['password'];
    	$in_post['pam_account']['login_password'] = $_POST['password'];
    	
    	$in_post['vcode'] = trim($_POST['vcode']);
    	
    	
    	if($in_post['response_json'] == 'true'){
    		$ajax_request = true;
    	}else{
    		$ajax_request = false;
    	}
    	if( !$this->userPassport->check_signup($in_post,$msg) ){
    		//$this->splash('failed',null,$msg,$ajax_request);
    		$this->rpcService->send_user_error('member_create_error', $msg);
    	}
    
    	$saveData = $this->userPassport->pre_signup_process($in_post);
    	
    	//print_r($saveData);exit;
    
    	if( $member_id = $this->userPassport->save_members($saveData,$msg) ){
    		$this->userObject->set_member_session($member_id);
    		$this->bind_member($member_id);
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
    		$data['uname'] = $saveData['pam_account']['login_name'];
    		$data['passwd'] = $in_post['pam_account']['psw_confirm'];
    		$data['email'] = $in_post['contact']['email'];
    		$data['refer_url'] = $refer_url ? $refer_url : '';
    		$data['is_frontend'] = true;
    		$obj_account=app::get('b2c')->model('member_account');
    		$obj_account->fireEvent('register',$data,$member_id);
    		/*
    		if(!strpos($_SESSION['pc_next_page'],'cart')){
    			$url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'sign_tips'));
    		}else{
    			$url = $_SESSION['pc_next_page'];
    		}
    		*/
    		//$this->splash('success',$url,app::get('b2c')->_('注册成功'),$ajax_request);
    		
    		return $data;
    	}
    	
    	
    	//$this->splash('failed',$back_url,app::get('b2c')->_('注册失败'),$ajax_request);
    	$this->rpcService->send_user_error('member_create_error', $msg);
    	
    }
    
    
    public function sendPSW(){
    	$username = $_POST['username'];
    	$member_id = $this->userObject->get_member_id_by_username($username);
    
    	if(!$member_id){
    		$msg = app::get('b2c')->_('该账号不存在，请检查');
    		$this->rpcService->send_user_error('member_error', $msg);
    	}
    
    	$pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
    	foreach($pamMemberData as $row){
    		if($row['login_type'] == 'mobile' && $row['disabled'] == 'false'){
    			$data['mobile'] = $row['login_account'];
    			$verify['mobile'] = true;
    		}
    
    		if($row['login_type'] == 'email' && $row['disabled'] == 'false'){
    			$data['email'] = $row['login_account'];
    			$verify['email'] = true;
    		}
    	}
    
    	if($verify['mobile'] || $verify['email']){
    		$this->pagedata['send_status'] = 'true';
    	}else{
    		$this->rpcService->send_user_error('member_verify_error', '由于您并未验证手机或者邮箱，无法自助找回密码，请联系网站客服！');
    	}
    	
    	$data['send_type'] = $row['login_type'];
    	
    	$this->pagedata['data'] = $data;
    	
    	return $data;
    }
    
    public function resetpwd_code(){
    	$send_type = $_POST['send_type']; //email  mobile   vcode   
    	$userVcode = kernel::single('b2c_user_vcode');
    	if( !$vcodeData = $userVcode->verify($_POST['vcode'],$_POST['username'],'forgot')){
    		$msg = app::get('b2c')->_('验证码错误');
    		$this->rpcService->send_user_error('verify_error', $msg);
    	}
    	$data['key'] = $userVcode->get_vcode_key($_POST[$send_type],'forgot');
    	$data['key'] = md5($vcodeData['vcode'].$data['key']);
    	$data['account'] = $_POST['username'];
    	return $data;
    }
    
    public function resetpassword(){
    	/*	
    	$userVcode = kernel::single('b2c_user_vcode');
    	$vcodeData = $userVcode->get_vcode($_POST['account'],'forgot');
    	$key = $userVcode->get_vcode_key($_POST['account'],'forgot');
    	//print_r($vcodeData); echo md5($vcodeData['vcode'].$key);exit;
    	if($_POST['account'] !=$vcodeData['account']  || $_POST['key'] != md5($vcodeData['vcode'].$key) ){
    		$msg = app::get('b2c')->_('页面已过期,请重新找回密码');
    		$this->rpcService->send_user_error('pages_error', $msg);
    	}
    	*/
    
    	if( !$this->userPassport->check_passport($_POST['login_password'],$_POST['psw_confirm'],$msg) ){
    		$this->rpcService->send_user_error('pwconfirm_error', $msg);
    	}
    
    	$member_id = $this->userObject->get_member_id_by_username($_POST['account']);
    	if( !$this->userPassport->reset_passport($member_id,$_POST['login_password']) ){
    		$msg = app::get('b2c')->_('密码重置失败,请重试');
    		$this->rpcService->send_user_error('resetpw_error', $msg);
    	}
    	
    	return array('status' =>'ok','msg'=>'新密码已经设置成功，请使用新密码重新登录' );
    }
    
    
    
    
    
    /**
     * 第三方登录
     * @param unknown $data
     */
    public function trust_login($post){
    	$userPassport = kernel::single('b2c_user_passport');
    	if( $userPassport->userObject->is_login() ){
    		$this->rpcService->send_user_error('login_error', '您已经是登录状态，不需要重新登录');
    	}
    	 
    	/*
    	openid: "1707357339",
    	provider_code: "qzone",
    	provider_openid: "ADB189CA2182398213E6FC0905BB0333",
    	nickname: "是非亦航",
    	realname: "是非亦航",
    	avatar: "http://qzapp.qlogo.cn/qzapp/100278270/ADB189CA2182398213E6FC0905BB0333/100",
    	email: "",
    	gender: "1",
    	address: "中国 广东 广州",
    	province: "广东",
    	city: "广州"
    	*/
    	 
    	$data['provider_code'] = $post['provider_code'];
    	$data['openid'] = $post['openid'];
    	$data['nickname'] = $post['nickname']?$post['nickname']:'';
    	$data['realname'] = $post['realname']?$post['realname']:'';
    	$data['avatar'] = $post['avatar']?$post['avatar']:'';
    	$data['email'] = $post['email']?$post['email']:'';
    	$data['gender'] = $post['gender']?$post['gender']:"2";
    	
    	$result['data'] = $data;
    	$login_name = $this->trust_save_login_data($result,$msg);
    	if(!$login_name)$this->rpcService->send_user_error('login_error', $msg);
    	
    	$row = app::get('pam')->model('auth')->getList('account_id,module_uid',array('module_uid'=>$login_name));
    	$member_id = $row[0]['account_id'];
    	
    	if(!$member_id)$this->rpcService->send_user_error('login_error', '登录ID错误');
    	
    	
    	
    	//记录登录信息
    	$b2c_members_model = kernel::single('b2c_mdl_members');
    	$member_point_model = kernel::single('b2c_mdl_member_point');
    	
    	$member_data = $b2c_members_model->getList( '*', array('member_id'=>$member_id) );
    	if(!$member_data){
    		kernel::single('b2c_service_vcode')->set_error_count();
    		$data['needVcode'] = kernel::single('b2c_service_vcode')->status();
    		//在登录认证表中存在记录，但是在会员信息表中不存在记录
    		//$msg = $this->app->_('登录失败：会员数据存在问题,请联系商家或客服');
    		//$this->splash('failed',null,$msg,true,$data);exit;
    		$data['msg'] = '登录失败：会员数据存在问题,请联系商家或客服';
    		$this->rpcService->send_user_error('login_error', $data);
    	}
    	
    	$member_data = $member_data[0];
    	$member_data['order_num'] = kernel::single('b2c_mdl_orders')->count( array('member_id'=>$member_id) );
    	
    	/*
    	 if($this->app->getConf('site.level_switch')==1)
    	 {
    	$member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
    	}
    	if($this->app->getConf('site.level_switch')==0)
    	{
    	$member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
    	}
    	*/
    	
    	$b2c_members_model->update($member_data,array('member_id'=>$member_id));
    	$this->userObject->set_member_session($member_id);
    	$this->bind_member($member_id);
    	$this->set_cookie('loginName',$_POST['uname'],time()+31536000);//用于记住密码
    	@kernel::single('b2c_mdl_cart_objects')->setCartNum();
    	
    	$member_data['session'] = $_SESSION;
    	
    	// 微店
     	//$member_data['microshop_info']  = array();
        if ( $member_data['member_type'] == 2 ) {
            $mdl    = app::get('microshop')->model('shop');
            $filter = array (
                'member_id' => $member_data['member_id']
            );
            
            if($mic_info = $mdl->getDetail($filter)) $member_data['microshop_info'] = $mic_info;
        }
    	
    	$member_data['avatar']  = $member_data['avatar'] ? kernel::single('base_storager')->image_path($member_data['avatar']) : $this->app->res_url.'/images/top-bg.png';
    	$member_data['cover']   = $member_data['cover'] ? kernel::single('base_storager')->image_path($member_data['cover']) : $this->app->res_url.'/images/top-bg.png';
    	
    	return $member_data;
    	 
    }
    
    function trust_save_login_data($result,&$msg){
    	$saveData['b2c_members'] = $this->pre_b2c_members_data($result);
    	$saveData['pam_account'] = $this->pre_pam_members_data($result);
    
    	$row = app::get('pam')->model('auth')->getList('auth_id,module_uid',array('module_uid'=>$saveData['pam_account']['login_account']));
    	$account = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$saveData['pam_account']['login_account']));
    	if($row && $account){//已有信息不用再次保存
    		return $saveData['pam_account']['login_account'];
    	}
    
    	$member_model = kernel::single('b2c_mdl_members');
    	$db = kernel::database();
    	$db->beginTransaction();
    	//保存到b2c members
    	if( $member_model->insert($saveData['b2c_members']) ){
    		$member_id = $saveData['b2c_members']['member_id'];
    		if(!(kernel::single('b2c_user_passport')->save_attr($member_id,$saveData['b2c_members'],$msg))){
    			$db->rollBack();
    			return false;
    		}
    
    		$saveData['pam_account']['member_id'] = $member_id;
    		if(!app::get('pam')->model('members')->save($saveData['pam_account'])){
    			$db->rollBack();
    			return false;
    		}
    
    		$authData = array(
    				'account_id'=>$member_id,
    				'module_uid'=>$saveData['pam_account']['login_account'],
    				'module'=>'openid_passport_trust',
    		);
    		if($row[0]['auth_id']){
    			$authData['auth_id'] = $row[0]['auth_id'];
    		}
    		if( !app::get('pam')->model('auth')->save($authData) ){
    			$db->rollBack();
    			return false;
    		}
    		
    		
			/*
    		$openidData = $this->pre_openid_data($member_id,$result);
    		if( !app::get('openid')->model('openid')->save($openidData) ){
    			$db->rollBack();
    			return false;
    		}
    		*/
    		
    	}else{
    		return false;
    	}
    	$db->commit();
    	
    	return $saveData['pam_account']['login_account'];
    }
    
    public function pre_b2c_members_data($result){
    	$lv_model = app::get('b2c')->model('member_lv');
    	$member_lv_id = $lv_model->get_default_lv();
    	$data['member_lv_id'] = $member_lv_id;
    	$arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
    	$data['currency'] = $arrDefCurrency['cur_code'];
    	$data['email'] = $result['data']['email'];
    	$data['name'] = empty($result['data']['nickname']) ? $result['data']['realname'] : $result['data']['nickname'];
    	$data['addr'] = $result['data']['address'];
    	$data['sex'] = $result['data']['gender'];
    	$data['trust_name'] = empty($result['data']['nickname'])?$result['data']['realname']:$result['data']['nickname'];
    	return $data;
    }
    
    public function pre_pam_members_data($result){
    	$data = $result['data'];
    	if(empty($data['nickname'])){
    		$login_name = $data['provider_code'].'_'.$data['realname'].'_'.$data['openid'];
    	}else{
    		$login_name = $data['provider_code'].'_'.$data['nickname'].'_'.$data['openid'];
    	}
    
    	$return = array(
    			'login_type' => 'local',
    			'login_account' => $login_name,
    			'login_password' => md5(time().$login_name),
    			'password_account' => $login_name, //登录密码加密账号
    			'disabled' =>  'false',
    			'createtime' => time()
    	);
    	return $return;
    }
    
    public function pre_openid_data($member_id,$result){
    	$save= array(
    			'member_id' => $member_id,
    			'openid' => $result['data']['openid'],
    			'provider_code' => (string)$result['data']['provider_code'],
    			'provider_openid' => (string)$result['data']['provider_openid'],
    			'avatar' => $result['data']['avatar'],
    			'email' => $result['data']['email'],
    			'address' => $result['data']['address'],
    			'gender' => $result['data']['gender'],
    			'nickname' => $result['data']['nickname'],
    			'realname' => $result['data']['realname'],
    	);
    	return $save;
    }
    
    
}

?>
