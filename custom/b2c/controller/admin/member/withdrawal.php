<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_member_withdrawal extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    function index(){
          $this->finder('b2c_mdl_member_withdrawal',array(
          'title'=>app::get('b2c')->_('提现列表'),
            'use_buildin_recycle'=>true,
            'base_filter' =>array('for_comment_id' => 0,'has_sent'=>'true'),//增加过滤，只显示已经发送的站内信@lujy
             'delete_confirm_tip'=>app::get('b2c')->_('删除后会员提现也会删除,确定删除吗?')
          ));

    }

    function addnew($id = null){
      if($id!=null){
          $mem_wit = $this->app->model('member_withdrawal');
          $aWit = $mem_wit->dump($id);

          $pam_member_info =kernel::single('b2c_user_object')->get_members_data(array('account'=>'login_account', 'members' => '*'),$aWit['member_id']);
          $this->pagedata['data'] = $aWit;
          $this->pagedata['data']['uname'] = $pam_member_info['account']['local'];
      }

      $this->display('admin/member/withdrawal.html');
    }

    function save(){
      $this->begin();
      $mem_wit = $this->app->model('member_withdrawal');
      $mem_adv =  $this->app->model('member_advance');
      $row = $mem_wit->dump($_POST['id']);
      if (!$row || $row['has_op'] == 'true') $this->end(false,app::get('b2c')->_('该数据已经处理！'));

      $data['modify_advance'] = -$row['amount'];
      $data['modify_memo'] = $_POST['remark'];
      #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
      if($obj_operatorlogs = kernel::service('operatorlog.members')){
        $olddata = app::get('b2c')->model('members')->dump($data['member_id']);
      }
      #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

      if(!$mem_adv->adj_amount($row['member_id'],$data,$msg,false)){
        $this->end(false,app::get('b2c')->_($msg));
      }

      #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
      if($obj_operatorlogs = kernel::service('operatorlog.members')){
        if(method_exists($obj_operatorlogs,'detail_advance_log')){
          $newdata = app::get('b2c')->model('members')->dump($data['member_id']);
          $obj_operatorlogs->detail_advance_log($newdata,$olddata);
        }
      }
      #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

      $_POST['has_op'] = 'true';
      if($mem_wit->save($_POST)){
        $this->end(true,app::get('b2c')->_('保存成功'));
      }else{
        $this->end(false,app::get('b2c')->_('保存失败'));
      }
    }
}
