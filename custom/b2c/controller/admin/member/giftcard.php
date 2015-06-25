<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_member_giftcard extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function index(){
 /*       if(app::get('dzg')->getConf('version_id') != 3){
            header("Content-type:text/html;charset=utf-8");
            echo '很抱歉！您当前的版本无法使用此功能';exit;
        } */
        if($_GET['action'] == 'export') $this->_end_message = '导出充值券';
            $this->finder('b2c_mdl_member_giftcard',array(
            'title'=>app::get('b2c')->_('充值券列表'),
            //'use_buildin_export'=>true,
            'use_buildin_recycle' => false,
            'use_buildin_filter'=>true,
            'actions'=>array(
                array('label'=>app::get('b2c')->_('作废'),'confirm'=>app::get('b2c')->_('确定作废选定充值券？作废后状态不可恢复'),'submit'=>'index.php?app=b2c&ctl=admin_member_giftcard&act=giftcard_invalid'),
            )
            
        ));
    }    
   
    //废除充值券
    function  giftcard_invalid(){
        $this->begin('index.php?app=b2c&ctl=admin_member_giftcard&act=index');
        $gcard_code = $_POST['gcard_code'];
        
        $gcardMdl = $this->app->model('member_giftcard');
        if($gcard_code && is_array($gcard_code)){
            $return = $gcardMdl->update(array('status'=>'dead'),array('gcard_code|in'=>$gcard_code)); 

            if($return){
                $this->end(app::get('b2c')->_('作废成功！'));
                //$this->splash('failed', 'index.php?app=b2c&ctl=admin_member_giftcard&act=index',app::get('b2c')->_('作废成功'));
                //exit;
            }else{
                $this->end(false,app::get('b2c')->_('作废失败！'));
            }    
        }
    }
}
?>