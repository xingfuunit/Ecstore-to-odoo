<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class wap_frontpage extends wap_controller{
    //todo
    protected $member = array();
    function __construct(&$app){
        parent::__construct($app);
        if($_COOKIE['S']['SIGN']['AUTO'] > 0){
            $minutes = 14*24*60;
            kernel::single('base_session')->set_sess_expires($minutes);
        }//如果有自动登录，设置session过期时间，单位：分
        if($_COOKIE['S']['SIGN']['REMEMBER'] !== '1'){
            setcookie("S[SIGN][REMEMBER]", null, time() - 3600);
            setcookie("loginName", null, time() - 3600);
        }
        $this->pagedata['site_b2c_remember'] = $_COOKIE['S']['SIGN']['REMEMBER'];

        $this->set_weixin_openid();
    }

    public function set_weixin_openid(){
        kernel::single('base_session')->start();
        if( !empty($_GET['signature']) &&  !empty($_GET['openid']) ){
            $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['u_eid'],'status'=>'active'));
            $flag = kernel::single('weixin_object')->check_wechat_sign($_GET['signature'], $_GET['openid']);
            if( $flag && !empty($bind)){
                $openid = $_GET['openid'];
            }
        }elseif( !empty($_GET['code']) && !empty($_GET['state']) ){
            $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
            if( !empty($bind) &&  kernel::single('weixin_wechat')->get_oauth2_accesstoken($bind['id'],$_GET['code'],$result) ){
                $openid = $result['openid'];
            }
        }
        /*$this->from_weixin = kernel::single('weixin_wechat')->from_weixin();
        if($this->from_weixin){
        if( $openid ){
            $bindTagData = app::get('pam')->model('bind_tag')->getRow('*',array('open_id'=>$openid));
            if( $bindTagData ){
                $_SESSION['weixin_u_nickname'] = $bindTagData['tag_name'];
            }else{
                $res = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
                $_SESSION['weixin_u_nickname'] = $res['nickname'];
            }
            $_SESSION['weixin_u_openid'] = $openid;
            $_SESSION['is_bind_weixin'] = false;
            if($openid){
            //一键登陆
            $userData = array(
                'login_account' => $openid,
                'login_password' => "123456"
            );
            $this->weixinObject = kernel::single('weixin_object');
            $this->userObject = kernel::single('b2c_user_object');
            $member_id = $this->login($userData,'',$msg);
            $this->userObject->set_member_session($member_id);
            }
        }
       }*/
        return true;
    }
    
    /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function create($demo_name){
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

            return true;
        }

    }
    
    /*
     * 前台用户登录验证方法
     *
     * @params $login_account 登录账号
     * @params $login_password 登录密码
     * @params $vcode 验证码
     * */
    public function login($userData,$vcode=false,&$msg){
        $userData = utils::_filter_input($userData);//过滤xss攻击
        //如果指定了登录类型,则不再进行获取(邮箱登录，手机号登录，用户名登录)
        if(!$userData['login_type']){
            $userPassport = kernel::single('b2c_user_passport');
            $userData['login_type'] = $userPassport->get_login_account_type($userData['login_account']);
        }
        $filter = array('login_type'=>$userData['login_type'],'login_account'=>$userData['login_account']);
        $account = app::get('pam')->model('members')->getList('member_id,password_account,login_password,createtime',$filter);
        if(!$account){
            $msg = app::get('pam')->_('用户名或密码错误');
            return false;
        }
        $login_password = pam_encrypt::get_encrypted_password($userData['login_password'],'member',array('createtime'=>$account[0]['createtime'],'login_name'=>$account[0]['password_account']));

        if($account[0]['login_password'] != $login_password ){
            $msg = app::get('pam')->_('用户名或密码错误');
            return false;
        }
        return $account[0]['member_id'];
    }//end function

    /*
     * 登录验证码验证方法
     *
     * @params $vcode 验证码
     * @return bool
     * */
    public function vcode_verify($vcode){
      if(!base_vcode::verify('b2c',$vcode)){
        return false; 
      }
      return true;
    }

    function verify_member(){
        kernel::single('base_session')->start();
        $userObject = kernel::single('b2c_user_object');
        if( app::get('b2c')->member_id = $userObject->get_member_id() ){
            $data = $userObject->get_members_data(array('members'=>'member_id'));
            if($data){
                //登录受限检测
                $res = $this->loginlimit(app::get('b2c')->member_id,$redirect);
                if($res){
                    $this->redirect($redirect);
                }else{
                    return true;
                }
            }else{
                $this->redirect(array('app'=>'b2c', 'ctl'=>'wap_passport', 'act'=>'error'));
            }
        }else{
            $this->redirect(array('app'=>'b2c', 'ctl'=>'wap_passport', 'act'=>'error'));
        }
    }
    /**
    * loginlimit-登录受限检测
    *
    * @param      none
    * @return     void
    */
    function loginlimit($mid,&$redirect) {
        $services = kernel::servicelist('loginlimit.check');
        if ($services){
            foreach ($services as $service){
                $redirect = $service->checklogin($mid);
            }
        }
        return $redirect?true:false;
    }//End Function

    public function bind_member($member_id){
        $columns = array(
            'account'=> 'member_id,login_account,login_password',
            'members'=> 'member_id,member_lv_id,cur,lang',
        );
        $userObject = kernel::single('b2c_user_object');
        $data = $userObject->get_members_data($columns);
        $secstr = kernel::single('b2c_user_passport')->gen_secret_str($member_id, $data['account']['login_name'], $data['account']['login_password']);
        $login_name = $userObject->get_member_name($data['account']['login_name']); 
        $this->cookie_path = kernel::base_url().'/';
        #$this->set_cookie('MEMBER',$secstr,0);
        $this->set_cookie('loginName',$login_name,time()+31536000);
        $this->set_cookie('UNAME',$login_name,0);
        $this->set_cookie('MLV',$data['members']['member_lv_id'],0);
        $this->set_cookie('CUR',$data['members']['cur'],0);
        $this->set_cookie('LANG',$data['members']['lang'],0);
        $this->set_cookie('S[MEMBER]',$member_id,0);
    }

    public function _check_verify_member($member_id=0)
    {
        if (isset($member_id) && $member_id)
        {
            $userObject = kernel::single('b2c_user_object');
            $current_member_id = $userObject->get_member_id();
            if ($member_id != $current_member_id)
            {
                $this->begin();
                $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'wap','ctl'=>'default','act'=>'index')));
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    public function get_current_member()
    {
        
      return kernel::single('b2c_user_object')->get_current_member();
    }

    function set_cookie($name,$value,$expire=false,$path=null){
        if(!$this->cookie_path){
            $this->cookie_path = kernel::base_url().'/';
            #$this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
            $this->cookie_life =  app::get('b2c')->getConf('system.cookie.life');
        }
        $this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
        $expire = $expire === false ? time()+$this->cookie_life : $expire;
        setcookie($name,$value,$expire,$this->cookie_path);
        $_COOKIE[$name] = $value;
    }

    function check_login(){
        kernel::single('base_session')->start();
        if($_SESSION['account'][pam_account::get_account_type('b2c')]){
            return true;
        }else{
            return false;
        }
    }
    /*获取当前登录会员的会员等级*/
    function get_current_member_lv()
    {
        kernel::single('base_session')->start();
        if($member_id = $_SESSION['account'][pam_account::get_account_type('b2c')]){
           $member_lv_row = app::get("pam")->model("account")->db->selectrow("select member_lv_id from sdb_b2c_members where member_id=".intval($member_id));
           return $member_lv_row ? $member_lv_row['member_lv_id'] : -1;
        }
        else{
            return -1;
        }
    }
    function setSeo($app,$act,$args=null){
        // 触屏版暂时用pc端的seo信息
        $app = str_ireplace("wap_","site_",$app);
        $seo = kernel::single('site_seo_base')->get_seo_conf($app,$act,$args);
        $this->title = $args['goods_cat'];
        $this->keywords = $seo['seo_keywords'];
        $this->description = $seo['seo_content'];
        $this->nofollow = $seo['seo_nofollow'];
        $this->noindex = $seo['seo_noindex'];
    }//End Function

    function get_member_fav($member_id=null){
        $obj_member_goods = app::get('b2c')->model('member_goods');
        return $obj_member_goods->get_member_fav($member_id);
    }


}
