<?php

use PHPUnit\Samples\Money\Money;
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_order extends b2c_frontpage{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        #$shopname = app::get('site')->getConf('site.name');
        #if(isset($sysconfig)){
        #    $this->title = app::get('b2c')->_('订单').'_'.$shopname;
        #    $this->keywords = app::get('b2c')->_('订单_').'_'.$shopname;
        #    $this->description = app::get('b2c')->_('订单_').'_'.$shopname;
        #}
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('b2c')->_('订单中心');
        $this->objMath = kernel::single("ectools_math");
    }

    public function create()
    {
        /**
         * 取到扩展参数,用来判断是否是团购立即购买，团购则不判断登录（无注册购买情况下）
         */
        
        $arr_args = func_get_args();
        $arr_args = array(
            'get' => $arr_args,
            'post' => $_POST,
        );
        
        $groupbuy = (array)json_decode($arr_args['post']['extends_args']);
        // 判断顾客登录方式.
        #$login_type = $this->app->getConf('site.login_type');
        $arrMember = $this->get_current_member();
        // checkout url
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $this->mCart = $this->app->model('cart');
        if(isset($_POST['isfastbuy']) && $_POST['isfastbuy']){
            $is_fastbuy = 'true';
            $fastbuy_filter['is_fastbuy'] = $is_fastbuy;
        }else{
            $is_fastbuy = false;
            $fastbuy_filter = true;
        }
        $aCart = $this->mCart->get_objects($fastbuy_filter);
        // hack by jason 判断如果是门店来的订单,则将库存设置为最大
        if(isset($_COOKIE['loginType']) && $_COOKIE['loginType'] == 'store'){
        	unset($aCart['cart_status']);
        	unset($aCart['cart_error_html']);
        	foreach ($aCart['object']['goods'] as $key=>$goods){
        		$goods['store']['real'] = 999999;
        		$goods['store']['store'] = 999999;
        		foreach ($goods['obj_items']['products'] as $k=>$products){
        			$aCart['object']['goods'][$key]['obj_items']['products'][$k]['store'] = 999999;
        		}
        	}
        }
        //hack by Jason end
        //当有活动时，在生成订单前做一个当前购买数量与实际库存的判断
        if( isset($aCart['cart_status'] )){

            $this->end(false,app::get('b2c')->_($aCart['cart_error_html']),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);

        }

        // 校验购物车是否为空
        if ($this->mCart->is_empty($aCart))
        {
            $this->end(false,app::get('b2c')->_('操作失败，购物车为空！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);
        }
        // 校验购物车有没有发生变化
        $md5_cart_info = $_POST['md5_cart_info'];
        if (!defined("STRESS_TESTING") && $md5_cart_info != kernel::single("b2c_cart_objects")->md5_cart_objects($is_fastbuy))
            //$this->end(false,app::get('b2c')->_('购物车内容发生变化，请重新结算！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);

        $msg = "";
        if(!$_POST['address']){
            $msg .= app::get('b2c')->_("请先确认收货地址")."<br />";
            $this->end(false, $msg,null,true,true);
        }else{
            $address = json_decode($_POST['address'],true);
            unset($_POST['address']);
            unset($_POST['purchase']);
            $addr = $this->app->model('member_addrs')->getList('*',array('addr_id'=>$address['addr_id'],'member_id'=>$arrMember['member_id']));
            if($this->app->getConf('site.checkout.zipcode.required.open') == 'true' && empty($addr[0]['zip']) ) {
                $this->end(false,app::get('b2c')->_('收货地址不完整，请填写邮编'),null,true,true);
            }
            
            $ship_area_name = app::get('ectools')->model('regions')->change_regions_data($addr[0]['area']);
            $_POST['delivery']['addr_id'] = $addr[0]['addr_id'];
            $_POST['delivery']['ship_area'] = $addr[0]['area'];
            $_POST['delivery']['ship_addr'] = $ship_area_name.$addr[0]['addr'];
            $_POST['delivery']['ship_zip'] = $addr[0]['zip'];
            $_POST['delivery']['ship_name'] = $addr[0]['name'];
            $_POST['delivery']['ship_mobile'] = $addr[0]['mobile'];
            $_POST['delivery']['ship_tel'] = $addr[0]['tel'];
            $_POST['delivery']['day'] = $addr[0]['day'];
            $_POST['delivery']['time'] = $addr[0]['time'];
        }

        if(!$_POST['shipping']){
            $msg .= app::get('b2c')->_("请先确认配送方式")."<br />";
            $this->end(false, $msg, '',true,true);
        }else{
            $shipping = json_decode($_POST['shipping'],true);
            unset($_POST['shipping']);
            $_POST['delivery']['shipping_id'] = $shipping['id'];
            $_POST['delivery']['is_protect'][$shipping['id']] = $_POST['is_protect'];
        }

        if(!$_POST['payment']){
            $msg .= app::get('b2c')->_("请先确认支付方式")."<br />";
            $this->end(false, $msg, '',true,true);
        }else{
            $payment_id = json_decode($_POST['payment']['pay_app_id'],true);
            $_POST['payment']['pay_app_id'] = $payment_id['pay_app_id'];
        }

        if($_POST['point']['score']){
            $_POST['payment']['dis_point'] = $_POST['point']['score'];
        }

        if (!$_POST['delivery']['ship_area'] ||  !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] ||  (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || !$_POST['delivery']['shipping_id'] || !$_POST['payment']['pay_app_id'])
        {
            if (!$_POST['delivery']['ship_area'] )
            {
                $msg .= app::get('b2c')->_("收货地区不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_addr'])
            {
                $msg .= app::get('b2c')->_("收货地址不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_name'])
            {
                $msg .= app::get('b2c')->_("收货人姓名不能为空！")."<br />";
            }

            /*
            if (!$_POST['delivery']['ship_email'] && !$arrMember['member_id'])
            {
                $msg .= app::get('b2c')->_("Email不能为空！")."<br />";
            }
            */

            if (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel'])
            {
                $msg .= app::get('b2c')->_("手机或电话必填其一！")."<br />";
            }

            if (!$_POST['delivery']['shipping_id'])
            {
                $msg .= app::get('b2c')->_("配送方式不能为空！")."<br />";
            }

            if (!$_POST['payment']['pay_app_id'])
            {
                $msg .= app::get('b2c')->_("支付方式不能为空！")."<br />";
            }

            if (strpos($msg, '<br />') !== false)
            {
                $msg = substr($msg, 0, strlen($msg) - 6);
            }
            eval("\$msg = \"$msg\";");
            $this->end(false, $msg, '',true,true);
        }

        $obj_dlytype = $this->app->model('dlytype');
        if ($_POST['payment']['pay_app_id'] == '-1')
        {
            $arr_dlytype = $obj_dlytype->dump($_POST['delivery']['shipping_id'], '*');
            if ($arr_dlytype['has_cod'] == 'false')
            {
                $this->end(false, $this->app->_("ship_method_consistent_error"),'',true,true);
            }
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);
        
        $order = &$this->app->model('orders');
        $_POST['order_id'] = $order_id = $order->gen_id();
        $_POST['member_id'] = $arrMember['member_id'] ? $arrMember['member_id'] : 0;
        $order_data = array();
        $obj_order_create = kernel::single("b2c_order_create");
        
        
        // 加入订单能否生成的判断
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if ($obj_checkorder)
        {
            if (!$obj_checkorder->check_create($aCart, $_POST['delivery']['ship_area'], $message))
                $this->end(false, $message);
        }
        $order_data = $obj_order_create->generate($_POST,'',$msg,$aCart);
        
        /*
        if($_POST['yucont'] == 1  ){
            if($order_data['total_amount'] <= $arrMember['advance']){
                $member_id = $order_data['member_id'];
                $psm = -$order_data['total_amount'];
                $msg = '预存款门店订单付款';
                $objAdvance = $this->app->model("member_advance");
                $status = $objAdvance->add($member_id, $psm, app::get('b2c')->_('预存款门店订单付款'), $msg);
            }else{
                return FALSE;
            }
         }
        */
        
        $obj_checkproducts = kernel::servicelist('b2c_order_check_products');
        if ($obj_checkproducts)
        {
            foreach($obj_checkproducts as $obj_check){
                if (!$obj_check->check_products($order_data, $messages))
                    $this->end(false, $messages);
            }
        }
        if (!$order_data || !$order_data['order_objects'])
        {
            $db->rollback();
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
        }
        if($_POST['czkgoods']==1){
            $order_data['czkcz_is'] = $_POST['czkgoods'];
        }
        
        // 订单自动完成与仓库数据
        $order_data['is_auto_complete'] = $_POST['is_auto_complete'];
        $order_data['staff_id'] = intval($_POST['staff']);
        $order_data['staff_name'] = $_POST['staff_name'];

        //所属仓库门店
        //if(isset($_POST['branch_id']) && intval($_POST['branch_id']) > 0 && $order_data['shipping']['shipping_name'] == '门店自提'){
        if( isset($_POST['branch_id']) && intval($_POST['branch_id']) > 0 ){
            $order_data['branch_id'] = intval($_POST['branch_id']);
            $branch_mdl = kernel::single("ome_mdl_branch");
            if($branch_mdl){
                $branch_obj = $branch_mdl->dump(array('branch_id'=>$order_data['branch_id']));
                $order_data['branch_name_user'] = $branch_obj['name'];
            }
        }else{
            $order_data['branch_id'] = 0;
            $order_data['branch_name_user'] = '';
        }

        $result = $obj_order_create->save($order_data, $msg);
        if ($result)
        {
            // 发票高级配置埋点
            foreach( kernel::servicelist('invoice_setting') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'saveInvoiceData') ) {
                        $services->saveInvoiceData($_POST['order_id'],$_POST['payment']);
                    }
                }
            }
            // 与中心交互
            $is_need_rpc = false;
            $obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
            foreach ($obj_rpc_obj_rpc_request_service as $obj)
            {
                if ($obj && method_exists($obj, 'rpc_judge_send'))
                {
                    if ($obj instanceof b2c_api_rpc_notify_interface)
                        $is_need_rpc = $obj->rpc_judge_send($order_data);
                }

                if ($is_need_rpc) break;
            }

            if ($is_need_rpc)
            {
              /*
                $obj_rpc_request_service = kernel::service('b2c.rpc.send.request');

                if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
                {
                    if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                        $obj_rpc_request_service->rpc_caller_request($order_data,'create');
                }
                else
                {
                    $obj_order_create->rpc_caller_request($order_data);
                    }*/
              //新的版本控制api
              $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
              $obj_apiv->rpc_caller_request($order_data, 'ordercreate');
            }
        }

        // 取到日志模块
        if ($arrMember['member_id'])
        {
            $obj_members = $this->app->model('members');
            #$arrPams = $obj_members->dump($arrMember['member_id'], '*', array(':account@pam' => array('*')));
            $arrPams['pam_account']['login_name'] = $arrMember['uname'];
        }

        // remark create
        $obj_order_create = kernel::single("b2c_order_remark");
        $arr_remark = array(
            'order_bn' => $order_id,
            'mark_text' => $_POST['memo'],
            'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'mark_type' => 'b0',
        );
        

        $log_text = "";
        if ($result)
        {
            $log_text[] = array(
                'txt_key'=>'订单创建成功！',
                'data'=>array(
                ),
            );
            $log_text = serialize($log_text);
        }
        else
        {
            $log_text[] = array(
                'txt_key'=>'订单创建失败！',
                'data'=>array(
                ),
            );
            $log_text = serialize($log_text);
        }
        $orderLog = $this->app->model("order_log");
        $sdf_order_log = array(
            'rel_id' => $order_id,
            'op_id' => $arrMember['member_id'],
            'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'creates',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );

        $log_id = $orderLog->save($sdf_order_log);

        if ($result)
        {
            foreach(kernel::servicelist('b2c_save_post_om') as $object)
            {
                $object->set_arr($order_id, 'order');
            }

            // 设定优惠券不可以使用
            if (isset($aCart['object']['coupon']) && $aCart['object']['coupon'])
            {
                $obj_coupon = kernel::single("b2c_coupon_mem");
                foreach ($aCart['object']['coupon'] as $coupons)
                {
                    if($coupons['used'])
                        $obj_coupon->use_c($coupons['coupon'], $arrMember['member_id']);
                }
            }

            // 订单成功后清除购物车的的信息
            $this->cart_model = &$this->app->model('cart_objects');
            $this->cart_model->remove_object('',null,$mag,$is_fastbuy);
            $this->app->model('cart')->unset_data();

            // 生成cookie有效性的验证信息
            setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$order_id));
            setcookie("purchase[addr][usable]", "", time() - 3600, kernel::base_url().'/');
            setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
            setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
            setcookie("checkout_b2c_goods_buy_info", "", time() - 3600, kernel::base_url().'/');

            // 得到物流公司名称
            if ($order_data['order_objects'])
            {
                $itemNum = 0;
                $good_id = "";
                $goods_name = "";
                foreach ($order_data['order_objects'] as $arr_objects)
                {
                    if ($arr_objects['order_items'])
                    {
                        if ($arr_objects['obj_type'] == 'goods')
                        {
                            $obj_goods = $this->app->model('goods');
                            $good_id = $arr_objects['order_items'][0]['goods_id'];
                            $obj_goods->updateRank($good_id, 'buy_count',$arr_objects['order_items'][0]['quantity']);
                            $arr_goods = $obj_goods->parent_getList('image_default_id',array('goods_id'=>$good_id));
                            $arr_goods = $arr_goods[0];
                        }

                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            $itemNum = $this->objMath->number_plus(array($itemNum, $arr_items['quantity']));
                            if ($arr_objects['obj_type'] == 'goods')
                            {
                                if ($arr_items['item_type'] == 'product')
                                    $goods_name .= $arr_items['name'] . ($arr_items['products']['spec_info'] ? '(' . $arr_items['products']['spec_info'] . ')' : '') . '(' . $arr_items['quantity'] . ')';
                            }
                        }
                    }
                }
                $arr_dlytype = $obj_dlytype->dump($order_data['shipping']['shipping_id'], 'dt_name');
                $arr_updates = array(
                    'order_id' => $order_id,
                    'total_amount' => $order_data['total_amount'],
                    'shipping_id' => $arr_dlytype['dt_name'],
                    'ship_mobile' => $order_data['consignee']['mobile'],
                    'ship_tel' => $order_data['consignee']['telephone'],
                    'ship_addr' => $order_data['consignee']['addr'],
                    'ship_email' => $order_data['consignee']['email'] ? $order_data['consignee']['email'] : '',
                    'ship_zip' => $order_data['consignee']['zip'],
                    'ship_name' => $order_data['consignee']['name'],
                    'member_id' => $order_data['member_id'] ? $order_data['member_id'] : 0,
                    'uname' => (!$order_data['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                    'itemnum' => count($order_data['order_objects']),
                    'goods_id' => $good_id,
                    'goods_url' => kernel::base_url(1).kernel::url_prefix().$this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$good_id)),
                    'thumbnail_pic' => base_storager::image_path($arr_goods['image_default_id']),
                    'goods_name' => $goods_name,
                    'ship_status' => '',
                    'pay_status' => 'Nopay',
                    'is_frontend' => true,
                );
                $order->fireEvent('create', $arr_updates, $order_data['member_id']);
            }

            $db->commit($transaction_status);
            
            //门店充值卡充值
            if($_POST['is_store_cz'] == 2  ){
                $orders_czkcz = $this->app->model('orders');
                $save_data['czkcz_is']=2;
                $orders_czkcz->update($save_data, array('order_id' => $order_id));
                $member_id = $order_data['member_id'];
                $msg = '门店充值卡充值';
                $objAdvances = $this->app->model("member_advance");
                $objAdvances->add($member_id, $order_data['total_amount'], app::get('b2c')->_('门店充值卡充值'), $msg);
              }
              
            /** 订单创建结束后执行的方法 **/
            $odr_create_service = kernel::servicelist('b2c_order.create');
            $arr_order_create_after = array();
            if ($odr_create_service)
            {
                foreach ($odr_create_service as $odr_ser)
                {
                    if(!is_object($odr_ser)) continue;

                    if( method_exists($odr_ser,'get_order') )
                        $index = $odr_ser->get_order();
                    else $index = 10;

                    while(true) {
                        if( !isset($arr_order_create_after[$index]) )break;
                        $index++;
                    }
                    $arr_order_create_after[$index] = $odr_ser;
                }
            }
            ksort($arr_order_create_after);
            if ($arr_order_create_after)
            {
                foreach ($arr_order_create_after as $obj)
                {
                    $obj->generate($order_data);
                }
            }
            /** end **/
        }
        else
        {
            $db->rollback();
        }

        if ($result)
        {
            $order_num = $order->count(array('member_id' => $order_data['member_id']));
            $obj_mem = $this->app->model('members');
            $obj_mem->update(array('order_num'=>$order_num), array('member_id'=>$order_data['member_id']));

            /** 订单金额为0 / 门店自动支付 **/
            if ($order_data['cur_amount'] == '0' || $_POST['is_store'] == 1 )
            {
                // 模拟支付流程
                $objPay = kernel::single("ectools_pay");
              
                $sdf = array(
                    'payment_id' => $objPay->get_payment_id($order_data['order_id']),
                    'order_id' => $order_data['order_id'],
                    'rel_id' => $order_data['order_id'],
                    'op_id' => $order_data['member_id'],
                    'pay_app_id' => $order_data['payinfo']['pay_app_id'],
                    'currency' => $order_data['currency'],
                    'payinfo' => array(
                        'cost_payment' => $order_data['payinfo']['cost_payment'],
                    ),
                    'pay_object' => 'order',
                    'member_id' => $order_data['member_id'],
                    'op_name' => $this->user->user_data['account']['login_name'],
                    'status' => 'ready',
                    'cur_money' => $order_data['cur_amount'],
                    'money' => $order_data['total_amount'],
                );
                $c_pay = 0;
                if ( $_POST['is_store'] == 1 ) {
                    if ( $_POST['yucont'] == 1 ) {
                        if ( $order_data['total_amount'] > $arrMember['advance'] ) {
                            $sdf['cur_money']   = $arrMember['advance'];
                            $sdf['money']       = $arrMember['advance'];
                            $c_pay  = 1;
                        }
                    }
                }
                // 暂停1s，防止erp同步时间未更新
                sleep(1);
                $is_payed = $objPay->gopay($sdf, $msg);
                if (!$is_payed){
                    $msg = app::get('b2c')->_('订单自动支付失败！');
                    $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')));
                }

                $obj_pay_lists = kernel::servicelist("order.pay_finish");
                $is_payed = false;
                foreach ($obj_pay_lists as $order_pay_service_object)
                {
                    $is_payed = $order_pay_service_object->order_pay_finish($sdf, 'succ', 'font',$msg);
                }
                // 预存款不够的情况下，线下继续支付
                if ( $c_pay == 1 ) {
                    $_money = $order_data['cur_amount'] - $arrMember['advance'];
                    $sdf = array (
                            'payment_id' => $objPay->get_payment_id($order_data['order_id']),
                            'order_id' => $order_data['order_id'],
                            'rel_id' => $order_data['order_id'],
                            'op_id' => $order_data['member_id'],
                            'pay_app_id' => 'offline',
                            'currency' => $order_data['currency'],
                            'payinfo' => array(
                                'cost_payment' => $order_data['payinfo']['cost_payment'],
                                ),
                            'pay_object' => 'order',
                            'member_id' => $order_data['member_id'],
                            'op_name' => $this->user->user_data['account']['login_name'],
                            'status' => 'ready',
                            'cur_money' => $_money,
                            'money' => $_money,
                    );
                    $is_payed = $objPay->gopay($sdf, $msg);
                    if (!$is_payed){
                        $msg = app::get('b2c')->_('订单自动支付失败！');
                        $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')));
                    }

                    $obj_pay_lists = kernel::servicelist("order.pay_finish");
                    $is_payed = false;
                    foreach ($obj_pay_lists as $order_pay_service_object)
                    {
                        $is_payed = $order_pay_service_object->order_pay_finish($sdf, 'succ', 'font',$msg);
                    }
                }
            }
          $cart_type = $this->_request->get_post('type');
          if($_POST['is_store'] == 1){
            /*
            $objOrders = $this->app->model('orders');
              // 更新订单支付信息
            $arr_updates = array(
                'order_id' => $order_id,
                'ship_status' => '1',
                'pay_status' => '1',
            );
           $objOrders->save($arr_updates);
            */
          }
          $cart_type = $this->_request->get_post('type');
          if($cart_type == 'x'){
            echo json_encode(array('error'=>app::get('b2c')->_($order_data['order_id'])));
    	    
            $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'index','arg0'=>$order_id,'arg1'=>'true')).'?type=x','',true);
          }else{
            $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'index','arg0'=>$order_id,'arg1'=>'true')),'',true);  
          }
        }
        else
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
    }
    
    /*
     * 
     * 创建报损/退货订单
     */
    function create_goods(){
     
        // 判断顾客登录方式.
        #$login_type = $this->app->getConf('site.login_type');
        $arrMember = $this->get_current_member();
        
        $this->mCart = $this->app->model('cart');
        if(isset($_POST['isfastbuy']) && $_POST['isfastbuy']){
            $is_fastbuy = 'true';
            $fastbuy_filter['is_fastbuy'] = $is_fastbuy;
        }else{
            $is_fastbuy = false;
            $fastbuy_filter = true;
        }
        $aCart = $this->mCart->get_objects($fastbuy_filter);

        // 校验购物车是否为空
        if ($this->mCart->is_empty($aCart))
        {
            return FALSE;
        }
       
        // 校验购物车有没有发生变化
        $md5_cart_info = $_POST['md5_cart_info'];
        if (!defined("STRESS_TESTING") && $md5_cart_info != kernel::single("b2c_cart_objects")->md5_cart_objects($is_fastbuy))
        $return_goods = $this->app->model('return_goods');
         $obj_filter = kernel::single('b2c_site_filter');
        $order = &$this->app->model('orders');
        $this->begin();
        
        $rand=date("YmdHis");
        $cart_goods = $aCart['object']['goods'];
        //print_r($aCart);exit;
        foreach ($cart_goods as $key=>$obj_goods){
           $return_order_items = $this->app->model('return_order_items'); 
           $save= array(
               'order_sn'   => $rand,
               'product_id' => $obj_goods['params']['product_id'],
               'goods_id'   => $obj_goods['params']['goods_id'],
               'type_id'    => 2,
               'type'       => $_POST['is_store'],
               'bn'         => $obj_goods['obj_items']['products'][0]['bn'],
               'name'       => $obj_goods['obj_items']['products'][0]['name'],
               'cost'       => $obj_goods['obj_items']['products'][0]['price']['cost'],
               'price'      => $obj_goods['obj_items']['products'][0]['price']['price'],
               'g_price'    => $obj_goods['obj_items']['products'][0]['price']['price'],
               'addon'      => $obj_goods['obj_items']['products'][0]['unit'],
               'local_id'   => $_POST['local_id'],
               'local_name'   => $_POST['local_name'],
               'amount'     => $obj_goods['obj_items']['products'][0]['price']['buy_price'],
               'nums'       => $obj_goods['quantity'],
               'create_time'=> time(),
            );
           $return_order_items->insert($save);
        }
        
        if ($_POST) {
            /*需要存储的数据*/
            $save_data = array(
                'type' => $_POST['is_store'],
                'order_sn' => $rand,
                'local_id' => $_POST['local_id'],
                'local_name' => $_POST['local_name'],
                'price' => $aCart['_cookie']['CART_TOTAL_PRICE'],
                'create_time'  => time(),
            );
            $return_goods->insert($save_data);
            
        }
        // 订单成功后清除购物车的的信息
        $this->cart_model = &$this->app->model('cart_objects');
        $this->cart_model->remove_object('',null,$mag,$is_fastbuy);
        $this->app->model('cart')->unset_data();
        if($_POST['is_store']==1){
            
            $this->end(true, $this->app->_("退货单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'index','arg0'=>$rand,'arg1'=>'true')),'',true);
        }else{
            $this->end(true, $this->app->_("报损单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'index','arg0'=>$rand,'arg1'=>'true')),'',true); 
        }
    } 
    
    function select_payment(){
        if($_POST['payment']['currency']){
            $sdf['cur'] = $_POST['payment']['currency'];
        }
        if($_POST['shipping']['shipping_id']){
            $has_cod = app::get('b2c')->model('dlytype')->getList('has_cod',array('dt_id'=>$_POST['shipping']['shipping_id']));
            $this->pagedata['has_cod'] = $has_cod[0]['has_cod'] =='true' ? 'true' : 'false';
        }
        
        $obj_payment_select = new ectools_payment_select();
        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
        $this->pagedata['current_currency'] = $sdf['cur'] ? $sdf['cur'] : '';
        $this->pagedata['app_id'] = 'b2c';//$app_id;
        $this->pagedata['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
        $this->pagedata['payment_html'] = $obj_payment_select->select_pay_method($this,$sdf, $member_id=0, $is_backend=false, $platform=array('iscommon','ispc'), $front_tpl="site/common/choose_payment.html",$_POST['czkcz']);
        echo $this->fetch('site/common/select_payment.html');
        exit;
    }

    function payment_change(){
        $objOrders = $this->app->model('orders');
        $objPay = kernel::single('ectools_pay');
        $objMath = kernel::single('ectools_math');
        $payments = $_POST['payment'];
        $order_id = $_POST['order_id'];
        $currency = $payments['currency'];
        $pay = json_decode($payments['pay_app_id'],true);
        $arrOrders = $objOrders->dump($order_id,'*');
        if ($arrOrders['payinfo']['pay_app_id'] != $pay['pay_app_id'] || $arrOrders['currency'] != $currency)
        {
            $class_name = "";
            $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
            foreach ($obj_app_plugins as $obj_app)
            {
                $app_class_name = get_class($obj_app);
                $arr_class_name = explode('_', $app_class_name);
                if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
                {
                    if ($arr_class_name[count($arr_class_name)-1] == $pay['pay_app_id'])
                    {
                        $pay_app_ins = $obj_app;
                        $class_name = $app_class_name;
                    }
                }
                else
                {
                    if ($app_class_name == $pay['pay_app_id'])
                    {
                        $pay_app_ins = $obj_app;
                        $class_name = $app_class_name;
                    }
                }
            }
            $strPayment = app::get('ectools')->getConf($class_name);
            $arrPayment = unserialize($strPayment);
            $getcur = app::get('ectools')->model('currency')->getcur($currency);
            $arrOrders['currency'] = $getcur['cur_code'];
            $arrOrders['cur_rate'] = $getcur['cur_rate'];
        }

        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])),$arrOrders['payed'])), $arrPayment['setting']['pay_fee']));
        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

        // 更新订单支付信息
        $arr_updates = array(
            'order_id' => $order_id,
            'payinfo' => array(
                            'pay_app_id' => $pay['pay_app_id'],
                            'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                        ),
            'currency' => $arrOrders['currency'],
            'cur_rate' => $arrOrders['cur_rate'],
            'total_amount' => $total_amount,
            'cur_amount' => $cur_money,
        );
        $objOrders->save($arr_updates);
    }
}

