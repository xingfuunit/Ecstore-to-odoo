<?php
class wap_ctl_lottery extends wap_controller{
    
	 //微信抽奖主页
     function index(){
        $this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
        $this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
        $this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
        $msg = '';
		if ($this->_is_bind() == true) {
			$_SESSION['wap_lottery'] = 1;
		//	$msg=app::get('b2c')->_('你未关注品珍鲜活，请先关注！');
		} else {
			$msg=app::get('b2c')->_('你未关注品珍鲜活，请先关注！');
			$_SESSION['wap_lottery'] = 0;
		}
    	$member_id = $_SESSION['account']['member'];
     	$lottery_log = $this->app->model('lottery_log');
     	$log_rs = $lottery_log->getList("*",array('member_id'=>$member_id));
     	if ($log_rs) {
			$msg=app::get('b2c')->_('你已抽过奖了,不能重复抽！');			
     	}
     	
     	$end_data = strtotime("2015-2-17 23:59:59");
     	if (time() > $end_data) {
     		$msg=app::get('b2c')->_('抽奖活动已结束！');
     	}

     	
        $this->pagedata['msg'] = $msg;
        $this->page('wap/lottery/index.html');
    }
    
    function show() {
    	$member_id = $_SESSION['account']['member'];
     	$lottery_log = $this->app->model('lottery_log');
     	$log_rs = $lottery_log->getList("*",array('member_id'=>$member_id));
        $this->pagedata['lottery_rs'] = $log_rs[0];
        $this->page('wap/lottery/show.html');
    }
    
