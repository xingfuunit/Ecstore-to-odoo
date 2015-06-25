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
	
	
	var $defaultOrder = array('z_count','DESC');
	
	//集赞获奖数
	public $_zan_success_num = 30;
	
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
	
	//重写getlist
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols, $filter, $offset, $limit, $orderType);

        if($arr_list){
			foreach ($arr_list as $key=>$arr)
			{
				$arr_list[$key]['area_id'] = $this->_area_list[$arr['area_id']];
				$arr_list[$key]['gift_id'] = $this->_gift_list[$arr['gift_id']];
			}
        }
		return $arr_list;
	}
	 
}