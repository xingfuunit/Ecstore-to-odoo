<?php
/**
 * ********************************************
 * Description   : erp 门店同步脚本
 * Filename      : store.php
 * Create time   : 2014-07-11 15:17:07
 * Last modified : 2014-07-11 15:37:16
 * License       : MIT, GPL
 * ********************************************
 */
class b2c_erp_store extends b2c_erp_base {

    public $method  = 'ome.shop.add';

    public $shop_bn_prefix = 'xbd_store_';

    /**
     * 同步
     * 
     * @param   array   $param  需要同步的数据
     * @return  string
     */
    public function sync( $param ) {
        $param['method']    = $this->method;
        return parent::sync($param);
    }

    /**
     * 获得店铺代码
     *
     * @param   int     $param  店铺ID
     * @return  string
     */
    public function getShopBN( $param ) {
        return $this->shop_bn_prefix.$param;
    }

}
