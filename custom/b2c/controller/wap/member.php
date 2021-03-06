<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_member extends wap_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('会员中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('会员中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('会员中心_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verify_member();
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->member = $this->get_current_member();
        $this->from_weixin = kernel::single('weixin_wechat')->from_weixin();
        /** end **/
    }

    /*
     *本控制器公共分页函数
     * */
    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='wap_member'){ //本控制器公共分页函数
        if (!$arg){
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        }else{
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }

    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    /*
     *会员中心首页
     * */
    public function index() {
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;

        #获取会员等级
        $obj_mem_lv = $this->app->model('member_lv');
        $levels = $obj_mem_lv->getList('name,lv_logo,disabled',array('member_lv_id'=>$this->member['member_lv']));
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
            $this->member['lv_logo'] = $levels[0]['lv_logo'];
        }
        
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);

        //交易提醒
        $msgAlert = $this->msgAlert();
        $this->member = array_merge($this->member,$msgAlert);
        
        $obj_pam_members = app::get('pam')->model('bind_tag');
        $aUser_name = $obj_pam_members->dump(array('member_id' => $this->member['member_id']));
        $this->pagedata['tag_name'] = $aUser_name['tag_name'];
        if(empty($aUser_name['tag_name'])){
        	$uname = kernel::single('b2c_user_object')->get_current_member();
        	$this->pagedata['tag_name'] = $uname['uname'];
        }
        
        $wei_member = app::get('pam')->model('members')->getList('*',array('member_id'=>$this->app->member_id));
        if(count($wei_member) > 1){
        	foreach($wei_member as $row){
        		if($row['login_type'] == 'mobile'){
        			$data['bind_type'] = '手机';
        			$data['bind_account'] = $row['login_account'];
        			break;
        		}
        	
        		if($row['login_type'] == 'email'){
        			$data['bind_type'] = '邮件';
        			$data['bind_account'] = $row['login_account'];
        			break;
        		}
        	
        		if($row['login_type'] == 'local'){
        			if(strlen($row['login_account']) < 24){
        				$userPassport = kernel::single('b2c_user_passport');
        				$bind_type = $userPassport->get_local_account_type($row['login_account']);
        				if($bind_type == 'card'){
        					$data['bind_type'] = '会员卡';
        				}else{
        					$data['bind_type'] = '账号';
        				}
        				$data['bind_account'] = $row['login_account'];
        				break;
        			}       			
        		}
        	}
        	if(strlen($data['bind_account'])>=7){
        		$data['bind_account'] = substr($data['bind_account'],0,7).'...';
        	}
        	$this->pagedata['bind_info'] = $data;
        	$this->pagedata['weixin_bind'] = '1';
        }else{
        	$this->pagedata['weixin_bind'] = '0';
        }
        
        //订单列表
#        $oRder = $this->app->model('orders');//--11sql
#        $aData = $oRder->fetchByMember($this->app->member_id,$nPage=1,array(),5); //--141sql优化点
#        $this->get_order_details($aData, 'member_latest_orders');//--177sql 优化点
#        $this->pagedata['orders'] = $aData['data'];

        //收藏列表
        $obj_member = $this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv'],$page=1,$num=4);//201sql
        $this->pagedata['favorite'] = $aData_fav['data'];
        #默认图片
