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
			error_log(date("Y-m-d H:i:s")."	".json_encode($erpData)."\n",3,ROOT_DIR.'/data/logs/sync_branch_log.php');
			$ids = array();
			foreach ($erpData as $key=>$value) {
					$sql ='SELECT branch_id,branch_name_user from sdb_b2c_orders where order_id = \''.$value['order_bn'].'\'';

					$ecData = kernel::database()->select($sql);
					$ecData = $ecData[0];
					
					$sql = 'UPDATE sdb_ome_orders SET branch_id='.'\''.$ecData['branch_id'].'\''.' , '.'branch_name_user='.'\''.$ecData['branch_name_user'].'\''.' WHERE order_bn='.'\''.$value['order_bn'].'\'';

					error_log("	".$sql,3,ROOT_DIR.'/data/logs/sync_branch_log.php');
					
					$erpSync = kernel::single('base_db_connect')->select($sql);
					
					error_log("	".json_encode($erpSync)."\n",3,ROOT_DIR.'/data/logs/sync_branch_log.php');
					
					
					
			}			
		}
		echo '--end--'; 
		exit;
		
    }
}
