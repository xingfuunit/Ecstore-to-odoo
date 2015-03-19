<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * @package weixin
 * @subpackage dbeav_model
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */
class weixin_mdl_qrcode extends dbeav_model
{
	//页面，客服，店员，活动 分组
	var $_group_list = array(
				'1'=> '页面',
				'2'=> '客服',
				'3'=> '店员',
				'4'=> '员工',
				'5'=> '活动',
			);
	
	/**
	 * 返回组数据
	 * @param unknown_type $group_id
	 * @return multitype:string
	 */
	public function getGroup($group_id){
		if($group_id){
			$re = '';
			foreach($this->_group_list as $k => $v){
				if($k == $group_id){
					$re =  $this->_group_list[$k];					
				}
			}
			return $re;
		}
		return $this->_group_list;
		
	}
	
    /**
     * 重写getList方法
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        
        if($arr_list){
        	foreach($arr_list as $k => &$v){
        		$v['group_id'] = $v['code_group'];
        		$v['code_group'] = $this->_group_list[$v['code_group']];
        	}
        }
        
        return $arr_list;
    }
    
    /**
     *  扫描数加  , 默认加1
     */
    public function addScanCount($code_id,$num = 1){
    	$code_info = $this->getRow('*',array('code_id'=> $code_id));
    	if($code_info){
    		$data = array('scan_count'=>$code_info['scan_count']+$num);
    		return $this->update($data,array('code_id'=>$code_id));
    	}
    	
    }
    
    /**
     * 增加关注数，默认1
     * @param unknown_type $code_id
     * @param unknown_type $num
     */
    public function addfollowCount($code_id,$num = 1){
    	$code_info = $this->getRow('*',array('code_id'=> $code_id));
    	if($code_info){
	    	$data = array('follow_count'=>$code_info['follow_count']+$num);
	    	return $this->update($data,array('code_id'=>$code_id));
    	}
    }
    
}//End Class
