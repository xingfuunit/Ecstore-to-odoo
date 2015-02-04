<?php
function theme_widget_qf_all_category(&$setting,&$render){
// 判断是否首页
    if('site_ctl_default' == get_class($render)){
        $result['isindex'] = true;
    }
    if( false&& base_kvstore::instance('b2c_goods')->fetch('goods_cat_ex_vertical_widget.data',$cat_list) ){
        return $cat_list;
    }
    $cat_mdl = app::get('b2c')->model('goods_cat');
    $brand_mdl  = app::get('b2c')->model('brand');
    $salesList = _ex_vertical_getSales();
    $brandlist = $brand_mdl->getAll();
    #$new_brandlist5 = array_slice($brandlist, 0,8,true);
    foreach ($brandlist as $key => $value) {
        $brand_list[$value['brand_id']] = $value;
    }
    $cat_list =$cat_mdl->get_cat_list();
    $kvstore_goods_cat_expires = app::get('b2c')->getConf('kvstore_goods_cat_expires');	
    $_returnData['brand_list'] = $brand_list;
    $all_brandids = array();
    foreach ($cat_list as $k=>$cat) {
        switch ($cat['step']){
        case 1:
            $all_cids 	= _ex_vertical_getAllChildAttr($cat_list,$cat['cat_id']);
            $all_cids[] 	= $cat['cat_id'];
            $all_typeids 	= _ex_vertical_getAllChildAttr($cat_list,$cat['cat_id'],'type');
            $all_typeids[] 	= $cat['type'];
            $all_brandids 	= _ex_vertical_getLinkBrandIds($all_typeids);
            $cat['brand'] = $all_brandids;
            //关联促销
            foreach ($salesList as $sale) {
                $allowLink = false;
                foreach ($sale['conditions']['conditions'] as $condition) {
                    switch ($condition['attribute']) {
                    case 'goods_cat_id':
                        $instersect = array_intersect($condition['value'],$all_cids);
                        if(count($instersect)>0){
                            $allowLink = true;
                        }
                        break;
                    case 'goods_brand_id':
                        $instersect = array_intersect($condition['value'],$all_brandids);
                        if(count($instersect)>0){
                            $allowLink = true;
                        }
                        break;
                    }
                }
                if($allowLink){
                    $cat['sales'][] = $sale;
                }
            }
            $_returnData['data'][$cat['cat_id']] = $cat;
            $_returnData['data'][$cat['cat_id']]['catimg']=$setting['resetcat'][$cat['cat_id']];
//             $_returnData['resetcat'][$cat['cat_id']]=$setting['resetcat'][$cat['cat_id']];
            break;
        case 2:
            $_returnData['data'][$cat['pid']]['lv2'][$cat['cat_id']] = $cat;
            break;
        case 3:
            $ids = explode(',',$cat['cat_path']);
            $_returnData['data'][$ids[1]]['lv2'][$cat['pid']]['lv3'][$cat['cat_id']] = $cat;
            break;

        }//end switch
    }
	
	/*虚拟分类开始*/
	$goods_cat=&app::get('b2c')->model('goods_virtual_cat');
    $pid = $goods_cat->dump($setting['virtualcat_id'],'parent_id');
    $data=$goods_cat->getMapTree($pid['parent_id'],'',$setting['virtualcat_id']);
    for($i=0;$i<count($data);$i++){
        $cat_path=$data[$i]['cat_path'];
        $cat_name=$data[$i]['cat_name'];
        $cat_id=$data[$i]['cat_id'];
        if(empty($cat_path) or $cat_path==","){//一
            $myData[$cat_id]['label']=$cat_name;
            $myData[$cat_id]['cat_id']=$cat_id;
            $myData[$cat_id]['url']=$data[$i]['url'];
        }
    }
    for($i=0;$i<count($data);$i++){
        $cat_path=$data[$i]['cat_path'];
        $cat_name=$data[$i]['cat_name'];
        $cat_id=$data[$i]['cat_id'];
        $url=$data[$i]['url'];
        $parent_id=$data[$i]['pid'];

        if(trim($cat_path) == ','){
            $count=0;
        }else{
            $count=count(explode(',',$cat_path));
        }
        if($count==2){//第二层
            $c_1=intval($parent_id);
            $c_2=intval($cat_id);
            $myData[$c_1]['sub'][$c_2]['label']=$cat_name;
            $myData[$c_1]['sub'][$c_2]['cat_id']=$cat_id;
            $myData[$c_1]['sub'][$c_2]['url']=$url;
        }
        if($count==3){//第三层
            $tmp=explode(',',$cat_path);
            $c_1=intval($tmp[0]);
            $c_2=intval($tmp[1]);
            $c_3=intval($cat_id);
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['label']=$cat_name;
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['cat_id']=$cat_id;
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['url']=$url;
        }
    }
	$_returnData['virtualcat']=$myData;
	/*虚拟分类结束*/	
	
	foreach($setting['menus'] as $menus){		
		$_returnData['menus'] = $menus;
	}
	
	
	//print_r($_returnData['resetcat']);
	$_returnData['page'] = app::get('site')->router()->get_query_info('module');
    return $_returnData;
}


function _ex_vertical_getAllChildAttr($arr,$pid,$attribute = 'cat_id'){
    foreach ($arr as $item) {
        if(in_array($pid, explode(',', $item['cat_path']))){
            $_return[] = $item[$attribute];
        }
    }
    return $_return;
}
function _ex_vertical_getLinkBrandIds($typeids){
    $sql = 'SELECT brand_id FROM '.kernel::database()->prefix.'b2c_type_brand WHERE type_id  in('.implode(',',array_unique($typeids)).')';
    $res = app::get('b2c')->model('brand')->db->select($sql );
    foreach ($res as $key => $value) {
        $_return[] = $value['brand_id'];
    }
    if($_return){
        $_return = array_unique($_return);
    }
    return $_return;
}

function _ex_vertical_getSales(){
    $goods_sales_mdl = app::get('b2c')->model('sales_rule_goods');
    $goods_sales_list = $goods_sales_mdl->getList('*');
    return $goods_sales_list;
}

function _ex_vertical_getAd(){
	$goods_sales_mdl = app::get('b2c')->model('sales_rule_goods');
	$goods_sales_list = $goods_sales_mdl->getList('*');
	return $goods_sales_list;
}
?>