<?php

class mobileapi_ctl_admin_sales_touchscreen extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';
    
    function index(){
        $this->finder('mobileapi_mdl_sales_touchscreen',array(
            'title'=>app::get('b2c')->_('电视触屏'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加电视触屏图片'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_sales_touchscreen&act=create','target'=>'_blank'),

            )
            ));
    }

    function create(){
		//默认值
		$adInfo = array(
			'ad_img_w' =>1920,
			'ad_img_h' =>1080
		);
		
        $this->pagedata['adInfo'] = $adInfo;
        $this->pagedata['touchscreen_position'] = $this->app->model('sales_touchscreen')->get_sales_touchscreen_position_list();
    	$this->singlepage('admin/sales/touchscreen_detail.html');
    }

    function save(){
    	$this->begin('');
    	$objAd = $this->app->model('sales_touchscreen');
    	$_POST['last_modify'] = time();
    	if ($objAd->save($_POST)) {
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }

    function edit($ad_id){
    	header("Cache-Control:no-store");
    	
        $this->path[] = array('text'=>app::get('b2c')->_('电视触屏图片编辑'));
        $objAd = $this->app->model('sales_touchscreen');
        $this->pagedata['adInfo'] = $objAd->dump($ad_id);

        $this->pagedata['touchscreen_position'] = $this->app->model('sales_touchscreen')->get_sales_touchscreen_position_list();
        $this->singlepage('admin/sales/touchscreen_detail.html');
    }

}
