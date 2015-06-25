<?php

class mobileapi_ctl_admin_indexad extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';


    function index(){
        $this->finder('mobileapi_mdl_indexad',array(
            'title'=>app::get('b2c')->_('首页广告'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加首页广告'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_indexad&act=create','target'=>'_blank'),

            )
            ));
    }

    function create(){
    	$obj_adgroup = $this->app->model('indexad_group');
    	$this->pagedata['adgrouplist'] = $obj_adgroup->getList('group_id,group_name,group_code',array(),0,-1,'ordernum asc, group_id asc');
    	
    	$adInfo = array(
    			"disabled" => "false",
    			"ordernum" => "50",
    	);
    	$this->pagedata['adInfo'] = $adInfo;
    	
    	$this->singlepage('admin/detail.html');
    }

    function save(){
    	$this->begin('');
    	$objAd = $this->app->model('indexad');
    	$_POST['last_modify'] = time();
    	
    	$data = $_POST;
    	
    	$obj_adgroup = $this->app->model('indexad_group');
    	$adgroup = $obj_adgroup->dump($data['group_id']);
    	if($adgroup){
    		$data['group_name'] = $adgroup['group_name'];
    		$data['group_code'] = $adgroup['group_code'];
    	}
    	
    	if ($objAd->save($data)) {
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }

    function edit($ad_id){
    	header("Cache-Control:no-store");
    	
        $this->path[] = array('text'=>app::get('b2c')->_('广告编辑'));
        $objAd = $this->app->model('indexad');
        $this->pagedata['adInfo'] = $objAd->dump($ad_id);
        
        $obj_adgroup = $this->app->model('indexad_group');
        $this->pagedata['adgrouplist'] = $obj_adgroup->getList('group_id,group_name,group_code',array(),0,-1,'ordernum asc, group_id asc');

        $this->singlepage('admin/detail.html');
    }

}
