<?php
/**
 * ********************************************
 * Description   : 获得店铺相关接口
 * Filename      : shop.php
 * Create time   : 2014-07-11 21:41:58
 * Last modified : 2014-07-11 21:41:58
 * License       : MIT, GPL
 * ********************************************
 */
class b2c_shop {

    /**
     * 获得店铺绑定信息
     *
     * @return  int     0 => 未绑定, > 0 绑定
     */
    public function getShopBindStatus() {
        $obj_b2c_shop   = app::get('b2c')->model('shop');
        $node_type      = array('ecos.ome','ecos.ocs');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type|in'=>$node_type));
        return $cnt;
    }

}
