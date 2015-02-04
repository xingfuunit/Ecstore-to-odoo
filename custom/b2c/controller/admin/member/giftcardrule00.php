<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_member_giftcardrule extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app){
        parent::__construct($app);
        //header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function index(){
/*        if(app::get('dzg')->getConf('version_id') != 3){
            header("Content-type:text/html;charset=utf-8");
            echo '很抱歉！您当前的版本无法使用此功能';exit;
        } */
        $this->finder('b2c_mdl_member_giftcardrule',array(
            'title'=>app::get('b2c')->_('礼品卡规则'),
            'allow_detail_popup' =>true,
            'use_view_tab'=>true,  
            'use_buildin_filter'=>true,
            'allow_detail_popup'=>ture,          
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加礼品卡规则'),'href'=>'index.php?app=b2c&ctl=admin_member_giftcardrule&act=add_giftcard_rule','target'=>'dialog::{width:680,height:250,title:\''.app::get('b2c')->_('添加礼品卡规则').'\'}'),
            )
        ));
    }

    
    //生成礼品卡
    public function add_giftcard($rule_id=null){       
        $rule_id = $rule_id ? $rule_id : $_GET['p'][0];  
        if($rule_id){
            $gcruleMdl = $this->app->model('member_giftcardrule');
            $gcrule = $gcruleMdl->getList('*',array('rule_id'=>$rule_id));
            $gcrule = $gcrule[0];
            $gcrule['end_time'] = strtotime($gcrule['d_valid'].' days');            
            $this->pagedata['gcrule'] = $gcrule;            
        }
        $this->display('admin/member/add_giftcard.html');

    }

    //保存并生成礼品卡
    public function save_autogiftcard(){
        $arr = $_POST;        
        $this->begin('index.php?app=b2c&ctl=admin_member_giftcardrule&act=index');
        $gcMdl = $this->app->model('member_giftcardrule');
        if(!$_POST['nums'] || $_POST['nums'] > 10000){
            header("Content-type: text/html; charset=UTF-8");
            //echo __('<script>alert("'.app::get('b2c')->_("数量错误！一次生成不能超过500张").'")</script>');   
            $this->end(false,app::get('b2c')->_("数量错误！一次生成不能超过500张"));         
            exit;

        }
       
        if(strtotime($_POST['end_time']) <= time()){            
            $this->end(false,app::get('b2c')->_("有效截止日期不可早于当前日期"));         
            exit;            
        }
        $arr['op_id'] = $this->user->user_id;  //管理员信息只能在控制层获取
        $arr['op_name'] = $this->user->user_data['name'];
        //生成礼品卡
        if($gcMdl->auto_giftcard($arr)){
             
            //$this->export_giftcardlog($arr['log_id']); //生成时直接下载
            $this->end(true, app::get('b2c')->_('生成成功'));

        }

      

    }

    //添加礼品卡规则
    public function add_giftcard_rule($rule_id=null){
        if($rule_id){
            $gcruleMdl = $this->app->model('member_giftcardrule');
            $gcrule = $gcruleMdl->getList('*',array('rule_id'=>$rule_id));

            $this->pagedata['gcrule'] = $gcrule[0];

        }
        $this->display('admin/member/add_giftcard_rule.html');
    }

    public function save(){
        $this->begin();
        $gcruleMdl = $this->app->model('member_giftcardrule');

        if($gcruleMdl->validate($_POST,$msg)){
            $_POST['gcard_money'] = floatval($_POST['gcard_money']);
           

            $gcruleMdl->save($_POST);

            $this->end(true,app::get('b2c')->_('保存成功'));
            
        }else{
            $this->end(false,$msg);
        }

    }

    //导出日志
    public function export_giftcardlog($log_id){
        
        $log_id = $log_id ? $log_id : $_GET['log_id'];
        if(intval($log_id)){          
            $model = app::get('b2c')->model('member_giftcardrule');
            $rulelogMdl = app::get('b2c')->model('member_giftcardlog');
            $c_token = $rulelogMdl->getList('c_token',array('log_id'=>$log_id));
            $c_token = $c_token[0];
            $oImportType = kernel::service('desktop_io');
            $oImportType->init($model);
            $offset = 0;
            $data = array('name'=> $model->fget_task_name);
            $_args = array('c_token'=>$c_token);
            $oImportType->export_header( $data,$model);
            $method_name = 'fgetlist_csv';
            while( $listFlag = $model->$method_name($data,$_args,$offset) ){
                $offset++;
                $oImportType->export( $data,$offset,$model );
            } 
        }
    }
}
?>