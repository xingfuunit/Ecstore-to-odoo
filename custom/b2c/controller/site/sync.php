<?php
/**
 * ********************************************
 * Description   : 定时同步函数
 * Filename      : sync.php
 * Create time   : 2014-07-11 11:50:07
 * Last modified : 2014-07-11 11:50:07
 * License       : MIT, GPL
 * ********************************************
 */
class b2c_ctl_site_sync {
    /**
     * ec同步订单里的门店名到erp
     */
    public function index(){
		
        $week = time()-3600*24*1;
		$sql ="SELECT  order_bn,order_id,branch_id,FROM_UNIXTIME(createtime,'%Y%m%d') as createtime FROM sdb_ome_orders where shipping = '门店收银' and  branch_id = '0' and createtime>='{$week}'";

		$erpData = kernel::single('base_db_connect')->select($sql);

		if($erpData){
			error_log(date("Y-m-d H:i:s")."	".json_encode($erpData),3,ROOT_DIR.'/data/logs/sync_branch_log.php');
			$ids = array();
			foreach ($erpData as $key=>$value) {
					$ids['order_id'][] = $value['order_bn'];
			}
			$this->re_request($ids);
		}
		echo '--end--';
		exit;
    }
	private function re_request($order_no){

        if($order_no){
            $apilog_id = $order_no;
        }

        $request_mdl = kernel::single('apiactionlog_router_request');
        $result = $request_mdl->re_request($apilog_id);

		error_log("	".json_encode($result)."\n",3,ROOT_DIR.'/data/logs/sync_branch_log.php');

    }

}
