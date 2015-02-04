<?php

class b2c_ctl_admin_local_dailycash extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	function index() {	
          
            $this->finder('ome_mdl_dailycash',array(
            'title'=>app::get('b2c')->_('现金日结记录'),
            'allow_detail_popup'=>false,
            'use_buildin_filter'=>false,           
            'use_view_tab'=>true,
             ));
	}

       function printing($cash_id){
           $this->pagedata['res_url'] = $this->app->res_url;
           $this->_systmpl = $this->app->model('member_systmpl');
           $this->pagedata['printContent']['dailycash'] = true;
         
           $dailycash=  app::get('ome')->model('dailycash')->dump($cash_id);
            $staff=app::get('b2c')->model('local_staff')->dump($dailycash['staff_id']);      
            $branch=app::get('ome')->model('branch')->dump($dailycash['branch_id']);
            $dailycash['branch_name']=$branch['name'];
            $dailycash['staff_name']=$staff['staff_name'];
           $this->pagedata['cash']=$dailycash;
           $this->pagedata['page_title'] = app::get('b2c')->_('门店现金日结记录');
           $this->pagedata['content_cash'] = $this->_systmpl->fetch('admin/local/print_dailycash',$this->pagedata);
           
           $this->display('admin/local/print.html');
        }


}
