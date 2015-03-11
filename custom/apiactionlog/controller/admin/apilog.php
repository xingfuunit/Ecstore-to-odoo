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
        		$week = time()-3600*24*14;
        		$aData = kernel::database()->select("SELECT a.apilog_id FROM sdb_apiactionlog_apilog a,sdb_b2c_orders b WHERE a.worker='store.trade.update' AND (a.task_name='订单变更' or a.task_name='订单新增') AND b.shipping != '门店收银'  AND a.original_bn=b.order_id AND b.pay_status='1' AND b.ship_status='0' AND b.status != 'finish' AND a.status='fail' AND a.last_modified>='{$week}'  ");
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
}
