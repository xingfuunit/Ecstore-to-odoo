<?php
class b2c_ctl_admin_local_dailycash extends desktop_controller {
	function __construct($app) {
		parent::__construct ( $app );
		$this->ui = new base_component_ui ( $this );
		$this->app = $app;
		header ( "cache-control: no-store, no-cache, must-revalidate" );
	}
	function index() {
		$this->finder ( 'ome_mdl_dailycash', array (
				'title' => app::get ( 'b2c' )->_ ( '现金日结记录' ),
				'allow_detail_popup' => false,
				'use_buildin_filter' => false,
				'use_view_tab' => true 
		) );
	}
	function printing($cash_id) {
		$this->pagedata ['res_url'] = $this->app->res_url;
		$this->_systmpl = $this->app->model ( 'member_systmpl' );
		$this->pagedata ['printContent'] ['dailycash'] = true;
		
		$dailycash = app::get ( 'ome' )->model ( 'dailycash' )->dump ( $cash_id );
		$staff = app::get ( 'b2c' )->model ( 'local_staff' )->dump ( $dailycash ['staff_id'] );
		$branch = app::get ( 'ome' )->model ( 'branch' )->dump ( $dailycash ['branch_id'] );
		$dailycash ['branch_name'] = $branch ['name'];
		$dailycash ['staff_name'] = $staff ['staff_name'];
		$this->pagedata ['cash'] = $dailycash;
		$this->pagedata ['page_title'] = app::get ( 'b2c' )->_ ( '门店现金日结记录' );
		$this->pagedata ['content_cash'] = $this->_systmpl->fetch ( 'admin/local/print_dailycash', $this->pagedata );
		
		$this->display ( 'admin/local/print.html' );
	}
	
	function total() {
		
		$branch_id = intval($_GET['branch_id']);
		$this->pagedata['branch_id']=$branch_id;
		//仓库
		$local_store=app::get('ome')->model('branch')->getList('*',array('disabled'=>'false','is_show'=>'true'));
		$this->pagedata['local_store']=$local_store;
		$today = isset($_GET['today']) ? $_GET['today'] : date('Y-m-d');
		$today = strtotime("$today");		
		$today = date('Y-m-d',$today);
		$this->pagedata['today']=$today;
		
		$total = app::get ( 'ome' )->model ( 'dailycash' )->get_total($today,$branch_id);
		$this->pagedata['total']=$total;
		
		$this->page ( 'admin/local/total.html' );
		
	}
	
	function total_print() {
		$branch_id = intval($_GET['branch_id']);
		$local_store=app::get('ome')->model('branch')->getList('*',array('disabled'=>'false','branch_id'=>$branch_id));
		$branch_name = empty($local_store[0]['name']) ? '全部' : $local_store[0]['name'];
		$this->pagedata['branch_name'] = $branch_name;
		$today = isset($_GET['today']) ? $_GET['today'] : date('Y-m-d');
		$this->pagedata['today']=$today;
		$total = app::get ( 'ome' )->model ( 'dailycash' )->get_total($today,$branch_id);
		$this->pagedata['total']=$total;
		$this->pagedata ['res_url'] = $this->app->res_url;
		$this->pagedata ['printContent'] ['dailycash'] = true;
		$this->pagedata ['page_title'] = app::get ( 'b2c' )->_ ( '门店日结报表' );
		$this->_systmpl = $this->app->model ( 'member_systmpl' );
		$this->pagedata ['content_cash'] = $this->_systmpl->fetch ( 'admin/local/print_total', $this->pagedata );
		$this->display ( 'admin/local/print.html' );
	}
	
}
