<?php

class mobileapi_ctl_admin_keywords extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';


    function index(){
        $this->finder('mobileapi_mdl_keywords',array(
            'title'=>app::get('b2c')->_('移动热门关键字'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加关键字'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_keywords&act=create','target'=>'dialog::{ title:\''.app::get('b2c')->_('添加关键字').'\', width:600, height:300}'),

            )
            ));
    }

    function create(){
    	$this->display('admin/keywords/detail.html');
    }

    function save(){
    	$this->begin();
    	$objAd = $this->app->model('keywords');
    	if ($objAd->save($_POST)) {
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }

    function edit($kw_id){
    	header("Cache-Control:no-store");
    	
        $this->path[] = array('text'=>app::get('b2c')->_('广告编辑'));
        $oKey = $this->app->model('keywords');
        $this->pagedata['info'] = $oKey->dump($kw_id);

        $this->display('admin/keywords/detail.html');
    }

}
