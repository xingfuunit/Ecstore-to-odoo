<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * mdl_cart 购物车model
 * $ 2010-04-28 20:03 $
 */

class b2c_mdl_member_coupon_sendlog extends dbeav_model{

	function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
		$orderType = empty($orderType) ? 'sendlog_id desc' : $orderType;
		return parent::getList($cols, $filter, $offset, $limit, $orderType);
	}
}
