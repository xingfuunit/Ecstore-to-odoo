<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_vipday{
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_vipday&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }
}
