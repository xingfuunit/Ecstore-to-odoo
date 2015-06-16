<?php
/**
 * july by 2015-06-15
 */
include_once(ROOT_DIR.'/app/base/lib/static/utils2.php');


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
		 *		sid: 门店id，如果没有即读取默认值.
		 *	
		 *	返回json结构
		 *	array(
		 *		'act' =>1,			//1=没有变化，不用更新，0=已经变化了，请 reload,2=出错
		 *		'key' => '',		//md5 后的json数据
		 *		'data' =>	array(		//json数据
		 *			'type' 		=> $rw['pos_id'],
		 *			'img' 		=> $rw['ad_img'],
		 *			'vod' 		=> $rw['vodfile'],
		 *			'url' 		=> $rw['ad_url']
		 *		);
		 *	);
		 * */
		$key = utils2::CheckSql(''.$_GET['key']);

		$sid = utils2::CheckSql(''.$_GET['sid']);
		if(strlen($sid)>0){
			if(!utils2::IsRndKey($sid)){
				$sid = '';
			}
		}
		
		$uuid = utils2::CheckSql(''.$_GET['uuid']);

		//------------------------------------------------
		//如果存在key，即ajax 返回 json
		if(strlen($key)>0){
			if(strlen($uuid)>0 && strlen($sid)==0){
				$sid = $this->get_sid_device($uuid);
			}
				
			return $this->ajaxGetJson($key,$sid);
		}

		//-----------------------------------------------
		require_once(ROOT_DIR.'/custom/b2c/view/wap/touchscreen/index.html');
    }
	
	//向页面输出 json 内容
	function ajaxGetJson($key,$sid){

		//如果有指定门店，即返回门店数据，
		//如果没有返回默认值
		$rs = $this->get_sales_touchscreen($sid);
		if(!(isset($rs) && is_array($rs))){
			return $this->ajaxWriteErr('没有任何数据！请联系总部！');
		}

		//------------------------------------------------
		$arr = array();
		if(isset($rs) && is_array($rs)){
			foreach($rs as $rw){
				$arr[] = array(
					'type' 		=> $rw['pos_id'],
					'img' 		=> $rw['ad_img'],
					'vod' 		=> $rw['vodfile'],
					'url' 		=> $rw['ad_url']
				);
			}
		}
		
		//输出
		return $this->ajaxWriteJson($key,$arr);
	}
	
	//根据uuid，即设备id查找对应的门店设备
	function get_sid_device($uuid){
		$sql = "select branch_bn from sdb_mobileapi_sales_touchscreendevice where disabled='false' and device_name='".$uuid."'";
		$row = kernel::database()->selectRow( $sql );
		$sid = '';
		if(isset($row) && isset($row['branch_bn'])){
			$sid = $row['branch_bn'];
		}else{
            $sql = "insert into sdb_mobileapi_sales_touchscreendevice (device_name,branch_bn,branch_name) values ('".$uuid."','','')";
            kernel::database()->exec($sql);
		}
		return $sid;
	}
	
	//图片调用方式， 根据  门店编号 获得对应的 数据
	function get_sales_touchscreen($sid){
		
		$arr = $tmp = array();
		
		$sqlSelect = "select ad_id,pos_id,ad_img,ad_url,vodfile from sdb_mobileapi_sales_touchscreen where disabled='false' ";
		$sqlOrder = ' order by ordernum desc,ad_id desc';
		
		//存在门店编码，先检查是否存在门店自己的视频
		if(strlen($sid)>0){
			
			//3 = 触屏视频
			$sql = $sqlSelect . " and branch_bn='".$sid."' and pos_id=3 " . $sqlOrder;
			$tmp[3] = kernel::database()->selectRow( $sql );
			
			//不存在视频，读取轮播图片
			if((isset($tmp[3]) && is_array($tmp[3]) && count($tmp[3])>0)==false){
				unset($tmp[3]);
				//-----------------------------------------
				//1 = 触屏顶部-轮换图 1080x1280
				$sql = $sqlSelect . " and branch_bn='".$sid."' and pos_id=1 " .  $sqlOrder .' limit 10';
				$tmp[1] = kernel::database()->select( $sql );
				if((isset($tmp[1]) && is_array($tmp[1]) && count($tmp[1])>0)==false){
					unset($tmp[1]);
				}
			}
			
			//-----------------------------------------
			//2 = 触屏底部-固定图 1080x640
			$sql = $sqlSelect . " and branch_bn='".$sid."' and pos_id=2 " . $sqlOrder;
			$tmp[2] = kernel::database()->selectRow( $sql );
			if((isset($tmp[2]) && is_array($tmp[2]) && count($tmp[2])>0)==false){
				unset($tmp[2]);
			}
			
			//-----------------------------------------
			//4 = 触屏视频背景 1080x1280
			$sql = $sqlSelect . " and branch_bn='".$sid."' and pos_id=4 " . $sqlOrder;
			$tmp[4] = kernel::database()->selectRow( $sql );
			if((isset($tmp[4]) && is_array($tmp[4]) && count($tmp[4])>0)==false){
				unset($tmp[4]);
			}
		}
		
		//-----------------------------------------
		//不存在定制，读取默认值视频
		if(!isset($tmp[2])){
			$sql = $sqlSelect . " and branch_bn='' and pos_id=2 " . $sqlOrder;
			$tmp[2] = kernel::database()->selectRow( $sql );
			if((isset($tmp[2]) && is_array($tmp[2]) && count($tmp[2])>0)==false){
				unset($tmp[2]);
			}
		}
		
		//不存在定制，读取默认值视频
		if(!isset($tmp[3]) && !isset($tmp[1])){
			$sql = $sqlSelect . " and branch_bn='' and pos_id=3 " . $sqlOrder;
			$tmp[3] = kernel::database()->selectRow( $sql );
			
			//不存在默认值视频，读取默认值轮播图片
			if((isset($tmp[3]) && is_array($tmp[3]) && count($tmp[3])>0)==false){
				unset($tmp[3]); 
				
				//-----------------------------------------
				//1 = 触屏顶部-轮换图 1080x1280
				$sql = $sqlSelect . " and branch_bn='' and pos_id=1 " .  $sqlOrder .' limit 10';
				$tmp[1] = kernel::database()->select( $sql );
				if((isset($tmp[1]) && is_array($tmp[1]) && count($tmp[1])>0)==false){
					unset($tmp[1]);
				}
			}
		}
		
		//-----------------------------------------
		//不存在定制，读取默认值视频背景
		if(!isset($tmp[4])){
			$sql = $sqlSelect . " and branch_bn='' and pos_id=4 " . $sqlOrder;
			$tmp[4] = kernel::database()->selectRow( $sql );

			if((isset($tmp[4]) && is_array($tmp[4]) && count($tmp[4])>0)==false){
				unset($tmp[4]);
			}
		}
		
		//-----------------------------------------
		//1 = 触屏顶部-轮换图 1080x1280
		if(isset($tmp[1]) && is_array($tmp[1]) && count($tmp[1])>0){
			foreach($tmp[1] as $k => $v){
				$arr[] = $v;
			}
		}
		//-----------------------------------------
		//2 = 触屏底部-固定图 1080x640
		if(isset($tmp[2]) && is_array($tmp[2]) && count($tmp[2])>0){
			$arr[] = $tmp[2];
		}
		
		//-----------------------------------------
		//3 = 触屏视频
		if(isset($tmp[3]) && is_array($tmp[3]) && count($tmp[3])>0){
			$arr[] = $tmp[3];
		}
		
		//-----------------------------------------
		//4 = 触屏视频背景 1080x1280
		if(isset($tmp[3]) && isset($tmp[4]) && is_array($tmp[4]) && count($tmp[4])>0){
			$arr[] = $tmp[4];
		}

		//-----------------------------------------
		if(isset($arr) && is_array($arr)){
			foreach($arr as $k => $v){
				if(strlen($v['ad_img'])>5){
					$arr[$k]['ad_img'] = base_storager::image_path($v['ad_img']);
				}
			}
			return $arr;
		}
		
		return false;
	}
	
	//向页面输出 json 内容
	function ajaxWriteErr($msg){
		$ret = array(
			'act' =>2,
			'key' => '',
			'data' => $msg
		);
		
		$json = json_encode($ret);
		$this->_response->set_body($json);
		return;
	}
	
	//向页面输出 json 内容
	function ajaxWriteJson($key,$arr){
			
		$newkey = md5(json_encode($arr));
		//-----------------------------------------------
		//如果ajax传过来的参数 key 和现在的  md5($json) 相同，即广告没有变化，不用 reload
		$ret = array(
			'act' =>1,
			'key' => '',
			'data' => ''
		);
		
		if($key != $newkey ){
			$ret = array(
				'act' =>0,
				'key' => $newkey,
				'data' => $arr
			);
		}
		
		$json = json_encode($ret);
		$this->_response->set_body($json);
		return;
	}
}