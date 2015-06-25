<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class diyapi_ctl_services extends base_controller{
	
	var $xml_encodeing;
	var $cats_xml_tree;
	
	function __construct($app){
		parent::__construct($app);
		$this->xml_encodeing = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	}
    
    function index(){
        $this->pagedata['project_name'] = 'API 测试工具';
        $this->display('default.html');
    }
    
    
    public function admin_login(){
    	
    	$_POST['uname'] = $_POST['username'];
    	
    	$auth = pam_auth::instance('shopadmin');
    	$auth->set_appid('desktop');
    	$passport_module = kernel::single('pam_passport_basic');
    	$module_uid = $passport_module->login($auth,$auth_data);
    	
    	$str_xml = $this->xml_encodeing;
    	$str_xml .= "<data>";
    	
    	if($module_uid){
    		$auth_data['account_type'] = $auth->type;
    		$auth->account()->update('pam_passport_basic', $module_uid, $auth_data);
    		
    		//获取该项记录集合
    		$users = app::get('desktop')->model('users');
    		$roles= app::get('desktop')->model('roles');
    		$sdf_users = $users->dump($module_uid);
    		
    		if($sdf_users['super']){
    			$roles_info = "超级用户";
    		}else{
    			$workgroup=$roles->getList('*');
    			$hasrole=app::get('desktop')->model('hasrole');
    			$role_name = array();
    			foreach($workgroup as $key=>$group){
    				$rolesData=$hasrole->getList('*',array('user_id'=>$module_uid,'role_id'=>$group['role_id']));
    				if($rolesData){
    					$role_name[] = $group['role_name'];
    				}
    			}
    			$roles_info = implode(',', $role_name);
    		}
    		
    		
			$str_xml .= "<code>ok</code>";
    		$str_xml .= "<info>".$auth_data['log_data']."</info>";
    		
    		$str_xml .= "<contents><username>".$_POST['uname']."</username><uid>$module_uid</uid><role>$roles_info</role></contents>";
    		
    	}else{
    		$str_xml .= "<code>fail</code>";
    		$str_xml .= "<info>".implode(',', $auth_data)."</info>";
    	}
    	
    	$str_xml .= "</data>";
    	 
    	echo $str_xml;
    	exit;
    }
    
    public function sign_data(){
    	
    	$query_params=array(
    			'direct' => 'true',
    			'date'=>date('Y-m-d H:m:s',time())
    	);
    	
    	unset($_POST['sign']);
    	
    	$params = array_merge($query_params, $_POST);
    	
//     	print_r($params);
    	
    	$token = base_certificate::token();
    	
    	$query_params['sign'] = $sign = $this->get_sign($params,$token);
    	
    	$sign_string = "";
    	foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $sign_string .= $key.'['.$k.']='.$v.'&';
                }
            }else{
               $sign_string .= $key.'='.$value.'&'; 
            }
    		
    	}
    	
    	exit($sign_string.'sign='.$query_params['sign']);
    }
    
    
    private function get_sign($params,$token){
    	//exit($this->assemble($params));
    	return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
    
    
    private function assemble($params)
    {
    	if(!is_array($params)) return null;
    	ksort($params,SORT_STRING);
    	$sign = '';
    	foreach($params AS $key=>$val){
    		$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
    	}
    	return $sign;
    }
}
