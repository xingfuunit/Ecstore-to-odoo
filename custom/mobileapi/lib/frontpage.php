<?php
/**
* 
*/
class mobileapi_frontpage
{
	//todo
    protected $member = array();
    function __construct(&$app){
//         parent::__construct($app);
        $this->obj_session = kernel::single('base_session');
        $this->obj_session->start();
    }

    /**
    * 检测用户是否登陆
    *
    * 当用户没有登陆则提示
    *
    * @param      none
    * @return     void
    */
	function verify_member(){
		$userObject = @kernel::single('b2c_user_object');
		if( $this->app->member_id = $userObject->get_member_id() ){
            $data = $userObject->get_members_data(array('members'=>'member_id'));
            if($data){
                return true;
            }else{
                kernel::single('base_rpc_service')->send_user_error('need_login', '无效登录');
            }
        }else{
        	kernel::single('base_rpc_service')->send_user_error('need_login', '请重新登录');
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
    	$this->set_cookie('UNAME',$login_name,0);
    	$this->set_cookie('MLV',$data['members']['member_lv_id'],0);
    	$this->set_cookie('CUR',$data['members']['cur'],0);
    	$this->set_cookie('LANG',$data['members']['lang'],0);
    	$this->set_cookie('S[MEMBER]',$member_id,0);
    }
    
    function set_cookie($name,$value,$expire=false,$path=null){
    	if(!$this->cookie_path){
    		$this->cookie_path = kernel::base_url().'/';
    		#$this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
    		$this->cookie_life =  $this->app->getConf('system.cookie.life');
    	}
    	$this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
    	$expire = $expire === false ? time()+$this->cookie_life : $expire;
    	setcookie($name,$value,$expire,$this->cookie_path);
    	$_COOKIE[$name] = $value;
    }

    public function get_current_member()
    {
        
      return kernel::single('b2c_user_object')->get_current_member();
    }
}