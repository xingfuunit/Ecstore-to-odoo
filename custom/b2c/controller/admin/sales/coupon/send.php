<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_sales_coupon_send extends desktop_controller{

    function index(){
    	if (isset($_POST['sub'])) {
    		$userObject = kernel::single('b2c_user_object');
			$this->begin( );
			$coupons_id = intval($_POST['coupons_id']);
			$member_list = $_POST['member_list'];
			if (empty($coupons_id) || empty($member_list)) {
				$this->end(false, '请选择优惠卷和填用户名！');
			}
			$member_list_arr = explode(',',$member_list);
			$member_ids = array();
			foreach ($member_list_arr as $key=>$value) {
				$member_id = $userObject->get_member_id_by_username($value);
				if (empty($member_id)) {$this->end(false, '用户名:'.$value.'不存在！');}
				$member_ids[] = $member_id;
			}
			//发送优惠卷
			$send_coupon = $this->_send_coupon($coupons_id,$member_ids);
			if (empty($send_coupon)) {$this->end(false, '发送失败！');}
			//通知
			$this->_notify($member_ids,$coupons_id);
			$member_coupon_sendlog = $this->app->model('member_coupon_sendlog');
			
			$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_id'=>$coupons_id));
			
			$data = array();
			$data['sendlog_id'] = '';
			$data['sendtime'] = time();
			$data['member_list'] = $member_list;
			$data['cpns_name'] = $coupons_arr[0]['cpns_name'];
			$data['cpns_prefix'] = $coupons_arr[0]['cpns_prefix'];
			$data['code_list'] = $send_coupon;
			$member_coupon_sendlog->insert($data);
			$this->end(true, '优惠卷发送成功！');
    	} else {
			$coupons_obj = $this->app->model("coupons");
			$coupons_list = $coupons_obj->getList("*",array('cpns_status'=>1,'cpns_type'=>1));
			$this->pagedata['coupons_list'] = $coupons_list;
	      	$this->page('admin/sales/coupon/send.html');
	      	
	      	
		}
    }
    
    function item() {
        $this->finder('b2c_mdl_member_coupon_sendlog', array(
                'title'=>app::get('b2c')->_('发送优惠券'),
            	'use_buildin_recycle'=>false,
                ));
    	
    }
	
	//发送优惠卷
	function _send_coupon($cpns_id,$member_ids) {
		//从下载接口拿优惠卷
		$coupons = $this->app->model('coupons');
		$coupons_num = $coupons->downloadCoupon($cpns_id,count($member_ids));
		$new_time = time();
		if (empty($coupons_num)) {
			return false;
		}
		$member_coupon = $this->app->model('member_coupon');
		foreach ($member_ids as $key=>$value) {
			$num = current($coupons_num);
			next($coupons_num);
			
			$data = array(
	            'memc_code'=>$num,
	            'cpns_id'=>$cpns_id,
	            'member_id'=>$value,
	            'memc_source'=>'a',
	            'memc_enabled'=>'true',
	            'memc_used_times'=>0,
	            'memc_gen_time'=>$new_time,
	            'disabled'=>'false',
	            'memc_isvalid'=>'true',
	        );
			$member_coupon->insert($data);
		}
		
		$coupons_txt = implode(',',$coupons_num);
		return $coupons_txt;
	}
	//通知
	
	function _notify($member_ids,$coupons_id) {
		/** 判断是否能够发送 **/
		$conf_coupon_notify = $this->app->getConf('messenger.actions.coupon-send');
		if (!$conf_coupon_notify){
			return false;
		}
		
		$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_id'=>$coupons_id));
		if (empty($coupons_arr)) {$this->end(false,app::get('b2c')->_('优惠卷不存在！'));}
		$obj = array('cpns_name'=>$coupons_arr[0]['cpns_name']);
		$arr_conf_coupon_notify = explode(',',$conf_coupon_notify);
		//发邮箱
		$systmpl = $this->app->model('member_systmpl');
		foreach ($member_ids as $item) {
			$member_rs = app::get('pam')->model('members')->getList('*',array('member_id'=>$item));
			
			foreach ($member_rs as $item2) {
				
				if (in_array('b2c_messenger_email',$arr_conf_coupon_notify) && $item2['login_type'] == 'email'){
					//发邮件
					$content = $systmpl->fetch('messenger:b2c_messenger_email/coupon-send',$obj);
		            $mobile = $item2['login_account'];
		            $tmpl = 'coupon-send';
		            $tmpl_name = 'messenger:b2c_messenger_email/coupon-send'; 
		            $mobile = $item2['login_account'];
		            $data = array('vcode'=>$content);
					$messengerModel = $this->app->model('member_messenger');
					$send_mail = $messengerModel->_send('b2c_messenger_email',$tmpl_name,$mobile,$data,$tmpl);
					
					if(!$send_mail){
						$this->end(false,app::get('b2c')->_('操作失败！'));
					}
				}
				
				if (in_array('b2c_messenger_sms',$arr_conf_coupon_notify) && $item2['login_type'] == 'mobile'){
					//短信
					$content = $systmpl->fetch('messenger:b2c_messenger_sms/coupon-send',$obj);
		    		$sender = 'b2c_messenger_sms';
		    		$tmpl = 'coupon-send';
		    		$tmpl_name = 'messenger:b2c_messenger_sms/'.$tmpl; 
		    		$mobile = $item2['login_account'];
		    		$data = array('vcode'=>$content);
		    		$messengerModel = $this->app->model('member_messenger');
		    		$send_sms = $messengerModel->_send($sender,$tmpl_name,(string)$mobile,$data,$tmpl,$tmpl);
					if(!$send_sms){
						$this->end(false,app::get('b2c')->_('操作失败！'));
					}
				}
			}
			if (in_array('b2c_messenger_msgbox',$arr_conf_coupon_notify)){
				//站内信暂不发
			}
			
		}
		return true;
		
	}
	
	
}
