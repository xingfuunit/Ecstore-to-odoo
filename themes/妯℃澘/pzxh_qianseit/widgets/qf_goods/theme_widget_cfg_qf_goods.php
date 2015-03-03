<?php
  function theme_widget_cfg_qf_goods(){		
		$data['goods_order_by'] = b2c_widgets::load('Goods')->getGoodsOrderBy();
		return $data;
	}
?>
