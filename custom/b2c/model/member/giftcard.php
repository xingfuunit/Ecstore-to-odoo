<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_mdl_member_giftcard extends dbeav_model{
    var $defaultOrder = array('gcard_id','DESC'); //列表默认排序
    function save($aData){
    	parent::save($aData);
    }

    //重写列表显示名字
    public function modifier_is_overdue($row){
    	if($row === 'true'){
    		return app::get('b2c')->_('已过期');
    	}else{
    		return app::get('b2c')->_('未过期');
    	}

    }

    public function modifier_used_status($row){
    	if($row === 'true'){
    		return app::get('b2c')->_('已使用');
    	}else{
    		return app::get('b2c')->_('未使用');
    	}

    }

    
}
?>
