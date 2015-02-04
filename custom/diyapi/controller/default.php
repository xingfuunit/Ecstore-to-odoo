<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class diyapi_ctl_default extends base_controller{
    
    function index(){
        $this->pagedata['project_name'] = 'API 测试工具';
        
        $this->pagedata['token'] = base_certificate::token();
        #$this->pagedata['api_url'] = '/ec_app/index.php/api';
        $api_url    = preg_replace('/\/diyapi(\/?)$/', '/api',$_SERVER['REQUEST_URI']);
        $this->pagedata['api_url'] = $api_url;
        $this->pagedata['base_url_full'] = kernel::base_url('full');
        $this->pagedata['new_api_url'] = app::get('site')->router()->gen_url(array('app'=>'webtool','ctl'=>'site_index', 'act'=>'index'));

        $this->display('default.html');
    }
    
    //mobileapi test
    public function m_test(){
		header("Content-type: text/html; charset=utf-8"); 
		
		//商品评论
		/*
		$page = 1;
		$limit = 10;
		$gid = 1;
		
		$objdisask = kernel::single('b2c_message_disask');
		$aComment = $objdisask->good_all_disask($gid,'discuss',$page,null,$limit);
		
		$objPoint = kernel::single('b2c_mdl_comment_goods_point');
        $aComment['goods_point'] = $objPoint->get_single_point($gid);
        $aComment['total_point_nums'] = $objPoint->get_point_nums($gid);
        $aComment['_all_point'] = $objPoint->get_goods_point($gid);    	
    	
    	print_r($aComment);exit;
    	*/
    	//end 商品评论
    	
		//商品评论
		$gid = 1;
		
    	$objGoods = kernel::single("b2c_mdl_goods");
		$objProduct =  kernel::single("b2c_mdl_products");
		
		$aLinkId['goods_id'] = array();
		foreach($objGoods->getLinkList($gid) as $rows){
			if($rows['goods_1']==$gid){
				$aLinkId['goods_id'][] = $rows['goods_2'];
			}else {
				$aLinkId['goods_id'][] = $rows['goods_1'];
			}
		}
		if(count($aLinkId['goods_id'])>0){
			$aLinkId['marketable'] = 'true';
			$goodslink = $objGoods->getList('name,price,goods_id,image_default_id,marketable',$aLinkId,0,500);
			foreach ($goodslink as $k => $v){
				$goodslink[$k]['image_default'] = kernel::single('base_storager')->image_path($v['image_default_id'], 'm');
			}
		}
		print_r($goodslink);exit;
		//end 

    }
    
    
    function test_api(){
    	$str = "不是21-P,sdf";
    	$pmatch = preg_match("/.*?(\d+)P.*/i",$str,$matches);
    	
    	echo $pmatch;
    	print_r($matches);exit;
    	
    	
    	$certi = base_certificate::get('certificate_id');
    	 
    	$app_id = 'b2c';
    	$_node_token = base_shopnode::get('token',$app_id);
    	
    	$token = base_certificate::token();
    	 
    	/*
    	$params=array(
    			'V'=>1.0,
    			'Method'=>'b2c.order.detail',
    			'app_id'=>$app_id,
    			'Date'=>'2014-02-26 17:16:30',
    			'certi_id'=>$app_id,
    			'node_id'=>$node_id,
    	);
    	*/
    	
    	$headers = array(
    			'Connection'=>'Close',
    	);

    	$query_params=array(
    			'method'=>'diyapi.hello.get_hello',
    			'date'=>date('Y-m-d H:m:s',time()),
    			'direct' => 'true'
    	);
    	
    	$query_params['sign'] = $sign = $this->get_sign($query_params,$token);
    	
    	$core_http = kernel::single('base_httpclient');
    	$response = $core_http->set_timeout(6)->post('http://vip.hcyy.cn/index.php/api',$query_params,$headers);
    	
    	//$result = json_decode($response,true);
    	
    	print_r($response);
    	echo "==============custom====== 00 999 ";
    	print_r($query_params);
    	echo "certi = ". $certi.' ####### token='.$token.'++++++'.$sign.'====== app_node_token='.$_node_token;
    	exit;
    	
    	echo kernel::single('system_request')->gen_sign($params,$token);
    	
    	$this->pagedata['project_name'] = $params;
    	
    	$this->display('default_api.html');
    }
    
    
    function order_api_test() {
    	
    	$certi = "1631992830";
    	$token = 'a4997c88b7f32fe293ad839a59c405037126673482b8c702b8f6b7424a099f80';
    	$api_url = "http://www.a02toby.com/index.php/api";
    	
    	/*
    	$certi = "1470976631";
    	$token = 'dbfdd1d813d20b8116f79f48ffd1d774f677419391082cfab134036c1f2e326c';
    	 */
    	$headers = array(
    			'Connection'=>'Close',
    	);

    	$query_params=array(
    			'method'=>'b2c.order.search',
    			'date'=>date('Y-m-d H:m:s',time()),
    			'direct' => 'true',
    			'task' => date('Y-m-d H:m:s',time()),
    			'start_time' => "2014-03-01",
    			'end_time' => "2014-05-16",
    			'page_no' => 1,
    			'page_size' => 20,
    	);
    	
    	$query_params['sign'] = $sign = $this->get_sign($query_params,$token);
    	
    	$core_http = kernel::single('base_httpclient');
    	$response = $core_http->set_timeout(6)->post($api_url,$query_params,$headers);
    	
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    	echo "\r\n";
    	
    	echo "api_url = $api_url \r\n";
    	echo "token = $token \r\n";
    	echo "certi = $certi \r\n";
    	echo "method = b2c.order.search \r\n";
    	echo "返回数据 = $response  \r\n";
    	
    	exit;
    	
    }
    
    function get_sign($params,$token){
    	return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }
    function assemble($params)
    {
    	if(!is_array($params)) return null;
    	ksort($params,SORT_STRING);
    	$sign = '';
    	foreach($params AS $key=>$val){
    		$sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
    	}
    	return $sign;
    }
    
    /**
     * 又一城接口测试
     */
    public function u1_api_test() {
    	
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    	
    	$api_url = 'http://wqbopenapi.ushopn6.com/wqbnew/api.rest';
    	$sKey = "U1CITYFXSTEST";//第三方Key
    	$sSecret = "AABCNCDBCJIDODHDUER";//第三方Secret
    	$sMethod = "IOpenAPI.GetProClass";//接口名称
    	$sFormat = "json";//返回方式[xml 或 json]
    	//--------------------------------
    	
    	$str = strtolower($sSecret.$sMethod."appKey".$sKey.'format'.$sFormat);
    	$str = $this->u1Asc($str); 
    	
    	//$str = iconv("utf-8","gb2312//IGNORE",$str);
    	$sToken = md5($str);//参数yToken
    	$postData = "user=$sKey&method=$sMethod&token=$sToken&format=$sFormat&appKey=$sKey"; // 要发放的数据
    	
    	$headers = array(
    			'Connection'=>'Close',
    			'api-version'=>'2.0',
    	);
    	
    	$query_params=array(
    			'user'=>$sKey,
    			'method'=>$sMethod,
    			'token' =>$sToken,
    			'appKey'=>$sKey,
    			'format'=>$sFormat,
    			
    	);
    	
    	$postData = "";
    	foreach ($query_params as $k => $v){
    		$postData .= $k.'='.$v.'&';
    	}
    	
    	$this->pagedata['postData'] = substr($postData, 0,-1);
    	
    	
    	//print_r($query_params);

    	$core_http = kernel::single('base_httpclient');
    	$response = $core_http->set_timeout(3000)->post($api_url,$query_params,$headers);
    	print_r($response);
    	
    	$this->pagedata['api_url'] = $api_url;
    	$this->pagedata['query_params'] = $query_params;
    	
    	$this->display('u1_api_test.html');
    }
    
    private function u1Asc($input_string){
    	$input_array = str_split($input_string);
    	sort($input_array);
    	return implode("", $input_array);
    }
    
}
