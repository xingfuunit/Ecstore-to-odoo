<?php
class b2c_ctl_admin_vipday extends desktop_controller {
	function __construct($app) {
		parent::__construct ( $app );
		$this->ui = new base_component_ui ( $this );
		$this->app = $app;
		header ( "cache-control: no-store, no-cache, must-revalidate" );
	}
	function index() {
		
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
	}
}