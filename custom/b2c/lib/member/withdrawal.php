<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_member_withdrawal{
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);

        $this->column_uname = app::get('b2c')->_('用户名');
        $this->userObject = kernel::single('b2c_user_object');
    }    
    
    public function column_uname($row){
        $pam_member_info = $this->userObject->get_members_data(array('account'=>'login_account', 'members' => '*'),$row['member_id']);
        $this->pam_member_info[$row['member_id']] = $pam_member_info;
        return $pam_member_info['account']['local'];
    }

    var $column_edit = '操作';
    function column_edit($row){
        $return = '<a href="index.php?app=b2c&ctl=admin_member_withdrawal&act=addnew&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑').'\', width:680, height:300}">'.app::get('b2c')->_('编辑').'</a>';
        return $return;
        
    } 


    
}
