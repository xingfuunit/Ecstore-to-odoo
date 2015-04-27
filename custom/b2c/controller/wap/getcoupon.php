<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * ctl_active
 *
 * @uses b2c_frontpage
 * @package
 *
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class b2c_ctl_wap_getcoupon extends wap_frontpage {
function index(){

	$s_date = '2015-05-06 00:00:00';
	$date1 = strtotime($s_date);
	$date2 = time();
	$d1= getdate();
	if($date2<$date1){
		if($d1['hours']<10){
			$d = new DateTime("10:00", new DateTimeZone("Asia/Shanghai"));//每日开始时间
			$d2 = $d->format("Y-m-d H:i:s");
			$start_time = strtotime($d2);
		}else{
			$start_time = 0;
		}
		 
	}
	if($date2>$date1){
		echo json_encode(array('error'=>'活动已结束'));
		return;
	}
	if($start_time){
		echo json_encode(array('error'=>'亲！请您再等等'));
		return;
	}
	
	
	
    		$userObject = kernel::single('b2c_user_object');
    		
    		if($_POST['coupons_tab']=='1'){
    			$cpns_prefix = 'BXSP';
    			$cpns_prefix2 = 'BXSPMY';
    			$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_prefix'=>$cpns_prefix));
    			$coupons_id = $coupons_arr[0]['cpns_id'];
    			$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_prefix'=>$cpns_prefix2));
    			$coupons_id2 = $coupons_arr[0]['cpns_id'];
    		}elseif($_POST['coupons_tab']=='3'){
    			$cpns_prefix = 'BSNMY';
    			$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_prefix'=>$cpns_prefix));
    			$coupons_id = $coupons_arr[0]['cpns_id'];
    		}
    		if(empty($_POST['user'])){
    			echo json_encode(array('error'=>'您还没有登录，请登录'));
    			return;
    		}
    		//$member_id = $userObject->get_member_id_by_username($_POST['user']);
    		$this->member = $this->get_current_member();
    		$member_id = $this->member['member_id'];
    		if (empty($member_id)){
    			echo json_encode(array('error'=>'用户名:'.$_POST['user'].'不存在！'));
    			return;
    		}
    		$member_ids = array();
    		$member_ids[] = $member_id;
    		$db = kernel::database();
    		
    		$sql = 'select * from sdb_b2c_member_coupon where member_id=\''.$member_id.'\' and cpns_id=\''.$coupons_id.'\'';
    		//error_log($sql);
    		$row = $db->select($sql);
    		if($row && $row[0]['memc_code']){
    			echo json_encode(array('error'=>'亲！您已领取过该优惠券了'));
    			return;
    		}
    		//每天限50张start
    		$d = new DateTime("00:00:00", new DateTimeZone("Asia/Shanghai"));//每日开始时间
    		$d2 = $d->format("Y-m-d H:i:s");
    		$s_time = strtotime($d2);
    		$d = new DateTime("23:59:59", new DateTimeZone("Asia/Shanghai"));//每日开始时间
    		$d2 = $d->format("Y-m-d H:i:s");
    		$e_time = strtotime($d2);
    		$sql = 'select count(*) as count from sdb_b2c_member_coupon where '.' cpns_id=\''.$coupons_id.'\' and memc_gen_time>\''.$s_time.'\' and  memc_gen_time<\''.$e_time.'\'';
    		$row = $db->select($sql);
    		if($row && $row[0]['count']>=20){
    			echo json_encode(array('error'=>'亲！优惠券领完了，明天继续喔'));
    			return;
    		}
    		//每天限50张end
			if(isset($coupons_id2) && $coupons_id2){
				$ret = $this->send_cp($coupons_id,$member_ids);
				$ret1 = $this->send_cp($coupons_id2,$member_ids);
				if($ret && $ret1){
					echo json_encode(array('success'=>'优惠券领取成功'));
					return;
				}else{
					echo json_encode(array('error'=>'优惠券领取失败，请稍后重试'));
					return;
				}
			}else{
				$ret = $this->send_cp($coupons_id,$member_ids);
				if($ret){
					echo json_encode(array('success'=>'优惠券领取成功'));
					return;
				}else{
					echo json_encode(array('error'=>'优惠券领取失败，请稍后重试'));
					return;
				}
			}
    		
			
			
    	
    }
    
	function send_cp($coupons_id,$member_ids){
		//发送优惠卷
		$send_coupon = $this->_send_coupon($coupons_id,$member_ids);
		if (empty($send_coupon)) {
			//echo json_encode(array('error'=>'优惠券领取失败，请稍后重试'));
			return false;
		}
			
		$member_coupon_sendlog = $this->app->model('member_coupon_sendlog');
			
		$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_id'=>$coupons_id));
		$member_list = implode(',',$member_ids);
		$data = array();
		$data['sendlog_id'] = '';
		$data['sendtime'] = time();
		$data['member_list'] = $member_list;
		$data['cpns_name'] = $coupons_arr[0]['cpns_name'];
		$data['cpns_prefix'] = $coupons_arr[0]['cpns_prefix'];
		$data['code_list'] = $send_coupon;
		$member_coupon_sendlog->insert($data);
		return true;
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
	
	

}