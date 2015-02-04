<?php
class b2c_ctl_admin_weixin_lottery extends desktop_controller{
	 //微信抽奖主页
     function index(){
        $this->finder('wap_mdl_lottery_log', array(
                'title'=>app::get('b2c')->_('微信抽奖'),
            	'use_buildin_recycle'=>false,
                ));
    }
    
	
}