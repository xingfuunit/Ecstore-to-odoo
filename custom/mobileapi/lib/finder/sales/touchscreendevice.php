<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class mobileapi_finder_sales_touchscreendevice{
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=mobileapi&ctl=admin_sales_touchscreendevice&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['device_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }
    var $column_view = '预览';
    function column_view($row){
		if (isset($_SERVER['SERVER_NAME']) && strlen(''.$row['device_name'])>0 ){
			$url=$_SERVER['SERVER_NAME']; 
			return '<a href="http://'.$url.'/wap/touchscreen.html?uuid='.$row['device_name'].'" target="_blank">'.app::get('b2c')->_('预览').'</a>';
		}
        return '';
    }
}
