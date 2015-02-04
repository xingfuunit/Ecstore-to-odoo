<?php
function theme_widget_qf_brandcarousel(&$setting,&$smarty){
	$row = ($setting['limit'])?$setting['limit']:20; //总数
	$col = ($setting['colums'])?$setting['colums']:5;//列数
	//获取品牌
	$brand_list = app::get('b2c')->model('brand')->getList('*',array(),0,$row,'ordernum desc');
	
	$data['slideData'] = array();
	$data['slidenum'] = ceil(@count($brand_list)/$col); //计算层数
	
	for($i=0;$i<$data['slidenum'];$i++){
		$data['slideData'][] = array_slice($brand_list,$i*$col,$col);
	}
	//print_r($data);exit;
    return $data;
}
?>
