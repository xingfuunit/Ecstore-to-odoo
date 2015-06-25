<?php
/**
 * ********************************************
 * Description   : 微店数据模型
 * Filename      : shop.php
 * Create time   : 2014-06-19 16:26:06
 * Last modified : 2014-06-19 16:27:07
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_mdl_shop extends dbeav_model {

    function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 获得商店详情
     * 
     * @param   array   $param          参数
     * @param   number  $update_visit   是否更新访问，1->更新(默认), 其它 -> 不更新
     * @return  array
     */
    function getDetail( $param = array(), $update_visit = 1 ) {
        $info   = array();
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
        $member_id  = $param['member_id'] ? $param['member_id'] : 0;
        if ( $shop_id > 0 || $member_id > 0 ) {
            if ( $shop_id ) {
                $filter['shop_id']      = $param['shop_id'];
            }
            if ( $member_id ) {
                $filter['member_id']    = $param['member_id'];
            }
            $info   = parent::dump($filter);
            // 添加商品访问
            if ( $info['is_open'] == 1 ) {
                if ( $update_visit == 1 ) {
                    $_info      = array (
                        'see_num'   => $info['see_num'] + 1 
                    );  
                    parent::update( $_info, array('shop_id' => $info['shop_id']) );
                }
                $_param = array (
                        'app'   => 'microshop',
                        'ctl'   => 'site_index',
                        'full'  => 1,
                        'act'   => 'detail',
                        'arg0'  => $info['shop_id'],
                );  
                $info['shop_link']  = app::get('site')->router()->gen_url($_param);
                
                $m_mdl  = app::get('b2c')->model('members');
                $m_info = $m_mdl->dump($info['member_id']);
                $info['follow_num'] = $m_info['follow_num'];
                $info['fans_num']   = $m_info['follow_num'];
                $info['cover']      = $m_info['cover'] ? kernel::single('base_storager')->image_path($m_info['cover']) : $this->app->res_url.'/images/top-bg.png';
                $info['avatar']     = $m_info['avatar'] ? kernel::single('base_storager')->image_path($m_info['avatar']) : $this->app->res_url.'/images/top-bg.png';
                $info['info']       = $m_info['info'];
            }
        }
        return $info;
    }
}
