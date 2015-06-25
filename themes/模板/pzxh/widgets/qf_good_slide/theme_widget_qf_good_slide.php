<?php
function theme_widget_qf_good_slide(&$setting,&$render){
    $_return = false;
    switch ($setting['selector']) {
    case 'filter':
        parse_str($setting['goods_filter'],$goodsFilter);
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
        $goodsFilter['goodsNum'] = (intval($setting['limit'])>0)?intval($setting['limit']):6;
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
        //$_return = array_values($_return['goodsRows']);
        break;
    case 'select':
        $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
		$goodsFilter['goodsNum'] = (intval($setting['limit'])>0)?intval($setting['limit']):6;		
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
        foreach (json_decode($setting['goods_select_linkobj'],1) as $obj) {
            if(trim($obj['pic'])!=""){
                $_return['goodsRows'][$obj['id']]['goodsPicL'] = 
                    $_return['goodsRows'][$obj['id']]['goodsPicM'] = 
                    $_return['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
            }
            if(trim($obj['nice'])!=""){
                $_return['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
            }
        }

        break;

    }
    $gids = array_keys($_return['goodsRows']);
    if(!$gids||count($gids)<1)return $_return;
    $mdl_product = app::get('b2c')->model('products');
    $products = $mdl_product ->getList('product_id, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true'));

    foreach ($products as $product) {

        $_return['goodsRows'][$product['goods_id']]['products'][] = $product;
    }
	$_return['slideData'] = array();
	$col = ($setting['colums'])?$setting['colums']:4;//列数
	$_return['slidenum']=ceil(@count($_return['goodsRows'])/$col);
	for($i=0;$i<$_return['slidenum'];$i++){
		$_return['slideData'][] = array_slice($_return['goodsRows'],$i*$col,$col);
	}
	
	//print_r($_return['slideData']);
	return $_return;
}
?>
