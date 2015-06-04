<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class mobileapi_mdl_sales_touchscreen extends dbeav_model{
	

	function __construct($app) {
		parent::__construct($app);
	}
	
	var $defaultOrder = array('ad_id',' DESC');
	
	var $touchscreen_position = array(
			1 => array(
					'id' => '1',
					'name' => '电视触屏-大图 1920x1080',
					'key' => 'touchscreen_banner',
			)
	);
	
	
	function get_sales_touchscreen_position_list(){
		return $this->touchscreen_position;
	}
	
	
	//图片调用方式， 根据  $ads_position key 获得对应的 图片数据
	function get_sales_touchscreen($key){
		$ads_id = '';
		foreach($this->touchscreen_position as $k => $v){
			if($v['key'] == $key){
				$ads_id =$k;
			}
		}
		
		if($ads_id){
			$re = $this->getList('*',array('touchscreen_position'=>$ads_id,'disabled'=>'false'),0,-1," ordernum ASC");
			if($re){
				foreach($re as $k => $v){
					$re[$k]['img_url'] = base_storager::image_path($v['ad_img']);
				}
			}
			return $re;
		}
		return  false;
	}
	
    function delete($filter, $subSdf = 'delete')
    {
		//------------------------------------
		//同步删除视频文件
        $obj = $this->app->model('sales_touchscreen');
        $rs  = $obj->dump($filter['ad_id']);
		if(isset($rs) && is_array($rs)){
			$vodfile = $rs['vodfile'];
			if(strlen($vodfile)>5){
				@unlink(ROOT_DIR .$vodfile);
			}
		}
		//------------------------------------
        return parent::delete($filter);
    }
	
}
