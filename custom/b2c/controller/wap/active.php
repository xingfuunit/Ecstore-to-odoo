<?php
class b2c_ctl_wap_active extends wap_frontpage{

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
		  $this->pagedata['IMG_PZFRESH'] = IMG_PZFRESH;
    	  $this->pagedata['active_name'] = $active_name;
          $this->page('wap/active/'.$active_name.'/index.html',false);
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
    }
}