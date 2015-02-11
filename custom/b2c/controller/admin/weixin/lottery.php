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
    	$obster_list = $obster_model->getList("*",array(),0,-1,'z_count DESC');
    	
    	if($obster_list){
    		foreach($obster_list as $k => $v){
    			$obster_list[$k]['area'] = $obster_model->_area_list[$v['area_id']];
    			$obster_list[$k]['gift'] = $obster_model->_gift_list[$v['gift_id']];
    		}
    	}
    	
    	//参与者总数
    	$join_count = $obster_model->count();
    	$this->pagedata['join_count'] = $join_count;

    	//获奖者数
    	$join_win = $obster_model->count(array('z_count|than'=>$obster_model->_zan_success_num-1));
    	$this->pagedata['join_win'] = $join_win;
    	
    	//赞总数量
    	$zan_count = $obster_zlist_model->count();
    	$this->pagedata['zan_count'] = $zan_count;

    	$gift_count = array();
    	//礼品统计数量
    	foreach($obster_model->_gift_list as $k=>$v){
    		$gift_count[$k] = $obster_model->count(array('gift_id'=>$k,'z_count|than'=>$obster_model->_zan_success_num-1));
    	}
    	$this->pagedata['gift_count'] = $gift_count;
    	$this->pagedata['gift_list'] = $obster_model->_gift_list;

    	$this->pagedata['obster_list'] = $obster_list;
    	$this->page('admin/weixin/lobster_list.html');
    }
    
	
}