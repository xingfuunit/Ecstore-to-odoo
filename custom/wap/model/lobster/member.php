<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 *	龙虾集赞活动
 */

class wap_mdl_lobster_member extends dbeav_model{
	
	//礼品数据
	public $_gift_list = array(
			'1' => '龙虾券',
			'2' => '满200减80优惠券',
			'3'	=> '奇异果券',
			'4' => '20元话费',
	);
	
	//地区数据
	public $_area_list = array(
			'1'=> '广东',
			'2'=> '其他地区',
	);
	 
}