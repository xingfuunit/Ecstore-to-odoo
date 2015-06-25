<?php

class b2c_ctl_admin_local_charge extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	function index() {	
          
            $this->finder('ome_mdl_charge',array(
            'title'=>app::get('b2c')->_('交接班结账单'),
            'allow_detail_popup'=>false,
            'use_buildin_filter'=>false,           
            'use_view_tab'=>true,
             ));
	}
        
        function printing($charge_id){
           $this->pagedata['res_url'] = $this->app->res_url;
           $this->_systmpl = $this->app->model('member_systmpl');
           $this->pagedata['printContent']['charge'] = true;
           
           $charge=  app::get('ome')->model('charge')->dump($charge_id);
           $this->pagedata['charge']=$charge;
           $this->pagedata['content_charge'] = $this->_systmpl->fetch('admin/local/print_charge',$this->pagedata);
           $this->pagedata['page_title'] = app::get('b2c')->_('品珍收银交接班结账单');
           $this->display('admin/local/print.html');
        }

        


}
