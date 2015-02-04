<?php
/**
* 
*/
class microshop_frontpage
{
	//todo
    protected $member = array();
    protected $rpcService = array();
    function __construct(&$app){
        parent::__construct($app);
        $this->rpcService = kernel::single('base_rpc_service');
    }

    /**
    * 检测用户是否登陆
    *
    * 当用户没有登陆则提示
    *
    * @param      int   $type   类型,API调用时type = 2
    * @return     mix
    */
	function verify_member( $type = 1 ){
		$userObject = kernel::single('b2c_user_object');
		if( $this->app->member_id = $userObject->get_member_id() ){
            $data = $userObject->get_members_data(array('members'=>'member_id'));
            if($data) {
                //登录受限检测
                $res = $this->loginlimit($data['members']['member_id'],$redirect);
                if ( $res ) {
                    $type == 1 ? $this->redirect($redirect) : $this->rpcService->send_user_error('need_login', '无效登录');
                } else {
                    return true;
                }
            } else {
                $type == 1 ? $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error')) : $this->rpcService->send_user_error('need_login', '无效登录');
            }
        } else {
            $type == 1 ? $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error')) : $this->rpcService->send_user_error('need_login', '无效登录');
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
        return $redirect ? true : false;
    }//End Function
}
