<?php

/*
 * 前台登录
 * */
class pam_passport_site_basic
{

    /*
     * 前台用户登录验证方法
     *
     * @params $login_account 登录账号
     * @params $login_password 登录密码
     * @params $vcode 验证码
     * */
    public function login($userData,$vcode=false,&$msg,$post_date =''){
        $userData = utils::_filter_input($userData);//过滤xss攻击
        if($vcode && !$this->vcode_verify($vcode) ){
            $msg = app::get('pam')->_('验证码错误');
            return false;
        }

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
        if(isset($post_date['store']) && $post_date['store'] > 0){
        		$obj_local_store = app::get('b2c')->model('local_store');
        		
        		$local_store_list = $obj_local_store->getList('*',array(
        				'local_id'=>intval($post_date['store']),
        		),0,1);
        		
        		//$local_store = $obj_local_store->dump();
        		$local_store = $local_store_list[0];
        		if($local_store){
        			$_SESSION['local_store'] = $local_store;
        		}
        		
        		$obj_member_addrs = app::get('b2c')->model('member_addrs');
        		$member_addrs = $obj_member_addrs->getList('*',array(
        				'member_id'=>$account[0]['member_id'],
        				'local_id' => $local_store['local_id'],
        		),0,1);
        		
        		
        		$in_addr_data = $local_store;
        		$in_addr_data['member_id'] = $account[0]['member_id'];
        		
        		unset($in_addr_data['local_name']);
        		if($member_addrs[0]){
        			$in_addr_data['addr_id'] = $member_addrs[0]['addr_id'];
        		}
        		
        		//print_r($in_addr_data);exit;
        		
        		kernel::single('b2c_member_addrs')->purchase_save_addr($in_addr_data,$in_addr_data['member_id'],$msg);
        	}
        return $account[0]['member_id'];
    }//end function
    
     /*
     * 前台用户登录验证方法
     *
     * @params $login_account 登录账号
     * @params $login_password 登录密码
     * @params $vcode 验证码
     * */
    public function login_webpos($userData,$vcode=false,&$msg,$post_date =''){
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
       
       
        if(isset($_SESSION['local_store']) && $_SESSION['local_store']['branch_id'] > 0){
        		$obj_local_store = app::get('ome')->model('branch');
        		
        		$local_store_list = $obj_local_store->getList('*',array(
        				'branch_id'=>intval($_SESSION['local_store']['branch_id']),
        		),0,1);
        		
        		//$local_store = $obj_local_store->dump();
        		$local_store = $local_store_list[0];
                        
        		if($local_store){
        			$_SESSION['local_store'] = $local_store;
        		}
        		
        		$obj_member_addrs = app::get('b2c')->model('member_addrs');
        		$member_addrs = $obj_member_addrs->getList('*',array(
        				'member_id'=>$account[0]['member_id'],
        				'local_id' => $local_store['branch_id'],
        		),0,1);
        		
        		
        		$in_addr_data = $local_store;
        		$in_addr_data['member_id'] = $account[0]['member_id'];
        		
        		unset($in_addr_data['name']);
        		if($member_addrs[0]){
        			$in_addr_data['addr_id'] = $member_addrs[0]['addr_id'];
        		}
        		
        		$in_addr_data['addr'] = $in_addr_data['address'];
                        $in_addr_data['name'] = $account[0]['password_account'];
                        $in_addr_data['local_id'] = $in_addr_data['branch_id'];
                        $in_addr_data['tel'] = $in_addr_data['phone'];
                        $in_addr_data['time'] = time();
                       // print_r($in_addr_data);exit;
        		kernel::single('b2c_member_addrs')->purchase_save_addr($in_addr_data,$in_addr_data['member_id'],$msg);
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
}
