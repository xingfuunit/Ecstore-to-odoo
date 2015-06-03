<?php
class b2c_ctl_wap_touchscreen extends wap_frontpage{

    var $noCache = true;
    var $show_gotocart_button = true;

    public function __construct(&$app) {
        parent::__construct($app);
    }

    public function index(){
		/*
		 * july  2015-06-01
		 *
		 * 请求参数：
		 * 		key：get请求，如果有值，即 sales_touchscreen 转 json后的md5值，
		 * 			key 的状态说明：
		 * 	 			key == '' ，即 非ajax ，直接输出页面html
		 *				key == 'json',	即 ajax请求 返回json数据
		 *				key == (32位md5) , 即 ajax请求 先判断 md5 key 是否改变，如果改变，返回新的json，如果没有改变，返回 1
		 *	
		 *	返回json结构
		 *	array(
		 *		'act' =>1,			//1=没有变化，不用更新，0=已经变化了，请 reload
		 *		'key' => '',		//md5 后的json数据
		 *		'data' =>			//json数据
		 *	);
		 * */
		$active_key = ''.$this->_request->get_get('key');
		
		if(strlen($active_key)>0){
			$touchscreen_model = app::get('mobileapi')->model('sales_touchscreen');

			$rs = $touchscreen_model->get_sales_touchscreen('touchscreen_banner');
			$arr = array();
			
			if(isset($rs) && is_array($rs)){
				foreach($rs as $rw){
					//如果存在视频，即只放视频，
					//如果不存在视频，就放全部图片
					if($rw['url_type'] == 'video' && strlen(''.$rw['vodfile'])>5){
						$arr = array();	
						$arr[] = array(
							'name' 		=> $rw['ad_name'],
							'type' 		=> $rw['url_type'],
							'img' 		=> $rw['img_url'],
							'vod' 		=> $rw['vodfile'],
							'url' 		=> $rw['ad_url'],
							'width' 	=> $rw['ad_img_w'],
							'height' 	=> $rw['ad_img_h']
						);
						break;
					}
					$arr[] = array(
						'name' 		=> $rw['ad_name'],
						'type' 		=> $rw['url_type'],
						'img' 		=> $rw['img_url'],
						'vod' 		=> $rw['vodfile'],
						'url' 		=> $rw['ad_url'],
						'width' 	=> $rw['ad_img_w'],
						'height' 	=> $rw['ad_img_h']
					);
				}
			}
			$key  = md5(json_encode($arr));
			
			//-----------------------------------------------
			//如果ajax传过来的参数 key 和现在的  md5($json) 相同，即广告没有变化，不用 reload
			$ret = array(
				'act' =>1,
				'key' => '',
				'data' => ''
			);
			
			if($active_key != $key ){
				$ret = array(
					'act' =>0,
					'key' => $key,
					'data' => $arr
				);
			}
			
			$json = json_encode($ret);
			$this->_response->set_body($json);
			return;
		}
		//-----------------------------------------------
		require_once(ROOT_DIR.'/custom/b2c/view/wap/touchscreen/index.html');
        //$this->page('wap/touchscreen/index.html');
    }
}