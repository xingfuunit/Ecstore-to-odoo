<?php
function theme_widget_cfg_qf_all_category(){
   	$o1 = app::get('b2c')->model('goods_cat');
	$o2 = &app::get('b2c')->model('goods_virtual_cat');
	$sql = "SELECT cat_name,cat_id FROM sdb_b2c_goods_cat WHERE parent_id=0";
	$result['cats'] = $o1->db->select($sql);;
	$result['vcats'] = $o2->getMapTree(0,'');	
	//print_r($result);exit;
    return $result;
}
?>