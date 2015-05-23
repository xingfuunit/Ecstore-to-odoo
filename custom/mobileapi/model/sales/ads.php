<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class mobileapi_mdl_sales_ads extends dbeav_model{
	

	function __construct($app) {
		parent::__construct($app);
	}
	
	var $defaultOrder = array('ad_id',' DESC');
	
	var $ads_position = array(
			/*1 => array(
					'id' => '1',
					'name' => '促销页面',
					'key' => 'promo_page'
			),
			2 => array(
					'id' => '2',
					'name' => '商品列表页面',
					'key' => 'goods_page',
			),*/
			3 => array(
					'id' => '3',
					'name' => '首页-顶部滚动广告 1024x640',
					'key' => 'index_roll_banner',
			),
			4 => array(
					'id' => '4',
					'name' => '首页-促优惠券广告 160x160',
					'key' => 'index_coup_banner',
			),
			5 => array(
					'id' => '5',
					'name' => '首页-促销图片广告 640x260',
					'key' => 'index_pic_banner',
			),
			6 => array(
					'id' => '6',
					'name' => '首页-大图送免邮券 640x260',
					'key' => 'index_coup_mianyou',
			)
	);
	
	
	function get_sales_ads_position_list(){
		return $this->ads_position;
	}
	
	
	//图片调用方式， 根据  $ads_position key 获得对应的 图片数据
	function get_sales_ads($key){
		$ads_id = '';
		foreach($this->ads_position as $k => $v){
			if($v['key'] == $key){
				$ads_id =$k;
			}
		}
		
		if($ads_id){
			$re = $this->getList('*',array('ad_position'=>$ads_id,'disabled'=>'false'),0,-1," ordernum ASC");
			if($re){
				foreach($re as $k => $v){
					$re[$k]['img_url'] = base_storager::image_path($v['ad_img']);
				}
			}
			return $re;
		}
		return  false;
	}
	
}
