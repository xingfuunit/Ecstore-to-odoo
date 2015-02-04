<?php

class mobileapi_rpc_staff  extends mobileapi_frontpage {
    
    function __construct($app){
		parent::__construct($app);
		
		$this->app = app::get('b2c');
		//$this->verify_member();
		
		$this->rpcService = kernel::single('base_rpc_service');
		
		$this->_request = kernel::single('base_component_request');
		$this->_response = kernel::single('base_component_response');
                $this->staffMdl=$this->app->model('local_staff');
                $this->staffObject=kernel::single('b2c_local_staff');
    }
    
    //门店员工登陆
    public function post_login(){
        $login_data=array(
            'login_name'=>$_POST['login_name'],
//            'login_password'=>md5($_POST['login_password']),
//            'local_id'=>$_POST['local_id'],
           
        );
       
        $staff = $this->staffMdl->getList('*',$login_data);
         
        if(!$staff){
            $data['msg']='登陆失败：账号错误';
            $this->rpcService->send_user_error('login_error', $data);
        }
        if($staff[0]['disabled']=='true'){
            $data['msg']='登陆失败：账号已失效';
            $this->rpcService->send_user_error('login_error', $data);            
        }
        $staff_id=$staff[0]['staff_id'];
        $branch_id=$staff[0]['local_id'];
        
        $update_data=array(
            'logintime'=>time()
        );
        $this->staffMdl->update($update_data,array('staff_id'=>$staff_id));
        $this->staffObject->set_staff_session($staff_id,local_id);
        return $staff[0];
        
    }
    
    //门店员工退出
    public  function logout(){
        $staff_id=$_POST['staff_id'];
        //$local_id=$_POST['$local_id'];
        $update_data=array(
            'logouttime'=>time(),
        );
         if($this->staffMdl->update($update_data,array('staff_id'=>$staff_id))){
             unset($_SESSION['ome']);
             return true;  
         }else{
             return false;
         }
              
    }
    
}

