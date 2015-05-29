<?php

class b2c_ctl_admin_local_webpos extends desktop_controller {
	public function __construct($app) {
		parent::__construct ( $app );
	}
	
	function index() {
		$webpos_log = $this->app->model('webpos_log');
		$this->finder('b2c_mdl_webpos_log',array(
				'title'=>app::get('b2c')->_('门店员工操作日志'),
				'allow_detail_popup'=>false,
				'use_buildin_filter'=>false,
		));
	}
}