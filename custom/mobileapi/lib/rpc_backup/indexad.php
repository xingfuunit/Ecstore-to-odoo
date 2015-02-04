<?php
/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_indexad {
	
	/**
	 */
	function __construct($app) {
		$this->app = $app;
	}
	
	function get_all_list() {
		
		$db = kernel::database();
		
		/*
		$sql = "select *  FROM `sdb_mobileapi_indexad` where disabled = 'false' order by group_code asc, ordernum asc;";
		$indexads = $db->select($sql);
		
		$groupad = array();
		foreach ($indexads as $key => $v){
			
			$v['ad_img'] = base_storager::image_path($v['ad_img']);
			$groupad[$v['group_code']]['group_code'] = $v['group_code'];
			$groupad[$v['group_code']]['group_name'] = $v['group_name'];
			$groupad[$v['group_code']]['items'][] = $v;
		}
		
		$res = array();
		foreach ($groupad as $key => $value) {
			$res[] = $value;
		}
		*/
		
		$sql = "select *  FROM `sdb_mobileapi_indexad_group` where disabled = 'false' order by ordernum asc, group_id asc;";
		$indexads_group = $db->select($sql);
		$now_time = time();
		foreach ($indexads_group as $key => $v){
			$sql = "select *  FROM `sdb_mobileapi_indexad` where disabled = 'false' and group_id = '".$v['group_id']."' order by ordernum asc, ad_id asc;";
			$indexads = $db->select($sql);
			$groupad = array();
			foreach ($indexads as $ads){
				if ($ads['url_type'] == 'goods') {
					$sql = 'select g.buy_count, sg.initial_num FROM `sdb_starbuy_special_goods` AS sg LEFT JOIN `sdb_b2c_products` AS p ON p.product_id = sg.product_id LEFT JOIN `sdb_b2c_goods` AS g ON p.goods_id = g.goods_id WHERE sg.product_id = '.$ads['ad_url'];
					$counts = $db->selectrow($sql);
					$ads['buy_count'] = $counts['buy_count'] + $counts['initial_num'];
				}
				$ads['ad_img'] = base_storager::image_path($ads['ad_img']);
				$groupad[] = $ads;
			}
			$indexads_group[$key]['items'] = $groupad;
			$indexads_group[$key]['system_time'] = $now_time; 
		}
		
		return $indexads_group;
		
		//print_r($res);exit;
	}
}

?>