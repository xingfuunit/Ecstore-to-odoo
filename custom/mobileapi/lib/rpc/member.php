<?php
/**
* 
*/
class mobileapi_rpc_member extends mobileapi_frontpage{
	
	function __construct($app)
	{
		parent::__construct($app);
		
		$this->app = $app;
		$this->verify_member();
        $this->member = $this->get_current_member();
		$this->app->rpcService = kernel::single('base_rpc_service');
        $this->pagesize = 10;
	}

	/**
     * 取到个人信息字段
     * @param null
     * @return string json
    */
	public function setting()
	{
		$userObject = kernel::single('b2c_user_object');
		$membersData = $userObject->get_pam_data('*',$this->app->member_id);
		foreach ($membersData as $value) {

 		}

		$attr = kernel::single('b2c_user_passport')->get_signup_attr($this->app->member_id);
		foreach ($attr as $key => $value) {
			unset($attr[$key]['attr_order'], $attr[$key]['attr_search'], $attr[$key]['attr_group'], $attr[$key]['attr_sdfpath']);
			if ($value['attr_column'] == 'profile[gender]') {
				$attr[$key]['attr_option'][] = 'male';
				$attr[$key]['attr_option'][] = 'female';
			}
			if ($value['attr_type'] == 'checkbox') 
				$attr[$key]['attr_column'] = 'box:'.$value['attr_column'];
		}

		return array('mem' => 'null', 'attr' => $attr);

		/*
			attr 字段列表
			attr_id =>   ID
            attr_show => 是否显示
            attr_name => 显示名称
            attr_type => 字段类型			checkbox(多选类型)||gender(单选类型)||text(文本类型)||select(单选类型)||....
            attr_required => 是否必填 	bool(true为必填)
            attr_option => 字段选项类型数据数组
            			[0] => value
						[1] => value
            attr_valtype => 
            attr_tyname => 字段项类型
            attr_column => 字段name
            attr_sdfpath => 
            attr_value => 保存后字段值			值||array() 
		*/
	}

