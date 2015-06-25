<?php
/**
 * ********************************************
 * Description   : 专辑finder
 * Filename      : special.php
 * Create time   : 2014-06-16 14:34:38
 * Last modified : 2014-06-18 17:01:28
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_finder_special {

    public $column_member_name = '所属用户';
    public $special_info = array();
    function column_member_name($row) {
        $info   = $this->_getSpecialInfo($row);
        $filter = array (
            'member_id'     => $info['member_id'],
            'login_type'    => 'local'
        );
        $member_info    = app::get('pam')->model('members')->getList('login_account', $filter);
        return $member_info[0]['login_account'];
    }

    public $column_shop_name    = '所属微店';
    function column_shop_name($row) {
        $info   = $this->_getSpecialInfo($row);
        $shop   = app::get('microshop')->model('shop')->dump($info['shop_id'], 'shop_name');
        return $shop['shop_name'];
    }

    public $column_see_products = '查看商品';
    function column_see_products($row) {
        return '<a href="index.php?app='.$_GET['app'].'&ctl=admin_products&act=index&filter[special_id]='.$row['special_id'].'">'.$this->column_see_products.'</a>';
    }

    /**
     * 获得专辑信息
     */
    private function _getSpecialInfo( $row ) {
        if ( $this->special_info[$row['special_id']] ) {
            $info   = $this->special_info[$row['special_id']];
        } else {
            $mdl    = app::get('microshop')->model('special');
            $info   = $mdl->dump($row['special_id']);
            $this->special_info[$row['special_id']] = $info;
        }
        return $info;
    }
    
}
