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
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class b2c_ctl_site_active extends b2c_frontpage{

    var $noCache = true;
    var $show_gotocart_button = true;
    
    

    public function __construct(&$app) {

        parent::__construct($app);
        
    }

    public function index(){
    	  $this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	  $this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	  $this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	  $active_name = $this->_request->get_get('name');
    	  if(!$active_name){
    	  	$this->redirect('/');
    	  }
    	  if($active_name=='51meat'){
    	  	$this->member = $this->get_current_member();
    	  	$this->pagedata['member'] = $this->member;
    	  	$this->meat_active_get_time();
    	  }
    	  if($active_name=='vipday'){
    	  	$aData = kernel::database ()->select ( "SELECT vipday_name from sdb_b2c_vipday where current='true'" );
    	  	$aData = $aData[0];
    	  	if($aData['vipday_name']!=='seafood'){
    	  		$active_name = $active_name.'_'.$aData['vipday_name'];
    	  	}
    	  }
    	  
    	  
		  $this->pagedata['IMG_PZFRESH'] = IMG_PZFRESH;
    	  $this->pagedata['active_name'] = $active_name;
    	  $active = array(
    	  		'51meat',//51活动
    	  		'mqj',//母亲节
    	  		'vipday',//会员日-海鲜类（seafood）
    	  		'vipday_meat',//会员日-肉类（meat）
    	  		'vipday_fruit',//会员日-水果（fruit）
    	  		'vipday_jingxuan',//会员日-精选食品（jingxuan）
    	  		'childrenday',//儿童节
    	  		'hgt',//海归天团黑豚肉
    	  		);
          if(in_array($active_name,$active)){
          	$this->page('site/active/'.$active_name.'/index.html',true);//活动页面全屏，不要head和foot
          }else{
          	$this->page('site/active/'.$active_name.'/index.html');
          }
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
    /**
     * 金枪鱼众筹 活动
     */
    public function zhongchou(){
    	$shopname = '金枪鱼众筹活动';
    	$this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	
    	//日期写入数据
    	$zc_rule = array(
    		'0'=> 13,
    		'1'=> 3,
    		'2'=> 2,
    		'3'=> 3,
    		'4'=> 2,
    		'5'=> 2,
    		'6'=> 4,
    		'7'=> 3,
    		'8'=> 1,
    		'9'=> 3,
    		'10'=> 3,
    		'11'=> 5,
    		'12'=> 2,
    		'13'=> 3,
    		'14'=> 3,
    		'15'=> 2,
    		'16'=> 2,
    		'17'=> 3,
    		'18'=> 1,
    	);

    	//开始日期
    	$s_date = '2015-1-27 00:00:01';
    	$date1 = strtotime($s_date);
    	$date2 = time();
    	$days=floor(abs($date1-$date2)/3600/24);
    	
    	if($days < 18){
    		$zc_data = array_slice($zc_rule,0,$days+1);
    		
    		//构造假众筹数
    		$dates = 0;
    		foreach($zc_data as $k => $v){
    			$dates += $v;
    		}
    	}else{
    		$dates = 60;
    	}
		
    	
    	$this->pagedata['zc_data'] = $dates;
    	$active_name = 'zhongchou';
    	$this->page('site/active/'.$active_name.'/index.html');
    } 
    
    
    /**
     * 肉食专题
     */
    public function meatstyle(){
    	$shopname = '将肉食进行到底';
    	$this->title = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->keywords = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	$this->description = app::get('b2c')->_('品珍鲜活-您的品质生活供应商').'_'.$shopname;
    	
    	$bn_array=array('210001','210004','210012','210002','210011','210005','210007','210013','210018','230022','230014','230018','220001');
    	$count_arry = array();
    	$mdl_goods = $this->app->model('goods');
    	foreach($bn_array as $ba){
    		$count = $mdl_goods->db->select("select buy_count from sdb_b2c_goods where bn ='$ba'");
    		$count_arry[] = $count[0][buy_count];
    	}
    	$this->pagedata['count'] = $count_arry;
    	$this->page('site/active/meatstyle/index.html');
    }   
    

    public function postdata(){
    	error_log("here");
    	error_log(print_r($_POST,1));
    }
    
}

