<?php

class b2c_ctl_admin_return_goods extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	 function index(){
            $this->finder('b2c_mdl_return_goods',array(
            'title'=>app::get('b2c')->_('退货列表'),
            'use_buildin_export'=>true,
            'orderBy'=>'create_time desc',
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'base_filter'=>array('type'=>1), //对tab数据进行过滤筛选
                ));
        }
        
        function breakage() {
            $this->finder('b2c_mdl_return_goods',array(
            'title'=>app::get('b2c')->_('报损列表'),
            'use_buildin_export'=>true,
            'orderBy'=>'create_time desc',
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'base_filter'=>array('type'=>2), //对tab数据进行过滤筛选
            
            ));
	}
        
        function goods_sn() {
            $this->finder('b2c_mdl_return_order_items',array(
            'title'=>app::get('b2c')->_('报损/退货商品列表'),
            'use_buildin_export'=>true,
            'orderBy'=>'item_id desc',
            'allow_detail_popup'=>true,
            'use_buildin_filter'=>true,
            'use_buildin_recycle'=>true,
            'use_view_tab'=>true,
            ));
	}
        /*public function _views(){
            $o = $this->app->model('return_goods');
            $pc_filter = array('type'=>1);
            $mobile_filter = array('type'=>2);

            $pc_num = count($o->getList('*',$pc_filter));
            $mobile_num = count($o->getList('*',$mobile_filter));
            $show_menu = array(
                1=>array('label'=>app::get('b2c')->_('退货'),'optional'=>false,'addon'=>$pc_num,     'filter'=>$pc_filter),
                2=>array('label'=>app::get('b2c')->_('报损'),'optional'=>false,'addon'=>$mobile_num, 'filter'=>$mobile_filter)
            );
            return $show_menu;
        }
        */
}