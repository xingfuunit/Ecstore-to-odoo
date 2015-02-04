<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_cfg_qf_goodscatnav($app){
    $o = &app::get('b2c')->model('goods_cat');
   $goodslist=$o->getList('cat_id,cat_name',array('parent_id'=>0,'disabled'=>'false'),0,-1,'p_order asc');
 //  print_r($goodslist);
   return $goodslist;
}
?>
