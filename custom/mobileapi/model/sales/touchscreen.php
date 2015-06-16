<?php
/**
 * july by 2015-06-15
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
	
	
    function delete($filter, $subSdf = 'delete'){

		$bn = $this->get_branch_bn();
		
		//------------------------------------
		//同步删除视频文件
        $obj = $this->app->model('sales_touchscreen');
        $rs  = $obj->dump($filter['ad_id']);
		if(isset($rs) && is_array($rs)){
				
			//------------------------------------------------------
			//检查权限
			//如果当前登陆用户，是门店账号，但删除的资料不是该门店，
			//即没有权限，退出。
			if(strlen($bn)>0){
				if($bn != $rs['branch_bn']){
					return false;
				}
			}
			//------------------------------------------------------
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

	//增加门店权限的过滤
	//如果当前后台操作员账号是门店账号，并指定了相关门店，就只能查看它自己的门店的资料
	//july by 2015-06-15
    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if(!$filter['branch_bn']){
			$bn = $this->get_branch_bn();
			if(strlen($bn)>0){
				$filter['branch_bn'] = $bn;	
			}
        }

        $filter = parent::_filter($filter);
        return $filter;
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
