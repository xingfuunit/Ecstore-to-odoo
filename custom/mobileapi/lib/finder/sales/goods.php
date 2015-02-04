<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class mobileapi_finder_sales_goods{
    var $column_edit = '编辑';
    function column_edit($row){
    	$str  =  '<a href="index.php?app=mobileapi&ctl=admin_sales_goods&act=edit&p[0]='.$row['rule_id'].'&finder_id='.$_GET['_finder']['finder_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
        return $str;
    }
}
