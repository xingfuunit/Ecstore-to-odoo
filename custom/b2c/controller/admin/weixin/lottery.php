<?php
class b2c_ctl_admin_weixin_lottery extends desktop_controller{
	
	public function __construct($app) {
		parent::__construct($app);
	}
	
	 //微信抽奖主页
     function index(){
        $this->finder('wap_mdl_lottery_log', array(
                'title'=>app::get('b2c')->_('微信抽奖'),
            	'use_buildin_recycle'=>false,
                ));
    }
    
    /**
     * 龙虾抽奖列表
     */
    function lobster_list(){
    	
    	$obster_model = app::get('wap')->model('lobster_member');
    	$obster_zlist_model = app::get('wap')->model('lobster_zlist');
    	
    	//参与者总数
    	$join_count = $obster_model->count();
    	//获奖者数
    	$join_win = $obster_model->count(array('z_count|than'=>$obster_model->_zan_success_num-1));
    	//赞总数量
    	$zan_count = $obster_zlist_model->count();
    	
    	$custom_actions[] =  array('label'=>app::get('b2c')->_('活动参加人数:'.$join_count),'javascript:void(0);','');
    	$custom_actions[] =  array('label'=>app::get('b2c')->_('获奖人数:'.$join_win),'javascript:void(0);','');
    	$custom_actions[] =  array('label'=>app::get('b2c')->_('赞总人数:'.$zan_count),'javascript:void(0);','');
    	
    	//礼品统计数量
    	$gift_count = array();
    	foreach($obster_model->_gift_list as $k=>$v){
    		$gift_count[$k] = $obster_model->count(array('gift_id'=>$k,'z_count|than'=>$obster_model->_zan_success_num-1));
    		$custom_actions[] =  array('label'=>app::get('b2c')->_('获得['.$v.']人数:'.$gift_count[$k]),'javascript:void(0);','');
    	}
    	
    	$this->finder('wap_mdl_lobster_member', array(
    			'title'=>app::get('b2c')->_('微信抽奖'),
    			'use_buildin_recycle'=>false,
    			'actions'=>$custom_actions,
    	));
    }
    
	
}