<?php
/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_cart  extends mobileapi_frontpage {
	
	/**
	 * 
	 * @var base_rpc_service
	 */
	var $rpcService;
	
	/**
	 */
	function __construct($app)
	{
		parent::__construct($app);
		
		$this->app = app::get('b2c');
		//$this->verify_member();
		
		$this->rpcService = kernel::single('base_rpc_service');
		
		$this->mCart = kernel::single('b2c_mdl_cart');
		$this->objMath = kernel::single("ectools_math");
		
		$this->_request = kernel::single('base_component_request');
		$this->_response = kernel::single('base_component_response');
	}

    public function batch_add_cart($params = array(), $rpcService) {
        $goodsObj = json_decode($params['goods']);
        if(!is_array($goodsObj)) return;
        $_REQUEST = null;
        foreach ($goodsObj as $key => $value) {
            $_REQUEST['product_id'] = $value->product_id;
            $_REQUEST['num'] = $value->num;
            if($value->buy_code) $_REQUEST['buy_code'] = $value->buy_code;
            if($value->btype) $_REQUEST['btype'] = $value->btype;
            $this->add();
            $_REQUEST = null;
        }

        return '添加成功';
    }

    public function add(){
    	
    	$product_id = intval($_REQUEST['product_id']);
    	$num = isset($_REQUEST['num']) && intval($_REQUEST['num'])?intval($_REQUEST['num']):1;
    	$buy_code = trim($_REQUEST['buy_code']);
    	
    	$db = kernel::database();
    	$product_info = $db->selectrow("SELECT goods_id,product_id, store,freez FROM `sdb_b2c_products` where product_id = '$product_id' ");
    	/*
    	if(!$product_info){
    		kernel::single('base_rpc_service')->send_user_error('need_login', '产品ID错误!');
    	}
    	*/
    	
    	$goods['goods_id'] = $product_info['goods_id'];
    	$goods['product_id'] = $product_id;
    	$goods['num'] = $num;
    	
    	//库存检测
    	if($num > $product_info['store'] - $product_info['freez']){
    		kernel::single('base_rpc_service')->send_user_error('store_error', '产品库存不够本次购买');
    	}
    	
    	if($buy_code)$goods['extends_params'] = array('buy_code'=>"$buy_code");
    	
    	$post_data['response_json'] = 'true';
    	$post_data['0'] = 'goods';
    	$post_data['goods'] = $goods;
    	//$post_data['goods'] = $goods;
    	$post_data['btype'] = isset($_REQUEST['btype']) ?trim($_REQUEST['btype']):'';
    	
    	
    	//组合促销期间不能加入购物车，只能直接购买
    	if($post_data['btype'] != 'is_fastbuy'){
    		$starbuy_products_object = @kernel::single("starbuy_special_products");
    		if($starbuy_products_object->ifSpecial($product_id) === 'starbuy'){
    			kernel::single('base_rpc_service')->send_user_error('cart_add_error', '该产品正在做组合促销，请直接购买!');
    		}
    	}
    	
    	 
    	$re=$this->api_add_cart('goods',$post_data);
    	
    	if($re['code'] == 'ok'){
    		return $re['info'];
    	}
    	
    	kernel::single('base_rpc_service')->send_user_error('cart_add_error', $re['info']);    	
    	
    }


    public function batch_remove($params = array(), $rpcService)
    {
        $obj = json_decode($params['items']);
        $mCartObject = app::get('b2c')->model('cart_objects');

        if(!is_array($obj)) return;
        foreach ($obj as $value) {
            if($value->obj_type == 'coupon'){
                $flag = $mCartObject->remove_object('coupon', $value->obj_ident, $msg);
            }elseif($value->obj_type == 'all'){
                $flag = $mCartObject->remove_object('', null, $msg);
            }else{
                $flag = $mCartObject->remove_object($value->obj_type, $value->obj_ident, $msg);
            }
        }

        return '删除成功';
    }
    
    
    private function api_add_cart($type='goods',$data){
    	$arr_objects = array();
    	if ($objs = kernel::servicelist('b2c_cart_object_apps'))
    	{
    		foreach ($objs as $obj)
    		{
    			if ($obj->need_validate_store()){
    				$arr_objects[$obj->get_type()] = $obj;
    			}
    		}
    	}
    	
    	
    	 
    	$errorRequest = true;
    	
    	if (method_exists($arr_objects[$type], 'get_data'))
    	{
    		if (!$aData = $arr_objects[$type]->get_data($data,$msg))
    		{
    			return array('code'=>'fail','info'=>$msg);
    		}
    	}
    	 
    	/**
    	 * 处理校验各自的数据是否可以加入购物车
    	 */
    	/*
    	if (!$arr_objects[$type])
    	{
    		$msg = app::get('b2c')->_('商品类型错误！');
    		return array('code'=>'fail','info'=>$msg);
    	}*/
    	
    	if (method_exists($arr_objects[$type], 'check_object'))
    	{
    		if (!$arr_objects[$type]->check_object($aData,$msg))
    		{
    			return array('code'=>'fail','info'=>$msg);
    		}
    	}
    	
    	 
    	$aData = $data;
    	$obj_cart_object = kernel::single('b2c_cart_objects');
    	if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
    	{
    		return array('code'=>'fail','info'=>$msg);
    	}

    	if($aData['btype'] == 'is_fastbuy'){
    		$obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg,true);
    	}else{
    		$obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg);
    	}
    	
    	//echo md5("26");
    	//echo $obj_ident;exit;//goods_2_19
    	 
    	if(!$obj_ident){
    		return array('code'=>'fail','info'=>$msg);
    	}else{
    		return array('code'=>'ok','info'=>$obj_ident);
    	}
    	 
    }
    
    public function add_coupon(){
		
    	
    	switch ($_REQUEST['isfastbuy']) {
    		case 'true':
    			$isfastbuy = true;
    			break;
    	
    		case 'group':
    			$isfastbuy = 'group';
    			break;
    	
    		default:
    			$isfastbuy = false;
    			break;
    	}
    	
    	
    	  $aData = Array(
    	  		'coupon' => trim($_REQUEST['coupon']),
    	  		'is_fastbuy' => $isfastbuy,
    	  		'response_json' => true, 
    	  		'0' => 'coupon');
    		
    	  	$re = $this->api_add_cart('coupon',$aData);
    	  	
    	  	if($re['code'] != 'ok'){
    	  		kernel::single('base_rpc_service')->send_user_error('coupon_add_error', $re['info']);
    	    }
    	  	 
    		$this->_common(1,$isfastbuy);
    		
    		
    		//只输出能够使用的优惠劵信息
    		$return_coupon_data = array();
    		foreach($this->pagedata['aCart']['object']['coupon'] as $key=>$coupon_data){
    			if($coupon_data['used'] == 'true'){
    				$return_coupon_data[$key]['coupon'] = $coupon_data['coupon'];
    				$return_coupon_data[$key]['cpns_id'] = $coupon_data['cpns_id'];
    				$return_coupon_data[$key]['name'] = $coupon_data['name'];
    				$return_coupon_data[$key]['obj_ident'] = $coupon_data['obj_ident'];
    			}
    		}
    		if($return_coupon_data){
    			$arr_json_data = array(
    					'success'=>app::get('b2c')->_('优惠券使用成功！'),
    					'coupon_info'=>$return_coupon_data,
    					'md5_cart_info'=>kernel::single('b2c_cart_objects')->md5_cart_objects(),
    			);
    			return $arr_json_data;
    		}else{
    			$ident = 'coupon_'.$aData['coupon'];
    			$this->app->model('cart_objects')->remove_object('coupon', $ident, $msg);
    			kernel::single('base_rpc_service')->send_user_error('coupon_error', '优惠券添加使用失败!');
    			//$this->splash('error',$url,'优惠券使用失败！',true);
    		}    	
    }
    
    public function get_list(){
    	$this->_common(1);
    	$this->pagedata['aCart']['subtotal_prefilter'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['discount_amount_prefilter']));
    	$this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
    	
    	$cartObj = $this->pagedata['aCart'];
    	
    	if($cartObj && isset($cartObj['object'])){
    		if($cartObj['object']['goods']){
    			foreach ($cartObj['object']['goods'] as $k=>$v){
    				foreach ($cartObj['object']['goods'][$k]['obj_items']['products'] as $k2 => $v2) {
    					$cartObj['object']['goods'][$k]['obj_items']['products'][$k2]['thumbnail_url'] = kernel::single('base_storager')->image_path($v2['thumbnail']);
    				}
    			}
    		}
    		
    	}
    	
    	
    	return $cartObj;
    }
    
    
    public function update() {
    	//$aParams = $this->_request->get_params(true);
    	
    	$mCartObject = app::get('b2c')->model('cart_objects');
    	
    	$obj_type = $_REQUEST['obj_type'];
    	$obj_ident = $_REQUEST['obj_ident'];
    	$arr_object['quantity'] = (float)$_REQUEST['quantity'];
    	
    	$_flag = $mCartObject->update_object($obj_type, $obj_ident, $arr_object);
    	
    	if( !$_flag ) {
    		kernel::single('base_rpc_service')->send_user_error('cart_update_error', '购物车修改失败！');
    	}

    	return $_flag;
    	
    }
    
    public function remove() {
    	$mCartObject = app::get('b2c')->model('cart_objects');
    	
    	$obj_type = $_REQUEST['obj_type'];
    	$obj_ident = $_REQUEST['obj_ident'];
    	
    	if($obj_type == 'coupon'){
    		$flag = $mCartObject->remove_object('coupon', $obj_ident, $msg);
    	}elseif($obj_type == 'all'){
    		$flag = $mCartObject->remove_object('', null, $msg);
    	}else{
    		$flag = $mCartObject->remove_object($obj_type, $obj_ident, $msg);
    	}
    	
    	if( !$flag ){
    		kernel::single('base_rpc_service')->send_user_error('cart_remove_error', $msg);
    	}

    	return $flag;
    	
    }
    
    public function checkout(){
    	
    	switch ($_REQUEST['isfastbuy']) {
    		case 'true':
    			$isfastbuy = true;
    		break;

    		case 'group':
    			$isfastbuy = 'group';
    		break;
    		
    		default:
    			$isfastbuy = false;
    		break;
    	}
    	
    	
    	if($isfastbuy != 'group' ){
    		$this->mCart->unset_data();
    	}
    	
    	$this->_common(false,$isfastbuy);
    	
    	$cartObj = $this->pagedata['aCart'];
    	if($cartObj && isset($cartObj['object'])){
    		if($cartObj['object']['goods']){
    			foreach ($cartObj['object']['goods'] as $k=>$v){
    				foreach ($cartObj['object']['goods'][$k]['obj_items']['products'] as $k2 => $v2) {
    					$cartObj['object']['goods'][$k]['obj_items']['products'][$k2]['thumbnail_url'] = kernel::single('base_storager')->image_path($v2['thumbnail']);
    				}
    			}
    		}
    	
    	}
    	
    	$this->pagedata['aCart'] = $cartObj;
    	
        $this->pagedata['checkout'] = 1;
        $this->pagedata['md5_cart_info'] = kernel::single("b2c_cart_objects")->md5_cart_objects($isfastbuy);
        //会员信息
        $arrMember = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_id,cur',));
        $arrMember = $arrMember['members'];

        $this->pagedata['member_id'] = $arrMember['member_id'];
        /** 判断请求的参数是否是group **/
        $arr_request_params = $this->_request->get_params();
        //是否是团购订单
        //$arr_request_params[0] == 'group' ? $this->pagedata['is_group_orders'] = 'true' : $this->pagedata['is_group_orders'] = 'false';
        $isfastbuy == 'group' ? $this->pagedata['is_group_orders'] = 'true' : $this->pagedata['is_group_orders'] = 'false';
        $this->pagedata['app_id'] = $app_id;

        // 如果会员已登录，查询会员的信息
        $obj_member_addrs = $this->app->model('member_addrs');
        $obj_dltype = $this->app->model('dlytype'); //配送方式model
        $member_point = 0; //会员积分
        $str_def_currency = $arrMember['cur'] ? $arrMember['cur'] : ""; //会员设置的默认货币

        /*获取收货地址 start*/
        $def_addr = kernel::single('b2c_member_addrs')->get_default_addr($arrMember['member_id']);
        $this->pagedata['def_addr'] = $def_addr;
        $member_addr_list = $obj_member_addrs->getList('*',array('member_id'=>$arrMember['member_id']));
        $this->pagedata['member_addr_list'] = $member_addr_list;
        //邮编是否开启
        $this->pagedata['site_checkout_zipcode_required_open'] = $this->app->getConf('site.checkout.zipcode.required.open');
        /* 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        /*收货地址 end*/

        if($def_addr){
            //是否有默认的当前的配送方式
            $area = explode(':',$def_addr['area']);
            //$this->pagedata['dlytype_html'] = kernel::single('b2c_order_dlytype')->select_delivery_method($this,$area[2],$this->pagedata['aCart']);
            $this->pagedata['shipping_method'] = (isset($_COOKIE['purchase']['shipping']) && $_COOKIE['purchase']['shipping']) ? unserialize($_COOKIE['purchase']['shipping']) : '';
            $this->pagedata['has_cod'] = $this->pagedata['shipping_method']['has_cod'];
        }
        
       

        $currency = app::get('ectools')->model('currency');
        if($this->pagedata['shipping_method']){
            // 是否有默认的支付方式
            $this->pagedata['arr_def_payment'] = (isset($_COOKIE['purchase']['payment']) && $_COOKIE['purchase']['payment']) ? unserialize($_COOKIE['purchase']['payment']) : '';
            /*支付方式列表*/
            $currency = app::get('ectools')->model('currency');
            $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
            if (!$str_def_currency){
                $arrDefCurrency = $currency->getDefault();
                $str_def_currency = $arrDefCurrency['cur_code'];
            }else{
                $arrDefCurrency = $currency->getcur($str_def_currency);
            }
            $aCur = $currency->getcur($str_def_currency);
            $this->pagedata['current_currency'] = $str_def_currency;
            $obj_payments = kernel::single('ectools_payment_select');
            $this->pagedata['payment_html'] = $obj_payments->select_pay_method($this, $arrDefCurrency, false,false,array('iscommon','iswap'),'API');
            //$obj_payment_select->select_pay_method($this, $sdf, false,false,array('iscommon','iswap'),'API');
            /*end*/

            $ret = $currency->getFormat();
            $ret =array(
                'decimals'=>$this->app->getConf('system.money.decimals'),
                'dec_point'=>$this->app->getConf('system.money.dec_point'),
                'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
                'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
                'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
                'sign' => $ret['sign']
            );
            $this->pagedata['money_format'] = json_encode($ret);
        }

        //是否开启发票
        $trigger_tax = $this->app->getConf('site.trigger_tax');
        if($trigger_tax == 'true'){
            $personal_tax_ratio = $this->app->getConf('site.personal_tax_ratio'); //个人发票税率
            $company_tax_ratio = $this->app->getConf('site.company_tax_ratio'); //公司发票税率
            $tax_content = $this->app->getConf('site.tax_content'); //发票内容选项
            if($tax_content){
                $arr_tax_content = explode(',',$tax_content);
                foreach($arr_tax_content as $tax_content_value){
                    $select_tax_content[$tax_content_value] = $tax_content_value;
                }
            }
            $tax_setting = array(
                'trigger_tax' => $trigger_tax,
                'personal_tax_ratio' => $personal_tax_ratio,
                'company_tax_ratio' => $company_tax_ratio,
                'tax_content' =>$select_tax_content ? $select_tax_content : 0,
            );
            $this->pagedata['tax_setting'] = $tax_setting;
        }//end 发票

        /**
         取到优惠券的信息
         */
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($arrMember['member_id']);
        if( is_array($aData) ) {
            foreach( $aData as $_key => $_val ) {
                if( $_val['memc_used_times'] ) unset($aData[$_key]);
            }
        }
        $this->pagedata['coupon_lists'] = $aData;
        /*end*/


        $total_item = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount_prefilter']));
        // 取到商店积分规则
        $policy_method = $this->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $policy_rate = $this->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($this->objMath->number_plus(array(0, $this->pagedata['aCart']['subtotal_gain_score'])));
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            case '3':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $subtotal_gain_score = round($this->pagedata['aCart']['subtotal_gain_score']);
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }

        $total_amount = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount']));
        if ($total_amount < 0) $total_amount = 0;

        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis){
            $obj_point_dis->set_order_total($total_amount);
            //$this->pagedata['point_dis_html'] = $obj_point_dis->get_html($arrMember['member_id']);
            //$this->pagedata['point_dis_js'] = $obj_point_dis->get_javascript($arrMember['member_id']);
        }

        // 得到cart total支付的信息
        $this->pagedata['order_detail'] = array(
            'cost_item' => $total_item,
            'total_amount' => $total_amount,
            'currency' => $this->app->getConf('site.currency.defalt_currency'),
            'pmt_order' => $this->pagedata['aCart']['discount_amount_order'],
            'pmt_amount' => $this->pagedata['aCart']['discount_amount'],
            'totalConsumeScore' => $subtotal_consume_score,
            'totalGainScore' => $subtotal_gain_score,
            'totalScore' => $totalScore,
            'cur_code' => $strDefCurrency,
            'cur_display' => $strDefCurrency,
            'cur_rate' => $aCur['cur_rate'],
            'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
        );

        //会员积分
       $this->pagedata['order_detail']['totalScore'] = $member_point;
        $odr_decimals = $this->app->getConf('system.money.decimals');
        $total_amount = $this->objMath->get($this->pagedata['order_detail']['total_amount'], $odr_decimals);
        $this->pagedata['order_detail']['discount'] = $this->objMath->number_minus(array($this->pagedata['order_detail']['total_amount'], $total_amount));
        $this->pagedata['order_detail']['total_amount'] = $total_amount;
        $this->pagedata['order_detail']['current_currency'] = $strDefCurrency;

        // 获得商品的赠品信息
        $arrM_info = array();
      
        foreach ($this->pagedata['aCart']['object']['goods'] as $arrGoodsInfo){
            if (isset($arrGoodsInfo['gifts']) && $arrGoodsInfo['gifts']){
                $this->pagedata['order_detail']['gift_p'][] = array(
                    'storage' => $arrGoodsInfo['gifts']['storage'],
                    'name' => $arrGoodsInfo['gifts']['name'],
                    'nums' => $arrGoodsInfo['gifts']['nums'],
                );
            }

            // 得到商品购物信息的必填项目
            $goods_id = $arrGoodsInfo['obj_items']['products'][0]['goods_id'];
            $product_id = $arrGoodsInfo['obj_items']['products'][0]['product_id'];
            // 得到商品goods表的信息
            $objGoods = $this->app->model('goods');
            $arrGoods = $objGoods->dump($goods_id, 'type_id');
            if (isset($arrGoods) && $arrGoods && $arrGoods['type']['type_id']){
                $objGoods_type = $this->app->model('goods_type');
                $arrGoods_type = $objGoods_type->dump($arrGoods['type']['type_id'], '*');

                if ($_COOKIE['checkout_b2c_goods_buy_info']){
                    $goods_need_info = json_decode($_COOKIE['checkout_b2c_goods_buy_info'], 1);

                }
                if ($arrGoods_type['minfo']){
                    if ($arrGoodsInfo['obj_items']['products'][0]['spec_info']){
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'] . '(' . $arrGoodsInfo['obj_items']['products'][0]['spec_info'] . ')';
                    }else{
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'];
                    }
                    $arrM_info[$product_id]['nums'] = $this->objMath->number_multiple(array($arrGoodsInfo['obj_items']['products'][0]['quantity'],$arrGoodsInfo['quantity']));

                    foreach ($arrGoods_type['minfo'] as $key=>$arr_minfo){
                        if (isset($goods_need_info[$product_id][$key]) && $arr_minfo['label'] == $goods_need_info[$product_id][$key]['name']){
                            $arr_minfo['value'] = $goods_need_info[$product_id][$key]['val'][0];
                        }else{
                            $no_value = true;
                        }
                        $arrM_info[$product_id]['minfo'][] = $arr_minfo;
                    }
                }
            }
        }

      
        return $this->pagedata;
       
        
    }
    
    //配送方式根据送货地址联动
    public function delivery_change(){
    	//$this->set_header();
    	
    	switch ($_REQUEST['isfastbuy']) {
    		case 'true':
    			$isfastbuy = true;
    		break;
    		
    		case 'group':
    			$isfastbuy = 'group';
    		break;
    		
    		default:
    			$isfastbuy = false;
    		break;
    	}
    	
    	$this->_common(false,$isfastbuy);
    	
    	$area_id = $_POST['area'];
    	$shipping_method = $_POST['shipping_method'];
    	$obj_delivery = new b2c_order_dlytype();
    	$sdf = array();
    	$sdf = $this->pagedata['aCart'];
    	if (isset($_POST['payment']) && $_POST['payment'])
    		$sdf['pay_app_id'] = $_POST['payment'];
    	$this->pagedata['app_id'] = $app_id;
    	$deliverys = $obj_delivery->select_delivery_method($this,$area_id,$sdf,$shipping_method,'API');
    	
    	foreach ($deliverys as $key =>$v){
    		$deliverys[$key]['shipping'] = '{"id":'.$v['dt_id'].',"has_cod":"'.$v['has_cod'].'","dt_name":"'.$v['dt_name'].'","money":"'.$v['money'].'"}';
    	
    	}
    	return $deliverys;
    }
    
    //支付方式根据配送方式联动
    public function payment_change(){
    	
    	$obj_payment_select = new ectools_payment_select();
    	if($_POST['payment']['currency']){
    		$sdf['cur'] = $_POST['payment']['currency'];
    	}
    
    	if($_POST['shipping']){
    		$shipping = json_decode($_POST['shipping'],true);
    		$this->pagedata['has_cod'] = $shipping['has_cod'];
    	}
    	$currency = app::get('ectools')->model('currency');
    	$this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
    	$this->pagedata['current_currency'] = $sdf['cur'] ? $sdf['cur'] : '';
    	$this->pagedata['app_id'] = $app_id;
    	$payments = $obj_payment_select->select_pay_method($this, $sdf, false,false,array('iscommon','iswap'),'API');
    	
    	$payments_ret = array();
    	foreach ($payments as $key => $value) {
    		$payments_ret[] = $value;
    	}
    	
    	return $payments_ret;
    }
    
    public function total(){
    	
    	switch ($_REQUEST['isfastbuy']) {
    		case 'true':
    			$isfastbuy = true;
    		break;
    		
    		case 'group':
    			$isfastbuy = 'group';
    		break;
    		
    		default:
    			$isfastbuy = false;
    		break;
    	}
    	
    	$this->_common(false,$isfastbuy);
    	$obj_total = new b2c_order_total();
    	$sdf_order = $_POST;
    	if($_POST){
    		$payment = json_decode($_POST['payment']['pay_app_id'],true);
    		$shipping = json_decode($_POST['shipping'],true);
    		$address = json_decode($_POST['address'],true);
    		$address_area = explode(':',$address['area']);
    	}
    	
    	
    	$sdf_order['cur'] = $_POST['payment']['currency'];
    	$sdf_order['shipping_id'] = $shipping['id'];
    	$sdf_order['is_protect'] = $_POST['is_protect'];
    	$sdf_order['is_tax'] = $_POST['payment']['is_tax'];
    	$sdf_order['tax_type'] = $_POST['payment']['tax_type'];
    	$sdf_order['payment'] = $payment['pay_app_id'];
    	$member_id = kernel::single('b2c_user_object')->get_member_id();
    	$sdf_order['member_id'] = $member_id;
    	$sdf_order['area_id'] = $address_area[2]?$address_area[2]:$address['area'];
    	$sdf_order['dis_point'] = floor($_POST['point']['score']);
    	$arr_cart_object = $this->pagedata['aCart'];
    	//$this->set_header();
    	
    	$payment_detail = $obj_total->payment_detail($this,$arr_cart_object,$sdf_order);
    	return $payment_detail;
    }
    
    
    private function _common($flag=0,$is_fastbuy=false) {
    	// 购物车数据信息
    	//$aData = $this->_request->get_params(true);
    	$aData = array();
    	if($is_fastbuy){
    		$aData[0] = $is_fastbuy;
    		$aData['is_fastbuy'] = $is_fastbuy;
    		
    	}
    	$aCart = $this->mCart->get_objects($aData);
    
    	$this->_item_to_disabled( $aCart,$flag ); //处理购物扯删除项
    	$this->pagedata['aCart'] = $aCart;
    
    	if( $this->show_gotocart_button ) $this->pagedata['show_gotocart_button'] = 'true';
    
    	if( $this->ajax_update === true ) {
    		foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
    			if( !is_object($object) ) continue;
    
    			//应该判断是否实现了接口
    			if( !method_exists( $object,'get_update_num' ) ) continue;
    			if( !method_exists( $object,'get_type' ) ) continue;
    
    			$this->pagedata['edit_ajax_data'] = $object->get_update_num( $aCart['object'][$object->get_type()],$this->update_obj_ident );
    			if( $this->pagedata['edit_ajax_data'] ) {
    				//$this->pagedata['edit_ajax_data'] = json_encode( $this->pagedata['edit_ajax_data'] );
    				if( $object->get_type()=='goods' ) {
    					$this->pagedata['update_cart_type_godos'] = true;
    					if( !method_exists( $object,'get_error_html' ) ) continue;
    					$this->pagedata['error_msg'] = $object->get_error_html( $aCart['object']['goods'],$this->update_obj_ident );
    				}
    				break;
    			}
    		}
    	}
    
    
    	// 购物车是否为空
    	$this->pagedata['is_empty'] = $this->mCart->is_empty($aCart);
    	//ajax_html 删除单个商品是触发
    	if($this->ajax_html && $this->mCart->is_empty($aCart)) {
    		$arr_json_data = array(
    				'is_empty' => 'true',
    				'number'=>array(
    						'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
    						'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
    				),
    		);
    		$this->pagedata = $arr_json_data;
    		$this->page('wap/cart/cart_empty.html', true);
    		return ;
    	}
    
    	// 购物车数据项的render
    	$this->pagedata['item_section'] = $this->mCart->get_item_render();
    
    	// 购物车数据项的render
    	$this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render();
    
    	// 优惠信息项render
    	$this->pagedata['solution_section'] = $this->mCart->get_solution_render();
    
    	//未享受的订单规则
    	$this->pagedata['unuse_rule'] = $this->mCart->get_unuse_solution_cart($aCart);
    
    	$imageDefault = app::get('image')->getConf('image.set');
    	$this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
    }
    
    private function _item_to_disabled( &$aCart,$flag ) {
    
    	foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
    		if( !is_object($object) ) continue;
    		$o[$object->get_type()] = $object;
    	}
    
    	$arr_cart_disabled_session = $_SESSION['cart_objects_disabled_item'];
    	foreach( (array)$aCart['object'] as $_obj_type => $_arr_by_obj_type ) {
    		$tmp = $arr_cart_disabled_session[$_obj_type];
    		if( isset($arr_cart_disabled_session[$_obj_type]) ) {
    			if( !$o[$_obj_type] ) continue;
    			if( !method_exists( $o[$_obj_type],'apply_to_disabled' ) ) continue;
    			$aCart['object'][$_obj_type] = $o[$_obj_type]->apply_to_disabled( $_arr_by_obj_type, $tmp, $flag );
    			$_SESSION['cart_objects_disabled_item'][$_obj_type] = $tmp;
    		} else {
    			if( $flag )
    				unset($_SESSION['cart_objects_disabled_item'][$obj_type]);
    		}
    	}
    }

    public function get_invoice() {
        $trigger_tax = $this->app->getConf('site.trigger_tax');
        if ($trigger_tax) {
            $personal_tax_ratio = $this->app->getConf('site.personal_tax_ratio'); //个人发票税率
            $company_tax_ratio = $this->app->getConf('site.company_tax_ratio'); //公司发票税率
            $tax_content = $this->app->getConf('site.tax_content'); //发票内容选项
            if($tax_content){
                $arr_tax_content = explode(',',$tax_content);
                foreach($arr_tax_content as $tax_content_value){
                    $select_tax_content[$tax_content_value] = $tax_content_value;
                }
            }
            $tax_setting = array(
                'trigger_tax' => $trigger_tax,
                // 'personal_tax_ratio' => $personal_tax_ratio,
                // 'company_tax_ratio' => $company_tax_ratio,
                'tax_content' =>$select_tax_content ? $select_tax_content : 0,
            );

            $tax_setting['tax_list'] = array(
                0=>array(
                    'label' => '不需要发票',
                    'value' => 'false',
                ),
                1=>array(
                    'label' => $tax_setting['personal_tax_ratio'] ? '个人发票(税率'.($tax_setting['personal_tax_ratio']*100).'%)' : '',
                    'value' => 'personal',
                    'tax_ratio' => $personal_tax_ratio,
                ),
                2=>array(
                    'label' => $tax_setting['company_tax_ratio'] ? '公司发票(税率'.($tax_setting['company_tax_ratio']*100).'%)' : '',
                    'value' => 'company',
                    'tax_ratio' => $company_tax_ratio,
                ),
            );
            return $tax_setting;
        }
    }

    // 门店列表 (根据地区3级id查询门店)
    public function get_stores_list($params, $rpcService) {
        /*
        $addr_id = $params['addr_id'];
        if (!$addr_id > 0) 
            $rpcService->send_user_error('error', '没有收货地址，请填写收货地址！');
        $objMem = $this->app->model('members');
        $def_addr = $objMem->getAddrById($addr_id);
        $area = explode(':',$def_addr['area']);
        $da_id = app::get('ectools')->model('regions')->getDlAreaById($area[2]);
        $list = app::get('ome')->model('branch')->getList('branch_id, name', array('region_id'=>$da_id['p_region_id']));
        */
        $list = app::get('ome')->model('branch')->getList('branch_id, name, address', array('is_show'=>'true'));
        return $list;
    }
    
}

?>