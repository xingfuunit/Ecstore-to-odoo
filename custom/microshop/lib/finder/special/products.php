<?php
/**
 * ********************************************
 * Description   : 专辑finder
 * Filename      : special_products.php
 * Create time   : 2014-06-16 14:34:38
 * Last modified : 2014-06-19 10:17:42
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_finder_special_products {

    public $column_member_name  = '所属用户';
    public $products_info       = array();
    function column_member_name($row) {
        $info   = $this->_getProductsInfo($row);
        $filter = array (
            'member_id'     => $info['member_id'],
            'login_type'    => 'local'
        );
        $member_info    = app::get('pam')->model('members')->getList('login_account', $filter);
        return $member_info[0]['login_account'];
    }

    public $column_special_name = '所属专辑';
    function column_special_name($row) {
        $info   = $this->_getProductsInfo($row);
        $spec   = app::get('microshop')->model('special')->dump($info['special_id'], 'special_name');
        return $spec['special_name'];
    }

    public $column_products_name = '商品描述';
    public $b2c_products_info    = array();
    function column_products_name($row) {
        $info   = $this->_getProductsInfo($row);
        $product= $this->_getB2CProductsInfo($info);
        $url    = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$info['product_id']));
        return '<a href="'.$url.'" target="_blank">'.$product['name'].'</a>('.$product['spec_info'].')';
    }
    
    public $column_products_price = '商品售价';
    function column_products_price($row) {
        $info   = $this->_getProductsInfo($row);
        $product= $this->_getB2CProductsInfo($info);
        return $product['price']['price']['price'];
    }

    public $column_is_market = '是否正在销售';
    function column_is_market($row) {
        $info   = $this->_getProductsInfo($row);
        $product= $this->_getB2CProductsInfo($info);
        return $product['status'];
    }

    /**
     * 获得专辑产品数据
     */
    private function _getProductsInfo( $row ) {
        if ( $this->products_info[$row['special_products_id']] ) {
            $info   = $this->products_info[$row['special_products_id']];
        } else {
            $mdl    = app::get('microshop')->model('special_products');
            $info   = $mdl->dump($row['special_products_id']);
            $this->products_info[$row['special_products_id']] = $info;
        }
        return $info;
    }

    /**
     * 获得b2c中的产品数据
     * 
     * @param   array   $info   专辑产品数据，必需包含product_id字段
     */
    private function _getB2CProductsInfo( $info ) {
        if ( $this->b2c_products_info[$info['product_id']] ) {
            $product= $this->b2c_products_info[$info['product_id']];
        } else {
            $product= app::get('b2c')->model('products')->dump($info['product_id']);
            $this->b2c_products_info[$info['product_id']] = $product;
        }
        return $product;
    }
}
