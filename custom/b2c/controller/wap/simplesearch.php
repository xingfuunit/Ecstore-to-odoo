<?php
/**
 * july by 2015-06-15
 */

class b2c_ctl_wap_simplesearch extends wap_frontpage{

    public function __construct(&$app) {
        parent::__construct($app);
    }

    public function index(){
		// "/wap/simplesearch.html?find=no"
		$find = ''.$_GET['find'];
		if(strlen($find)>0){
			$find = 'no';
		}
		
		$this->pagedata['find'] = $find;
		$this->pagedata['keywords'] = kernel::single('mobileapi_rpc_keywords')->get_itmes();
		$this->page('wap/simplesearch/index.html');
    }

}