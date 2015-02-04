<?php
/**
 * ********************************************
 * Description   : 订单推广分成处理接口
 * ********************************************
 */
class microshop_rpc_promotioninto_orderfinish {

	function __construct( $app ) {
		$this->app = app::get("b2c");
		$this->db  = kernel::database();
	}

	public function generate($order){
		
		$order_id = $order['order_id'];
		
		if($order['status'] != 'finish') return false; //未完成的的不建立提成数据
		
		$sql="SELECT pri_id FROM `sdb_microshop_promotion_into` where from_system = 'ecstore' and order_id = '".$order_id."'";
		$proi = $this->db->selectrow($sql);
		if($proi) return false; //已经建立不再建立提成数据
		
		$sql = "SELECT * FROM `sdb_b2c_order_items` where buy_code != '' and order_id = '$order_id' and cost < price and cost > 0 ";
		$order_items = $this->db->select($sql);
		
		$proi_model = kernel::single('microshop_mdl_promotion_into');
		
		if($order_items){
			foreach ($order_items as $p_item){
				
				$buy_code = explode('_', $p_item['buy_code']);
				$special_id = $buy_code[0];
				$sql = "SELECT special_name,member_id FROM `sdb_microshop_special` where special_id = '$special_id'";
				$special = $this->db->selectrow($sql);
				
				$sql = "SELECT agency_id,member_id FROM `sdb_microshop_shop` where member_id = '".$special['member_id']."'";
				$microshop_shop = $this->db->selectrow($sql);
				
				$sql = "SELECT number,agency_no FROM `sdb_b2c_delivery_items` where order_item_id = '".$p_item['item_id']."'";
				$delivery_items = $this->db->select($sql);
				
				if($special && $p_item['cost'] > 0){
					$proi_row = array(
							'pro_member_id' => $special['member_id'], //微店推广会员
							'agency_id' => $microshop_shop['agency_id']?$microshop_shop['agency_id']:0, //经销商ID
							'ship_members' => $delivery_items, //发货商编号，数量
							'order_id' => $p_item["order_id"],
							'ext_order_id' => '', //第三方系统订单号
							'from_system' => 'ecstore',
							'from_client' => $buy_code[1],
							'special_id' =>  $special_id,
							'special_name' => $special['special_name'],
							'product_id' => $p_item['product_id'],
							'bn' => $p_item['bn'],
							'name' => $p_item['name'],
							'cost' => $p_item['cost'],  //成本价
							'price' => $p_item['price'], //商品售出价
							'nums' => $p_item['nums'],
							'pri_status' => '0',
							'addtime' => time(),
					);
					$proi_model->insert($proi_row);
				}
				
			}
		}
	}
	
}

?>