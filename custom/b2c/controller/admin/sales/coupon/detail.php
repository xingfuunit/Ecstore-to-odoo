<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_sales_coupon_detail extends desktop_controller{

    function index(){
		kernel::single('b2c_member_coupon_detail')->set_params($_POST)->display();
		
		/* $this->finder('b2c_mdl_member_coupon_detail',array(
            'title'=>app::get('b2c')->_('查询优惠券，用户所有的优惠券'),
            'use_buildin_recycle'=>false,
            )); */
    }
    
	
	
}
