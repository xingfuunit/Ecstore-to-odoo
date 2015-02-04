<?php

class timedbuy_rpc_timedbuy {
	
	function __construct($app)
	{
		$this->app = $app;
		$this->app->rpcService = kernel::single('base_rpc_service');
        $this->pagesize = 10;
	}
	
	function getcat_by_name($post) {
		$now_time = time();
		
		$sql = "SELECT * FROM  `sdb_b2c_sales_rule_goods` where 
		c_template = 'b2c_promotion_conditions_goods_selectgoods' and s_template = 'timedbuy_promotion_solution_timedbuy' 
		and to_time >= '$now_time' and status = 'true' and apply_time >= create_time ";
		
		$db = kernel::database();
		$sales = $db->select($sql);
		
		$rules = array();
		foreach ($sales as $key => $value) {
			$rules[$value['name']]['name'] = $value['name'];
			$rules[$value['name']]['rule_ids'][] = $value['rule_id'];
			$rules[$value['name']]['to_time'] = $rules[$value['name']]['to_time'] < $value['to_time']?$value['to_time']:$rules[$value['name']]['to_time'];
		}
		
		$rules_by_name = array();
		foreach ($rules as $key => $value) {
			$value['rule_ids'] = implode(',', $value['rule_ids']);
			$rules_by_name[] = $value;
		}
		
		return array('items' => $rules_by_name);
	}
	
	function goods_list($post) {
		$rule_ids = $post['rule_ids'];
		
		$db = kernel::database();
		
		$sql = "SELECT * FROM  `sdb_b2c_goods_promotion_ref` where rule_id in ($rule_ids) order by to_time asc ";
		$goods = $db->select($sql);
		$g_mdl  = app::get('b2c')->model('goods');
		
		foreach ($goods as $key => $value) {
			$goods[$key]['action_solution'] = unserialize($value['action_solution']);
			$goods[$key]['price'] = $goods[$key]['action_solution']['timedbuy_promotion_solution_timedbuy']['price'];
			$goods_info = $g_mdl->dump($value['goods_id']);
			$goods[$key]['name'] = $goods_info['name'];
			$goods[$key]['default_img_url'] = kernel::single('base_storager')->image_path($goods_info['image_default_id'],'m');
			$goods[$key]['buy_count'] = $goods_info['buy_count'];
			$goods[$key]['old_price'] = $goods_info['price'];	
		}
		
		return array('items' => $goods);
		
	}
	
}

?>