#        $imageDefault = app::get('image')->getConf('image.set');
#        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            // 当前会员实际可以使用的积分
            $obj_extend_point->get_usage_point($this->member['member_id'], $real_usage_point);
        }
        
        //来自微信的是否允许推出
        if(kernel::single('weixin_wechat')->from_weixin() && !WX_AOUTH_AUTO_REG){
        	$this->pagedata['is_show_logout'] = '1';
        }
        
        if ($real_usage_point < 0)
            $real_usage_point = 0;
        
        
        $this->member['point'] = $real_usage_point;
        
        //订单数与优惠卷数
        $obj_orders = $this->app->model('orders');
        
        $order_num = kernel::database()->select("select count(*) total from sdb_b2c_orders where (pay_status='0' or pay_status='1') and ship_status='0' and status='active' and member_id='".$this->app->member_id."'"); 
        $this->member['order_num'] = $order_num[0]['total'];
        
        $coupon_num = kernel::database()->select("select count(*) total from sdb_b2c_member_coupon where member_id='".$this->app->member_id."' and disabled='false' and memc_isvalid='true'");
        $this->member['coupon_num'] = $coupon_num[0]['total']; 
        
        //输出
        $this->pagedata['member'] = $this->member;
        $this->set_tmpl('member');
        //未评价商品咨询开关
        $this->pagedata['comment_switch_discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->pagedata['comment_switch_ask'] = $this->app->getConf('comment.switch.ask');
        $this->page('wap/member/index.html');
    }   
    
    /*
    *	会员等级页
    */
    function bership() {
        $obj_pam_members = app::get('pam')->model('bind_tag');
        $aUser_name = $obj_pam_members->dump(array('member_id' => $this->app->member_id));
        $this->pagedata['tag_name'] = $aUser_name['tag_name'];
        $wei_member = app::get('pam')->model('members')->getList('*',array('member_id'=>$this->app->member_id));
        if(count($wei_member) > 1){
        	foreach($wei_member as $row){
        		if($row['login_type'] == 'mobile'){
        			$data['bind_type'] = '手机';
        			$data['bind_account'] = $row['login_account'];
        			break;
        		}
        	
        		if($row['login_type'] == 'email'){
        			$data['bind_type'] = '邮件';
        			$data['bind_account'] = $row['login_account'];
        			break;
        		}
        	
        		if($row['login_type'] == 'local'){
        			if(strlen($row['login_account']) < 24){
        				$userPassport = kernel::single('b2c_user_passport');
        				$bind_type = $userPassport->get_local_account_type($row['login_account']);
        				if($bind_type == 'card'){
        					$data['bind_type'] = '会员卡';
        				}else{
        					$data['bind_type'] = '账号';
        				}
        				$data['bind_account'] = $row['login_account'];
        				break;
        			}       			
        		}
        	}
        	$this->pagedata['bind_info'] = $data;
        	$this->pagedata['weixin_bind'] = '1';
        }else{
        	$this->pagedata['weixin_bind'] = '0';
        }
        
        #获取会员等级
        
        $obj_mem_lv = $this->app->model('member_lv');
        $levels = $obj_mem_lv->getList('name,lv_logo,disabled,member_lv_id,experience,point,dis_count',array('name|noequal'=>'门店店员'),0,10,'experience asc');
        
        $this->member['next_lv'] = '';
        $this->member['next_lv_experience'] = '';
        $this->member['next_lv_percent'] = '100';
        
        $is_next = false;
        
        foreach ($levels as $key=>$value) {
        	if ($is_next == true) {
        		$is_next = false;
        		$this->member['next_lv'] = $value['name'];
        		$this->member['next_lv_experience'] =  $value['experience'] - $this->member['experience'];
        		$this->member['next_lv_percent'] =  $this->member['experience']/$value['experience']*100;
        	}
        	if ($value['member_lv_id'] == $this->member['member_lv']) {
	            $this->member['levelname'] = $value['name'];
	            $this->member['lv_logo'] = $value['lv_logo'];
	            $is_next = true;
        	}
        }
        
        //echo '<xmp>';
        //var_dump($this->member);
      //  echo '</xmp>';
          /*
        var_dump($levels);
      
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
            $this->member['lv_logo'] = $levels[0]['lv_logo'];
        }*/
        $this->pagedata['levels'] = $levels;
        $this->pagedata['member'] = $this->member;
        
        
    	$this->page('wap/member/bership.html');
    }
    
    public  function wallet(){
        $this->title = '品鲜钱包';
        $this->pagedata['member'] = $this->member;  
        $this->pagedata['title'] = $this->title;
        $this->page('wap/member/wallet.html');
    }
    /*
     *会员中心首页交易提醒 (未付款订单,到货通知，未读的评论咨询回复)
     * */
    private function msgAlert(){
        //获取待付款订单数
        $oRder = $this->app->model('orders');//--11sql
        $un_pay_orders = $oRder->count(array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active'));
        $member['un_pay_orders'] = $un_pay_orders;

        //到货通知
        $member_goods = $this->app->model('member_goods');
        $member['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);

        //评论咨询回复
        $mem_msg = $this->app->model('member_comments');
        $object_type = array('discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->app->member_id,'object_type'=> $object_type,'mem_read_status' => 'false','display' => 'true'));
        $un_readAskMsg = 0;
        $un_readDiscussMsg = 0;
        foreach($aData as $val){
            if($val['object_type'] == 'ask'){
                $un_readAskMsg += 1;
            }else{
                $un_readDiscussMsg += 1;
            }
        }
        $member['un_readAskMsg'] = $un_readAskMsg;
        $member['un_readDiscussMsg'] = $un_readDiscussMsg;
        return $member;
    }

    //积分历史
    function point_history($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('积分历史'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = $this->app->model('members');
        $member_point = $this->app->model('member_point');
        $obj_gift_link = kernel::service('b2c.exchange_gift');
        if ($obj_gift_link)
        {
            $this->pagedata['exchange_gift_link'] = $obj_gift_link->gen_exchange_link();
        }
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_detail_point($this->app->member_id);
            }
        }
        $data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($data['score']['event']);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'point_history');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->page('wap/member/point_history.html');
    }

    //我的订单
    public function orders($pay_status='nopayed', $nPage=1)
    {
        $this->title = app::get('b2c')->_('我的订单');
         $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $order = $this->app->model('orders');
        if ($pay_status == 'all')
        {
            $aData = $order->fetchByMember($this->app->member_id,$nPage);
        }
        else
        {
            $order_status = array();
            if ($pay_status == 'nopayed')
            {
            	$this->title = '待支付';
                $order_status['pay_status'] = 0;
                $order_status['status'] = 'active';
            }
            if($pay_status=='tuihuo'){
                $this->title = '我的退货';
                $order_status['pay_status'] = 1;
                $order_status['ship_status'] = 4;
            }
             if($pay_status=='daishouhuo'){
                $this->title = '待收货';
                $order_status['pay_status'] = array(1,2);
                $order_status['ship_status'] = '0';
                $order_status['status'] = 'active';
            }
            //bySam 20150521
            //$order_status = array('pay_status'=>0,'ship_status'=>array(1,2,3));
            $aData = $order->fetchByMember($this->app->member_id,$nPage,$order_status);
        }
        $this->pagedata['status'] = $pay_status;
        
        //添加条数显示
        $o1 = array();
        $o1['pay_status'] = 0;
        $o1['status'] = 'active';
        $c1 = $order->countByMember($this->app->member_id,$o1);//待支付
        unset($o1);
        $o1 = array();
        $o1['pay_status'] = 1;
        $o1['ship_status'] = 4;
        $c2 = $order->countByMember($this->app->member_id,$o1);//我的退货
        unset($o1);
        $o1 = array();
        $o1['pay_status'] = array(1,2);
        $o1['ship_status'] = '0';
        $o1['status'] = 'active';
        $c3 = $order->countByMember($this->app->member_id,$o1);//待收货
        unset($o1);
        $o1 = array();
        $c4 = $order->countByMember($this->app->member_id,$o1);//全部
        
        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        foreach($aData['data'] as $k => &$v) {
            foreach($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc',array('goods_id'=>$v2['product']['goods_id']));
                $select_spec_private_value_id = reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                $spec_desc_goods = reset($spec_desc_goods[0]['spec_desc']);
                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                }else{
                    if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                }
            }
        }
        $this->pagedata['orders'] = $aData['data'];

        $arr_args = array($pay_status);
        $this->pagination($nPage,$aData['pager']['total'],'orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['is_orders'] = "true";
        $this->pagedata['title'] = $this->title;
        $this->pagedata['order_num_list'] = array(
        		'daizhifu'=>$c1,
        		'tuihuo'=>$c2,
        		'daishouhuo'=>$c3,
        		'all'=>$c4,
        		);
        $this->page('wap/member/orders.html');
    }
    
    function cancel($order_id){
        $this->pagedata['cancel_order_id'] = $order_id;
        $this->page('wap/member/order_cancel_reason.html');
    }
    
    function docancel(){
        $arrMember = kernel::single('b2c_user_object')->get_current_member(); //member_id,uname
        //开启事务处理
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $order_cancel_reason = $_POST['order_cancel_reason'];
        if($order_cancel_reason['reason_type'] == 7 && !$order_cancel_reason['reason_desc'])
        {
            $this->splash('error','','请输入详细原因',true);
        }
        if(strlen($order_cancel_reason['reason_desc'])>150)
        {
            $this->splash('error','','详细原因过长，请输入50个字以内',true);
        }
        if($order_cancel_reason['reason_type'] != 7 && strlen($order_cancel_reason['reason_desc']) > 0)
        {
            $order_cancel_reason['reason_desc'] = '';
        }
        $order_cancel_reason = utils::_filter_input($order_cancel_reason);
        $order_cancel_reason['cancel_time'] = time();
        $mdl_order = app::get('b2c')->model('orders');
        $sdf_order_member_id = $mdl_order->getRow('member_id', array('order_id'=>$order_cancel_reason['order_id']));
        if($sdf_order_member_id['member_id'] != $arrMember['member_id'])
        {
            $db->rollback();
            $this->splash('error','',"请勿取消别人的订单",true);
            return;
        }

        $mdl_order_cancel_reason = app::get('b2c')->model('order_cancel_reason');
        $result = $mdl_order_cancel_reason->save($order_cancel_reason);
        if(!$result)
        {
            $db->rollback();
            $this->splash('error','',"订单取消原因记录失败",true);
        }
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_cancel_reason['order_id'],'',$message))
        {
            $db->rollback();
            $this->splash('error','',$message,true);
        }

        $sdf['order_id'] = $order_cancel_reason['order_id'];
        $sdf['op_id'] = $arrMember['member_id'];
        $sdf['opname'] = $arrMember['uname'];
        $sdf['account_type'] = 'member';

        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            if($order_object = kernel::service('b2c_order_rpc_async')){
                $order_object->modifyActive($sdf['order_id']);
            }
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            $db->commit($transaction_status);
            $this->splash('success',$url,"订单取消成功",true);
        }
        else
        {
            $db->rollback();
            $this->splash('error','',"订单取消失败",true);
        }
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
            $obj_specification = $this->app->model('specification');
            $obj_spec_values = $this->app->model('spec_values');
            $obj_goods = $this->app->model('goods');
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
                                $o = $this->app->model('order_items');
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
                                $select_spec_private_value_id = reset($arr_items['products']['spec_desc']['spec_private_value_id']);
                                $spec_desc_goods = reset($arr_goods['spec_desc']);
                                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                                    $arr_goods['image_default_id'] = $default_product_image;
                                }else{
                                    if( !$arr_goods['image_default_id'] && !$oImage->getList("image_id",array('image_id'=>$arr_goods['image_default_id']))){
                                        $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                }

                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_product','act'=>'index','arg0'=>$arr_items['products']['product_id']));;
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
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")) !== false)
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
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0)
    {
        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'wap_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");
        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }
        if($sdf_order['member_id']){
            $member = $this->app->model('members');
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
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $sdf_order['cur_money'] = $objMath->number_minus(array($sdf_order['cur_amount'], $sdf_order['payed']));
        $this->pagedata['order'] = $sdf_order;
// echo "<pre>";print_r($this->pagedata['order']);exit;
        /** 将商品促销单独剥离出来 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    $this->pagedata['order']['goods_pmt'][$arr_pmt['product_id']][$key] =  $this->pagedata['order']['order_pmt'][$key];
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

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
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        // 生成订单日志明细
        //$oLogs =$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        $this->pagedata['order']['payment'] = $arr_payments_cfg;

        #//物流跟踪安装并且开启
        #$logisticst = app::get('b2c')->getConf('system.order.tracking');
        #$logisticst_service = kernel::service('b2c_change_orderloglist');
        #if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
        #    $this->pagedata['services']['logisticstack'] = $logisticst_service;
        #}
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->pagedata['controller'] = "orders";

        $this->page('wap/member/orderdetail.html');
    }

    function deposit(){
    	//判断微信来源时，获取openid
    	$from_weixin = kernel::single('weixin_wechat')->from_weixin();
    	
    	if ($from_weixin) {
            $wxpayjsapi_conf = app::get('ectools')->getConf('weixin_payment_plugin_wxpayjsapi');
            $wxpayjsapi_conf = unserialize($wxpayjsapi_conf);
            if(!$_GET['code'])
            {
                $return_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'deposit','args'=>'','full'=>1));
                $appId_to_get_code = trim($wxpayjsapi_conf['setting']['appId']);
                kernel::single('weixin_wechat')->get_code($appId_to_get_code, $return_url);
            }else{
                $code = $_GET['code'];
                $openid = kernel::single('weixin_wechat')->get_openid_by_code($wxpayjsapi_conf['setting']['appId'], $wxpayjsapi_conf['setting']['Appsecret'], $code);
                if($openid == null)
                    $this->end(false,  app::get('b2c')->_('获取openid失败'), $this->gen_url(array('app'=>'wap','ctl'=>'default','act'=>'index')));
            }
            $this->pagedata['from_url'] = '?openid='.$openid;
    	}
    	
    	
    	
    	
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预存款充值'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        
        //区分来自微信 和wap端的支付方式
        if($from_weixin){
        	$aOld = $opay->getList('*', array('status' => 'true', 'platform'=>array('iscommon','iswx'), 'is_frontend' => true));
        }else{
        	$aOld = $opay->getList('*', array('status' => 'true', 'platform'=>array('iscommon','iswap'), 'is_frontend' => true));
        }
        

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
            if(($val['app_id']!='deposit') && ($val['app_id']!='offline')){
                $aData[] = $val;
            }
        }

        $this->pagedata['total'] = $this->member['advance'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'balance'));

        $this->page('wap/member/deposit.html');
    }


    //预存款交易记录
    public function balance($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的预存款'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $member = $this->app->model('members');
        $mem_adv = $this->app->model('member_advance');
        $items_adv = $mem_adv->get_list_bymemId($this->app->member_id);
        $count = count($items_adv);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $mem_adv->getList('*',array('member_id' => $this->app->member_id),$aPage['start'],$this->pagesize,'mtime desc');

        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'balance');
        $this->pagedata['advlogs'] = $params['data'];
        $data = $member->dump($this->app->member_id,'advance');
        $this->pagedata['total'] = $data['advance']['total'];
        // errorMsg parse.
        $this->pagedata['errorMsg'] = json_decode($_GET['errorMsg']);
        $this->page('wap/member/balance.html');
    }

    function favorite($nPage=1){
        $this->title = app::get('b2c')->_('我的收藏');
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];
        foreach($aProduct as $k=>$v){
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
        $this->pagedata['favorite'] = $aProduct;
        $this->pagination($nPage,$aData['page'],'favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'ajax_del_fav','args'=>array('goods')));
        $this->pagedata['title'] = $this->title;
        $this->page('wap/member/favorite.html');
    }

    /*
     *删除商品收藏
     * */
     function ajax_del_fav($object_type='goods'){
     	$gid = intval($_POST['gid']);
     	 
        if(!$gid){
          //  $this->splash('error',null,app::get('b2c')->_('参数错误！'));
            $this->splash('error', null, app::get('b2c')->_('参数错误！'), '', '', true);
        }
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$gid,$maxPage)){
         //   $this->splash('error',null,app::get('b2c')->_('移除失败！'));
            $this->splash('error', null, app::get('b2c')->_('移除失败！'), '', '', true);
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'favorite','args'=>array($current_page)));
             //   $this->splash('success',$reload_url,app::get('b2c')->_('成功移除！'));
                $this->splash('success', null, app::get('b2c')->_('成功移除！'), '', '', true);
            }
            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
                }
            }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'favorite'));
            //$this->splash('success',$reload_url,app::get('b2c')->_('成功移除！'));
            $this->splash('success', null, app::get('b2c')->_('成功移除！'), '', '', true);
        }
    }
    /*
     *删除全部商品收藏
     * */
    function ajax_del_all_fav() {
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,'goods')){
            $this->splash('error', null, app::get('b2c')->_('移除失败！'), '', '', true);
        }else{
        	
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);
            $this->splash('success', null, app::get('b2c')->_('成功移除！'), '', '', true);
        }
    }
    
	
    function ajax_fav() {
        $object_type = $_POST['type'];
        $goods_id = $_POST['gid'];

        $obj_goods = $this->app->model('goods');

        $isGoodsMarketable = $obj_goods->count(array('goods_id'=>$goods_id,'marketable'=>true));

        if( $isGoodsMarketable==0 )
            $this->splash('failed', null, 'not marketable', '', '', true);

        $obj_member_goods = $this->app->model('member_goods');

        $member_id = $this->app->member_id;

        $filter = array(
            'goods_id'=>$goods_id,
            'member_id'=>$member_id,
            'type'=>'fav',
            'object_type'=>'goods'
        );
        $isGoodsFaved = $obj_member_goods->parent_count($filter);

        if($isGoodsFaved == 0){
            if (!kernel::single('b2c_member_fav')->add_fav($this->app->member_id,$object_type,$goods_id)){
                $this->splash('failed', null, app::get('b2c')->_('商品收藏添加失败！'), '', '', true);
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);
                // change to json for ajax request
                $this->splash('success', null, 'saved', '', '', true);
            }
        }else{
            if(!$obj_member_goods->delFav($member_id,$goods_id)){
                $this->splash('failed', null, 'db error', '', '', true);
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);
                $this->splash('success', null, 'deleted', '', '', true);
            }
        }
    }

    //收获地址
    function receiver(){
        $this->title = app::get('b2c')->_('配送地址');
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $objMem = $this->app->model('members');
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<10 ? 1 : 0);
        $this->pagedata['num'] = count($this->pagedata['receiver']);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->page('wap/member/receiver.html');
    }


    /*
     * 设置和取消默认地址，$disabled 2为设置默认1为取消默认
     */
    function set_default($addrId=null,$disabled=2){
        // $addrId = $_POST['addr_id'];
        if(!$addrId) $this->splash('failed',null, app::get('b2c')->_('参数错误'),true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$member_id)){
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled)){
		        setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
		        setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
                $this->splash('success',$url,$message,'','',true);
            }else{
                $this->splash('failed',$url,$message,'','',true);
            }
        }else{
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),true);
        }
    }

    /*
     *添加、修改收货地址
     * */
    function modify_receiver($addrId=null){
        if(!$addrId){
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id)){
            if($aRet = $obj_member->getAddrById($addrId)){
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else{
                $this->_response->set_http_response_code(404);
                $this->_response->set_body(app::get('b2c')->_('修改的收货地址不存在！'));
                exit;
            }
            $this->page('wap/member/modify_receiver.html');
        }else{
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }

    function address_list($to_edit=0){
        $this->title = app::get('b2c')->_('配送地址');
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $objMem = $this->app->model('members');
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<10 ? 1 : 0);
        $this->pagedata['addr_count'] = count($this->pagedata['receiver']);
        $this->pagedata['num'] = count($this->pagedata['receiver']);
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->pagedata['to_edit'] = $to_edit;

        $this->page('wap/member/address_list.html');
    }

    /*
     *保存收货地址
     * */
    function save_rec(){
        if(!$_POST['def_addr']){
            $_POST['def_addr'] = 0;
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST,$this->app->member_id,$msg);
        if(!$save_data){
            $this->splash('failed',null,$msg,'','',true);
        }
        setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
        setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
        $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver')),app::get('b2c')->_('保存成功'),'','',true);
    }

    //添加收货地址
    function add_receiver(){
        $obj_member = $this->app->model('members');
        if($obj_member->isAllowAddr($this->app->member_id)){
            $this->page('wap/member/modify_receiver.html');
        }else{
            $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver')),app::get('b2c')->_('最多添加10个收货地址'),'','',true);
        }
    }


    //删除收货地址
    function del_rec($addrId=null){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'receiver'));
        $obj_member = $this->app->model('members');
        if($obj_member->check_addr($addrId,$this->app->member_id))
        {
            if($obj_member->del_rec($addrId,$message,$this->app->member_id))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'null', app::get('b2c')->_('操作失败'),'','',true);
        }
    }

    /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
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

    /*
     * 获取评论咨询的数据
     *
     * */
    public function getComment($nPage=1,$type='discuss'){
        //获取评论咨询基本数据
        $comment = kernel::single('b2c_message_disask');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,$type);
        $gids = array();
        $productGids = array();
        foreach((array)$aData['data'] as $k => $v){
            if($v['type_id'] && !in_array($v['type_id'],$gids) ){
                $gids[] = $v['type_id'];
            }
            if(!$v['product_id'] && !in_array($v['type_id'],$productGids) ){
                $productGids[] = $v['type_id'];
            }

            if($v['items']){//统计回复未读的数量
                $unReadNum = 0;
                foreach($v['items'] as $val){
                    if($val['mem_read_status'] == 'false' ){
                        $unReadNum += 1;
                    }
                }
            }
            $aData['data'][$k]['unReadNum'] = $unReadNum;
        }

        //获取货品ID
        $productData = $productGids ? $this->app->model('products')->getList('goods_id,product_id',array('goods_id'=>$productGids,'is_default'=>'true')) : array();
        foreach((array) $productData as $p_row){
            $productList[$p_row['goods_id']] = $p_row['product_id'];
        }
        $this->pagedata['productList'] = $productList;

        //评论咨询商品信息
        $goodsData = $gids ? $this->app->model('goods')->getList('goods_id,name,price,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$gids)) : null;
        if($goodsData){
            foreach($goodsData as $row){
                $goodsList[$row['goods_id']] = $row;
            }
        }
        $this->pagedata['goodsList'] = $goodsList;

        //评论咨询私有的数据
        if($type == 'discuss'){
            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            if($this->pagedata['point_status'] == 'on'){//如果开启评分则获取评论评分
                $objPoint = $this->app->model('comment_goods_point');
                $goods_point = $objPoint->get_single_point_arr($gids);
                $this->pagedata['goods_point'] = $goods_point;
            }
        }else{
            $gask_type = unserialize($this->app->getConf('gask_type'));//咨询类型
            foreach((array)$gask_type as $row){
                $gask_type_list[$row['type_id']] = $row['name'];
            }
            $this->pagedata['gask_type'] = $gask_type_list;
        }
        return $aData;
    }

    function comment($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $comment = $this->getComment($nPage,'discuss');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagination($nPage,$comment['page'],'comment');
        $this->output();
    }

    function ask($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $this->pagedata['controller'] = "comment";
        $comment = $this->getComment($nPage,'ask');
        $this->pagedata['commentList'] = $comment['data'];
        $this->pagedata['commentType'] = 'ask';
        $this->pagination($nPage,$comment['page'],'ask');
        $this->action_view = 'comment.html';
        $this->output();
    }

    /*
     *未评论商品
     **/
    public function nodiscuss($nPage=1){
        //面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('未评论商品'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        //获取会员已发货的商品日志
        $sell_logs = $this->app->model('sell_logs')->getList('order_id,product_id,goods_id',array('member_id'=>$this->app->member_id));
        //获取会员已评论的商品
        $comments = $this->app->model('member_comments')->getList('order_id,product_id',array('author_id'=>$this->app->member_id,'object_type'=>'discuss','for_comment_id'=>'0'));
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

        $this->pagedata['list'] = $sell_logs_data;
        $this->pagination($nPage,$totalPage,'nodiscuss');

        if($gids){
            //获取商品信息
            $goodsData = $this->app->model('goods')->getList('goods_id,name,image_default_id',array('goods_id'=>$gids));
            $goodsList = array();
            foreach((array)$goodsData as $goods_row){
                $goodsList[$goods_row['goods_id']]['name'] = $goods_row['name'];
                $goodsList[$goods_row['goods_id']]['image_default_id'] = $goods_row['image_default_id'];
            }
            $this->pagedata['goods'] = $goodsList;

            $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            $this->pagedata['verifyCode'] = $this->app->getConf('comment.verifyCode');
            if($this->pagedata['point_status'] == 'on'){
                //评分类型
                $comment_goods_type = $this->app->model('comment_goods_type');
                $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                if(!$this->pagedata['comment_goods_type']){
                    $sdf['type_id'] = 1;
                    $sdf['name'] = app::get('b2c')->_('商品评分');
                    $addon['is_total_point'] = 'on';
                    $sdf['addon'] = serialize($addon);
                    $comment_goods_type->insert($sdf);
                    $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
                }
            }

        $this->pagedata['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.discuss');
        }
        $this->page('wap/member/nodiscuss.html');
    }

    //我的优惠券
    function coupon($nPage=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('优惠券'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->app->member_id,$nPage);
        	$sortAdata = array();
        if ($aData) {
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status'] !=1) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                    $aData[$k]['order'] = 5;
                    continue;
                }

                $member_lvs = explode(',',$item['time']['member_lv_ids']);
                if (!in_array($this->member['member_lv'],(array)$member_lvs)) {
                    $aData[$k]['coupons_info']['cpns_status'] = false;
                    $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    $aData[$k]['order'] = 5;
                    continue;
                }

                $curTime = time();
                if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                    if ($item['memc_used_times']<$this->app->getConf('coupon.mc.use_times')){
                        if ($item['coupons_info']['cpns_status']){
                            $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                            $aData[$k]['order'] = 1;
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                            $aData[$k]['order'] = 5;
                        }
                    }else{
                        $aData[$k]['coupons_info']['cpns_status'] = false;
                        $aData[$k]['memc_status'] = app::get('b2c')->_('已使用');
                        $aData[$k]['order'] = 4;
                    }
                }else{
                	if($curTime < $item['time']['from_time']){
                		$aData[$k]['coupons_info']['cpns_status'] = false;
                		$aData[$k]['memc_status'] = app::get('b2c')->_('还未开始');
                		$aData[$k]['order'] = 2;
                	}
                	if($curTime > $item['time']['to_time']){
                		$aData[$k]['coupons_info']['cpns_status'] = false;
                		$aData[$k]['memc_status'] = app::get('b2c')->_('已过期');
                		$aData[$k]['order'] = 3;
                	}
                }
            }
        }

        for($i=1;$i<=5;$i++){
        	foreach($aData as $key => $item){
        		if($aData[$key]['order'] == $i){ //将优惠券进行排序:1.可以使用;2未开始;3.已过期;4.已使用;5.其他
        			$sortAdata[] = $item;
        		}
        	}
        }
        $total = $oCoupon->get_list_m($this->app->member_id);
        $this->pagination($nPage,ceil(count($total)/$this->pagesize),'coupon');
        $this->pagedata['coupons'] = $sortAdata;
        $this->pagedata['title'] = app::get('b2c')->_('优惠券');
        $this->page('wap/member/coupon.html');
    }


    /**
     * 添加留言
     * @params string order_id
     * @params string message type
     */
    public function add_order_msg( $order_id , $msgType = 0 ){
        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $order_id;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        $this->page('wap/member/add_order_msg.html');
    }

    /**
     * 订单留言提交
     * @params null
     * @return null
     */
    public function toadd_order_msg()
    {
        if(!$_POST['msg']['orderid']){
            $this->splash(false,app::get('b2c')->_('参数错误'),true);
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $_POST['to_type'] = 'admin';
        $_POST['author_id'] = $this->app->member_id;
        $_POST['author'] = $this->member['uname'];
        $is_save = true;
        $obj_order_message = kernel::single("b2c_order_message");
        if ($obj_order_message->create($_POST)){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'orderdetail','arg0'=>$_POST['msg']['orderid']));
            $this->splash(success,$url,app::get('b2c')->_('留言成功'),'','',true);
        }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'add_order_msg','arg0'=>$_POST['msg']['orderid'],'arg1'=>1));
            $this->splash(false,$url,app::get('b2c')->_('留言失败'),'','',true);
        }
    }

    /*
     *会员中心 修改密码页面
     * */
    function security($type = ''){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['title'] = app::get('b2c')->_('修改密码');
        $this->page('wap/member/modify_password.html');
    }
    
     /*
     *会员中心 绑定会员页面
     * */
    function weixinset($type = ''){
    	//if(!$this->from_weixin){
    	//	$url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
    	//	$this->redirect($url);
    	//}
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('用户绑定设置'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['title'] = app::get('b2c')->_('会员绑定');
        $this->page('wap/member/modify_username.html');
    }
    
    function bind_member(){
    	$bind_type = trim($_POST['bind_type']);
    	$wei_member = app::get('pam')->model('members')->getList('*',array('member_id'=>$this->app->member_id));
    	$userPassport = kernel::single('b2c_user_passport');
    	$account = $_POST['login_account'.'_'.$bind_type];
    	$account_pam_member = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$account));
    	$old_account_password = trim($_POST['login_password'.'_'.$bind_type]);
    	$new_account_password = trim($_POST['new_password'.'_'.$bind_type]);
    	$vode = $_POST['vcode'.'_'.$bind_type];
    	$login_member_id = intval($this->app->member_id);
    	$from_to =$_POST['from_to'.'_'.$bind_type];
    	if(!$from_to){
    		$from_to = 'old_to_weixin';   		
    	}
    	
    	if(!$account || !isset($account) || strlen($account) < 4){
    		$msg = app::get('b2c')->_('请输入正确的账号');
    		$this->splash('failed',null,$msg,'','',true);
    	}
    	
    	//if((isset($old_account_password) || !empty($new_account_password)) && strlen($old_account_password) < 6){
    	//	$msg = app::get('b2c')->_('原账号密码必须为6-20位字符');
    	//	$this->splash('failed',null,$msg,'','',true);
    	//}
    	
    	if(!$new_account_password || !isset($new_account_password) || strlen($new_account_password) < 6){
    		$msg = app::get('b2c')->_('绑定后密码必须为6-20位字符');
    		$this->splash('failed',null,$msg,'','',true);
    	}
    	
    	//if(count($wei_member) > 1){
    	//	$msg = app::get('b2c')->_('该微信账号已经绑定过其他账号,不能再进行绑定');
    	//	$this->splash('failed',null,$msg,'','',true);
    	//}
    	if(!$wei_member){
    		$msg = app::get('b2c')->_('当前账号错误');
    		$url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'logout'));
            $this->splash('failed',$url,$msg,'','',true);
    	}else{
    		$this->check_bind_info($wei_member, $account, $bind_type);			
    	}
    	if($bind_type == 'email' || $bind_type == 'account' || $bind_type == 'mobile'){
    		    		
    		if($bind_type == 'email' && !$account_pam_member[0]['member_id']){
    			//验证码    			
    		    	$userVcode = kernel::single('b2c_user_vcode');
    				if( !$userVcode->verify($vode,$account,'reset')){
        					$msg = app::get('b2c')->_('验证码错误');
    						$this->splash('failed',null,$msg,'','',true);
     	 			}
    		}
    		
    		if($bind_type == 'mobile' && !$account_pam_member[0]['member_id']){
    			$res = kernel::single('b2c_user_vcode')->verify($vode,$account,'signup');
    			if(!$res){
    				$msg = app::get('b2c')->_('短信验证码错误');
    				$this->splash('failed',null,$msg,'','',true);
    			}
    		}
    		    		    		    		
    		$status = $userPassport->bind_member($bind_type,$from_to,$login_member_id,$account,$old_account_password,$new_account_password);
    		switch ($status){
    		case 'update_passwd_failed' :
    			$msg = app::get('b2c')->_('密码更新失败');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'failed' :
    			$msg = app::get('b2c')->_('绑定失败');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'wrong_password' :
    			$msg = app::get('b2c')->_('原会员账号密码错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'old_member_wrong' :
    			$msg = app::get('b2c')->_('当前会员信息错误错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'insert_membercard_wrong' :
    			$msg = app::get('b2c')->_('会员卡注入错误');
    			$this->splash('failed',null,$msg,'','',true);
    		case 'add_advance_wrong' :
    			$msg = app::get('b2c')->_('增加预存款是失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'reduce_advance_wrong' :
    			$msg = app::get('b2c')->_('减少预存款失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'add_point_wrong' :
    			$msg = app::get('b2c')->_('增加积分失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'reduce_point_wrong' :
    			$msg = app::get('b2c')->_('减少积分失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'delete_oldcard_failed' :
    			$msg = app::get('b2c')->_('删除绑定的旧会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_level_failed' :
    			$msg = app::get('b2c')->_('等级更新失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_newcard_failed' :
    			$msg = app::get('b2c')->_('更新新会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_oldcard_failed' :
    			$msg = app::get('b2c')->_('更新旧会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_oldmember_failed' :
    			$msg = app::get('b2c')->_('更新旧会员失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_member_failed' :
    			$msg = app::get('b2c')->_('更新会员信息失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_passwd_failed' :
    			$msg = app::get('b2c')->_('更新会员密码失败');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_old_cardmember_failed' :
    			$msg = app::get('b2c')->_('更新旧会员卡会员失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_lo_failed' :
    			$msg = app::get('b2c')->_('更新日志失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'openid_rebind' :
    			$msg = app::get('b2c')->_('该账号已经绑定过微信');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'order_uncompleted' :
    			$msg = app::get('b2c')->_('被绑定的账号有未完成的订单,不能进行绑定！');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_coupon_failed' :
    			$msg = app::get('b2c')->_('合并优惠券失败！');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'ok' :
    			$msg=app::get('b2c')->_('绑定成功！');
            	$url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'logout'));
            	$this->splash('success',$url,$msg,'','',true);
    			break;
    	}
    	}elseif($bind_type == 'card'){   		
    		if($from_to == 'old_to_weixin'){
    			$type = "card_to_member";
    		}
    		if($from_to == 'weixin_to_old'){
    			$type = "member_to_card";
    		}
    		$this->save_weixin_card($account,$old_account_password,$new_account_password,$type);
    	}else{
    		$msg = app::get('b2c')->_('请选择要绑定的信息');
    		$this->splash('failed',null,$msg,'','',true);
    	}
    }
    
    
    function save_weixin_card($card,$card_password,$new_account_password,$type){
    	$this->userPassport = kernel::single('b2c_user_passport');
    	$userPassport = kernel::single('b2c_user_passport');
    	$login_member_id = intval($this->app->member_id);
    	if( !$card || !is_numeric($card)){
    		$msg = app::get('b2c')->_('请填写正确的会员卡号');
    		$this->splash('failed',null,$msg,'','',true);
    	}
    	$member_card = $this->app->model('member_card')->getList('*',array('card_number'=>$card));
    	if(!$member_card){//先从会员卡表中直接读取卡号,判断卡号是否存在
    		$msg = app::get('b2c')->_('会员卡不存在');
    		$this->splash('failed',null,$msg,'','',true);
    	}else{//会员卡号存在
    		$member_id = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$card));
    		if($member_id[0]['member_id']){//卡号存在且卡已被激活,要检查该会员卡是否被绑定,还要检查是否改了密码,验证密码的一致性
    			$new_card = '';
    			$pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id[0]['member_id']));
    			if(count($pamMemberData) > 1){//被激活后判断是否被绑定
    				foreach($pamMemberData as $pmd){
    					if($pmd['login_type'] == 'local' && strlen($pmd['login_account']) > 25){
    						$msg = app::get('b2c')->_('该会员卡已被绑定过！');
    						$this->splash('failed',null,$msg,'','',true);
    					}
    				}
    			}
    			
    			$use_pass_data['login_name'] = $card;
    			$use_pass_data['createtime'] = $pamMemberData[0]['createtime'];
    			$login_password = pam_encrypt::get_encrypted_password($card_password,'member',$use_pass_data);
    			if($login_password != $pamMemberData[0]['login_password']){//会员卡被激活之后,可能被改密码,要进行密码验证
    				$msg = app::get('b2c')->_('会员卡密码错误');
    				$this->splash('failed',null,$msg,'','',true);
    			}
    		}else{//卡号存在且未被激活
    			$new_card = '1';
    			$card_psw_isright = $this->app->model('member_card')->getList('*',array('card_number'=>$card,'card_password'=>$card_password));
    			if(!$card_psw_isright){//直接对比会员卡表中的密码是否一致即可
    				$msg = app::get('b2c')->_('会员卡密码错误');
    				$this->splash('failed',null,$msg,'','',true);
    			}
    		}
    	}
    	$status = $this->userPassport->_bind_member_card($new_card, $type, $login_member_id, $card,$new_account_password);
    	switch ($status){
    		case 'update_log_failed' :
    			$msg = app::get('b2c')->_('绑定日志更新失败');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'wrong_email' :
    			$msg = app::get('b2c')->_('邮箱格式错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'wrong_mobile' :
    			$msg = app::get('b2c')->_('手机格式错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_coupon_failed' :
    			$msg = app::get('b2c')->_('更新优惠券错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_passwd_failed' :
    			$msg = app::get('b2c')->_('更新密码错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'old_member_wrong' :
    			$msg = app::get('b2c')->_('当前会员信息错误');
    			$this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'insert_membercard_wrong' :
    			$msg = app::get('b2c')->_('会员卡注入错误');
    			$this->splash('failed',null,$msg,'','',true);
    		case 'add_advance_wrong' :
    			$msg = app::get('b2c')->_('增加预存款失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'reduce_advance_wrong' :
    			$msg = app::get('b2c')->_('减少预存款失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'add_point_wrong' :
    			$msg = app::get('b2c')->_('增加积分失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'reduce_point_wrong' :
    			$msg = app::get('b2c')->_('减少积分失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'delete_oldcard_failed' :
    			$msg = app::get('b2c')->_('删除绑定的旧会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_level_failed' :
    			$msg = app::get('b2c')->_('等级更新失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_newcard_failed' :
    			$msg = app::get('b2c')->_('更新新会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_oldcard_failed' :
    			$msg = app::get('b2c')->_('更新旧会员卡失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_oldmember_failed' :
    			$msg = app::get('b2c')->_('更新旧会员失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_cardmember_failed' :
    			$msg = app::get('b2c')->_('更新会员卡会员失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_old_cardmember_failed' :
    			$msg = app::get('b2c')->_('更新旧会员卡会员失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'update_lo_failed' :
    			$msg = app::get('b2c')->_('更新日志失败');
    			 $this->splash('failed',null,$msg,'','',true);
    			break;
    		case 'ok' :
    			$msg=app::get('b2c')->_('绑定成功！');
            	$url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'logout'));
            	$this->splash('success',$url,$msg,'','',true);
    			break;
    	}
    }
    
    /*
     *保存会员绑定信息
     * */
    function save_weixin(){
        $userPassport = kernel::single('b2c_user_passport');
        $mobile = strlen($_POST['login_account'.'_'.$bind_type]);
        if($mobile < 11){
            $this->splash('failed',null,'请使用手机绑定','','',true);exit;
        }
        $data['pam_account']['login_name'] = $_POST['login_account'.'_'.$bind_type];
        $_POST['license'] = 'on';
        $data['pam_account']['login_password'] = $_POST['login_password'.'_'.$bind_type];
        $data['pam_account']['psw_confirm'] = $_POST['login_password'.'_'.$bind_type];
        $data['vcode'] = $_POST['vcode'.'_'.$bind_type];
        if( !$userPassport->check_signup($data,$msg) ){
            $this->splash('failed',null,$msg,'','',true);exit;
            //$this->splash('failed',null,$msg,'','',true);exit;
        }
        $passwdlen = strlen($_POST['login_password'.'_'.$bind_type]);
        if($mobile==11){
            $obj_member = app::get('pam')->model('members');
            $sdf = $obj_member->getList('login_account',array('login_type'=>'mobile','login_account'=>$_POST['login_account'.'_'.$bind_type]));
            if($sdf[0]['login_account']){
               $msg=app::get('b2c')->_('该手机已存在！');
               $this->splash('failed',null,$msg,'','',true);
            }
            $type_login = 'mobile';
        }elseif($passwdlen<6){
            $msg=app::get('b2c')->_('密码长度至少为六位！');
            $this->splash('failed',null,$msg,'','',true);
        }else{
            $msg=app::get('b2c')->_('手机或邮箱格式不正确！');
            $this->splash('failed',null,$msg,'','',true);
        }
        $member = $this->member;
        //会员手机验证赠送积分
        $reason_type = 'mobile_score';
        $point = 300;
        $data_rand = rand(0,10);
        $error_msg = '赠送失败';
        $member_id = intval($this->app->member_id);
        $app = app::get('b2c');
        $app->model('member_point')->change_point($member_id,+$point,$error_msg,$reason_type,$data_rand,$member_id,$member_id);
        if($userPassport->set_new_account($this->app->member_id,trim($_POST['login_account'.'_'.$bind_type]),$msg) ){
            $userPassport->reset_passport($this->app->member_id,$_POST['login_password'.'_'.$bind_type]);
           //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($this->app->member_id);
            }
           $msg=app::get('b2c')->_('绑定成功！');
            $url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            $this->splash('success',$url,$msg,'','',true); 
        }else{
            $msg=app::get('b2c')->_('绑定失败！');
            $this->splash('failed',null,$msg,'','',true);
        }
        
        
    }
    
    /*
     *保存修改密码
     * */
    function save_security(){
        $passport_login = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'login'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'logout','arg0'=>$passport_login));
        $userPassport = kernel::single('b2c_user_passport');
        $result = $userPassport->save_security($this->app->member_id,$_POST,$msg);
        if($result){
            $this->splash('success',$url,$msg,'','',true);
        }else{
            $this->splash('failed',null,$msg,'','',true);
        }
    }
    
    function message($nMsgId=false, $status='send') { //给管理员发信件
		 $this->pagedata['title']= '意见反馈';
        $num=$this->get_msg_num(); //方法
		if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($init['author_id'] == $this->app->member_id)
            {
                $this->pagedata['init'] = $init;
                $this->pagedata['msg_id'] = $nMsgId;
            }
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
        $this->pagedata['controller'] = "inbox";
        $this->set_tmpl_file("/default2.html");
        //$this->output();
		$this->page('wap/member/member_message.html');//文件路径
    }
	
	 /*
     *获取收件箱未读信息数量
     * */
    function get_msg_num(){
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $this->pagedata['inbox_num'] = count($row)?count($row):0;
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $this->pagedata['outbox_num'] = count($row)?count($row):0;
    }
	
	
	 
    /*
     *发送站内信
     * */
    function send_msg(){
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
                $this->splash('success','member/index.html',app::get('b2c')->_('保存到草稿箱成功'),$_POST['response_json'],'',true);
            }else{
                $this->splash('success','member/index.html',app::get('b2c')->_('发送成功'),$_POST['response_json'],'',true);
            }
            } else {
                $this->splash('failed','wap/member/member_message.html',app::get('b2c')->_('发送失败'),$_POST['response_json'],'',true);
            
			}
        }
        else {
            $this->splash('failed','member-message.html',app::get('b2c')->_('必填项不能为空'),'','',true);
        
		}
		
    }

	 /*
     * 我的推荐人
     * */
	function referee() {
		$this->pagedata['title']= '我的推荐人';
		$rs = $this->app->model('referee')->getList('*',array('member_id'=>$this->app->member_id));
		if ($rs) {
			$this->pagedata['referee_account'] = $rs[0]['referee_account'];
		}
	//	var_dump($rs);
		$this->page('wap/member/member_referee.html');
	}
	
	 /*
     * 我的推荐人保存
     * */
	function referee_save() {
		$referee_account = $_POST['referee_account'];
		$this->userObject = kernel::single('b2c_user_object');
		$referee_id = $this->userObject->get_member_id_by_username($referee_account);
		
        if(!$referee_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'referee'));
            $msg = app::get('b2c')->_('该账号不存在，请检查'); 
            $this->splash('failed',$url,$msg);
        }
		
	//	$this->app->member_id;
		$referee_obj = $this->app->model('referee');
		$referee_count = $referee_obj->count(array('member_id'=>$this->app->member_id));
		if ($referee_count) {
			$data = array();
			$data['referee_id'] = $referee_id;
			$data['referee_account'] = $referee_account;
			$referee_obj->update($data,array('member_id'=>$this->app->member_id));
		} else {
			$data = array();
			$data['member_id'] = $this->app->member_id;
			$data['referee_id'] = $referee_id;
			$data['referee_account'] = $referee_account;
			$referee_obj->insert($data);
		}
        $this->splash('success','member/index.html',app::get('b2c')->_('保存成功'));
	}
	
	/**
	 *  会员中心充值券充值
	 */
	function giftcard(){
		$this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index','full'=>1)));
		$this->path[] = array('title'=>app::get('b2c')->_('充值券充值'),'link'=>'#');
		$GLOBALS['runtime']['path'] = $this->path;
		

		$this->pagedata['total'] = $this->member['advance'];
		$this->pagedata['member_id'] = $this->app->member_id;
		$this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'giftcard'));
		
		$this->page('wap/member/giftcard.html');
	}
	
	/**
	 * 会员中心 充值券充值 提交
	 */
	function gc_dopayment($pay_object){
		
		$url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'giftcard','full'=>1));
		//验证验证码
		if($_POST["verifycode"]){
			if(!base_vcode::verify('b2c_wap_gc',$_POST['verifycode']))
			{
				$msg = app::get('b2c')->_("验证码输入错误!");
				$this->splash('failed',$url,$msg,false,0,true);
				exit;
			}
		}
		if( 'giftcard' == $pay_object ){
			$memMdl = $this->app->model('members');
			$giftcardMdl = app::get('b2c')->model('member_giftcard');
			$gcard_code = $_POST['giftcard']['gcard_code'];
			$member_id = $this->app->member_id;
			
			//开始事务
			$this->begin($this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'giftcard','arg0'=>time())));
			
			if($memMdl->gc_validate($gcard_code,$msg)){
				
				//验证成功清除session
				$_SESSION['giftcard'][$member_id] = 0;
		
				//处理充值
				$gc_info = $giftcardMdl->getList('*',array('gcard_code'=>$gcard_code));
				$gc_info = $gc_info[0];
		
				$advanceMdl = app::get('b2c')->model('member_advance');
				 
				$pam_account = app::get('pam')->model('account');
				$account = $pam_account->getList("login_name",array('account_id'=>$member_id));
				$u_name = $account[0]['login_name'];
				$message = '充值券充值,券号：'.$gcard_code;
		
				//标记充值券已使用
				$is_true = $giftcardMdl->update(array('used_status'=>'true','uname'=>$u_name,'used_time'=>time()),array('gcard_code'=>$gcard_code));
				 
				$affect_row = $advanceMdl->db->affect_row();
				if($is_true){
		
					//开始事务
					$db = kernel::database();
					$transaction_status = $db->beginTransaction();
		
					if($affect_row){
						$db->commit($transaction_status);
		
						//金额写入预存款
						$rerurn = $advanceMdl->add($member_id,$gc_info['gcard_money'],$message,$errMsg);
						if($rerurn){
							
							// 增加经验值
							$obj_member = $this->app->model('members');
							$obj_member->change_exp($member_id, floor($gc_info['gcard_money']));
							
							$this->end(true,app::get('b2c')->_('充值券充值成功！'),null,false,true);
						}else{
							$db->rollback();
							$this->end(false,$errMsg,null,false,true);
						}
					}else{
						$db->rollback();
						$this->end(false,app::get('b2c')->_('您发出了重复的请求，该请求只能生效一次！'),null,false,true);
					}
					 
				}else{
					//事件回滚
					$db->rollback();
					$this->end(false,app::get('b2c')->_('充值券状态更新失败！'),null,false,true);
				}
		
			}else{
				$this->splash('failed',$url,$msg,false,0,true);
				exit;
			}
			 
		}
		
	}
	
	public function bind_member_card(){
		$this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
		$this->path[] = array('title'=>app::get('b2c')->_('会员卡绑定设置'),'link'=>'#');
		$GLOBALS['runtime']['path'] = $this->path;
		$member_id = intval($this->app->member_id);
		$member_id = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id,'login_type'=>'local','disabled'=>'false'));
		$this->pagedata['title'] = app::get('b2c')->_('会员卡绑定');
		$this->page('wap/member/bind_member_card.html');
	}
	
	/*
	 *保存会员绑定信息
	* */
	function save_weixi2n(){
		$userPassport = kernel::single('b2c_user_passport');
		$card = trim($_POST['card_number']);
		$card_password = trim($_POST['card_password']);
		$userPassport->_bind_member_card();
		if(!is_numeric($card) && strlen($card) != 8){
			$this->splash('failed',null,'请输入正确的会员卡号','','',true);exit;
		}
		
		$member_id = intval($this->app->member_id);
		
		$data['pam_account']['login_name'] = $_POST['login_account'];
		$_POST['license'] = 'on';
		$data['pam_account']['login_password'] = $_POST['login_password'];
		$data['pam_account']['psw_confirm'] = $_POST['login_password'];
		$data['vcode'] = $_POST['vcode'];
		if( !$userPassport->check_signup($data,$msg) ){
			$this->splash('failed',null,$msg,'','',true);exit;
			//$this->splash('failed',null,$msg,'','',true);exit;
		}
		$passwdlen = strlen($_POST['login_password']);
		if($mobile==11){
			$obj_member = app::get('pam')->model('members');
			$sdf = $obj_member->getList('login_account',array('login_type'=>'mobile','login_account'=>$_POST['login_account']));
			if($sdf[0]['login_account']){
				$msg=app::get('b2c')->_('该手机已存在！');
				$this->splash('failed',null,$msg,'','',true);
			}
			$type_login = 'mobile';
		}elseif($passwdlen<6){
			$msg=app::get('b2c')->_('密码长度至少为六位！');
			$this->splash('failed',null,$msg,'','',true);
		}else{
			$msg=app::get('b2c')->_('手机或邮箱格式不正确！');
			$this->splash('failed',null,$msg,'','',true);
		}
		$member = $this->member;
		//会员手机验证赠送积分
		$reason_type = 'mobile_score';
		$point = 300;
		$data_rand = rand(0,10);
		$error_msg = '赠送失败';
		$member_id = intval($this->app->member_id);
		$app = app::get('b2c');
		$app->model('member_point')->change_point($member_id,+$point,$error_msg,$reason_type,$data_rand,$member_id,$member_id);
		if($userPassport->set_new_account($this->app->member_id,trim($_POST['login_account']),$msg) ){
			$userPassport->reset_passport($this->app->member_id,$_POST['login_password']);
			//增加会员同步 2012-05-15
			if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
				$member_rpc_object->modifyActive($this->app->member_id);
			}
			$msg=app::get('b2c')->_('绑定成功！');
			$url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
			$this->splash('success',$url,$msg,'','',true);
		}else{
			$msg=app::get('b2c')->_('绑定失败！');
			$this->splash('failed',null,$msg,'','',true);
		}
	
	
	}
	
	public function check_bind_info($wei_member,$account,$bind_type){
		$userPassport = kernel::single('b2c_user_passport');
		foreach($wei_member as $wm){
			if($wm['login_type'] == 'email'){
				$wei_email = $wei_email + 1;
			}
			if($wm['login_type'] == 'local'){
				$wei_local = $wei_local + 1;
				$local_type = $userPassport->get_local_account_type($wm['login_account']);
				if($local_type == 'account'){
					$wei_local_account = $wei_local_account + 1;
				}
				if($local_type == 'card'){
					$wei_local_card = $wei_local_card + 1;
				}
				if($local_type == 'openid'){
					$wei_local_openid = $wei_local_openid + 1;
				}
			}
			if($wm['login_type'] == 'mobile'){
				$wei_mobile = $wei_mobile + 1;
			}
		}
		
		if($bind_type == 'email' && $wei_email >=1){
			$msg = app::get('b2c')->_('当前账号已经绑定过邮箱了！');
			$this->splash('failed',null,$msg,'','',true);
		}
		if($bind_type == 'mobile' && $wei_mobile >=1){
			$msg = app::get('b2c')->_('当前账号已经绑定过手机了！');
			$this->splash('failed',null,$msg,'','',true);
		}
		if($bind_type == 'account' || $bind_type == 'card'){
			$local_bind_type = $userPassport->get_local_account_type($account);
			if($local_bind_type == 'account' && $wei_local_account >= 1){
				$msg = app::get('b2c')->_('当前账号已经绑定过账号了！');
				$this->splash('failed',null,$msg,'','',true);
			}
			if($local_bind_type == 'card' && $wei_local_card >= 1){
				$msg = app::get('b2c')->_('当前账号已经绑定过会员卡了！');
				$this->splash('failed',null,$msg,'','',true);
			}
			if($local_bind_type == 'openid' && $wei_local_openid >= 1){
				$msg = app::get('b2c')->_('当前账号已经绑定过微信了！');
				$this->splash('failed',null,$msg,'','',true);
			}
		}
	}
	
}
