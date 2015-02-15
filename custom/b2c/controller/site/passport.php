<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_site_passport extends b2c_frontpage{
    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
        $this->userObject = kernel::single('b2c_user_object');
        $this->userPassport = kernel::single('b2c_user_passport');
    }

    /*
     * 如果是登录状态则直接跳转到会员中心
     * */
    public function check_login($mini){
        if( $this->userObject->is_login() )
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            if($_GET['mini_passport']==1 || $mini){
                kernel::single('site_router')->http_status(302);return;
            }else{
                //您已经是登录状态，不需要重新登录
                $this->redirect($url);
            }
        }
        return false;
    }

    public function index(){
        //如果会员登录则直接跳转到会员中心
        $this->check_login();
        $this->login();
    }

    /*
     * 登录view
     * */
    public function login($mini=0){
        //如果会员登录则直接跳转到会员中心
        $this->check_login($mini);

        $flag = false;
        if($_GET['mini_passport']==1 || $mini) {
            $flag = true;
            $this->pagedata['mini_passport'] = 1;
        }

        //是否开启验证码
        $this->pagedata['show_varycode'] = kernel::single('b2c_service_vcode')->status();

        //信任登录openapi
        foreach(kernel::servicelist('openid_imageurl') as $object){
            if(is_object($object)){
                if(method_exists($object,'get_image_url')){
                    $login_image_url[] = $object->get_image_url();
                }
            }
        }

        //登陆页面左侧大图
        $images_id = app::get('b2c')->getConf('site.loginlogo');
        $strorager = kernel::single("base_storager"); 
        $images_url = $strorager->image_path($images_id,'l');
        $this->pagedata['image_url'] = $images_url;

        $this->pagedata['login_image_url'] = $login_image_url;
        $this->pagedata['loginName'] = $_COOKIE['loginName'];

        $this->userPassport->set_next_page();
        $this->set_tmpl('passport');
        $this->page('site/passport/index.html', $flag);
    }//end function

    /**
     * 检查登陆账号是否需要开启手机验证
     */
    public function login_ajax_account(){
        $login_account = trim($_POST['uname']);
        if( $this->userPassport->check_login_account($login_account,$msg) ){
            echo json_encode(array('needVerify'=>'true'));exit;
        }else{
            echo json_encode(array('needVerify'=>'false'));exit;
        }
    }

    /*
     * 登录验证
     * */
    public function post_login(){
        //_POST过滤
        $post = utils::_filter_input($_POST);
        unset($_POST);
        $userData = array(
            'login_account' => $post['uname'],
            'login_password' => $post['password']
        );

        //是否需要进行手机验证
        if( !kernel::single('b2c_user_vcode')->mobile_login_verify($post['mobileVcode'],$post['uname'],'activation')){
            $msg = app::get('b2c')->_('手机短信验证码错误'); 
            $this->splash('failed',null,$msg,true);exit;
        }

        if(kernel::single('b2c_service_vcode')->status() && empty($post['verifycode'])){
            $msg = app::get('b2c')->_('请输入验证码!'); 
            $this->splash('failed',null,$msg,true);exit;
        }

        $member_id = kernel::single('pam_passport_site_basic')->login($userData,$post['verifycode'],$msg);
        if(!$member_id){
        	$member_card = $this->app->model('member_card')->getList('*',array('card_number'=>$userData['login_account'],'card_state'=>0));
        	if($member_card){
        		if($member_card[0]['expired_time'] > time() || !$member_card[0]['expired_time']){//判断如果是会员卡有没有超过会员卡的失效时间
        			$member_id = $this->userPassport->create_card_member($member_card[0]);
        			if(!$member_id){
        				$msg = app::get('b2c')->_('登陆错误,请重试');
        				$this->splash('failed',null,$msg,true);exit;
        			}
        		}else{
        			$msg = app::get('b2c')->_('该会员卡已超过了激活的时间');
        			$this->splash('failed',null,$msg,true);exit;
        		}       		
        	}else{
        		//设置登陆失败错误次数 一个小时三次错误后需要自动开启验证码
            	kernel::single('b2c_service_vcode')->set_error_count();
            	$data['needVcode'] = kernel::single('b2c_service_vcode')->status();
            	$this->splash('failed',null,$msg,true,$data);exit;
        	}
            
        }

        $b2c_members_model = $this->app->model('members');
        $member_point_model = $this->app->model('member_point');

        $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
        if(!$member_data){
            kernel::single('b2c_service_vcode')->set_error_count();
            $data['needVcode'] = kernel::single('b2c_service_vcode')->status();
            //在登录认证表中存在记录，但是在会员信息表中不存在记录
            $msg = $this->app->_('登录失败：会员数据存在问题,请联系商家或客服');
            $this->splash('failed',null,$msg,true,$data);exit;
        }

        $member_data = $member_data[0];
        $member_data['order_num'] = $this->app->model('orders')->count( array('member_id'=>$member_id) );

        if($this->app->getConf('site.level_switch')==1)
        {
            $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
        }
        if($this->app->getConf('site.level_switch')==0)
        {
            $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
        }

        $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        $this->userObject->set_member_session($member_id);
        $this->bind_member($member_id);
        $this->set_cookie('loginName',$post['uname'],time()+31536000);//用于记住密码
        $this->app->model('cart_objects')->setCartNum();
        $url = $this->userPassport->get_next_page('pc');
        if( !$url ){
            $url = kernel::single('b2c_frontpage')->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        $this->splash('success',$url,app::get('b2c')->_('登录成功'),true);
    }//end function
    
    

    public function namecheck() {
        $obj_member = &$this->app->model('members');
        $name = trim($_POST['pam_account']['login_name']);
        if (strlen($name) == 0) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写登录帐号，最少4个字符')));
            exit;
        }

        if (strlen($name) < 4) {
            echo json_encode(array('error' => app::get('b2c')->_('登录账号最少4个字符')));
            exit;
        } elseif (strlen($name) > 100) {
            echo json_encode(array('error' => app::get('b2c')->_('登录账号过长，请换一个重试')));
            exit;
        }

        if (!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', $name)) {
            echo json_encode(array('error' => app::get('b2c')->_('该登录账号包含非法字符')));
            exit;
        } else {
            if (!$obj_member->is_exists($name)) {
                echo json_encode(array('success' => app::get('b2c')->_('该登录账号可用')));
                exit;
            } else {
                echo json_encode(array('error' => app::get('b2c')->_('该登录账号已经被占用，请换一个重试')));
                exit;
            }
        }
    }

    public function passwordcheck() {
        $psd = trim($_POST['pam_account']['login_password']);
        if (strlen($psd) == 0) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写登录密码，最少8个数字')));
            exit;
        }

        if (strlen($psd) < 8) {
            echo json_encode(array('error' => app::get('b2c')->_('登录密码最少8个数字')));
            exit;
        } elseif (strlen($psd) > 100) {
            echo json_encode(array('error' => app::get('b2c')->_('登录密码过长，请换一个重试')));
            exit;
        }

        if (!preg_match('/^\+?[1-9][0-9]*$/', $psd)) {
            echo json_encode(array('error' => app::get('b2c')->_('该密码账号包含非法字符')));
            exit;
        }
    }

    public function mobilecheck() {
        $obj_member = &$this->app->model('members');
        $psd = trim($_POST['mobile']);
        if (strlen($psd) == 0) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写手机号码')));
            exit;
        }

        if (strlen($psd) != 11) {
            echo json_encode(array('error' => app::get('b2c')->_('手机号码格式输入不正确')));
            exit;
        } else {
            if (!$obj_member->is_exists_mobile($psd)) {
                echo json_encode(array('success' => app::get('b2c')->_('该手机号可用')));
                exit;
            } else {
                echo json_encode(array('error' => app::get('b2c')->_('该手机号已经被占用，请换一个重试')));
                exit;
            }
        }


        if (!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/", $psd)) {
            echo json_encode(array('error' => app::get('b2c')->_('该手机号码包含非法字符')));
            exit;
        }
    }

    public function psw_confirmcheck() {
        $psd = trim($_POST['pam_account']['psw_confirm']);
        if (strlen($psd) == 0) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写登录密码，最少8个数字')));
            exit;
        }

        if (strlen($psd) < 8) {
            echo json_encode(array('error' => app::get('b2c')->_('登录密码最少8个数字')));
            exit;
        } elseif (strlen($psd) > 100) {
            echo json_encode(array('error' => app::get('b2c')->_('登录密码过长，请换一个重试')));
            exit;
        }

        if (!preg_match('/^\+?[1-9][0-9]*$/', $psd)) {
            echo json_encode(array('error' => app::get('b2c')->_('该密码账号包含非法字符')));
            exit;
        }
    }

    function emailcheck() {
        $email = trim($_POST['contact']['email']);
        if (!$email) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写邮箱')));
            exit;
        }
        if (!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i', $email)) {
            echo json_encode(array('error' => app::get('b2c')->_('请填写正确的邮箱，例如：yourname@hotmail.com')));
            exit;
        }
        $obj_member = $this->app->model('members');
        $account = app::get('pam')->model('account')->getList('login_name', array('login_name' => $email));
        $m_email = $obj_member->is_exists_email($email);
        if ($m_email || $account) {
            echo json_encode(array('error' => app::get('b2c')->_('该邮箱已被使用，请换一个重试')));
            exit;
        } else {
            // echo json_encode(array('success'=>app::get('b2c')->_('该邮箱可以使用')));
            exit;
        }
    }

    function verify() {
        $this->begin($this->gen_url('passport', 'login'));
        $member_model = &$this->app->model('members');
        $verifyCode = app::get('b2c')->getConf('site.register_valide');
        if ($verifyCode == "true") {
            if (!base_vcode::verify('LOGINVCODE', strval($_POST['loginverifycode']))) {
                $this->splash('failed', $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'index')), app::get('b2c')->_('验证码错误'), true);
            }
        }
        $rows = app::get('pam')->model('account')->getList('account_id', array('account_type' => 'member', 'disabled' => 'false', 'login_name' => $_POST['login'], 'login_password' => pam_encrypt::get_encrypted_password($_POST['passwd'], pam_account::get_account_type($this->app->app_id), array('login_name' => $_POST['login']))));
        if ($rows) {
            $_SESSION['account'][pam_account::get_account_type($this->app->app_id)] = $rows[0]['account_id'];
            $this->bind_member($rows[0]['account_id']);
            $this->end(true, app::get('b2c')->_('登录成功，进入会员中心'), $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index')));
        } else {
            $_SESSION['login_msg'] = app::get('b2c')->_('用户名或密码错误');
            $this->end(false, $_SESSION['login_msg'], $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login')));
        }
    }

    function __restore() {
        if ($_SESSION['login_info']['post']) {
            call_user_func_array(array(&$this, 'redirect'), $_SESSION['login_info']['action']);
        }
    }

    //注册页面
    public function signup($url=null){
        //检查是否登录，如果已登录则直接跳转到会员中心
        $this->check_login();

        $this->userPassport->set_next_page();

        if( $_GET['mini_passport'] ){
            $this->pagedata['mini_passport'] = $_GET['mini_passport'];
            if(defined('DEBUG_JS') && constant('DEBUG_JS')){
                $path = 'js';
            }
            else {
                $path = 'js_mini';
            }
            $shop['url']['datepicker'] = app::get('site')->res_url.'/'.$path;
            $shop['base_url'] = kernel::base_url().'/';
            $this->pagedata['shopDefine'] = json_encode($shop);
        }

        //获取会员注册项
        $this->pagedata['attr'] = $this->userPassport->get_signup_attr();

        $this->set_tmpl('passport');
        //注册是否需要验证码
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');

        $this->page("site/passport/signup.html",$_GET['mini_passport']);
    }

    public function license(){
        $this->pagedata['reg_license'] = app::get('b2c')->getConf('b2c.register.setting_member_license');
        $this->page('site/passport/license.html');
    }

    public function privacy(){
        $this->pagedata['reg_privacy'] = app::get('b2c')->getConf('b2c.register.setting_member_privacy');
        $this->page('site/passport/license.html');
    }

    //注册的时，检查账号
    public function signup_ajax_check_name(){
        $flag = $this->userPassport->check_signup_account( trim($_POST['pam_account']['login_name']),$msg );
        if($flag){
            if($msg == 'mobile'){
                echo json_encode(array('needVerify'=>'true'));exit;
            }
            $this->splash('success',null,$this->app->_('该登录账号可用'),true );exit;
        }else{
            $this->splash('error',null,$msg,true);exit;
        }
    }

   /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function create(){
        if($_POST['response_json'] == 'true'){
            $ajax_request = true;
        }else{
            $ajax_request = false;
        }
        if( !$this->userPassport->check_signup($_POST,$msg) ){
            $this->splash('failed',null,$msg,$ajax_request);
        }

        $saveData = $this->userPassport->pre_signup_process($_POST);

        if( $member_id = $this->userPassport->save_members($saveData) ){
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
            $data['uname'] = $saveData['pam_account']['login_account'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['refer_url'] = $refer_url ? $refer_url : '';
            $data['is_frontend'] = true;
            $obj_account=$this->app->model('member_account');
            $obj_account->fireEvent('register',$data,$member_id);
            if(!strpos($_SESSION['pc_next_page'],'cart')){
                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'sign_tips'));
            }else{
                $url = $_SESSION['pc_next_page'];
            }
            $this->splash('success',$url,app::get('b2c')->_('注册成功'),$ajax_request);
        }

        $this->splash('failed',$back_url,app::get('b2c')->_('注册失败'),$ajax_request);
    }

    //注册后跳转页面
    public function sign_tips(){
        $member_id = $this->userObject->get_member_id();
        if(!$member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
            $this->splash('failed',$url,app::get('b2c')->_('页面已过期，请重新登录在会员中心设置'));
        }

        $url = $this->userPassport->get_next_page('pc');
        if(!$url){
          $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        $pamMembersData = $this->userObject->get_pam_data('login_account',$member_id);
        if($pamMembersData['local']){
            $this->redirect($url);//已近设置则不需要在设置 直接跳转到会员中心
        }

        $this->pagedata['data'] = $pamMembersData;
        $this->pagedata['url'] = $url;

        $this->set_tmpl('passport');
        $this->page("site/passport/sign-tips.html");
    }

    //设置用户名
    public function save_local_uname(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        if( $this->userPassport->set_local_uname($_POST['local_uname'],$msg) ){
            $member_id = $this->userObject->get_member_id();
            $this->bind_member($member_id);
            $this->splash('success',null,$msg,true);
        }else{
            $msg = $msg ? $msg : app::get('b2c')->_('页面已过期，请重新登录在会员中心设置');
            $this->splash('failed',null,$msg,true);
        }
    }

    /*----------- 次要流程 ---------------*/
    public function lost(){
        $this->check_login();
        $this->path[] = array('title'=>app::get('b2c')->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->set_tmpl('passport');
        $this->page("site/passport/forgot.html");
    }

    public function sendPSW(){
        $username = $_POST['username'];
        $member_id = $this->userObject->get_member_id_by_username($username);

        if(!$member_id){
            $msg = app::get('b2c')->_('该账号不存在，请检查'); 
            $this->splash('failed',null,$msg,true);
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
        }
        $this->pagedata['data'] = $data;
        
        $this->display("site/passport/forgot/forgot2.html");
    }


    //发送发送邮件验证码
    public function send_vcode_email()
    {

        $email = $_POST['uname'];
        $type = $_POST['type']; //激活activation

        if( !$type || !$email ){
            $msg = app::get('b2c')->_('请填写正确的邮箱');
            $this->splash('failed',null,$msg,true);
        }

        $login_type = $this->userPassport->get_login_account_type($email);
        if($login_type != 'email'){
            $msg = app::get('b2c')->_('请填写正确的邮箱');
            $this->splash('failed',null,$msg,true);   
        }

        if($type == 'reset' && !$this->userPassport->check_signup_account( trim($email),$msg )){
            $this->splash('failed',null,$msg,true);   
        }
        
        $userVcode = kernel::single('b2c_user_vcode');
        if($email){
            $vcode = $userVcode->set_vcode($email,$type,$msg);
        }
        if($vcode){
            //发送邮箱验证码
            $data['vcode'] = $vcode;
            $data['uname'] = $_POST['uname'];
            if( !$userVcode->send_email($type,(string)$email,$data) ){
                $msg = app::get('b2c')->_('参数错误');
                $this->splash('failed',null,$msg,true);
            }
        }else{
            $this->splash('failed',null,$msg,true);
        }    
        $msg = app::get('b2c')->_('邮件已发送');
        $this->splash('success',null,$msg,true);
    }

    //短信发送验证码
    public function send_vcode_sms(){
        $mobile = $_POST['uname'];
        $type = $_POST['type']; //激活activation

        if( !$type || !$mobile ){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,true);
        }

        $login_type = $this->userPassport->get_login_account_type($mobile);
        if($login_type != 'mobile'){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,true);   
        }

        if($type == 'reset' && !$this->userPassport->check_signup_account( trim($mobile),$msg )){
            $this->splash('failed',null,$msg,true);
        }
        
        $userVcode = kernel::single('b2c_user_vcode');
        if($mobile){
            $vcode = $userVcode->set_vcode($mobile,$type,$msg);
        }
        if($vcode){
            //发送验证码 发送短信
            $data['vcode'] = $vcode;
            if( !$userVcode->send_sms($type,(string)$mobile,$data) ){
                $msg = app::get('b2c')->_('发送失败');
                $this->splash('failed',null,$msg,true);
            }
        }else{
            $this->splash('failed',null,$msg,true);
        }
    }

    public function resetpwd_code(){
        $this->check_login();
        $send_type = $_POST['send_type'];
        $userVcode = kernel::single('b2c_user_vcode');
        if( !$vcodeData = $userVcode->verify($_POST[$send_type.'vcode'],$_POST[$send_type],'forgot')){
            $msg = app::get('b2c')->_('验证码错误'); 
            $this->splash('failed',null,$msg,true);exit;
        }
        $data['key'] = $userVcode->get_vcode_key($_POST[$send_type],'forgot');
        $data['key'] = md5($vcodeData['vcode'].$data['key']);
        $data['account'] = $_POST[$send_type]; 
        $this->pagedata['data'] = $data;
        $this->display('site/passport/forgot/forgot3.html');
    }

    public function resetpassword(){
        $this->check_login();
        $userVcode = kernel::single('b2c_user_vcode');
        $vcodeData = $userVcode->get_vcode($_POST['account'],'forgot');
        $key = $userVcode->get_vcode_key($_POST['account'],'forgot');
        if($_POST['account'] !=$vcodeData['account']  || $_POST['key'] != md5($vcodeData['vcode'].$key) ){
            $msg = app::get('b2c')->_('页面已过期,请重新找回密码');
            $this->splash('failed',null,$msg,true);exit;
        }

        if( !$this->userPassport->check_passport($_POST['login_password'],$_POST['psw_confirm'],$msg) ){
            $this->splash('failed',null,$msg,true);exit;
        } 

        $member_id = $this->userObject->get_member_id_by_username($_POST['account']);
        if( !$this->userPassport->reset_passport($member_id,$_POST['login_password']) ){
            $msg = app::get('b2c')->_('密码重置失败,请重试');
            $this->splash('failed','back',$msg,$_POST['response_json']);
        }
        $this->display('site/passport/forgot/forgot4.html');
    }

    public function error(){
        $this->unset_member();
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        $this->_response->set_redirect($back_url)->send_headers();
    }


    public function logout($url){
        if(!$url){
            $url = array('app'=>'site','ctl'=>'default','act'=>'index','full'=>1);
        }
        $this->unset_member();
        $this->app->model('cart_objects')->setCartNum($arr);
        $this->redirect($url);
    }

    public function unset_member(){
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }
        $this->app->member_id = 0;
        kernel::single('base_session')->set_cookie_expires(0);
        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',null,time()-3600);
        $this->set_cookie('UNAME','',time()-3600);
        $this->set_cookie('MLV','',time()-3600);
        $this->set_cookie('CUR','',time()-3600);
        $this->set_cookie('LANG','',time()-3600);
        $this->set_cookie('S[MEMBER]','',time()-3600);
        $this->set_cookie('S[SIGN][AUTO]',0,time()-3600);
        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }
    }
    
    /**
     * add by Jason 绑定会员卡
     */
    public function bind_member_card(){   
    	$login_member_id = $this->userObject->get_member_id();
    	$card = trim($_POST['card_number']);
    	$card_password = trim($_POST['card_password']);
    	$type = trim($_POST['type']); //绑定类型,card_to_member为卡转入会员,member_to_card为会员转入卡
    	if(!$type){
    		$msg = app::get('b2c')->_('请选择要绑定的类型');
    		$this->splash('failed',null,$msg,true);
    	}
    	
    	if( !$card || !is_numeric($card)){
    		$msg = app::get('b2c')->_('请填写正确的会员卡号');
    		$this->splash('failed',null,$msg,true);
    	}
    	$member_card = $this->app->model('member_card')->getList('*',array('card_number'=>$card));
    	if(!$member_card){//先从会员卡表中直接读取卡号,判断卡号是否存在
    		$msg = app::get('b2c')->_('会员卡不存在');
    		$this->splash('failed',null,$msg,true);
    	}else{//会员卡号存在
    		$member_id = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$card));
    		if($member_id[0]['member_id']){//卡号存在且卡已被激活,要检查该会员卡是否被绑定,还要检查是否改了密码,验证密码的一致性
    			$new_card = '';
    			$pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id[0]['member_id']));
    			if(count($pamMemberData) > 1){//被激活后判断是否被绑定
    				$msg = app::get('b2c')->_('该会员卡已被绑定');
    				$this->splash('failed',null,$msg,true);
    			}
    			$use_pass_data['login_name'] = $card;
    			$use_pass_data['createtime'] = $pamMemberData[0]['createtime'];
    			$login_password = pam_encrypt::get_encrypted_password($card_password,'member',$use_pass_data);
    			if($login_password != $pamMemberData[0]['login_password']){//会员卡被激活之后,可能被改密码,要进行密码验证
    				$msg = app::get('b2c')->_('会员卡密码错误');
    				$this->splash('failed',null,$msg,true);
    			}
    		}else{//卡号存在且未被激活
    			$new_card = '1';
    			$card_psw_isright = $this->app->model('member_card')->getList('*',array('card_number'=>$card,'card_password'=>$card_password));
    			if(!$card_psw_isright){//直接对比会员卡表中的密码是否一致即可
    				$msg = app::get('b2c')->_('会员卡密码错误');
    				$this->splash('failed',null,$msg,true);
    			}
    		}    		
    	}    	
    	$status = $this->_bind_member_card($new_card, $type, $login_member_id, $card);
    	switch ($status){
    			case 'old_member_wrong' :
					$msg = app::get('b2c')->_('当前会员信息错误错误');
    				$this->splash('failed',null,$msg,true);
					break;
				case 'insert_membercard_wrong' :
					$msg = app::get('b2c')->_('会员卡注入错误');
    				$this->splash('failed',null,$msg,true);
				case 'add_advance_wrong' :
					$msg = app::get('b2c')->_('增加预存款是失败');
    				$this->splash('failed',null,$msg,true);
					break;
				case 'reduce_advance_wrong' :
					$msg = app::get('b2c')->_('减少预存款失败');
    				$this->splash('failed',null,$msg,true);
					break;
				case 'add_point_wrong' :
					$msg = app::get('b2c')->_('增加积分失败');
    				$this->splash('failed',null,$msg,true);
    				break;
				case 'reduce_point_wrong' :
					$msg = app::get('b2c')->_('减少积分失败');
    				$this->splash('failed',null,$msg,true);
					break;
				case 'delete_oldcard_failed' :
					$msg = app::get('b2c')->_('删除绑定的旧会员卡失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'update_level_failed' :
					$msg = app::get('b2c')->_('等级更新失败');
					$this->splash('failed',null,$msg,true);
					break;		
				case 'update_newcard_failed' :
					$msg = app::get('b2c')->_('更新新会员卡失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'update_oldcard_failed' :
					$msg = app::get('b2c')->_('更新旧会员卡失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'update_oldmember_failed' :
					$msg = app::get('b2c')->_('更新旧会员失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'update_cardmember_failed' :
					$msg = app::get('b2c')->_('更新会员卡会员失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'update_old_cardmember_failed' :
					$msg = app::get('b2c')->_('更新旧会员卡会员失败');
					$this->splash('failed',null,$msg,true);
					break;
				case 'ok' :
					$msg = app::get('b2c')->_('绑定成功');
					$this->splash('success',null,$msg,true);
					break;
    		} 
    }
    
    private function _bind_member_card($new_card,$type,$member_id,$card_number){
    	$memberMdl = app::get('b2c')->model('members');
    	$pamMemberMdl = app::get('pam')->model('members');
    	$member_card = $this->app->model('member_card')->getList('*',array('card_number'=>$card_number));//会员卡表信息    	
    	$old_pammember_info = $pamMemberMdl->getList('*',array('member_id'=>$member_id));//现有会员pam_member信息
    	$old_member_id = $old_pammember_info[0]['member_id'];
    	$old_member_info = $memberMdl->getList('*',array('member_id'=>$member_id));//现有会员member信息
    	   	
    	if(!$old_member_info){
    		return 'old_member_wrong'; 
    	}   	

    	//开始事务
    	$db = kernel::database();
    	$transaction_status = $db->beginTransaction();
    	
    	foreach($old_pammember_info as $opi){
    		if($opi['login_type'] == 'local' && is_numeric($opi['login_account'])){
    			$pamMemberMdl->delete(array('member_id'=>$member_id,'login_type'=>'local'));//删除现有会员绑定的会员卡
    			$affect_delet_row = $pamMemberMdl->db->affect_row();
    			if(!$affect_delet_row){
    				$db->rollback();
    				return 'delete_oldcard_failed';
    			}
    		}
    	}    	
    	
    	$new_member_lv = $member_card[0]['card_lv_id'] > $old_member_info[0]['member_lv_id'] ? $member_card[0]['card_lv_id'] : $old_member_info[0]['member_lv_id'];//对比得出新等级ID
    	    	
    	$objAdvances = $this->app->model("member_advance");
    	$member_point = $this->app->model('member_point');
    	if($new_card){//如果是未被激活的卡
    		$new_member_id = $this->userPassport->create_card_member($member_card[0]);//新卡则先注册一个新账号    		
    		if(!$new_member_id){//用新卡注册新账号失败
    			$db->rollback();
    			return 'insert_membercard_wrong';
    		}
    		$new_member_info = $memberMdl->getList('*',array('member_id'=>$new_member_id));//获得新注册的会员卡账号的member表信息
    		$new_pammember_info = $pamMemberMdl->getList('*',array('member_id'=>$new_member_id));//获得新注册的会员卡pam_member表信息
    		if($type == 'card_to_member'){//如果是新卡,并且是卡转现有会员
    			$pamMemberMdl->update(              //
    			array('member_id'=>$member_id,'password_account'=>$old_pammember_info[0]['password_account'],'login_password'=>$old_pammember_info[0]['login_password'],'pay_password'=>$old_pammember_info[0]['pay_password'],'createtime'=>$old_pammember_info[0]['createtime'],'disabled'=>'true'),
    			array('member_id'=>$new_member_id));
    			$update_new_row = $pamMemberMdl->db->affect_row();
    			if(!$update_new_row){
    				$db->rollback();
    				return 'update_newcard_failed';
    			}
    			if($new_member_info[0]['advance']){ //如果新卡含有金钱,则将新卡金钱转移到现有会员上
    				$msg = '会员卡绑定预存款转移';    				
    				if(!$objAdvances->add($member_id, $new_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为合并的会员增加预存款
    					$db->rollback();
    					return 'add_advance_wrong';
    				}
    				if(!$objAdvances->add($new_member_id, -$new_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为被合并的会员增加预存款
    					$db->rollback();
    					return 'reduce_advance_wrong';
    				}
    			}
    			if($new_member_info[0]['point']){   //如果新卡含有积分,则将新卡积分转移到现有会员上
    				if(!$member_point->change_point($member_id,$new_member_info[0]['point'],$msg,'register_score',2,$member_id,$member_id,'exchange')){
    					$db->rollback();
    					return 'add_point_wrong';
    				}
    				if(!$member_point->change_point($new_member_id,-$new_member_info[0]['point'],$msg,'register_score',2,$new_member_id,$new_member_id,'exchange')){
    					$db->rollback();
    					return 'reduce_point_wrong';
    				}
    			}
    			$memberMdl->update(array('member_lv_id'=>$new_member_lv),array('member_id'=>$member_id));//更新现有会员的等级
    		}
    		if($type == 'member_to_card'){//如果是新卡,并且是现有会员转卡
    			$pamMemberMdl->update(array('disabled'=>'true'),array('member_id'=>$member_id,'login_type'=>'local'));//将原来的旧local账号设置为disabled true
    			$update_oldcard_row = $pamMemberMdl->db->affect_row();
    			if(!$update_oldcard_row){
    				$db->rollback();
    				return 'update_oldcard_failed';
    			}
    			$pamMemberMdl->update(//将现有会员的密码等信息重置为会员卡账号对应的密码信息
    			array('member_id'=>$new_member_id,'password_account'=>$new_pammember_info[0]['password_account'],'login_password'=>$new_pammember_info[0]['login_password'],'pay_password'=>$new_pammember_info[0]['pay_password'],'createtime'=>$new_pammember_info[0]['createtime'],'disabled'=>'true'),
    			array('member_id'=>$member_id));
    			$update_oldmember_row = $pamMemberMdl->db->affect_row();
    			if(!$update_oldmember_row){
    				$db->rollback();
    				return 'update_oldmember_failed';
    			}
    			
    			if($old_member_info[0]['advance'] > 0){//增减预存款
    				$msg = '会员卡绑定预存款转移';
    				if(!$objAdvances->add($new_member_id, $old_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为合并的会员增加预存款
    					$db->rollback();
    					return 'add_advance_wrong';
    				}
    				if(!$objAdvances->add($member_id, -$old_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为被合并的会员增加预存款
    					$db->rollback();
    					return 'reduce_advance_wrong';
    				}
    			}
    			if($old_member_info[0]['point'] > 0){//增减积分
    				if(!$member_point->change_point($new_member_id,$old_member_info[0]['point'],$msg,'register_score',2,$new_member_id,$new_member_id,'exchange')){
    					$db->rollback();
    					return 'add_point_wrong';
    				}
    				if(!$member_point->change_point($member_id,-$old_member_info[0]['point'],$msg,'register_score',2,$member_id,$member_id,'exchange')){
    					$db->rollback();
    					return 'reduce_point_wrong';
    				}
    			}
    			$memberMdl->update(array('member_lv_id'=>$new_member_lv),array('member_id'=>$new_member_id));//更新等级
    		}    		
    	}else{//如果是已激活且未被绑定的会员卡
    		$card_pammember_info = $pamMemberMdl->getList('*',array('login_account'=>$card_number));//会员卡会员pam_member表信息
    		$card_member_id = $card_pammember_info[0]['member_id'];
    		$card_member_info = $memberMdl->getList('*',array('member_id'=>$card_member_id));//会员卡会员member表信息
    		if($type == 'card_to_member'){//会员卡转现有会员
    			$pamMemberMdl->update(              //
    			array('member_id'=>$member_id,'password_account'=>$old_pammember_info[0]['password_account'],'login_password'=>$old_pammember_info[0]['login_password'],'pay_password'=>$old_pammember_info[0]['pay_password'],'createtime'=>$old_pammember_info[0]['createtime'],'disabled'=>'true'),
    			array('member_id'=>$card_member_id));
    			$update_cardmember_row = $pamMemberMdl->db->affect_row();
    			if(!$update_cardmember_row){
    				$db->rollback();
    				return 'update_cardmember_failed';
    			}
    			if($card_member_info[0]['advance'] > 0){
    				$msg = '会员卡绑定预存款转移';
    				if(!$objAdvances->add($old_member_id, $card_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为合并的会员增加预存款
    					$db->rollback();
    					return 'add_advance_wrong';
    				}
    				if(!$objAdvances->add($card_member_id, -$card_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为被合并的会员增加预存款
    					$db->rollback();
    					return 'reduce_advance_wrong';
    				}
    			}
    			if($card_member_info[0]['point'] > 0){
    				if(!$member_point->change_point($old_member_id,$card_member_info[0]['point'],$msg,'register_score',2,$old_member_id,$old_member_id,'exchange')){
    					$db->rollback();
    					return 'add_point_wrong';
    				}
    				if(!$member_point->change_point($card_member_id,-$card_member_info[0]['point'],$msg,'register_score',2,$card_member_id,$card_member_id,'exchange')){
    					$db->rollback();
    					return 'reduce_point_wrong';
    				}
    			}
    			$memberMdl->update(array('member_lv_id'=>$new_member_lv),array('member_id'=>$member_id));
    		}
    		if($type == 'member_to_card'){
    			$pamMemberMdl->update(//将现有会员的密码等信息重置为会员卡账号对应的密码信息
    			array('member_id'=>$card_member_id,'password_account'=>$card_pammember_info[0]['password_account'],'login_password'=>$card_pammember_info[0]['login_password'],'pay_password'=>$card_pammember_info[0]['pay_password'],'createtime'=>$card_pammember_info[0]['createtime'],'disabled'=>'true'),
    			array('member_id'=>$member_id));
    			$update_old_cardmember_row = $pamMemberMdl->db->affect_row();
    			if(!$update_old_cardmember_row){
    				$db->rollback();
    				return 'update_old_cardmember_failed';
    			}
    			if($old_member_info[0]['advance'] > 0){
    				$msg = '会员卡绑定预存款转移';
    				if(!$objAdvances->add($card_member_id, $old_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为合并的会员增加预存款
    					$db->rollback();
    					return 'add_advance_wrong';
    				}
    				if(!$objAdvances->add($old_member_id, -$old_member_info[0]['advance'], app::get('b2c')->_('会员卡绑定预存款转移'), $msg)){//为被合并的会员增加预存款
    					$db->rollback();
    					return 'reduce_advance_wrong';
    				}
    			}
    			if($old_member_info[0]['point'] > 0){
    				if(!$member_point->change_point($card_member_id,$old_member_info[0]['point'],$msg,'register_score',2,$card_member_id,$card_member_id,'exchange')){
    					$db->rollback();
    					return 'add_point_wrong';
    				}
    				if(!$member_point->change_point($old_member_id,-$old_member_info[0]['point'],$msg,'register_score',2,$old_member_id,$old_member_id,'exchange')){
    					$db->rollback();
    					return 'reduce_point_wrong';
    				}
    			}
    			$memberMdl->update(array('member_lv_id'=>$new_member_lv),array('member_id'=>$card_member_id));
    		}
    	}
    	$db->commit($transaction_status);
    	return 'ok';
    }
}
