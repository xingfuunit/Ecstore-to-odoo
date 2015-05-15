<?php

class mobileapi_ctl_admin_sales_ads extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';
    
    function index(){
        $this->finder('mobileapi_mdl_sales_ads',array(
            'title'=>app::get('b2c')->_('首页广告'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加首页广告'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_sales_ads&act=create','target'=>'_blank'),

            )
            ));
    }

    function create(){
        $this->pagedata['ads_position'] = $this->app->model('sales_ads')->get_sales_ads_position_list();
    	$this->singlepage('admin/sales/ads_detail.html');
    }

    function save(){
    	$this->begin('');
    	$objAd = $this->app->model('sales_ads');
    	$_POST['last_modify'] = time();
    	if ($objAd->save($_POST)) {
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }

    function edit($ad_id){
    	header("Cache-Control:no-store");
    	
        $this->path[] = array('text'=>app::get('b2c')->_('广告编辑'));
        $objAd = $this->app->model('sales_ads');
        $this->pagedata['adInfo'] = $objAd->dump($ad_id);

        $this->pagedata['ads_position'] = $this->app->model('sales_ads')->get_sales_ads_position_list();
        $this->singlepage('admin/sales/ads_detail.html');
    }

}
