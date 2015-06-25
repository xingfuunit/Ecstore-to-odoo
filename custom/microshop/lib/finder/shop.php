<?php
/**
 * ********************************************
 * Description   : 商店finder
 * Filename      : shop.php
 * Create time   : 2014-06-16 14:34:38
 * Last modified : 2014-06-17 11:46:35
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_finder_shop {

    public $column_edit = '编辑';

    function column_edit($row) {
        return '<a href="index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['shop_id'].'" target="_blank">'.$this->column_edit.'</a>';
    }

    public $column_shop_status = '微店开启状态';
    function column_shop_status($row) {
        $mdl    = app::get('microshop')->model('shop');
        $info   = $mdl->dump($row['shop_id']);
        $status = $info['is_open']  == 1 ? '开启' : '关闭';
        return $status;
    }

    public $column_see_special = '查看专辑';
    function column_see_special( $row ) {
        return '<a href="index.php?app='.$_GET['app'].'&ctl=admin_special&act=index&filter[shop_id]='.$row['shop_id'].'">'.$this->column_see_special.'</a>';
    }
    
}
