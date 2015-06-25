<?php
/**
 * ********************************************
 * Description   : 商品列表数据
 * Filename      : products.php
 * Create time   : 2014-06-11 17:07:45
 * Last modified : 2014-06-19 10:04:53
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_ctl_admin_products extends desktop_controller {

    var $workground = 'b2c.workground.microshop';
    
    /**
     * 列表
     */
    function index() {
        $this->finder('microshop_mdl_special_products',array(
            'title'=>app::get('microshop')->_('商品列表'),
            'use_buildin_filter'=>true,
            'use_buildin_export'=>true,
        ));
    }
}
