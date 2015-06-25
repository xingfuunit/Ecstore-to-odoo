<?php 
/**
 * ********************************************
 * Description   : 模板管理
 * Filename      : tmpl.php
 * Create time   : 2014-06-23 11:39:01
 * Last modified : 2014-06-23 11:39:01
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_theme_tmpl {
    
    /*
     * return tmpl
     */
    public function __get_tmpl_list()
    {
        $ctl = array(
            'microshop' => '微店',
        );
        return $ctl;
    }
    #End Func
}
