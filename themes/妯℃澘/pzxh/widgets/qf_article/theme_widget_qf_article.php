<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_qf_article($setting,&$render){
 
   $id = $setting['id'];
   $data = b2c_widgets::load('Article')->getNodeArticles($id);
   $result = array_reverse($data);
   //print_r($data); exit;
   return $result;
}
?>
