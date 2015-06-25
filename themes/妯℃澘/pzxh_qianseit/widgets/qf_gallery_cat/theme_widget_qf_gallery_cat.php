<?php
function theme_widget_qf_gallery_cat(&$setting,&$render){
    $ShowType = $setting['catShowType']; //级别显示模式，0表示显示全部分类；1表示自动判断,2表示指定分类时显示该分类下子分类;
    $showCatDepth=$setting['showCatDepth_default']; // 显示深度 1为仅显示1级 2为显示2级 3为显示三级
    //取当前分类
    if($render->pagedata['screen']){
        $cat_id=$render->pagedata['screen']['cat_id'];
        $pagetype="gallery";
    }elseif($render->pagedata['page_product_basic']){
        $gcgoods_id=$render->pagedata['page_product_basic']['goods_id'];
        $arrcat_id=app::get('b2c')->model('goods')->getRow('cat_id',array('goods_id'=>$gcgoods_id));
        $cat_id=$arrcat_id['cat_id'];
        $pagetype="product";
    }
    if($ShowType==2){
        $showcatid=$setting['show_cat_id']; //指定显示的分类id
        if(strstr($showcatid,",")){
            $showcatid=explode(",",$showcatid);
        }
        if($showcatid){
            $data = b2c_widgets::load('GoodsCat')->getGoodsCatMap($showcatid, true);
        }
    }
    elseif($ShowType==0){
       // base_kvstore::instance('b2c_goods')->fetch('goods_cat.data',$data);
        $data = b2c_widgets::load('GoodsCat')->getGoodsCatMap('', true);
    }
    elseif($ShowType==1){
        if($cat_id){
            $data = b2c_widgets::load('GoodsCat')->getGoodsCatMap($cat_id, true);
        }
    }
    //根据深度取产品
    $map = array();
    if($showCatDepth==1){
        foreach($data as $key=>$val){
            $map[$key]=theme_widget_qf_gallery_cat_get_item($val);
        }
    }
    if($showCatDepth==2){
        foreach($data as $key=>$val){
            $map[$key]=theme_widget_qf_gallery_cat_get_item_two($val);
        }
    }
    if($showCatDepth==3){
        foreach($data as $key=>$val){
            $map[$key]=theme_widget_qf_gallery_cat_get_item_three($val);
        }
    }
    $data = $map;

	$cat_ids = array();
    if ($cat_id){
        if ($cat_path = $render->app->model('goods_cat')->select()->columns('cat_path')->where('cat_id = ?',$cat_id)->instance()->fetch_one()){

            $arr =  explode(',',$cat_path);
            foreach ($arr as $a){
                if ($a) $cat_ids[] = $a;
         }
        }
    }
	$cat_ids[] = $cat_id;
    $data = array('cats' => $data, 'cur_cat_id' => $cat_id, 'cat_ids' => $cat_ids);

   //print_r($data);
	return $data;
}
function theme_widget_qf_gallery_cat_get_item($c){
	return array(
		'catId' => $c['catId'],
		'catName' => $c['catName'],
        'catLink'=>$c['catLink']
		//'catLink' => b2c_widgets::load('GoodsCat')->get_link(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array($c['cat_id']))),
		);
}
function theme_widget_qf_gallery_cat_get_item_two($c){
    $twoitem= array(
        'catId' => $c['catId'],
        'catName' => $c['catName'],
        'catLink'=>$c['catLink'],
    );
    if(!empty($c['items']) && is_array($c['items'])){
        foreach($c['items'] as $twkey=>$twval){
            $twoitems[$twkey]=array(
                'catId'=>$twval['catId'],
                'catName'=>$twval['catName'],
                'catLink'=>$twval['catLink'],
            );
        }
        $twoitem['items']=$twoitems;
    }
    return $twoitem;
}
function theme_widget_qf_gallery_cat_get_item_three($c){
    $threeitem= array(
        'catId' => $c['catId'],
        'catName' => $c['catName'],
        'catLink'=>$c['catLink'],
    );
    if(!empty($c['items']) && is_array($c['items'])){
        foreach($c['items'] as $trkey=>$trval){
            $treitems[$trkey]=array(
                'catId'=>$trval['catId'],
                'catName'=>$trval['catName'],
                'catLink'=>$trval['catLink'],
            );
            if(!empty($trval['items']) && is_array($trval['items'])){
                foreach($trval['items'] as $trikey=>$trival){
                    $treitems[$trkey]['items'][$trikey]=array(
                        'catId'=>$trival['catId'],
                        'catName'=>$trival['catName'],
                        'catLink'=>$trival['catLink'],
                    );
                }
            }
        }
        $threeitem['items']=$treitems;
    }
    return $threeitem;
}
?>