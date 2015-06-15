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
			'name' => '触屏顶部-轮换图 1080x1280',
			'key' => 'banner',
			'width' => 1080,
			'height'=> 1280,
			'type' => 'pic'
		),
		2 => array(
			'id' => '2',
			'name' => '触屏底部-固定图 1080x640',
			'key' => 'footer',
			'width' => 1080,
			'height'=> 640,
			'type' => 'pic'
		),
		3 => array(
			'id' => '3',
			'name' => '触屏视频',
			'key' => 'vod',
			'width' => 0,
			'height'=> 0,
			'type' => 'vod'
		),
		4 => array(
			'id' => '4',
			'name' => '触屏视频背景 1080x1280',
			'key' => 'bg',
			'width' => 1080,
			'height'=> 1280,
			'type' => 'bg'
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
			$re = $this->getList('*',array('touchscreen_position'=>$ads_id,'disabled'=>'false'),0,-1," ordernum desc");
			if($re){
				foreach($re as $k => $v){
					$re[$k]['img_url'] = base_storager::image_path($v['ad_img']);
				}
			}
			return $re;
		}
		return  false;
	}
	
    function delete($filter, $subSdf = 'delete'){
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
	
	 public function modifier_disabled($row){
        if ($row == 'true'){
            return "<span style='color:red'>是</span>";
        }else{
            return '否';
        }
    }
	
	
	//根据 $_SESSION['account'] 查询账号是否为门店账号，如果是，就返回 branch_bn
	//july
	public function get_branch_bn(){
		$bn = '';
		if(isset($_SESSION['account']) && is_array($_SESSION['account'])){
			if(isset($_SESSION['account']['user_data']['branch_bn'])){
				$bn = $_SESSION['account']['user_data']['branch_bn'];
			}
		}
		return $bn;
	}
	
}
