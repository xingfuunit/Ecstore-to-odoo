<?php
class b2c_ctl_admin_vipday extends desktop_controller {
	function __construct($app) {
		parent::__construct ( $app );
		$this->ui = new base_component_ui ( $this );
		$this->app = $app;
		header ( "cache-control: no-store, no-cache, must-revalidate" );
	}
/* 	function index() {
		
		if ($_POST) {
			if (! empty ( $_POST ['new_name'] )) {
				$new_name = $_POST ['new_name'];
				$aData = kernel::database ()->select ( "insert into  sdb_b2c_vipday set vipday_name='{$new_name}'" );
			}
			$vipday_name = $_POST ['vipday_name'];
			$aData = kernel::database ()->select ( "update  sdb_b2c_vipday set current='false'" );
			$aData = kernel::database ()->select ( "update  sdb_b2c_vipday set current='true' where id='{$vipday_name}'" );
		
		}
		$aData = kernel::database ()->select ( "SELECT * from sdb_b2c_vipday" );
		
		$this->pagedata ['data'] = $aData;
		
		$this->display ( 'admin/vipday/index.html' );
	} */
	function index(){
		//会员日
		$this->finder('b2c_mdl_vipday',array(
            'title'=>app::get('b2c')->_('会员日管理后台'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加会员日'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_vipday&act=create','target'=>'_blank'),

            )
            ));
		
	}
	function create(){
		$this->singlepage('admin/vipday/detail.html');
	}
	
	function save(){
		$this->begin('');
		$objAd = $this->app->model('vipday');
		$_time = $_POST['start_time'];
		$_time = strtotime("$_time");
		$_time = date('Y-m-d',$_time);
		$_POST['start_time'] = $_time . ' 00:00:00';
		$_POST['end_time'] = $_time . ' 23:59:59';
		if ($objAd->save($_POST)) {
			$vipday_name = $_POST ['id'];
			$aData = kernel::database ()->select ( "update  sdb_b2c_vipday set current='false'" );
			$aData = kernel::database ()->select ( "update  sdb_b2c_vipday set current='true' where id='{$vipday_name}'" );
			$this->end(true,app::get('b2c')->_('保存成功'));
		} else {
			$this->end(true,app::get('b2c')->_('保存失败'));
		}
		 
	}
	
	function edit($ad_id){
		header("Cache-Control:no-store");
		 
		$this->path[] = array('text'=>app::get('b2c')->_('会员日管理编辑'));
		$objAd = $this->app->model('vipday');
		$adInfo = $objAd->dump($ad_id);
		$_time = strtotime("{$adInfo['start_time']}");
		$adInfo['_time'] = date('Y-m-d',$_time);
		$this->pagedata['adInfo'] = $adInfo;
		
		$this->singlepage('admin/vipday/detail.html');
	}
	
}