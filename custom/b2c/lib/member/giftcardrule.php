<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_member_giftcardrule{    
    var $column_editbutton = '操作'; 
    var $detail_basic = '查看';   
   
    

	
	public function __construct($app) {
        $this->app = $app;
        $this->controller = app::get('b2c')->controller('admin_member_giftcardrule');
        //$this->detail_basic = app::get('b2c')->_('查看信息');
        $this->detail_log = app::get('b2c')->_('生成记录');
    }

    public function column_editbutton($row){
        return '<a href="index.php?app=b2c&ctl=admin_member_giftcardrule&act=add_giftcard_rule&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['rule_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑充值券规则').'\', width:680, height:250}">'.app::get('b2c')->_('编辑').'</a>&nbsp;&nbsp;
            <a href="index.php?app=b2c&ctl=admin_member_giftcardrule&act=add_giftcard&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['rule_id'].'" target="dialog::{title:\''.app::get('b2c')->_('生成充值券').'\', width:680, height:300}">'.app::get('b2c')->_('生成').'</a>';
        
    }
        
    //生成记录
    public function detail_log($rule_id){

        $render = $this->app->render();
        $gcruleMdl = $this->app->model('member_giftcardrule');
        $giftcard = $gcruleMdl->getList( '*',array('rule_id'=>$rule_id) );
        $giftcard = $giftcard[0];

        //日志分页
        $page = ($_POST['page']) ? $_POST['page'] : 1;
        $pageLimit = 10;
        $glog = $gcruleMdl->getGiftcardLogList($rule_id,$page-1,$pageLimit);
        $ui = new base_component_ui($this->app);
        $render->pagedata['gclogList'] = $glog;
        $render->pagedata['result'] = array('SUCCESS'=>app::get('b2c')->_('成功'),'FAILURE'=>app::get('b2c')->_('失败'));

        $pager = array(
            'current'=> $page,
            'total'=> ceil($glog['page']/$pageLimit),
            'link'=> 'javascript:W.page(\'index.php?app=b2c&ctl=admin_member_giftcardrule&act=index&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&action=detail&finder_id='.$_GET['_finder']['finder_id'].'&id='.$rule_id.'&finderview=detail_log&finder_name='.$_GET['_finder']['finder_id'].'\', {update:$E(\'.subtableform\').parentNode, method:\'post\', data:\'&page=%d\'});'
        );
        $render->pagedata['pager'] = $ui->pager($pager);
        $render->pagedata['pagestart'] = ($page-1)*$pageLimit;
        
        return $render->fetch('admin/member/giftcard_info.html',$this->app->app_id);
    }
}