	/**
     * 保存个人信息
     * @param  mixed
     * @return string json
     * @提交例子 '&contact[name]=测试&contact[area]=&profile[gender]=female&box:testing[]=选项1&box:testing[]=选项2&sfa=2222'
    */
	public function save_setting()
	{
        $member_model = app::get('b2c')->model('members');
        $userPassport = kernel::single('b2c_user_passport'); 
        $_POST = $this->check_input($_POST);
        if($_POST['local_name'] && !$userPassport->set_local_uname(strtolower($_POST['local_name']),$msg)){
            $this->app->rpcService->send_user_error('4003', $msg);
        }

        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }


        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
        $attr = app::get('b2c')->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($_POST as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($_POST[$post_key]);
            }
        }
        //---end

        $_POST['member_id'] = $this->app->member_id;
        if($member_model->save($_POST)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($_POST['member_id']);
            }

        }else{
            $this->app->rpcService->send_user_error('4003', '更新失败');
        }
	}

	/**
     * 保存修改密码
     * @param array
     * @return string json
    */
	public function save_security()
	{
		$userPassport = kernel::single('b2c_user_passport');
		$result = $userPassport->save_security($this->app->member_id,$_POST,$msg);
		if($result){
            return true;
        }else{
            $this->app->rpcService->send_user_error('4003', $msg);
        }
	}

	//我的订单
    public function orders()
    {
        $nPage = $_POST['n_page'] ? $_POST['n_page'] : 1;
        $pay_status = $_POST['pay_status'];  // 支付状态
        $ship_status = $_POST['ship_status']; //发货状态
        $status = $_POST['status'];  //订单状态
        $createtime_status = $_POST['createtime_status']; //订单时间
        $order = app::get('b2c')->model('orders');
        if (!isset($_POST['pay_status']) && !isset($_POST['ship_status']) && !isset($_POST['status']) && !isset($_POST['createtime_status'])) {
            $aData = $order->fetchByMember($this->app->member_id,$nPage);
        } else {
            $order_status = array();

            # 支付状态
            if (isset($pay_status) && $pay_status <= 5) {
                $order_status['pay_status'] = $pay_status;
                $order_status['status'] = 'active';
            }

            # 发货状态
            if (isset($ship_status) && $ship_status <= 4) {
                $order_status['ship_status'] = $ship_status;
                $order_status['status'] = 'active';
            }

            # 订单状态
            if (in_array($status, array('finish', 'active'))) {
                $order_status['status'] = $status;
            }

            # 一个月前的订单
            if ($createtime_status == 'prior_to'){
                $ago = time() - 86400 * 31;
                $order_status['createtime_to'] = $ago;
            } elseif ($createtime_status == 'recent') {
                $ago = time() - 86400 * 31;
                $order_status['createtime_from'] = $ago;
            }
            
            $aData = $order->fetchByMember($this->app->member_id,$nPage,$order_status);
        }

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        foreach($aData['data'] as $k => &$v) {
            foreach($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc',array('goods_id'=>$v2['product']['goods_id']));
                $select_spec_private_value_id = @reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                $spec_desc_goods = @reset($spec_desc_goods[0]['spec_desc']);
                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                }else{
                    if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                    $v2['product']['thumbnail_pic_src'] = base_storager::image_path($v2['product']['thumbnail_pic'], 's' );
                }
            }

            if ($v['payinfo']['pay_app_id']) {
                $pay_info = $obj_payments_cfgs->getPaymentInfo($v['payinfo']['pay_app_id']);
                $aData['data'][$k]['payinfo']['display_name'] = $pay_info['app_name'];
                $aData['data'][$k]['payinfo']['app_pay_type'] = $pay_info['app_pay_type'];
            }
        }

        foreach ($aData['data'] as $key => $value) {
            unset($aData['data'][$key]['goods_items']);
            $aData['data'][$key]['goods_items'] = array_values($value['goods_items']);
            if ($value['consignee']['area']) {
                $str = explode(':', $value['consignee']['area']);
                $aData['data'][$key]['consignee']['txt_area'] = str_replace('/','',$str[1]);
            }
        }
        return $aData['data'];
    }

    //订单详情
    public function orderdetail($param = array())
    {
        $order_id = $param['order_id'];
        if (!isset($order_id) || !$order_id)
        {
            $this->app->rpcService->send_user_error('4003', '订单编号不能为空！');
        }

        $objOrder = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");
        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            $this->app->rpcService->send_user_error('404', '订单号：'.$order_id.'不存在！');
        }
        if($sdf_order['member_id']){
            $member = app::get('b2c')->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $data['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $data['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $data['order'] = $sdf_order;
// echo "<pre>";print_r($this->pagedata['order']);exit;
        /** 将商品促销单独剥离出来 **/
        if ($data['order']['order_pmt'])
        {
            foreach ($data['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    $data['order']['goods_pmt'][$arr_pmt['product_id']][$key] =  $data['order']['order_pmt'][$key];
                    unset($data['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $data['ordermsg'] = $arrOrderMsg;

        //我已付款
        $$timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $data['timeHours'] = $timeHours;
        $data['timeMins'] = $timeMins;

        // 生成订单日志明细
        //$oLogs =$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        //$data['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($data['order']['payinfo']['pay_app_id']);
        $data['order']['payment'] = $arr_payments_cfg;

        #//物流跟踪安装并且开启
        #$logisticst = app::get('b2c')->getConf('system.order.tracking');
        #$logisticst_service = kernel::service('b2c_change_orderloglist');
        #if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
        #    $this->pagedata['services']['logisticstack'] = $logisticst_service;
        #}
        $data['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }

        return $data;
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData,$tml='member_orders')
    {
        if (isset($aData['data']) && $aData['data'])
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item)
            {
                $this->get_order_detail_item($arr_data_item,$tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item,$tpl='member_order_detail')
    {
        if (isset($arr_data_item) && $arr_data_item)
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = app::get('b2c')->model('specification');
            $obj_spec_values = app::get('b2c')->model('spec_values');
            $obj_goods = app::get('b2c')->model('goods');
            $oImage = app::get('image')->model('image');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods')
                    {
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            if (!$arr_items['products'])
                            {
                                $o = app::get('b2c')->model('order_items');
                                $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product')
                            {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product'] = $arr_items;
                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id,spec_desc', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                // 获取货品关联第一张图片
                                $select_spec_private_value_id = @reset($arr_items['products']['spec_desc']['spec_private_value_id']);
                                $spec_desc_goods = @reset($arr_goods['spec_desc']);
                                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                                    $arr_goods['image_default_id'] = $default_product_image;
                                }else{
                                    if( !$arr_goods['image_default_id'] && !$oImage->getList("image_id",array('image_id'=>$arr_goods['image_default_id']))){
                                        $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                }

                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                if ($arr_items['addon'])
                                {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else{
                                    unset($arrAddon,$arr_addon);
                                }
                                if ($arrAddon['product_attr'])
                                {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], " ") !== false)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            }
                            elseif ($arr_items['item_type'] == 'adjunct')
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj] = $arr_items;
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon'])
                                {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            }
                            else
                            {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift] = $arr_items;
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['price'] = $arr_items['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'])
                                    {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    }
                    else
                    {
                        if ($arr_objects['obj_type'] == 'gift')
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {
                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    if (!$arr_items['products'])
                                    {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score']*$arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'])
                                    {
                                        if (strpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                        }
                    }
                }
            }

        }
    }
    
   /**
     * 用户取消订单
     * @params string order id
     * @return null
     */
    public function cancel($param = array())
    {
        $order_id = $param['order_id'];
        $this->member = $this->get_current_member();
        $objM = app::get('b2c')->model('orders');
        $order = $objM->getRow('status,pay_status,ship_status',array('order_id'=>$order_id,'member_id'=>$this->member['member_id']));
        if(!$order){
            $this->app->rpcService->send_user_error('4003', '您无权进行此操作');
        }
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_id,'',$message))
        {
            $this->app->rpcService->send_user_error('4003', $message);
        }
        
        $sdf['order_id'] = $order_id;
        $sdf['op_id'] = $this->member['member_id'];
        $sdf['opname'] = $this->member['uname'];
        $sdf['account_type'] = $this->member['account_type'];
         
        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf))
        {
             return '操作成功';
        }
        else
        {
            $this->app->rpcService->send_user_error('4003', '参数错误');
        }
    }    
    
    //收货地址
    public function receiver(){
        $objMem = app::get('b2c')->model('members');
        return $objMem->getMemberAddr($this->app->member_id);
    }

    /*
     * 设置和取消默认地址，$disabled 2为设置默认1为取消默认
     */
    public function set_default(){
        $addrId = $_POST['addr_id'];
        $disabled = $_POST['disabled'];
        if(!$addrId) $this->app->rpcService->send_user_error('4003', '参数错误');
        $obj_member = app::get('b2c')->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$member_id)){
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled)){
                return '操作成功';
            }else{
                $this->app->rpcService->send_user_error('4003', $message);
            }
        }else{
            $this->app->rpcService->send_user_error('4003', '参数错误');
        }
    }

    /*
     *添加、修改收货地址
     * */
    public function modify_receiver(){
    	$addrId = $_POST['addr_id'];
        if(!$addrId){
            $this->app->rpcService->send_user_error('4003', '参数错误');
        }
        $obj_member = app::get('b2c')->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id)){
            if($aRet = $obj_member->getAddrById($addrId)){
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                return $aRet;
            }else{
            	$this->app->rpcService->send_user_error('4003', '修改的收货地址不存在！');
            }
        }else{
            $this->app->rpcService->send_user_error('4003', '参数错误');
        }
    }

    /*
     *获取地址数据
     * */
    public function get_regions(){
        
    	/*
    	$where = intval($_POST['p_region_id']) > 0?'r.p_region_id='.intval($_POST['p_region_id']):'r.p_region_id IS NULL';
    	
    	$sql="select region_id,local_name,(select count(*) from sdb_ectools_regions where p_region_id=r.region_id) as child_count from sdb_ectools_regions as r where ".$where." order by ordernum asc,region_id asc";
    	$rows = kernel::database()->select($sql);
    	
    	 return $rows;
    	
    	*/
    	
    	$this->getAllRegions('','',$region_count, $regions);
        $arr = array();
        foreach($regions as $v) {
            $arr[] = $v;
        }
        unset($this->regions);
        return $arr;
    }

    private function getAllRegions($p_regionid='', $pkey='', &$region_count=array(), &$regions){
        $show_depth = app::get('ectools')->getConf('system.area_depth');
        
        
        //仓库门开启时才在前端显示地址
        $branch_list = app::get('ome')->model('branch')->getList('branch_bn', array('is_show'=>'true'));
        $branch_bns = array();
        $branch_bns[] = '50';
        foreach ($branch_list as $value) {
        	$branch_bns[] = intval($value['branch_bn']);
        }
        $branch_where = ' and r.ordernum in ('.implode(',', $branch_bns).') '; 
        $swhere = ' and ordernum in ('.implode(',', $branch_bns).') ';
        
        
        if ($p_regionid)
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_ectools_regions where p_region_id=r.region_id $swhere) as child_count from sdb_ectools_regions as r where r.p_region_id=".intval($p_regionid)." $branch_where order by ordernum asc,local_name asc, region_id asc";
        else
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_ectools_regions where p_region_id=r.region_id $swhere) as child_count from sdb_ectools_regions as r where r.p_region_id is null order by ordernum asc,local_name asc, region_id asc";

        $row = kernel::database()->select($sql);
        
        if (isset($row) && $row)
        {
            $cur_row = current($row);
            if(!$region_count[$cur_row['region_grade']]) {
                $start_index = 0;
            }
            else {
                $start_index = $region_count[$cur_row['region_grade']];
            }
            foreach ($row as $key => $val)
            {
                $tmp = array(
                    $val['local_name'],
                    $val['region_id'],
                );
                $index = $pkey!==''?$pkey:$key;
                if($val['child_count']) {
                    if($val['region_grade']<$show_depth) {
                        $tmp[] = $start_index;
                    }
                    $start_index++;
                    $region_count[$cur_row['region_grade']] = $start_index;
                }
                if($val['region_grade'] != 1) {
                    $regions[$val['region_grade']][$index][] = implode(":", $tmp);
                }
                else {
                    $regions[$val['region_grade']][$index] = implode(":", $tmp);
                }
                if ($val['child_count'] && $val['region_grade']<$show_depth) {
                    $this->getAllRegions($val['region_id'], $start_index-1, $region_count, $regions);
                }
            }
        }
    }   

    /*
     *保存收货地址
     * */
    public function save_rec(){
        if(!$_POST['def_addr']){
            $_POST['def_addr'] = 0;
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST,$this->app->member_id,$msg);
        if(!$save_data){
             $this->app->rpcService->send_user_error('4003', $msg);
        }
        return '保存成功';
    }

    //添加收货地址
    public function add_receiver(){
        $obj_member = app::get('b2c')->model('members');
        if($obj_member->isAllowAddr($this->app->member_id)){
        	return true;
        }else{
        	$this->app->rpcService->send_user_error('4003', '最多添加10个收货地址');
        }
    }

    //删除收货地址
    public function del_rec(){
    	$addrId = $_POST['addr_id'];
        if(!$addrId) $this->app->rpcService->send_user_error('4003', '参数错误');
        $obj_member = app::get('b2c')->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id))
        {
            if($obj_member->del_rec($addrId,$message,$this->app->member_id))
            {
                return true;
            }
            else
            {
                $this->app->rpcService->send_user_error('4003', $message);
            }
        }
        else
        {
            $this->app->rpcService->send_user_error('4003', '操作失败');
        }
    }
    
    public function coupon($nPage=1) {
    	$nPage = $_POST['n_page'] ? $_POST['n_page'] : 1;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->app->member_id,$nPage);
        if ($aData) {
            $this->member = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_lv_id'));
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status'] !=1) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    continue;
                }

                $member_lvs = explode(',',$item['time']['member_lv_ids']);
                if (!in_array($this->member['members']['member_lv_id'],(array)$member_lvs)) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    continue;
                }

                $curTime = time();
                if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                    if ($item['memc_used_times']<app::get('b2c')->getConf('coupon.mc.use_times')){
                        if ($item['coupons_info']['cpns_status']){
                            $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                        }
                    }else{
                        $aData[$k]['coupons_info']['cpns_status'] = false;
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                    }
                }else{
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                }
            }
        }

        $total = $oCoupon->get_list_m($this->app->member_id);
        return $aData;
    }

    /*
     *发送站内信
     * */
    public function send_msg(){
        if(!isset($_POST['msg_to']) || $_POST['msg_to'] == '管理员'){
            $_POST['to_type'] = 'admin';
            $_POST['msg_to'] = 0;
        }else{
            $userObject = kernel::single('b2c_user_object');
            $to_id = $userObject->get_id_by_uname($_POST['msg_to']);
            if(!$to_id){
                $this->splash('failed',null,app::get('b2c')->_('收件人不存在'),$_POST['response_json']);
            }
            $_POST['to_id'] = $to_id;
        }
        if($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member['uname'];
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if($_POST['comment_id']){
                //$data['comment_id'] = $_POST['comment_id'];
                $_POST['comment_id'] = '';//防止用户修改comment_id
            }
            if( $objMessage->send($_POST) ) {
            if($_POST['has_sent'] == 'false'){
                return true;
            }else{
                return true;
            }
            } else {
                $this->app->rpcService->send_user_error('4003', '发送失败');
            }
        }
        else {
            $this->app->rpcService->send_user_error('4003', '必填项不能为空');
        }
    }

	/*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    private function check_input($data)
    {
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    private function arrContentReplace($array)
    {
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] =     $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

    /**
     * 关注会员
     */
    function follow( $param = array(), $rpcService ) {
        if ( $param['member_id'] > 0 && $param['follow_id'] > 0 ) {
            $mdl    = app::get('b2c')->model('member_follow');
            $data   = array (
                'member_id' => $param['member_id'],
                'follow_id' => $param['follow_id'],
            );
            $list   = $mdl->getList('*', $data);
            if ( $list ) {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('b2c')->_('已关注过了,请勿重复关注'),
                );
            } else {
                $data['addtime']    = time();
                if ( $mdl->save($data) ) {
                    $this->update_follow_fans_num($data, $rpcService);
                    $ret    = array (
                        'code'  => 1,
                        'data'  => $data,
                        'msg'   => app::get('b2c')->_('关注成功'),
                    );
                } else {
                    $ret    = array (
                        'code'  => -4,
                        'msg'   => app::get('b2c')->_('系统错误,请联系管理员'),
                    );
                }
            }
        } else {
            $ret    = array (
                'code'  => -1, 
                'msg'   => app::get('b2c')->_('会员ID或关注的用户ID不合法'),
            ); 
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }   
        return $ret;
    }

    /**
     * 取消关注
     */
    function unfollow( $param = array(), $rpcService ) {
        if ( $param['member_id'] > 0 && $param['follow_id'] > 0 ) {
            $mdl    = app::get('b2c')->model('member_follow');
            $data   = array (
                'member_id' => $param['member_id'],
                'follow_id' => $param['follow_id'],
            );
            $list   = $mdl->getList('*', $data);
            if ( $list ) {
                if ( $mdl->delete($data) ) {
                    $data['type'] = 'delete';
                    $this->update_follow_fans_num($data, $rpcService);
                    $ret    = array (
                        'code'  => 1,
                        'data'  => $data,
                        'msg'   => app::get('b2c')->_('取消关注成功'),
                    );
                }
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('b2c')->_('未关注过该用户'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1, 
                'msg'   => app::get('b2c')->_('会员ID或关注的用户ID不合法'),
            ); 
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }   
        return $ret;
    }

    /**
     * 更新关注/粉丝人数
     */
    function update_follow_fans_num( $param = array(), $rpcService ) {
        $mdl    = app::get('b2c')->model('members');
        $_type  = $param['type'] ? $param['type'] : 'add';
        $filter = array (
            'member_id' => array($param['member_id'], $param['follow_id'])
        );
        $list   = $mdl->getList('follow_num, fans_num, member_id', $filter);
        if ( $list ) {
            foreach ( $list AS $v ) {
                $fields = $v['member_id'] == $param['member_id'] ? 'follow_num' : 'fans_num';
                $_data  = array (
                    $fields => $_type == 'add' ? $v[$fields] + 1 : $v[$fields] - 1,
                );
                $mdl->update( $_data, array( 'member_id' => $v['member_id'] ));
            }
        }
        $ret    = array (
            'code'  => 1,
            'msg'   => app::get('b2c')->_('更新成功'),
        );
        return $ret;
    }

    /**
     * 我要开店
     */
    function open_shop( $param = array(), $rpcService ) {
    	
        if ( $param['member_id'] > 0 && trim($param['agency_id']) && trim($param['shop_name']) ) {
            $data   = array (
                'member_id' => $param['member_id'],
            );
            $mdl    = app::get('microshop')->model('shop');
            $info   = $mdl->getList('*', $data);
            if ( $info ) {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('b2c')->_('已经开过微店了,请勿重复操作'),
                );
            } else {
                $_filter    = array (
//                     'member_id' => $param['agency_id'], 
                	'agency_no' => trim($param['agency_id']), //更新为经销商编号
                    'member_type'   => 3,
                );
                $m_mdl  = app::get('b2c')->model('members');
                $_row   = $m_mdl->dump($_filter);
                if ( isset($_row['member_id']) && $_row['member_id'] > 0 ) {
                    $data['agency_id']  = $_row['member_id'];
                    $data['shop_name']  = trim($param['shop_name']);
                    $data['addtime']    = time();
                    
                    if ( $mdl->save($data) ) {
                        $_data  = array (
                                'member_type'   => 2,
                                );
                        // 修改用户状态
                        app::get('b2c')->model('members')->update($_data, array('member_id' => $param['member_id']));
                        $ret    = array (
                                'code'  => 1,
                                'data'  => $data,
                                'msg'   => app::get('b2c')->_('开店成功'),
                                );
                    } else {
                        $ret    = array (
                                'code'  => -3,
                                'msg'   => app::get('b2c')->_('系统错误,请联系管理员'),
                                );
                    }
                } else {
                    $ret    = array (
                        'code'  => -4,
                        'msg'   => app::get('b2c')->_('邀请您开店者的手机号错误'),
                    );
                }
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('b2c')->_('请确保会员ID/开店者的手机号/微店名称都合法'),
            );
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }   
        return $ret;
    }

    /**
     * 获得经销商
     */
    function get_agency() {
        $mdl    = app::get('b2c')->model('members');
        $filter = array (
            'member_type'   => 2,
        );
        return $mdl->getList('*', $filter, 0, -1);
    }

    /**
     * 获得关注列表
     */
    function get_follow($param = array(), $rpcService) {
        $member_id  = intval($param['member_id']);
        if ( $member_id > 0 ) {
            $page   = intval($request['page_no']);
            $page   = $page > 0 ? $page : 1;
    	    $db     = kernel::database();
            $filter = array (
                'member_id' => $member_id,
            );
            $sql    = 'SELECT count(*) AS sum FROM '.$db->prefix.'b2c_member_follow AS mf LEFT JOIN '.$db->prefix.'microshop_shop AS s ON s.member_id = mf.follow_id WHERE mf.member_id = '.$member_id.' AND s.is_open = 1';
            $count  = $db->selectRow($sql);
            $list   = array();
            if ( $count['sum'] ) { 
                $page_size  = intval($param['page_size']);
                $page_size  = $page_size > 0 ? $page_size : 20; 
                $order      = trim($param['order']);
                $order      = $order ? $order : 'mf.addtime';
                $order_type = trim($param['order_type']);
                $order_type = $order_type ? $order_type : 'DESC';
                $orderby    = ' ORDER BY '.$order.' '.$order_type;
                $offset     = ($page - 1) * $page_size;
                //$sql        = 'SELECT m.avatar, m.cover, m.follow_num, m.fans_num, s.* FROM '.$db->prefix.'b2c_member_follow AS mf LEFT JOIN '.$db->prefix.'b2c_members AS m ON m.member_id = mf.follow_id LEFT JOIN '.$db->prefix.'microshop_shop AS s ON s.member_id = mf.follow_id WHERE mf.member_id = '.$member_id.' AND s.is_open = 1'.$orderby;
                $sql        = 'SELECT s.* FROM '.$db->prefix.'b2c_member_follow AS mf LEFT JOIN '.$db->prefix.'microshop_shop AS s ON s.member_id = mf.follow_id WHERE mf.member_id = '.$member_id.' AND s.is_open = 1'.$orderby;
                $list       = $db->selectLimit($sql, $page_size, $offset);
                if ( $list ) {
                    $m_mdl  = app::get('b2c')->model('members');
                    foreach ( $list AS $k => $v ) {
                        $m_info = $m_mdl->dump($v['member_id']);
                        $list[$k]['avatar'] = $m_info['avatar'] ? kernel::single('base_storager')->image_path($m_info['avatar']) : $this->app->res_url.'/images/top-bg.png';
                        $list[$k]['cover'] = $m_info['cover'] ? kernel::single('base_storager')->image_path($m_info['cover']) : $this->app->res_url.'/images/top-bg.png';
                        $_param = array (
                            'app'   => 'microshop',
                            'ctl'   => 'site_index',
                            'full'  => 1,
                            'act'   => 'detail',
                            'arg0'  => $v['shop_id'],
                        );  
                        $list[$k]['shop_link']  = app::get('site')->router()->gen_url($_param);
                        $list[$k]['follow_num'] = $m_info['follow_num'];
                        $list[$k]['fans_num']   = $m_info['follow_num'];
                        $list[$k]['info']       = $m_info['info'];
                    }
                }
            }
            $ret        = array(
                'code'  => 1,
                'total_results' => $count['sum'],
                'items' => $list,
                'msg'   => app::get('b2c')->_('读取成功'),
            );
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('b2c')->_('请确保会员ID合法'),
            );
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }   
        return $ret;
    }


    public function upload_image()
    {
        # avatar 160 160
        # cover  640 316

        $uptype = $_POST['type'];


        if ($uptype != 'avatar' && $uptype != 'cover') $this->app->rpcService->send_user_error('4003', $uptype);

        $old_images = kernel::single('b2c_user_object')->get_members_data(array('members'=> $uptype));

        $size = array(
            'width'   => $uptype == 'avatar' ? '160' : '640',
            'height'  => $uptype == 'avatar' ? '160' : '316',
        );  // 尺寸

        if ($_FILES['file']['size'] > (3 * 1024 * 1024)) {
            $this->app->rpcService->send_user_error('4003', '上传文件不能超过3M！');
        }

        if ( $_FILES['file']['name'] != "" ) {
            $type = array("png","jpg","gif","jpeg"); //允许上传的文件

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type)) {
                $text = implode(",", $type);
                $this->app->rpcService->send_user_error('4003', '您只能上传以下类型文件'.$text);
            }
        }

        $mdl_img = app::get('image')->model('image');
        $image_name = $_FILES['file']['name'];
        $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
        $mdl_img->rebuild($image_id,array('L','M','S'));

        $image_src = base_storager::image_path($image_id, 'l');

        // 更新图片
        app::get('b2c')->model('members')->update(array($uptype => $image_id), array('member_id' => $this->app->member_id));

        // 删除旧图
        if ($old_images) {
            $mdl_img->delete_image($old_images,'network');
        }

        return $image_src;
    }

    private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    /**
     * 我要提现
     */
    public function withdrawal($param = array(), $rpcService)
    {
        $param['page_no'] = $param['page_no'] ? max(1,(int)$param['page_no']) : '1';
        $param['page_size'] = $param['page_size'] ? (int)$param['page_size'] : '10';
        
        $page_size = $param['page_size'];
        $page_no = ($param['page_no'] - 1) * $page_size;
        
        $db = kernel::database();
        
        $member = app::get('b2c')->model('members');
        $mem_wit = kernel::single('b2c_mdl_member_withdrawal');
        
//         $items_wit = $mem_wit->get_list_bymemId($this->app->member_id);
//         $witlogs = $mem_wit->getList('*',array('member_id' => $this->app->member_id),$page_no,$page_size,'create_time desc',false);
//         $total_results = $mem_wit->count();
	
        $sql = "SELECT * FROM `sdb_b2c_member_withdrawal` WHERE member_id='".$this->app->member_id."'";
        $witlogs = $db->select(" $sql order by  id desc LIMIT $page_no, $page_size");
        $rs = $db->select("SELECT count(*) FROM `sdb_b2c_member_withdrawal` WHERE member_id='".$this->app->member_id."'");
        if(!$rs[0]['count']){
        	$total_results = $rs[0]['count'];
        }
        
        $data['total_results'] = $total_results?$total_results:0;
        
        $total = $member->dump($this->app->member_id,'advance');
        
        $where = " where member_id = '".$this->app->member_id."' and has_op='true' ";
        $sum_row = $db->selectrow("SELECT SUM(amount) as total_withdrawal FROM `sdb_b2c_member_withdrawal` $where ");
        
        
        $data['total'] = $total['advance']['total'];
        $data['total_withdrawal'] = $sum_row['total_withdrawal']?$sum_row['total_withdrawal']:0;
       
        $data['witlogs'] = $witlogs;
        return $data;
    }

    /**
     * 申请提现
     */
    public function submit_withdrawal($param = array(), $rpcService)
    {
        $money = $param['money'];
        // 验证
        if (!preg_match('/^\d*$/', $money) || $money <= 0 || ($money % 100) != 0) {
            $rpcService->send_user_error('4003', '提交的金额不是数字或者金额小于0了！以佰元为单位！');
        }
        $member = app::get('b2c')->model('members');
        $mem_wit = app::get('b2c')->model('member_withdrawal');
        $total = $member->dump($this->app->member_id,'advance');
        if (($total['advance']['total'] - $money) < 0) {
            $rpcService->send_user_error('4003', '您当前的预存款余额不足');
        }

        $arr['member_id'] = $this->app->member_id;
        $arr['amount'] = $money;
        $arr['create_time'] = time();
        $arr['has_op'] = 'false';
        $arr['alipay_user'] = trim($param['alipay_user']);
        
        if ($mem_wit->insert($arr)) {
            return '申请成功，请等待管理员操作!';
        } else {
            $rpcService->send_user_error('4003', '申请错误稍后再试');
        }
        
    }

    private function get_start($nPage,$count)
    {
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    /**
     * 添加收藏商品
     */
    public function add_fav($param = array(), $rpcService)
    {
        $object_type = $param['type'];
        $nGid = $param['gid'];

        if(!$nGid) return false;
        $obj_member = app::get('b2c')->model('member_goods');
        if (!$obj_member->add_fav($this->app->member_id,$object_type,$nGid)){
            $rpcService->send_user_error('4003', '商品收藏添加失败！');
        }else{

            return '商品收藏添加成功';
        }
    }

    /**
     * 删除收藏商品
     */
    public function del_fav($param='', $rpcService)
    {
        $gid = $param['gid'];
        $object_type = $param['gid'] ? $param['gid'] : 'goods';
        if (!$gid) {
            $rpcService->send_user_error('4003', '参数错误！');
        }
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$gid,$maxPage)){
            $rpcService->send_user_error('4003', '移除失败！');
        }else{
            return '成功移除！';
        }
    }

    /**
     * 我收藏商品列表
     */
    public function favorite($param = array())
    {
        $nPage = $param['n_page'] ? $param['n_page'] : 1;
        $membersData = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_lv_id'));
        $aData = @kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$membersData['members']['member_lv_id'],$nPage, 10);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];
        foreach($aProduct as $k=>$v){
            $aProduct[$k]['image_default_url'] = base_storager::image_path($v['image_default_id'], 'm' );
            if($v['nostore_sell']){
                $aProduct[$k]['store'] = 999999;
                $aProduct[$k]['product_id'] = $v['spec_desc_info'][0]['product_id'];
            }else{
                foreach((array)$v['spec_desc_info'] as $value){
                    $aProduct[$k]['product_id'] = $value['product_id'];
                    $aProduct[$k]['spec_info'] = $value['spec_info'];
                    $aProduct[$k]['price'] = $value['price'];
                    if(is_null($value['store']) ){
                        $aProduct[$k]['store'] = 999999;
                        break;
                    }elseif( ($value['store']-$value['freez']) > 0 ){
                        $aProduct[$k]['store'] = $value['store']-$value['freez'];
                        break;
                    }else{
                        $aProduct[$k]['store'] = false;
                    }
                }
            }
        }

        $aData = app::get('b2c')->model('goods_cat')->getList('cat_id, cat_name', array('parent_id' => 0, 'disabled'=>'false'));
        foreach ($aData as $key => $value) {
            foreach ($aProduct as $v) {
                if ($value['cat_id'] == $v['parent_cat_id']) {
                    $aData[$key]['product'][] = $v;
                }
            }
        }
        return $aData;
    }
    
    /**
     * 我的收入
     */
    public function promotion_into_logs(){
    	
    	$sdf['page_no'] = $_POST['page_no'] ? $_POST['page_no'] : '1';
    	$sdf['page_size'] = $_POST['page_size'] ? $_POST['page_size'] : '20';
    	
    	$page_no = intval($sdf['page_no']) - 1;
    	$page_size = intval($sdf['page_size']);
    	
    	$where .= ' where 1 ';
    	$where .= " and pil.member_id = '".$this->app->member_id."' ";

    	$db = kernel::database();
    	$count_row = $db->selectrow("SELECT count(*) as row_count FROM `sdb_microshop_promotion_into_logs` as pil $where ");
    	$sum_row = $db->selectrow("SELECT SUM(pil.money) as total_money FROM `sdb_microshop_promotion_into_logs` as pil $where ");
    	 
    	$sql = "SELECT pil.money, pil.mtime, pi.order_id, pi.special_name, pi.name as product_name FROM `sdb_microshop_promotion_into_logs` as pil ".
      			" left join `sdb_microshop_promotion_into` as pi on pi.pri_id = pil.pri_id ".
    	      	" $where order by log_id desc LIMIT $page_no,$page_size;";
    	
    	$pils = $db->select($sql);
    	
    	if($pils){
	    	foreach ($pils as $key=>$v){
	    		$pils[$key]['format_mtime'] = date('m-d',$v['mtime']);
	    	}
    	}
    	
    	return array('total_results'=>$count_row['row_count'],'total_money' => $sum_row['total_money']>0 ? $sum_row['total_money']:0, 'items'=>$pils);
    	
    }

    /**
     * 未来评价的商品
     */
    public function nodiscuss($param = array(), $rpcService)
    {
        $nPage = $param['n_page'] ? $param['n_page'] : 1;

        //获取会员已发货的商品日志
        $sell_logs = app::get('b2c')->model('sell_logs')->getList('order_id,product_id,goods_id',array('member_id'=>$this->app->member_id));
        //获取会员已评论的商品
        $comments = app::get('b2c')->model('member_comments')->getList('order_id,product_id',array('author_id'=>$this->app->member_id,'object_type'=>'discuss','for_comment_id'=>'0'));
        $data = array();
        if($comments){
            foreach((array)$comments as $row){
                if($row['order_id'] && $row['product_id']){
                    $data[$row['order_id']][$row['product_id']] = $row['product_id'];
                }
            }
        }

        foreach((array)$sell_logs as $key=>$log_row){
            if($data && $data[$log_row['order_id']][$log_row['product_id']] == $log_row['product_id']){
                unset($sell_logs[$key]);
            }else{
                $filter['order_id'][] = $log_row['order_id'];
                $filter['product_id'][] = $log_row['product_id'];
                $filter['item_type|noequal'] = 'gift';
            }
        }

        $orderItemModel = app::get('b2c')->model('order_items');
        $limit = $this->pagesize;
        $start = ($nPage-1)*$limit;
        $i = 0;
        $nogift = $orderItemModel->getList('order_id,product_id',$filter);
        if($nogift){
            foreach($nogift as $row){
                $tmp_nogift_order_id[] = $row['order_id'];
                $tmp_nogift_product_id[] = $row['product_id'];
            }
        }
        foreach((array)$sell_logs as $key=>$log_row){
            if(in_array($log_row['order_id'],$tmp_nogift_order_id) && in_array($log_row['product_id'],$tmp_nogift_product_id) ){//剔除赠品,赠品不需要评论
                if($i >= $start && $i < ($nPage*$limit) ){
                    $sell_logs_data[] = $log_row;
                    $gids[] = $log_row['goods_id'];
                }
                if($nogift){
                    $i += 1;
                }
            }
        }
        $totalPage = ceil($i/$limit);
        if($nPage > $totalPage) $nPage = $totalPage;

        $result['pagination']['nPage'] = $nPage;
        $result['pagination']['totalPage'] = $totalPage;

        if($gids){
            //获取商品信息
            $goodsData = app::get('b2c')->model('goods')->getList('goods_id,name,image_default_id',array('goods_id'=>$gids));
            $goodsList = array();
            foreach((array)$goodsData as $goods_row){
                foreach ($sell_logs_data as $k => $v) {
                    if ($v['goods_id'] == $goods_row['goods_id']) {
                        $goodsList[$k]['goods_id'] = $goods_row['goods_id'];
                        $goodsList[$k]['product_id'] = $v['product_id'];
                        $goodsList[$k]['order_id'] = $v['order_id'];
                        $goodsList[$k]['goods_name'] = $goods_row['name'];
                        $goodsList[$k]['default_img_url'] = base_storager::image_path($goods_row['image_default_id'], 's' );
                    }
                }
            }
            shuffle($goodsList);
            $result['list'] = $goodsList;

            $result['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            $result['verifyCode'] = app::get('b2c')->getConf('comment.verifyCode');
            if($result['point_status'] == 'on'){
                //评分类型
                $comment_goods_type = app::get('b2c')->model('comment_goods_type');
                $result['comment_goods_type'] = $comment_goods_type->getList('*');
                if(!$result['comment_goods_type']){
                    $sdf['type_id'] = 1;
                    $sdf['name'] = app::get('b2c')->_('商品评分');
                    $addon['is_total_point'] = 'on';
                    $sdf['addon'] = serialize($addon);
                    $comment_goods_type->insert($sdf);
                    $result['comment_goods_type'] = $comment_goods_type->getList('*');
                }
            }

        return $result;
        //$result['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        }
    }

    /**
     * 会员等级
     */
    public function levels_info($value='')
    {
        $data['uname'] = $this->member['uname'];
        #获取会员等级
        $obj_mem_lv = app::get('b2c')->model('member_lv');
        $levels = $obj_mem_lv->getList('name,disabled',array('member_lv_id'=>$this->member['member_lv']));
        if($levels[0]['disabled']=='false'){
            $data['levelname'] = $levels[0]['name'];
        }

        #还差多少点升级
        $switch_lv = $obj_mem_lv->get_member_lv_switch($this->member['member_lv']);
        $data['experience'] = $this->member['experience'];
        if ($switch_lv['show'] == 'YES') {
            if ($switch_lv['switch_type'] == 0) {
                $data['next_lv'] = '还差'.($switch_lv['lv_data'] - $this->member['experience']).'积分升级为'.$switch_lv['lv_name'];
            } else {
                $data['next_lv'] = '还差'.($switch_lv['lv_data'] - $this->member['experience']).'经验值升级为'.$switch_lv['lv_name'];

            }
        }
         
        #获取所有的会员等级信息
        foreach ($switch_lv['level_list'] as $key => $value) {
            $data['level_list'][$key]['name'] = $value['name'];
            $data['level_list'][$key]['dis_count'] = $value['dis_count'];
            $data['level_list'][$key]['point'] = $value['point'];
            $range = $switch_lv['level_list'][$key+1]['point'] ? $switch_lv['level_list'][$key+1]['point']-1 : '无上限';
            $data['level_list'][$key]['range'] = $value['point'].'-'.$range;
        }

        return $data;
    }

    /**
     * 绑定修改手机、邮箱
     * 验证会员密码
     */
    public function verify($param='')
    {
        unset($_SESSION['vcodeVerifykey']['activation']);
        $pamMembersModel = app::get('pam')->model('members');
        $pamData = $pamMembersModel->getList('login_password,password_account,createtime',array('member_id'=>$this->app->member_id));
        $use_pass_data['login_name'] = $pamData[0]['password_account'];
        $use_pass_data['createtime'] = $pamData[0]['createtime'];
        $login_password = pam_encrypt::get_encrypted_password(trim($param['password']),'member',$use_pass_data);
        if($login_password !== $pamData[0]['login_password']){
            $this->app->rpcService->send_user_error('password_error', '登录密码错误');
        }else{
            $_SESSION['vcodeVerifykey']['activation'] = 'true';
            return;
        }
    }

    /**
     * 绑定修改手机、邮箱
     * 提交保存信息
     */
    public function verify_vcode($param=''){
        if (!$_SESSION['vcodeVerifykey']['activation']) {
            $this->app->rpcService->send_user_error('password_error', '为保障您的账户安全，请先验证您的身份');
        }
        $send_type = 'reset';
        $userVcode = kernel::single('b2c_user_vcode');
        if( !$userVcode->verify($param['vcode'],$param['uname'],$send_type)){
            $msg = app::get('b2c')->_('验证码错误'); 
            $this->app->rpcService->send_user_error('fail', $msg);
        }

        $userPassport = kernel::single('b2c_user_passport');
        $accountType = $userPassport->get_login_account_type($param['uname']);
        if($send_type == 'reset'){
            if( !$userPassport->set_new_account($this->app->member_id,trim($param['uname']),$msg) ){
                $msg = $msg ? $msg : app::get('b2c')->_('修改信息失败');
                $this->app->rpcService->send_user_error('fail', $msg);
            }
        }
        //增加会员同步 2012-05-15
        if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            $member_rpc_object->modifyActive($this->app->member_id);
        }
        
        return '修改成功';
    }

    /**
     * 美通券充值
     */
    public function mtk_recharge($params, $rpcService){
        $card_no = 374417024603;//$params['card_no'];
        if (!$card_no) $rpcService->send_user_error('4003', '请输入美通卡号');

        $client = new SoapClient("http://202.106.132.100/AppTest/WMAppCommWebService.asmx?wsdl");
        $arrPara = array((object) array(
            'authKey' => '',
            'cardNo' => substr($card_no, 0, -4),
        ));

        $result = $client->__Call("SearchMTKInfo",$arrPara);
        $xml = kernel::single('site_utility_xml');
        $arrData = $xml->xml2arrayValues($result->SearchMTKInfoResult, 0);
        $params = $arrData['OutputResult'];
        print_r($params);exit();

        # 申请
        $applyObj = $this->invokePosCommPayOper($card_no, 3);
        $applyData = (array)$applyObj->InvokePosCommPayOperResult;
        if ($applyData['RtnCode'] == 0) {
            $applyArr = $xml->xml2arrayValues($applyData['RtnXml'], 0);
            # 申请到平台支付序号后再调用支付确认
            $confirmObj = $this->invokePosCommPayOper($applyArr['OutputParam']['PlPayNO'], 5);
            print_r($confirmObj);exit();
        } else {
            $rpcService->send_user_error('4003', '操作失败，请稍后再试！');
        }
        
        if ($i) {
            # code...
        } else {
            # code...
        }
        

        # 充值操作
        $mem_adv = app::get('b2c')->model('member_advance');
        $aAdvanceInfo = array('modify_advance' => '19', 'modify_memo' => '');
        $mem_adv->adj_amount($this->app->member_id, $aAdvanceInfo, $msg, false);
    }

    function invokePosCommPayOper($no, $funcCode) {
        if ($funcCode == 3) {
            $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>
                       <InputParam>
                            <Head>
                                <ClientCode>1</ClientCode>
                                <PosPSID>23</PosPSID>
                                <WorkGuid>88888888</WorkGuid>
                            </Head>
                            <Body>
                                <InputParam>
                                    <OrgNO>wumart</OrgNO>
                                    <SiteNO>1600</SiteNO>
                                    <PosNO>1</PosNO>
                                    <SaleID>'.time().'</SaleID>
                                    <SaleDate>'.date('Y-m-d H:i:s', time()).'</SaleDate>
                                    <CashID>0</CashID>
                                    <PayNO>374417024603</PayNO>
                                    <PayPswd></PayPswd>
                                    <PayAmt>0</PayAmt>
                                </InputParam>
                            </Body>
                        </InputParam>';
        } elseif ($funcCode == 5) {
            $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>
                        <InputParam>
                            <Head>
                                <ClientCode>0</ClientCode>
                                <PosPSID>9</PosPSID>
                                <WorkGuid>88888888</WorkGuid>
                            </Head>
                            <Body>
                                <InputParam>
                                    <PlPayNO>'.$no.'</PlPayNO>
                                    <PayAmt>1</PayAmt>
                                    <AccType>0</AccType>
                                </InputParam>
                            </Body>
                        </InputParam>';
        }
        
        $dataObj = array((object) array(
            'funcCode' => $funcCode,
            'xmlStr' => $xmlStr
        ));

        $client = new SoapClient("http://10.254.10.2:8060/WMPayWebService.asmx?wsdl");
        $result = $client->__Call('InvokePosCommPayOper', $dataObj);
        return $result;
    }
    
    
    //站内信-我的收信列表
    function inbox() {
    	
    	$nPage=isset($_REQUEST['page_no'])?intval($_REQUEST['page_no']):1;
    	$this->pagesize = isset($_REQUEST['page_size'])?intval($_REQUEST['page_size']):20;
    	
    	$oMsg = kernel::single('b2c_message_msg');
    	$row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
    	$this->pagedata['inbox_num'] = count($row)?count($row):0;
    
    	$row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
    	$aData['data'] = $row;
    	$aData['total'] = count($row);
    	$count = count($row);
    	$aPage = $this->get_start($nPage,$count);
    	$params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$this->pagesize);
    	
    	$ret['message'] = $params['data'];
    	$ret['total_msg'] = $aData['total'];
    	$ret['unread_num'] = $this->pagedata['inbox_num'];
    	
    	return $ret;
    }
    
    //站内信-设置已读
    function view_msg(){
    	$nMsgId = $_POST['comment_id'];
    	$objMsg = kernel::single('b2c_message_msg');
    	$aMsg = $objMsg->getList('comment',array('comment_id' => $nMsgId,'for_comment_id' => 'all','to_id'=>$this->app->member_id));
    	if($aMsg[0]&&($aMsg[0]['author_id']!=$this->app->member_id&&$aMsg[0]['to_id']!=$this->app->member_id)){
    		$this->app->rpcService->send_user_error('4003', '更新失败');
    	}
    	$objMsg->setReaded($nMsgId);
    	
    	return "succ";
    }

    /**
     * 美通卡查询
     */
    function mtkinfo($params){
        $card_no = $params['card_no'];
        $card_no = 37441702;
        if (!$card_no) $rpcService->send_user_error('4003', '请输入美通卡号');

        $client = new SoapClient("http://202.106.132.100/AppTest/WMAppCommWebService.asmx?wsdl");
        $arrPara = array((object) array(
            'authKey' => '',
            'cardNo' => $card_no,
        ));

        $result = $client->__Call("SearchMTKInfo",$arrPara);
        $xml = kernel::single('site_utility_xml');
        $arrData = $xml->xml2arrayValues($result->SearchMTKInfoResult, 0);
        $params = $arrData['OutputResult'];
        if ($params['ResultFlag'] == 1) {
            return $params;
        }
        return '卡号不存在!';
    }
}