    function start() {
    	$member_id = $_SESSION['account']['member'];
    	
     	$end_data = strtotime("2015-2-17 23:59:59");
     	if (time() > $end_data) {
			$msg=app::get('b2c')->_('抽奖活动已结束！');
			$this->splash('failed',null,$msg,'','',true);
     	}
    	
		if (!$_SESSION['wap_lottery']) {
			$msg=app::get('b2c')->_('你未关注品珍鲜活，请先关注！');
			$this->splash('failed',null,$msg,'','',true);
		}
		
     	$lottery_log = $this->app->model('lottery_log');
     	$log_rs = $lottery_log->getList("*",array('member_id'=>$member_id));
     	if ($log_rs) {
			$msg=app::get('b2c')->_('你已抽过奖了,不能重复抽！');
			$this->splash('failed',null,$msg,'','',true);
     	}
    	
    	$rand_num = rand(1,10000);
    	$gift = array();
    	if ($rand_num >= 1 && $rand_num <= 10) {
    		$gift = array('gift_name'=>'智利车厘子一份','gift_type'=>'goods','cpns_prefix'=>'BCLZ500','position'=>'2');
    	} else if ($rand_num >= 11 && $rand_num <= 20) {
    		$gift = array('gift_name'=>'蓝琪儿一只','gift_type'=>'goods','cpns_prefix'=>'BLQE88','position'=>'8');
    	} else if ($rand_num >= 21 && $rand_num <= 30) {
    		$gift = array('gift_name'=>'英国三文鱼鱼柳一份','gift_type'=>'goods','cpns_prefix'=>'BSWY00','position'=>'4');
    	} else if ($rand_num >= 31 && $rand_num <= 40) {
    		$gift = array('gift_name'=>'英国红蛇果一份','gift_type'=>'goods','cpns_prefix'=>'BHSG4G','position'=>'6');
    	} else if ($rand_num == 41) {
    		$gift = array('gift_name'=>'智利熟冻帝王蟹一份','gift_type'=>'goods','cpns_prefix'=>'BZLSDDWX','position'=>'0');
    	} else if ($rand_num >=42 &&  $rand_num <= 1542) {
    		$gift = array('gift_name'=>'现金券5元(满29可用)','gift_type'=>'cpns','cpns_prefix'=>'B2001','position'=>'5');
    	} else if ($rand_num >=1543 &&  $rand_num <= 6543) {
    		$gift = array('gift_name'=>'现金券15元(满59可用)','gift_type'=>'cpns','cpns_prefix'=>'B2002','position'=>'1');
    	} else if ($rand_num >=6544 &&  $rand_num <= 8003) {
    		$gift = array('gift_name'=>'现金券30元(满99可用)','gift_type'=>'cpns','cpns_prefix'=>'B2003','position'=>'11');
    	} else {
    		$gift = array('gift_name'=>'现金券60元(满199可用)','gift_type'=>'cpns','cpns_prefix'=>'B2004','position'=>'7');
    	}
    	
    	
    	if ($gift['cpns_prefix'] != '') {
			//中奖
			$cpns_rs = app::get('b2c')->model('coupons')->getList("*",array('cpns_prefix'=>$gift['cpns_prefix']));
			if (empty($cpns_rs)) {
				$msg=app::get('b2c')->_('暂停抽奖，请稍后再试001！');
				$this->splash('failed',null,$msg,'','',true);
			}
			//发优惠卷
			$coupon_txt = $this->_send_coupon($cpns_rs[0]['cpns_id']);
    	}
    	
    	$member_rs = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
    	
    	$data = array();
    	$data['lotterylog_id'] = '';
    	$data['dateline'] = time();
    	$data['member_id'] = $member_id;
    	$data['login_account'] = $member_rs[0]['login_account'];
    	$data['gift_name'] = $gift['gift_name'];
    	$data['cpns_prefix'] = $gift['cpns_prefix'];
    	$data['gift_type'] = $gift['gift_type'];
    	$lottery_log->insert($data);
		$msg=app::get('b2c')->_("恭喜你中了".$gift['gift_name']."！");
		$this->splash('success',$gift['position'],$msg,'','',true);
    	
    }
   
   
   
   
	//判断是否关注
	protected function _is_bind() {
         $openid = parent::$this->openid;
         $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
         $uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
		 //var_dump($uinfo['subscribe']);exit;
         if ($uinfo['subscribe']) {
         	return true;
         } else {
         	return false;
         }
	}
	
	
	//发送优惠卷
	protected function _send_coupon($cpns_id) {
		
		$member_id = $_SESSION['account']['member'];
		if (!$member_id) {
			$msg=app::get('b2c')->_('暂停抽奖，请稍后再试002！');
			$this->splash('failed',null,$msg,'','',true);
		}
		//从下载接口拿优惠卷
		$coupons = app::get('b2c')->model('coupons');
		$coupons_num = $coupons->downloadCoupon($cpns_id,1);
		$new_time = time();
		if (empty($coupons_num)) {
			return false;
		}
		$member_coupon = app::get('b2c')->model('member_coupon');
		$memc_code = $coupons_num[0];
		$data = array(
            'memc_code'=>$memc_code,
            'cpns_id'=>$cpns_id,
            'member_id'=>$member_id,
            'memc_source'=>'a',
            'memc_enabled'=>'true',
            'memc_used_times'=>0,
            'memc_gen_time'=>$new_time,
            'disabled'=>'false',
            'memc_isvalid'=>'true',
        );
		$member_coupon->insert($data);
		return $memc_code;
	}
	
	
	public function live(){
		
		$this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
		$this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
		$this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
		
		$openid = parent::$this->openid;
        $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
       	
        $uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
        
        if (!$uinfo['subscribe']) {
        	$msg=app::get('b2c')->_('你未关注品珍鲜活，请先关注！');
        	print_r('你未关注品珍鲜活，请先关注！');
        	exit;
        	//关注页面
        }else{
//         	error_log(serialize($uinfo),3,'/mnt/hgfs/Dev/test.log');
        	$url_arr = array('openid'=>$uinfo['openid'],'nick_name'=>$uinfo['nickname']);
        	$url = LOTTERY_URL.'?'.http_build_query($url_arr);
        	header("Location: ".$url);
        	exit;
        }
        
        $msg=app::get('b2c')->_('数据获取错误，请返回！');
        print_r('数据获取错误，请返回！');
		exit;
	}
	
}