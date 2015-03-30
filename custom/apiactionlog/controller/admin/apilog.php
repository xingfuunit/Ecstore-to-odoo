<?php
class apiactionlog_ctl_admin_apilog extends desktop_controller{

    var $workground = "apiactionlog.wrokground.apilog";
    function index($status='all', $api_type='request'){
        $base_filter = '';
        $orderby = ' createtime desc ';
        switch($status){
            case 'all':
                $this->title = '所有同步日志';
                break;
            case 'running':
                $this->title = '同步运行中日志';
                $base_filter = array('status'=>'running');
                break;
            case 'success':
                $this->title = '同步成功日志';
                $base_filter = array('status'=>'success');
                break;
            case 'fail':
                $this->title = '同步失败日志';
                $base_filter = array('status'=>'fail','api_type'=>$api_type);
                break;
            case 'sending':
                //kernel::single('ome_sync_api_log')->clean();
                $this->title = '发起中日志';
                $base_filter = array('status'=>'sending');
                break;
        }

        if ($status=='fail' && ($api_type=='request' || $api_type=='request2')){
        	if ($api_type=='request2') {
        		//把真正失败的同步日志筛选出来 by michael
        		$week = time()-3600*24*7;
        		$aData = kernel::database()->select("SELECT a.apilog_id,a.original_bn FROM sdb_apiactionlog_apilog a WHERE  a.msg != 'w04001,4007' AND a.status IN ('fail','sending') AND a.api_type='request' and a.last_modified>='{$week}'  ORDER BY a.apilog_id");
        		$ids = array();
        		foreach ($aData as $key=>$value) {
        			$ids[] = $value['apilog_id'];
        		}
        		$ids = implode(',',$ids);
        		$base_filter = "apilog_id in  (".$ids.")";
        	}
        	
        	
            $actions =
                array(
                    array(
                        'label' => '批量重试',
                        'submit' => 'index.php?app=apiactionlog&ctl=admin_apilog&act=re_request',
                        'target' => "refresh",//dialog::{width:550,height:300,title:'批量重试'}",
                    ),
                );
        }

        $params = array(
            'title'=>$this->title,
            'actions'=> $actions,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'orderBy' => $orderby,
        );
        
		
		
        if($base_filter){
            $params['base_filter'] = $base_filter;
        }

        if(!isset($_GET['action'])) {
            $panel = kernel::single('apiactionlog_panel',$this);
            $panel->setId('api_log_finder_top');
            $panel->setTmpl('admin/finder/finder_panel_filter.html');
            $panel->show('apiactionlog_mdl_apilog', $params);
        }

        $this->finder('apiactionlog_mdl_apilog',$params);
    }

    function retry($log_id='', $retry_type='single'){
        if ($retry_type == 'single'){
            $this->pagedata['log_id'] = $log_id;
        }else{
            if (is_array($log_id['log_id'])){
                $this->pagedata['log_id'] = implode("|", $log_id['log_id']);
            }
        }
        $this->pagedata['isSelectedAll'] = $log_id['isSelectedAll'];
        $this->pagedata['retry_type'] = $retry_type;
        $this->display("admin/api/retry.html");
    }

    function re_request($order_no){
        $this->begin();
        if($order_no){
            $apilog_id = $order_no;
        }elseif($_POST){
            $apilog_id = $_POST;
        }

        $request_mdl = kernel::single('apiactionlog_router_request');
        $result = $request_mdl->re_request($apilog_id);
        $this->end($result);
        //echo json_encode($result);
        exit;
    }

    function batch_retry(){
        $this->retry($_POST, 'batch');
    }

    function xmlimport() {
        $this->finder(
            'apiactionlog_mdl_xmlimport',
            array(
                'title'=>app::get('apiactionlog')->_('商品数据导入日志'),
                    'use_buildin_set_tag' => false,
                    'use_buildin_filter' => true,
                    'orderBy' =>'log_id DESC',
            )
        );
    }
    
    
    //没有同步的订单　by michael
    
    function order_nosync() {
    	//查出log表中，无的订单
		$week = time()-3600*24*4;
		$sql ="SELECT order_id,FROM_UNIXTIME(createtime,'%Y%m%d') FROM sdb_b2c_orders where pay_status = '1' and createtime>='{$week}'";
		
		$ecData = kernel::database()->select($sql);
		
		$sql ="select * from sdb_ome_orders where createtime>='{$week}'";
		$erpData = kernel::single('base_db_connect')->select($sql);
		
		$ids = array();
		foreach ($ecData as $key=>$value) {
			$is_exist = 0;
			foreach ($erpData as $key2=>$value2) {
				if ($value['order_id'] == $value2['order_bn']) {
					if ($value2['pay_status'] == '0' ) {
				 		$ids[] = $value['order_id'];
				 	//	echo $value['order_id'];
				 	//	exit;
				 	}
				 	$is_exist++;
				}
			}
			
			if ($is_exist == 0) {
				$ids[] = $value['order_id'];
			}
			
		}
		
		
		$ids = implode(',',$ids);
		$base_filter = "order_id in  (".$ids.")";
		
 
                	
        $this->finder('b2c_mdl_orders', array(
                'title'=>app::get('b2c')->_('近４天没有同步的订单'),
            	'use_buildin_recycle'=>false,
            	'base_filter' =>$base_filter,
                ));
    }
	/**
	 *门店名没有同步的订单　by Sam
	*/
    function erp_order_no_branch() {
		$week = time()-3600*24*4;
		$sql ="SELECT  order_bn,order_id,branch_id,FROM_UNIXTIME(createtime,'%Y%m%d') FROM sdb_ome_orders where shipping = '门店收银' and  branch_id = '0' and createtime>='{$week}'";

		$erpData = kernel::single('base_db_connect')->select($sql);

		$ids = array();
		foreach ($erpData as $key=>$value) {
				$ids[] = $value['order_id'];
		}

		$ids = implode(',',$ids);
		$base_filter = " order_id in  (".$ids.")";
		
		$actions =
                array(
                    array(
                        'label' => '批量重试',
                        'submit' => 'index.php?app=apiactionlog&ctl=admin_apilog&act=re_request',
                        'target' => "refresh",//dialog::{width:550,height:300,title:'批量重试'}",
                    ),
                );
				
        $this->finder('b2c_mdl_orders', array(
                'title'=>app::get('b2c')->_('近４天门店名没有同步的订单'),
				'actions'=> $actions,
            	'use_buildin_recycle'=>false,
				'use_buildin_new_dialog' => false,
				'use_buildin_set_tag'=>false,
				'use_buildin_export'=>false,
				'use_buildin_import'=>false,
				'use_buildin_filter'=>true,
            	'base_filter' =>$base_filter,
                ));
    }
}
