<?php
/**
 * july by 2015-06-15
 */
class mobileapi_ctl_admin_sales_touchscreendevice extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';
	
	//视频上传后的保存目录
    var $vod_saveDir = '/public/vod/';

    function index(){
        $this->finder('mobileapi_mdl_sales_touchscreendevice',array(
            'title'=>app::get('b2c')->_('门店触屏设备'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加门店触屏设备'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_sales_touchscreendevice&act=create','target'=>'_blank'),

				)
            ));

        $this->page();
    }
	

    function create(){

        $this->pagedata['deviceInfo'] = $deviceInfo;
        $this->pagedata['branchs'] 	= kernel::database()->select("select branch_bn as id, name from sdb_ome_branch where is_show ='true' and  nostore_sell='false'  order by branch_bn asc");
    	$this->singlepage('admin/sales/touchscreendevice_detail.html');
    }
	
    function edit($ad_id){
    	header("Cache-Control:no-store");

        $obj = $this->app->model('sales_touchscreendevice');

        $this->pagedata['deviceInfo'] = $obj->dump($ad_id);
        $this->pagedata['branchs'] 	= kernel::database()->select("select branch_bn as id, name from sdb_ome_branch where is_show ='true' and  nostore_sell='false'  order by branch_bn asc");
        
        $this->singlepage('admin/sales/touchscreendevice_detail.html');
    }
	
    function save(){
    	$this->begin('');
    	$obj = $this->app->model('sales_touchscreendevice');
    	$_POST['last_modify'] = time();
		

		if(strlen($_POST['branch_bn'])>1){
			$row = kernel::database()->selectrow("select name from sdb_ome_branch where branch_bn='". $_POST['branch_bn'] ."'");
			if(isset($row) && is_array($row)){
				$_POST['branch_name'] = $row['name'];	
			}else{
				$_POST['branch_bn'] = '';
				$_POST['branch_name'] = '';	
			}
		}else{
			$_POST['branch_bn'] = '';
			$_POST['branch_name'] = '';
		}

    	if ($obj->save($_POST)) {			
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }
}
