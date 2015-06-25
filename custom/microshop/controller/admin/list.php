<?php
/**
 * ********************************************
 * Description   : 微店列表数据
 * Filename      : list.php
 * Create time   : 2014-06-11 17:07:45
 * Last modified : 2014-06-11 17:07:45
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_ctl_admin_list extends desktop_controller {

    var $workground = 'b2c.workground.microshop';
    
    /**
     * 列表
     */
    function index() {
        $this->finder('microshop_mdl_shop',array(
            'title'=>app::get('microshop')->_('微店列表'),
            'use_buildin_filter'=>true,
            'use_buildin_export'=>true,
        ));
    }

    /**
     * 编辑
     */
    function edit( $shop_id ) {
        header("Cache-Control:no-store");
        $this->pagedata['title']    = app::get('microshop')->_('编辑微店');
        $mdl    = $this->app->model('shop');
        $info   = $mdl->dump($shop_id);
        $filter = array (
            'member_id|in'  => array($info['member_id'], $info['agency_id']),
            'login_type'    => 'local'
        );
        $member_info    = app::get('pam')->model('members')->getList('*', $filter);
        if ( $member_info ) {
            foreach ( $member_info AS $v ) {
                $_type  = $v['member_id'] == $info['member_id'] ? 'member_login_name' : 'agency_login_name';
                $info[$_type]   = $v['login_account'];
            }
        }
        $this->pagedata['info'] = $info;
        $this->singlepage('admin/edit.html');
    }

    /**
     * 添加
     */
    function toAdd() {
        $this->begin('');
        $data   = $_POST;
        $mdl    = $this->app->model('shop');
        if ( $mdl->save($data) ) {
            $this->end(true,    app::get('microshop')->_('操作成功'));
        } else {
            $this->end(true,    app::get('microshop')->_('操作失败'));
        }
    }
}
