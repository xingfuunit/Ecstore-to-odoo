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
		  $this->pagedata['IMG_PZFRESH'] = IMG_PZFRESH;
    	  $this->pagedata['active_name'] = $active_name;
          $this->page('site/active/'.$active_name.'/index.html');
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
    
}

