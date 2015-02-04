<?php

class apiactionlog_finder_xmlimport{
  	var $detail_basic = "日志详情";

  	function __construct($app)
    {
        $this->app = $app;
        if($_GET['app']!='apiactionlog'){
            
            unset($this->column_edit);
        }
    }

  	function detail_basic($return_id){
  		$render  = app::get('apiactionlog')->render();

  		$logModel = app::get('apiactionlog')->model('xmlimport');
  		$log_data = $logModel->dump(array('log_id' => $return_id), '*');

  		$render->pagedata['log_info'] = $log_data['log_data'];
  		return $render->fetch('admin/detail/basic.html');
  	}
    
}

?>
