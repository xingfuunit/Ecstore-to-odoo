<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class weixin_mdl_qrcode_log extends dbeav_model
{
	var $_qrcode_log_type = array(
				'follow'=> 1,    //关注
				'scan'=> 2,		//扫描
			);
	
	public function get_type($type){
		return $this->_qrcode_log_type[$type];
	}
    
	/**
	 *  添加
	 * @param unknown_type $log_type
	 * @param unknown_type $qrcode_id
	 * @param unknown_type $tousername
	 * @param unknown_type $from_openid
	 * @param unknown_type $createtime
	 */
	public function add_qrcode_log($log_type,$qrcode_id,$tousername,$from_openid,$createtime){
		$data = array(
				'qrcode_id'		=>$qrcode_id,
				'tousername'	=>$tousername,
				'from_openid'	=>$from_openid,
				'createtime'	=>$createtime	
			);
		
		switch ($log_type)
		{
			case 'follow':
				//已关注过的，不再计数
				$is_add = $this->count(array('tousername'=>$tousername,'from_openid'=>$from_openid,'log_type'=>$this->get_type('follow')));
				if($is_add<1){
					$data['log_type'] = $this->get_type($log_type);
					return  $this->insert($data);
				}
				break;
			case 'scan':
				$data['log_type'] = $this->get_type($log_type);
				return $this->insert($data);
				break;
			default:
				break;
		}
		
		return false;
		
	}
	
	
}//End Class
