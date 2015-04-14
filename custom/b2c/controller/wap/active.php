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
		  $this->pagedata['IMG_PZFRESH'] = IMG_PZFRESH;
    	  $this->pagedata['active_name'] = $active_name;
          $this->page('wap/active/'.$active_name.'/index.html');
    }
}