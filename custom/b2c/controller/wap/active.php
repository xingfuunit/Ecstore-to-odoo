<?php
class b2c_ctl_wap_active extends wap_frontpage{

    var $noCache = true;
    var $show_gotocart_button = true;
    
    

    public function __construct(&$app) {

        parent::__construct($app);
        
    }
    
    /**
     * 活动列表页
     */
    public function alist(){
    	$this->pagedata['title'] = "品珍活动";
    	$this->page('wap/active/list.html');
    }

    public function index(){
    	
    	
    	$this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$active_name = $this->_request->get_get('name');
    	
    	  
    	  if(!$active_name){
    	  	$this->redirect('/');
    	  }
    	  
    	  if( $active_name=='51meat' ){
    	  	//判断是否来自微信， 来自微信获取 openid
    	  
    	  	if(kernel::single('weixin_wechat')->from_weixin()){
    	  		//如果来自微信 且已关注  自动登录并加入购物车
    	  		$openid = parent::$this->openid;
    	  		$bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
    	  		$uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
    	  		//未关注跳到关注页面
    	  		if (!$uinfo['subscribe']) {
    	  			$this->redirect('http://mp.weixin.qq.com/s?__biz=MzAxMjEwMjg2OA==&mid=206449921&idx=1&sn=61b0dc425fdba4925b6a42ac34f758f8#rd');
    	  		}
    	  	}
    	  }
    	  
    	  if($active_name=='51meat'){
    	  	$this->member = $this->get_current_member();
    	  	$this->pagedata['member'] = $this->member;
    	  	$this->meat_active_get_time();
    	  	
    	  	$this->title = app::get('b2c')->_('9块9包邮抢购澳洲进口牛扒！品珍私享奢华零距离！每天限抢50份！抢完即止~');
    	  	$this->keywords = app::get('b2c')->_('9块9包邮抢购澳洲进口牛扒！品珍私享奢华零距离！每天限抢50份！抢完即止~');
    	  	$this->description = app::get('b2c')->_('9块9包邮抢购澳洲进口牛扒！品珍私享奢华零距离！每天限抢50份！抢完即止~');
    	  	
    	  }
		  $this->pagedata['IMG_PZFRESH'] = IMG_PZFRESH;
    	  $this->pagedata['active_name'] = $active_name;
          $this->page('wap/active/'.$active_name.'/index.html');
    }
    private function meat_active_get_time(){
    	//结束日期
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
    	$this->pagedata['start_time'] = $start_time;
    	
    	//是否已抢50份
    	$db = kernel::database();
    			$cpns_prefix = 'BXSP';
    			$coupons_arr = $this->app->model('coupons')->getList("*",array('cpns_prefix'=>$cpns_prefix));
    			$coupons_id = $coupons_arr[0]['cpns_id'];
    			
    	//每天限50张start
    	$d = new DateTime("00:00:00", new DateTimeZone("Asia/Shanghai"));//每日开始时间
    	$d2 = $d->format("Y-m-d H:i:s");
    	$s_time = strtotime($d2);
    	$d = new DateTime("23:59:59", new DateTimeZone("Asia/Shanghai"));//每日开始时间
    	$d2 = $d->format("Y-m-d H:i:s");
    	$e_time = strtotime($d2);

    	$sql = 'select count(*) as count from sdb_b2c_member_coupon where '.' cpns_id=\''.$coupons_id.'\' and memc_gen_time>\''.$s_time.'\' and  memc_gen_time<\''.$e_time.'\'';

    	//error_log($sql);
    	$row = $db->select($sql);
    	if($row && $row[0]['count']>=50){
    		$this->pagedata['qiangwan'] = true;
    	}else{
    		$this->pagedata['qiangwan'] = false;
    	}
    	//每天限50张end
    }
}