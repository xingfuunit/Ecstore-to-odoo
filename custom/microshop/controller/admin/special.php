<?php
/**
 * ********************************************
 * Description   : 专辑列表数据
 * Filename      : special.php
 * Create time   : 2014-06-11 17:07:45
 * Last modified : 2014-06-18 16:57:40
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_ctl_admin_special extends desktop_controller {

    var $workground = 'b2c.workground.microshop';
    
    /**
     * 列表
     */
    function index() {
        $this->finder('microshop_mdl_special',array(
            'title'=>app::get('microshop')->_('专辑列表'),
            'use_buildin_filter'=>true,
            'use_buildin_export'=>true,
        ));
    }
}
