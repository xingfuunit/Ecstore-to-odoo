<?php
function theme_widget_qf_goods_show(&$setting,&$render){
    $goods_list = json_decode($setting['goods'],1);
    $goodsId = array();$goodsInfo = array();
    if (is_array($goods_list))
    foreach ($goods_list as $goods){
        $goodsId[] = $goods['id'];
        $goodsInfo[$goods['id']] = $goods;
    }
    $filter['goodsId'] = $goodsId;
    $data['info'] = $goodsInfo;
    $filter['goodsNum']= $setting['goodsNum'];
    $data['goodsdata'] = b2c_widgets::load('Goods')->getGoodsList($filter);
	//print_r($data);
    return $data; 
}
?>
