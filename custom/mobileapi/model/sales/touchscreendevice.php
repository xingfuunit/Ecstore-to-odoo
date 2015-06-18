<?php
/**
 * july by 2015-06-15
 */

class mobileapi_mdl_sales_touchscreendevice extends dbeav_model{

	function __construct($app) {
		parent::__construct($app);
	}
	
	var $defaultOrder = array('device_id',' DESC');
	
	
	 public function modifier_disabled($row){
        if ($row == 'true'){
            return "<span style='color:red'>是</span>";
        }else{
            return '否';
        }
    }

}
