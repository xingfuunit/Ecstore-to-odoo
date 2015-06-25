<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_mdl_member_withdrawal extends dbeav_model{
	
	function __construct($app){
		parent::__construct($app);
	}
	
    /**
     * getListByMemId 取得现有预存款充值记录
     *
     * @param mixed $member_id
     * @access public
     * @return void
     */
    function get_list_bymemId($member_id){
        return $this->getList('*',array('member_id'=>$member_id));
    }
}
?>
