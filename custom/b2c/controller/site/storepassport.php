<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_site_storepassport extends b2c_frontpage{
    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
        $this->userObject = kernel::single('b2c_user_object');
        $this->userPassport = kernel::single('b2c_user_passport');
    }

    function index()
    {
        $this->path[] = array('title'=>app::get('b2c')->_('门店店员登录'),'link '=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            /*
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            //您已经是登录状态，不需要重新登录
            $this->redirect($url);
            */
        	$url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
        	$this->logout($url);
        }
        
        if($this->mini_login()) return;
       
        $_SESSION['next_page'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x";
        
        $this->gen_login_form();
        $this->set_tmpl('storepassport');
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');
        foreach(kernel::servicelist('openid_imageurl') as $object)
        {
            if(is_object($object))
            {
                if(method_exists($object,'get_image_url'))
                {
                    $this->pagedata['login_image_url'][] = $object->get_image_url();
                }
            }
        }
        
        
        $this->pagedata['page_title'] = "商店登陆";
        $this->pagedata['res_url'] = app::get('b2c')->res_url;
        $this->page('site/storepassport/index.html');
    }


    function getuname()
    {
        $member = $this->get_current_member();
        $uname = $member['uname'] ? $member['uname'] : '';
        echo $uname;
        exit;
    }
    
    function login($mini=0)
    {
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
    	$this->redirect($url);exit;
    	
        $this->path[] = array('title'=>app::get('b2c')->_('会员登录'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        foreach(kernel::servicelist('openid_imageurl') as $object)
        {
            if(is_object($object))
            {
                if(method_exists($object,'get_image_url'))
                {
                    $this->pagedata['login_image_url'][] = $object->get_image_url();
                }
            }
        }
        if(!isset($_SESSION['next_page']))
        {
            $_SESSION['next_page'] = $_SERVER['HTTP_REFERER'];
        }
        if(strpos($_SESSION['next_page'],'passport')) unset($_SESSION['next_page']);
        
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            $this->bind_member($_SESSION['account'][pam_account::get_account_type($this->app->app_id)]);
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            if($_GET['mini_passport']==1 || $mini)
            {
                kernel::single('site_router')->http_status(404);return;
            }else
            {
                //您已经是登录状态，不需要重新登录
                $this->redirect($url);
            }
        }
        $flag = false;
        if($_GET['mini_passport']==1 || $mini) {
            $flag = true;
            $this->pagedata['mini_passport'] = 1;
            $this->pagedata['no_right'] = 1;
        }
        $this->pagedata['page_title'] = "商店登陆";
        $this->gen_login_form($_GET['mini_passport']);
        $this->set_tmpl('storepassport');
        $this->pagedata['res_url'] = app::get('b2c')->res_url;
        
        $this->page('site/storepassport/index.html', $flag);
    }

   /**
     * 打印选定订单
     * @param null
     * @return null
     */
    /**
     * 打印订单的接口
     * @param string 打印类型
     * @param string order id
     * @return null
     */
    public function print_order($order_id)
    {
        $order = &$this->app->model('orders');
        $member = &$this->app->model('members');
        //$order->setPrintStatus($order_id,$type,true);

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        
        $orderInfo = $order->dump($order_id, '*', $subsdf);
        
       // $orderInfo['self'] = $this->objMath->number_minus(array(0, $orderInfo['discount'], $orderInfo['pmt_goods'], $orderInfo['pmt_order']));

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $memberInfo = $member->getList('*', array('member_id'=>$orderInfo['member_id']));
        $order_items = array();
        $gift_items = array();
        foreach ($orderInfo['order_objects'] as $k=>$v)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            if ($v['obj_type'] == 'goods')
            {
                foreach ($v['order_items'] as $key => $item)
                {
                    if (!$item['products'])
                    {
                        $o = $this->app->model('order_items');
                        $tmp = $o->getList('*', array('item_id'=>$item['item_id']));
                        $item['products']['product_id'] = $tmp[0]['product_id'];
                    }

                    if ($item['item_type'] != 'gift')
                    {
                        if ($item['item_type'] == 'product')
                            $item['item_type'] = 'goods';
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$item['item_type']];
                        $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product'=>$item['products']['product_id']), $arrGoods, 'admin_order_printing');

                        $gItems[$k]['addon'] = unserialize($item['addon']);

                        if ($item['addon'] && unserialize($item['addon']))
                        {
                            $gItems[$k]['minfo'] = unserialize($item['addon']);
                        }
                        else
                        {
                            $gItems[$k]['minfo'] = array();
                        }

                        if ($item['item_type'] == 'goods')
                        {
                            $order_items[$k] = $item;
                            $order_items[$k]['small_pic'] = $arrGoods['image_default_id'] ? $arrGoods['image_default_id'] : '';
                            $order_items[$k]['is_type'] = $v['obj_type'];
                            $order_items[$k]['item_type'] = ($arrGoods) ? $arrGoods['category']['cat_name'] : '';
                            $order_items[$k]['minfo'] = $gItems[$k]['minfo'];
                            $order_items[$k]['link_url'] = $arrGoods['link_url'];
                            $order_items[$k]['goods_keywords'] = app::get('b2c')->model('goods_keywords')->getList('keyword',array('goods_id'=>$item['goods_id']));

                            $order_items[$k]['name'] = $item['name'];
                            if ($item['addon'])
                            {
                                $item['addon'] = unserialize($item['addon']);
                                if ($item['addon']['product_attr'])
                                {
                                    $order_items[$k]['name'] .= '(';
                                    foreach ($item['addon']['product_attr'] as $arr_special_info)
                                    {
                                        $order_items[$k]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、');
                                    }
                                    $order_items[$k]['name'] = substr($order_items[$k]['name'], 0, strrpos($order_items[$k]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['name'] .= ')';
                                }
                            }
                            $order_items[$k]['products']['spec_info'] = substr($item['products']['spec_info'],6);
                        }
                        else
                        {
                            $order_items[$k]['adjunct'][$index_adj] = $item;
                            $order_items[$k]['adjunct'][$index_adj]['small_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['adjunct'][$index_adj]['is_type'] = $v['obj_type'];
                            $order_items[$k]['adjunct'][$index_adj]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['adjunct'][$index_adj]['link_url'] = $arrGoods['link_url'];

                            $order_items[$k]['adjunct'][$index_adj]['name'] = $item['name'];
                            if ($item['addon'])
                            {
                                $item['addon'] = unserialize($item['addon']);
                                if ($item['addon']['product_attr'])
                                {
                                    $order_items[$k]['adjunct'][$index_adj]['name'] .= '(';
                                    foreach ($item['addon']['product_attr'] as $arr_special_info)
                                    {
                                        $order_items[$k]['adjunct'][$index_adj]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、');
                                    }
                                    $order_items[$k]['adjunct'][$index_adj]['name'] = substr($$order_items[$k]['adjunct'][$index_adj]['name'], 0, strpos($order_items[$k]['adjunct'][$index_adj]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['adjunct'][$index_adj]['name'] .= ')';
                                }
                            }

                            $index_adj++;
                        }
                    }
                    else
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$item['item_type']];
                        $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product'=>$item['products']['product_id']), $arrGoods, 'admin_order_printing');

                        $order_items[$k]['gifts'][$index_gift] = $item;
                        $order_items[$k]['gifts'][$index_gift]['small_pic'] = $arrGoods['image_default_id'];
                        $order_items[$k]['gifts'][$index_gift]['is_type'] = $v['obj_type'];
                        $order_items[$k]['gifts'][$index_gift]['item_type'] = $arrGoods['category']['cat_name'];
                        $order_items[$k]['gifts'][$index_gift]['link_url'] = $arrGoods['link_url'];

                        $order_items[$k]['gifts'][$index_gift]['name'] = $item['name'];
                        if ($item['addon'])
                        {
                            $item['addon'] = unserialize($item['addon']);
                            if ($item['addon']['product_attr'])
                            {
                                $order_items[$k]['gifts'][$index_gift]['name'] .= '(';
                                foreach ($item['addon']['product_attr'] as $arr_special_info)
                                {
                                    $order_items[$k]['gifts'][$index_gift]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、');
                                }
                                $order_items[$k]['gifts'][$index_gift]['name'] = substr($order_items[$k]['gifts'][$index_gift]['name'], 0, strpos($order_items[$k]['gifts'][$index_gift]['name'], app::get('b2c')->_('、')));
                                $order_items[$k]['gifts'][$index_gift]['name'] .= ')';
                            }
                        }

                        $index_gift++;
                    }
                }
            }
            
        }

        $order_sum = $this->sum_order($orderInfo['member_id']);
      
        $this->pagedata['goodsItem'] = $order_items;#print_r($order_items);exit;
        $this->pagedata['giftsItem'] = $gift_items;
        $this->pagedata['extend_items'] = $extend_items;
        $orderInfo['consignee']['telephone'] = $orderInfo['consignee']['telephone'] ? $orderInfo['consignee']['telephone'] :$orderInfo['consignee']['mobile'];
        $this->pagedata['orderInfo'] = $orderInfo;
        $this->pagedata['orderSum'] = $order_sum;
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
        $this->pagedata['storeplace_display_switch'] = $this->app->getConf('storeplace.display.switch');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $logo_id = app::get('b2c')->getConf('site.logo');
        $this->pagedata['logo_image'] = base_storager::image_path($logo_id);
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['shop'] = array(
            'name'=>app::get('site')->getConf('site.name'),
            'url'=>kernel::base_url(true),
            'email'=>$this->app->getConf('store.email'),
            'tel'=>$this->app->getConf('store.telephone'),
            'logo'=>$this->app->getConf('site.logo')
        );
        $this->_systmpl = &$this->app->model('member_systmpl');
      
     
        $this->pagedata['printType'] = array("cart");
        $this->pagedata['printContent']['cart'] = true;
        $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
        $this->pagedata['content_cart'] = $this->_systmpl->fetch('admin/order/print_cart',$this->pagedata);
        $this->pagedata['page_title'] = app::get('b2c')->_('购物单打印');
        $this->set_tmpl('storepassport');
        $this->display('site/storepassport/print_cart.html');

    }
    
    /**
     * 求出同一个会员对应订单的总额
     * @param string member id
     * @return array 订单数组
     */
    public function sum_order($member_id=null)
    {
        $obj_order = $this->app->model('orders');
        $aData = $obj_order->getList('total_amount',array('member_id' => $member_id));
        if($aData){
            $row['sum'] = count($aData);
            $row['sum_pay'] = 0;
            foreach($aData as $val){
                $row['sum_pay'] = $row['sum_pay']+$val['total_amount'];
            }
        }
        else{
            $row['sum'] = 0;
            $row['sum_pay'] = 0;
        }
        return $row;
    }
    
    private function mini_login()
    {
        if($_GET['mini_passport']==1)
        {
            $this->gen_login_form_mini();
            $this->pagedata["mini_passport"] = 1;
            $shop['base_url'] = kernel::base_url().'/';
            $this->pagedata['shopDefine'] = json_encode($shop);
            return true;
        }
        return false;
    }

    function gen_login_form_mini()
    {
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        #设置回调函数地址
        $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login'))));
        foreach(kernel::servicelist('passport') as $k=>$passport)
        {
            if($auth->is_module_valid($k))
            {
                $this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth,$signup_url),
                    );
            }
        }
    }


    function gen_login_form()
    {
        if($_SESSION['next_page'])
        {
            $url = $_SESSION['next_page'];
            $_SESSION['signup_next'] = $_SESSION['next_page'];
        }
        else
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        unset($_SESSION['next_page']);
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        $auth->set_appid($this->app->app_id);
        
        $obj_local_store = app::get('ome')->model('branch');
        $pagedata['local_store'] = $obj_local_store->getList('branch_id,name',array());
        
        if($_GET['mini_passport'] == 1)
        $pagedata['signup_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup'))."?mini_passport=1";
        else
        $pagedata['signup_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup'));
        $pagedata['lost_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'lost'));
        $pagedata['loginName'] = $_COOKIE['loginName'];
        #设置回调函数地址
        if($_GET['mini_passport']==1)
        {
            $redirect_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login','arg'=>base64_encode($url)));
            $auth->set_redirect_url(base64_encode($redirect_url));
        }
        else
         $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login','arg'=>base64_encode($url)))));
        
        foreach(kernel::servicelist('passport') as $k=>$passport)
        {
            if($auth->is_module_valid($k))
            {
                $this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth, 'b2c', 'site/storepassport/member-signin.html', $pagedata),
                    );
            }
        }
    }


    function post_login_mini()
    {
        $member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        if($member_id)
        {
            $this->bind_member($member_id);
            $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')),app::get('b2c')->_('登录成功'),true);
        }
        else
        {
            $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),app::get('b2c')->_('登录失败'),true);
        }
    }
    
    /*
     * 登录验证
     * */
    public function post_login_webpos(){
        //_POST过滤
        $post = utils::_filter_input($_POST);
        unset($_POST);
        $userData = array(
            'login_account' => $post['uname'],
        );
        
        $member_id = kernel::single('pam_passport_site_basic')->login_webpos($userData,'',$msg);
        if(!$member_id){ 
          $msg = app::get('b2c')->_('登陆账号错误'); 
            $this->splash('failed',null,$msg,true);exit;
        }
        $b2c_members_model = $this->app->model('members');
        $member_point_model = $this->app->model('member_point');

        $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
        
       
        $member_data = $member_data[0];
        $member_data['order_num'] = $this->app->model('orders')->count( array('member_id'=>$member_id) );

        if($this->app->getConf('site.level_switch')==1)
        {
            $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
        }
        if($this->app->getConf('site.level_switch')==0)
        {
            $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
        }

        $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        $this->userObject->set_member_session($member_id);
        $this->bind_member($member_id);
        $this->set_cookie('loginName',$post['uname'],time()+31536000);//用于记住密码
        $this->set_cookie('loginType','store',time()+31536000);//hack by Jason 门店登录的标志写入cookie中
        $this->app->model('cart_objects')->setCartNum();
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x";
        $this->splash('success',$url,app::get('b2c')->_('登录成功'),true);
    }//end function
    
    /*
     * 匿名登录
     * */
    public function post_login_webpos_niming(){
        //_POST过滤
        $post['uname'] = '匿名账号';
        $userData = array(
            'login_account' => $post['uname'],
        );
        
        $member_id = kernel::single('pam_passport_site_basic')->login_webpos($userData,'',$msg);
        if(!$member_id){ 
            $msg = app::get('b2c')->_('登陆账号错误'); 
            $this->splash('failed',null,$msg,true);exit;
        }
        $b2c_members_model = $this->app->model('members');
        $member_point_model = $this->app->model('member_point');

        $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
        
       
        $member_data = $member_data[0];
        $member_data['order_num'] = $this->app->model('orders')->count( array('member_id'=>$member_id) );

        if($this->app->getConf('site.level_switch')==1)
        {
            $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
        }
        if($this->app->getConf('site.level_switch')==0)
        {
            $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
        }

        $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        $this->userObject->set_member_session($member_id);
        $this->bind_member($member_id);
        $this->set_cookie('loginName',$post['uname'],time()+31536000);//用于记住密码
        $this->set_cookie('loginType','store',time()+31536000);//hack by Jason 门店登录的标志写入cookie中
        $this->app->model('cart_objects')->setCartNum();
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x";
        $this->redirect($url);
    }//end function

    /*
     * 登录验证
     * */
    public function post_login(){
        //_POST过滤
        $post = utils::_filter_input($_POST);
        $post_date =$_POST;
        unset($_POST);
        $userData = array(
            'login_account' => $post['uname'],
            'login_password' => $post['password']
        );
        
        $local_store_listData = app::get('b2c')->model('local_staff')->getRow('*',array('login_name'=>$post_date['uname'],'login_password'=>$post_date['password']));
        
       if($local_store_listData['staff_id']>0 && isset($local_store_listData['staff_id'])){
                if(isset($post_date['store']) && $post_date['store'] > 0){
                    
        		$obj_local_store = app::get('ome')->model('branch');
        		$local_store_list = $obj_local_store->getList('*',array(
        				'branch_id'=>$post_date['store'],
        		),0,1);
        		$local_store = $local_store_list[0];
        		if($local_store){
        			$_SESSION['local_store'] = $local_store;
        		}
        		
                        
        		$in_addr_data = $local_store;
        		$in_addr_data['member_id'] = $account[0]['member_id'];		
        	}
       }else{
           $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport','act'=>'index')),app::get('b2c')->_('员工账号或密码错误'),true);
       }
        if($local_store_listData['branch_id']!=$post['store']){
          $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport','act'=>'index')),app::get('b2c')->_('会员没有权限登录该门店！'),true); 
       }
        $this->userObject->set_member_session_webpos($local_store_listData);
        $this->set_cookie('loginName',$post['uname'],time()+31536000);//用于记住密码
        $this->set_cookie('loginType','store',time()+31536000);//hack by Jason 门店登录的标志写入cookie中
        $this->app->model('cart_objects')->setCartNum();
        app::get('b2c')->model('local_staff')->update(array('logintime'=> time()),array('staff_id'=>$_SESSION['account']['staff']));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x";
        $this->splash('success',$url,app::get('b2c')->_('登录成功'),true);
    }//end function
    
    public function passwordcheck()
    {
        $psd = trim($_POST['pam_account']['login_password']);
        if(strlen($psd) == 0){
            echo json_encode(array('error'=>app::get('b2c')->_('请填写登录密码，最少8个数字')));exit;
        }

        if(strlen($psd) < 8){
            echo json_encode(array('error'=>app::get('b2c')->_('登录密码最少8个数字')));exit;
        }elseif(strlen($psd)>100){
            echo json_encode(array('error'=>app::get('b2c')->_('登录密码过长，请换一个重试')));exit;
        }

        if(!preg_match('/^\+?[1-9][0-9]*$/', $psd)){
            echo json_encode(array('error'=>app::get('b2c')->_('该密码账号包含非法字符')));exit;
        }
    }
    
    public function webpos_passwordcheck()
    {
            $member_id = $_SESSION['account']['member'];
            $account = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
            $use_pass_data['login_name'] = $account[0]['password_account'];
            $use_pass_data['createtime'] = $account[0]['createtime'];
            $login_password = pam_encrypt::get_encrypted_password(trim($_POST['password']),'member',$use_pass_data);
            
            if($login_password !== $account[0]['login_password']){
                    echo json_encode(array('ret'=>app::get('b2c')->_('会员密码错误!')));
                    return;
              }else{
                    echo json_encode(array('ret'=>app::get('b2c')->_('会员密码正确!')));
                    return;
              }
    }

    public function namecheck()
    {
        $obj_member=&$this->app->model('members');
        $name = trim($_POST['pam_account']['login_name']);
        if(strlen($name) == 0){
            echo json_encode(array('error'=>app::get('b2c')->_('请填写登录帐号，最少4个字符')));exit;
        }

        if(strlen($name) < 4){
            echo json_encode(array('error'=>app::get('b2c')->_('登录账号最少4个字符')));exit;
        }elseif(strlen($name)>100){
            echo json_encode(array('error'=>app::get('b2c')->_('登录账号过长，请换一个重试')));exit;
        }

        if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', $name)){
            echo json_encode(array('error'=>app::get('b2c')->_('该登录账号包含非法字符')));exit;
        }else{
            if(!$obj_member->is_exists($name)){
                echo json_encode(array('success'=>app::get('b2c')->_('该登录账号可用')));exit;
            }else{
                echo json_encode(array('error'=>app::get('b2c')->_('该登录账号已经被占用，请换一个重试')));exit;
            }
        }
    }

    function emailcheck()
    {
        $email = trim($_POST['contact']['email']);
        if(!$email) {
            echo json_encode(array('error'=>app::get('b2c')->_('请填写邮箱')));
            exit;
        }
        if(!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i', $email)){
            echo json_encode(array('error'=>app::get('b2c')->_('请填写正确的邮箱，例如：yourname@hotmail.com')));
            exit;
        }
        $obj_member = $this->app->model('members');
        $account = app::get('pam')->model('account')->getList('login_name',array('login_name'=>$email));
        $m_email = $obj_member->is_exists_email($email);
        if($m_email || $account)
        {
            echo json_encode(array('error'=>app::get('b2c')->_('该邮箱已被使用，请换一个重试')));
            exit;
        }else{
            // echo json_encode(array('success'=>app::get('b2c')->_('该邮箱可以使用')));
            exit;
        }
    }

    public function mobilecheck()
    {
        $obj_member=&$this->app->model('members');
        $psd = trim($_POST['contact']['phone']['mobile']);
        if(strlen($psd) == 0){
            echo json_encode(array('error'=>app::get('b2c')->_('请填写手机号码')));exit;
        }

        if(strlen($psd) != 11){
            echo json_encode(array('error'=>app::get('b2c')->_('手机号码格式输入不正确')));exit;
        }else{
            if(!$obj_member->is_exists_mobile($psd)){
                echo json_encode(array('success'=>app::get('b2c')->_('该手机号可用')));exit;
            }else{
                echo json_encode(array('error'=>app::get('b2c')->_('该手机号已经被占用，请换一个重试')));exit;
            }
        }
        
            
        if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$psd)){
            echo json_encode(array('error'=>app::get('b2c')->_('该手机号码包含非法字符')));exit;
        }
    }
    
    public function psw_confirmcheck()
    {
        $psd = trim($_POST['pam_account']['psw_confirm']);
        if(strlen($psd) == 0){
            echo json_encode(array('error'=>app::get('b2c')->_('请填写登录密码，最少8个数字')));exit;
        }

        if(strlen($psd) < 8){
            echo json_encode(array('error'=>app::get('b2c')->_('登录密码最少8个数字')));exit;
        }elseif(strlen($psd)>100){
            echo json_encode(array('error'=>app::get('b2c')->_('登录密码过长，请换一个重试')));exit;
        }

        if(!preg_match('/^\+?[1-9][0-9]*$/', $psd)){
            echo json_encode(array('error'=>app::get('b2c')->_('该密码账号包含非法字符')));exit;
        }
    }
    
    
    function verify(){
        $this->begin($this->gen_url('passport','login'));
        $member_model = &$this->app->model('members');
        $verifyCode = app::get('b2c')->getConf('site.register_valide');
        if($verifyCode == "true"){
        if(!base_vcode::verify('LOGINVCODE',strval($_POST['loginverifycode']))){
               $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),app::get('b2c')->_('验证码错误'),true);

            }
        }
        $rows=app::get('pam')->model('account')->getList('account_id',array('account_type'=>'member','disabled' => 'false','login_name'=>$_POST['login'],'login_password'=>pam_encrypt::get_encrypted_password($_POST['passwd'],pam_account::get_account_type($this->app->app_id),array('login_name'=>$_POST['login']))));
        if($rows){
            $_SESSION['account'][pam_account::get_account_type($this->app->app_id)] = $rows[0]['account_id'];
            $this->bind_member($rows[0]['account_id']);
            $this->end(true,app::get('b2c')->_('登录成功，进入会员中心'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')));
        }else{
            $_SESSION['login_msg']=app::get('b2c')->_('用户名或密码错误');
            $this->end(false,$_SESSION['login_msg'],$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login')));
        }
    }

    function __restore(){
        if($_SESSION['login_info']['post']){
            call_user_func_array(array(&$this,'redirect'),$_SESSION['login_info']['action']);
        }
    }

    function signup($url=null)
    {
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member', 'full' => 1));
            $this->redirect($url);
        }
        $this->path[] = array('title'=>app::get('b2c')->_('会员注册'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $login_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login'));
        foreach(kernel::servicelist('api_signup') as $signup)
        {
            if(is_object($signup))
            {
                if($signup->get_status())
                {
                    $signup_url = $signup->get_url();
                    echo "<script>location.href='{$signup_url}';</script>";
                }
            }
        }
        
        $_SESSION['signup_next'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport')); 
       
        $flag = false;
        if($_GET['mini_passport']==1)
        {
            $flag = true;
            $this->pagedata['mini_passport'] = 1;
        }
        $member_model = $this->app->model('members');
        $mem_schema = $member_model->_columns();
        $attr =array();
        foreach($this->app->model('member_attr')->getList() as $item)
        {
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item)
        {
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath)
            {
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1)
                {
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }
            else
            {
                $name = $item['attr_column'];
            }
            if($attr[$key]['attr_type'] == 'select' ||$attr[$key]['attr_type'] == 'checkbox'){
                $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
            }
            $attr[$key]['attr_column'] = $name;
            if($attr[$key]['attr_column']=="birthday"){
                $attr[$key]['attr_column'] = "profile[birthday]";
            }
        }
        $this->pagedata['attr'] = $attr;
        $this->pagedata['next_url'] = $url;
        $this->pagedata['page_title'] = "商店注册";
        $this->set_tmpl('storepassport');
        $this->pagedata['res_url'] = app::get('b2c')->res_url;  
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');
        if($flag) {
            if(defined('DEBUG_JS') && constant('DEBUG_JS')){
                $path = 'js';
            }
            else {
                $path = 'js_mini';
            }
            $shop['url']['datepicker'] = kernel::base_url().'/app/site/statics/'.$path;
            $shop['base_url'] = kernel::base_url().'/';
            $this->pagedata['shopDefine'] = json_encode($shop);
        }
        $this->page("site/storepassport/signup.html", $flag);

    }

    /**
     * save_attr
     * 保存会员注册信息
     *
     * @access private
     * @return bool
     */
    private function save_attr($member_id=null,$aData,&$msg)
    {
        if(!$member_id)
        {
            $msg = app::get('b2c')->_('注册失败');
            return false;
        }
        $member_model = &$this->app->model('members');
        $aData['pam_account']['account_id'] = $member_id;
        if(!$_POST['profile']['birthday']) unset($aData['profile']['birthday']);
        if($aData['profile']['gender'] == 1){
            $aData['profile']['gender'] = 'male';
        }
        elseif($aData['profile']['gender'] ===0){
            $aData['profile']['gender'] = 'female';
        }
        else{
            $aData['profile']['gender'] = 'no';
        }
        foreach($aData as $key=>$val)
        {
            if(strpos($key,"box:") !== false)
            {
                $aTmp = explode("box:",$key);
                $aData[$aTmp[1]] = serialize($val);
            }
        }

        if($aData['contact']['name']&&!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $aData['contact']['name']))
        {
            $msg = app::get('b2c')->_('姓名包含非法字符');
            return false;
        }
        $obj_filter = kernel::single('b2c_site_filter');
        $aData = $obj_filter->check_input($aData);
        if($member_model->save($aData))
        {
            /*$obj_emailconf = kernel::single('desktop_email_emailconf');
            $aTmp = $obj_emailconf->get_emailConfig();
            $acceptor =  $aData['contact']['email'];    //收件人邮箱
            $aTmp['shopname'] = app::get('site')->getConf('site.name');
            $subject = '注册成功';
            $body = "";
            $email = kernel::single('desktop_email_email');
            $email->ready($aTmp);
            $res = $email->send($acceptor,$subject,$body,$aTmp);*/
          
                
            $msg = app::get('b2c')->_('注册成功');
            return true;
        }
        $msg  = app::get('b2c')->_('注册失败');
        return false;

    }

   /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function create(){
        if($_POST['response_json'] == 'true'){
            $ajax_request = true;
        }else{
            $ajax_request = false;
        }
        if( !$this->userPassport->check_signup($_POST,$msg) ){
            $this->splash('failed',null,$msg,$ajax_request);
        }

        $saveData = $this->userPassport->pre_signup_process($_POST);
        
        
        if( $member_id = $this->userPassport->save_members($saveData) ){
            $this->userObject->set_member_session($member_id);
            $this->bind_member($member_id);
            foreach(kernel::servicelist('b2c_save_post_om') as $object) {
                $object->set_arr($member_id, 'member');
                $refer_url = $object->get_arr($member_id, 'member');
            }
            /*注册完成后做某些操作! begin*/
            foreach(kernel::servicelist('b2c_register_after') as $object) {
                $object->registerActive($member_id);
            }
            //增加会员同步 2012-5-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->createActive($member_id);
            }
            /*end*/
            $data['member_id'] = $member_id;
            $data['uname'] = $saveData['pam_account']['login_account'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['mobile'] = $_POST['contact']['mobile'];
            $data['refer_url'] = $refer_url ? $refer_url : '';
            $data['is_frontend'] = true;
            $obj_account=$this->app->model('member_account');
            $obj_account->fireEvent('register',$data,$member_id);
            if(!strpos($_SESSION['pc_next_page'],'cart')){
                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
            }else{
                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
            }
            
            //注册插入手机号码到tel字段里
            $savdee = $_POST['mobile'];
            $member_dd = $this->app->model('members');
            $dad = $member_dd->update(array('tel'=>$savdee), array('member_id' => $member_id));
            
            $this->splash('success',$url,app::get('b2c')->_('注册成功'),$ajax_request);
        }

        $this->splash('failed',$back_url,app::get('b2c')->_('注册失败'),$ajax_request);
    }

    //注册后跳转页面
    public function sign_tips(){
        $member_id = $this->userObject->get_member_id();
        if(!$member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport','act'=>'index'));
            $this->splash('failed',$url,app::get('b2c')->_('页面已过期，请重新登录在会员中心设置'));
        }

        $url = $this->userPassport->get_next_page('pc');
        if(!$url){
          $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        $pamMembersData = $this->userObject->get_pam_data('login_account',$member_id);
        if($pamMembersData['local']){
            $this->redirect($url);//已近设置则不需要在设置 直接跳转到会员中心
        }

        $this->pagedata['data'] = $pamMembersData;
        $this->pagedata['url'] = $url;

        $this->set_tmpl('passport');
        $this->page("site/passport/sign-tips.html");
    }

    function sendPSW()
    {
        $flag = false;
        $members = app::get('b2c')->model('members')->getList('member_id',array('tel'=>$_POST['mobile']));
        $membersdd = app::get('b2c')->model('members')->getList('member_id',array('mobile'=>$_POST['mobile']));
        if($members[0]['member_id'] != '' && $membersdd[0]['member_id'] != ''){
            $msg = app::get('b2c')->_('您的手机未注册，请检查是否填写错误。');
        }else {
            if(!$membersdd[0]){
                $members = $membersdd;
            }
            $account = app::get('pam')->model('account')->getList('login_name',array('account_id'=>$members[0]['member_id'],'account_type'=>'member'));
            $rend = $this->randomkeys(6);
            $resetpwdtime = time();
            $sdf['member_id'] = $members[0]['member_id'];
            $sdf['resetpwd'] = md5($rend.$_POST['email']);
            $sdf['resetpwdtime'] = $resetpwdtime;
            if(app::get('b2c')->model('members')->save($sdf)){
                
                    $this->send_sms_ec($_POST['mobile'],"尊敬的用户，您的验证码：".$rend."；您可以用来进行找回密码的操作。 【鲜八度果蔬便利店】");
                    $flag = true;
                    $this->pagedata['send_status'] = 'true';
                    $this->pagedata['email'] = $_POST['email'];
                    if($_POST['resend'] == 'true'){
                        $msg = app::get('b2c')->_('已发送成功');
                        $this->splash('failed','back',$msg,true);
                    }
                
            }else{
                $msg = '发送失败，请与商家联系';
            }
        }
        if(!$flag){
            $this->splash('failed',null,$msg,true);
        }
        $this->display("site/storepassport/forgot2.html");
    }

    function resetpwd_code(){
        if(strlen($_POST['code']) != 6){
            $msg = app::get('b2c')->_('验证码错误，请重新填写');
            $this->splash('failed','back',$msg,$_POST['response_json']);
        }
        $resetpwd_code = md5(trim($_POST['code']).$_POST['email']);
        $members = app::get('b2c')->model('members')->getList('resetpwd,resetpwdtime',array('resetpwd'=>$resetpwd_code));
        if($members){
            if($members[0]['resetpwd']+1800 < time()){
                $data['email'] = $_POST['email'];
                $data['mobile'] = $_POST['mobile'];
                $data['key'] = $members[0]['resetpwd'];
                $this->pagedata['data'] = $data;
                $this->display('site/storepassport/forgot3.html');
                exit;
            }else{
                $msg = app::get('b2c')->_('验证码已过期，请重新获取');
            }
        }else{
            $msg = app::get('b2c')->_('验证码错误，请重新填写');
        }
        $this->splash('failed','back',$msg,$_POST['response_json']);
    }

    function resetpassword(){
        $pwd_len = strlen(trim($_POST['pam_account']['login_password']));
        $pwd_confirm_len = strlen(trim($_POST['pam_account']['psw_confirm']));
        if( $pwd_len > 20 || $pwd_len < 8 || $pwd_confirm_len > 20 || $pwd_len < 8 || !preg_match('/^\+?[1-9][0-9]*$/', $_POST['pam_account']['login_password']) )
        {
            $msg = app::get('b2c')->_('格式不正确，请重新填写，8-20个数字');
            $this->splash('failed','back',$msg,$_POST['response_json']);
        }
        elseif($_POST['pam_account']['login_password'] != $_POST['pam_account']['psw_confirm'])
        {
            $msg = app::get('b2c')->_('密码与确认密码不相符，请重新填写');
            $this->splash('failed','back',$msg,$_POST['response_json']);
        }
        if($_POST['key']){
            $members_model = app::get('b2c')->model('members');
            $members = $members_model->getList('member_id,resetpwd,resetpwdtime,email',array('resetpwd'=>$_POST['key']));
        }
        if($members && $members[0]['resetpwd']+1800 < time() && $members[0]['mobile'] == $_POST['mobile']){
            $data['account_id'] = $members[0]['member_id'];
            $data['passport'] = $members[0]['member_id'];

            $account_model =  app::get('pam')->model('account');
            $aMem = $account_model->getList('login_name',array('account_id'=>$data['account_id']));
            $use_pass_data['login_name'] = $aMem[0]['login_name'];
            $data['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['pam_account']['login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
            if($account_model->save($data)){
                $members_model->update(array('resetpwd'=>null,'resetpwdtime'=>null),array('member_id'=>$data['account_id']));
                $this->display('site/storepassport/forgot4.html');
            }else{
                $msg = app::get('b2c')->_('密码重置失败,请重试');
                $this->splash('failed','back',$msg,$_POST['response_json']);
            }
        }else{
            $msg = app::get('b2c')->_('页面已过期，请刷新页面重试');
            $this->splash('failed','back',$msg,$_POST['response_json']);
        }
    }

    ####随机取6位字符数
    function randomkeys($length)
    {
        $pattern = '1234567890';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,9)};    //生成php随机数
        }
        return $key;
     }

    function send_email($login_name,$user_email,$new_password,$member_id)
    {
        $ret = $this->app->getConf('messenger.actions.account-lostPw');
        $ret = explode(',',$ret);
        if(!in_array('b2c_messenger_email',$ret)) return false;
        $data['uname'] = $login_name;
        $data['passwd'] = $new_password;
        $data['email'] = $user_email;
        $obj_account=&$this->app->model('member_account');
        return $obj_account->fireEvent('lostPw',$data,$member_id);
    }
    
    function send_email_reg($login_name,$user_email,$password,$member_id)
    {
        $ret = $this->app->getConf('messenger.actions.account-register');
        $ret = explode(',',$ret);
        if(!in_array('b2c_messenger_email',$ret)) return false;
        $data['uname'] = $login_name;
        $data['passwd'] = $password;
        $data['email'] = $user_email;
        $obj_account=&$this->app->model('member_account');
        return $obj_account->fireEvent('register',$data,$member_id);
    }

   
        
    function lost()
    {
        $this->path[] = array('title'=>app::get('b2c')->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            $this->redirect($url);
        }
        $this->set_tmpl('storepassport');
        $this->page("site/storepassport/forgot.html");
    }

    function repass($secret)
    {
        $this->begin($this->gen_url('passport','repass'));
        $objRepass = $this->app->model('member_pwdlog');
        if($objRepass->isValiad($secret))
        {
            $this->pagedata['secret'] = $secret;
            $this->set_tmpl('passport');
            $this->page("site/passport/resetpass.html");
        }
        else
        {
            $this->end(true,app::get('b2c')->_('参数不正确，请重新申请密码取回'),$this->gen_url('passport','lost'));
        }
    }

    function error()
    {
        $this->unset_member();
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        $this->_response->set_redirect($back_url)->send_headers();
    }

    public function logoutwebpos($url){
        if(!$url){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x";
        }
        $this->unset_member();
        $this->app->model('cart_objects')->setCartNum($arr);
        $this->redirect($url);
    }
    
    function logout($url)
    {
       
        if(!$url){
            $url = array('app'=>'site','ctl'=>'default','act'=>'index','full'=>1);
        }
        $this->unset_member();
        
        app::get('b2c')->model('local_staff')->update(array('logouttime'=> time()),array('staff_id'=>$_SESSION['account']['staff']));
        unset($_SESSION['local_store']);
        unset($_SESSION['account']['staff']);
        unset($_SESSION['account']['staff_name']);
        $this->app->model('cart_objects')->setCartNum($arr);
        if($_GET['type'] == 'outlog'){
           $this->splash('success', 'storepassport.html');
       }
        $this->redirect($url);
    }

    function unset_member()
    {
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }
        $this->app->member_id = 0;
        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',null,time()-3600);
        $this->set_cookie('UNAME','',time()-3600);
        $this->set_cookie('MLV','',time()-3600);
        $this->set_cookie('CUR','',time()-3600);
        $this->set_cookie('LANG','',time()-3600);
        $this->set_cookie('loginType','',time()-3600);//hack by Jason 登出的时候把是否门店登录的cookie也清空
        $this->set_cookie('S[MEMBER]','',time()-3600);
        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }
    }
    public function dailucash(){
        $this->set_tmpl('storepassport');
        $staff_id   = $_SESSION['account']['staff'];
        if ( $staff_id > 0 ) {
            $this->pagedata['local_store']  = $_SESSION['local_store'];
            $ls_mdl     = app::get('b2c')->model('local_staff');
            $staff_info = $ls_mdl->dump($_SESSION['account']['staff']);
            $this->pagedata['staff_name'] = $staff_info['staff_name'];
            $this->pagedata['account'] = $_SESSION['account'];
        }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
            $this->logout($url);
        }
        $this->pagedata['page_title']   = app::get('b2c')->_("门店现金日结");
        $this->page('site/storepassport/dailucash.html'); 
    }
    
    public function create_dailucash(){
        $dailycash = app::get('ome')->model('dailycash');
        if($_POST){
            /*需要存储的数据*/
            $save_data = array(
                'branch_id' => $_POST['branch_id'],
                'branch_name' => trim($_POST['branch_name']),
                'staff_id' => $_POST['staff_id'],
                'staff_name' => trim($_POST['staff_name']),
                'bank_name' => trim($_POST['bank_name']),
                'bank_num' => trim($_POST['bank_num']),
                'import_money' => trim($_POST['import_money']),
                'import_money_upper'  => trim($_POST['import_money_upper']),
                'cash_amount'  => trim($_POST['cash_amount']),
                'cash_amount_upper' => trim($_POST['cash_amount_upper']),
                'cash_balance' => trim($_POST['cash_balance']),
                'cash_balance_upper' => trim($_POST['cash_balance_upper']),
                'ctime' => time(),
                'memo' => trim($_POST['memo']),
            );
            
            if($dailycash->insert($save_data)){
                $this->begin();
                $this->end(true, $this->app->_("添加成功！"),$this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport')),'',true);
            }else{
                $this->begin();
                $this->end(false,app::get('b2c')->_('添加失败！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart'))."?type=x",true,true);
            }
        }
        
    }
    
    /**
     * 支付明细
     */
    public function payDetail() {
        $this->set_tmpl('storepassport');
        $pre    = date("YmdHis");
        $maxOrder = str_pad($pre,12,0); 
        $this->pagedata['maxOrder']   = $maxOrder;
        $this->pagedata['account'] = $_SESSION['account'];
        $staff_id   = $_SESSION['account']['staff'];
        $ls_mdl     = app::get('b2c')->model('local_staff');
        $staff_info = $ls_mdl->dump($staff_id);
        $liststaff  = $ls_mdl->getList('*', array('branch_id'=>$_SESSION['local_store']['branch_id']));
        foreach ($liststaff AS $k=>$v ){
            if($v['staff_id'] != $_SESSION['account']['staff']){
                $listsrf[$k] = $v;
            }
        }
        $this->pagedata['staff_info']   = $staff_info;
        $this->pagedata['liststaff']   = $listsrf;
        $this->pagedata['time_local']   = time();
        if ( $staff_id > 0 ) {
            $o_mdl      = app::get('b2c')->model('orders');
            $staff_info = $ls_mdl->dump($staff_id);
            $logintime  = $staff_info['logintime'];
            //$time1 = $_POST['time1'];
            //$time2 = $_POST['time2'];
            // 订单过滤条件
            $filter     = array (
                    'staff_id'          => $staff_id,
                    'createtime|bthan'  => $logintime,
                    //'createtime|bthan'  => $time1,
                    //'createtime|sthan'  => $time2,
                    'pay_status'        => '1',
                    );
            $list   = $o_mdl->getList('order_id,pmt_order', $filter);
            if ( $list ) {
                $e_o_b_mdl  = app::get('ectools')->model('order_bills');
                $order_ids  = array();
                foreach ( $list AS $v ) {
                    $order_ids[]    = $v['order_id'];
                }
                // 支付单过滤条件
                $filter     = array (
                        'rel_id'    => $order_ids,
                        );
                $o_b_list   = $e_o_b_mdl->getList('bill_id', $filter);
                if ( $o_b_list ) {
                    $e_p_mdl    = app::get('ectools')->model('payments');
                    $bill_ids   = array();
                    foreach ( $o_b_list AS $v ) {
                        $bill_ids[] = $v['bill_id'];
                    }
                    // 支付数据过滤条件
                    $filter     = array (
                            'payment_id'    => $bill_ids,
                            'status'        => 'succ',
                            );
                    $p_list = $e_p_mdl->getList('*', $filter);
                    if ( $p_list ) {
                        $pay_sum    = 0;
                        $order_nums = 0;
                        foreach ( $p_list AS $v ) {
                            $pay_sum    += $v['money'];
                            $pay_result[$v['pay_app_id']]['pay_name'] = $v['pay_name'];
                            $pay_result[$v['pay_app_id']]['pay_money'] += $v['money'];
                        }
                        foreach ( $list AS $v ) {
                            $order_yuhui  += $v['pmt_order'];
                        }
                                                
                        $pay_result['sum']['pay_name']  = app::get('b2c')->_('总支付');
                        $pay_result['sum']['pay_money'] = $pay_sum;
                        $order_nums = count($list);
                        foreach ( $pay_result AS $v ) {
                            if($v['pay_name'] == 'xianjin'){
                                $paymoney['xianjin'] = $v['pay_money'];
                            }
                            if($v['pay_name'] == 'shuaka'){
                                $paymoney['shuaka'] = $v['pay_money'];
                            }
                            if($v['pay_name'] == '预存款'){
                                $paymoney['yucunkuan'] = $v['pay_money'];
                            }
                            if($v['pay_name'] == '总支付'){
                                $paymoney['count_money'] = $v['pay_money'];
                            }
                        }
                        $this->pagedata['order_nums']   = $order_nums;
                        $this->pagedata['order_yuhui']   = $order_yuhui;
                        $this->pagedata['pay_result']   = $pay_result;
                        $this->pagedata['paymoney']   = $paymoney;
                    }
                }
            }
        } else {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport'));
            $this->logout($url);
            //$error_msg  = app::get('b2c')->_('门店未登录');
        }
        $this->pagedata['page_title']   = app::get('b2c')->_("员工交接班结账单");
        $this->page('site/storepassport/pay-detail.html');
    }
    
    public function create_paydetail(){
        if(!$_POST['staff']){
            $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_storepassport','act'=>'payDetail')),app::get('b2c')->_('对不起，没有交接的员工！'),true); 
        }
        $arr_obj = explode('--',$_POST['staff']);
        $_POST['jieban_id'] = $arr_obj[0];
        $_POST['jieban_name'] = $arr_obj[1];
        unset($_POST['staff']);
        $dailycash = app::get('ome')->model('charge');
        $_POST['end_time'] = time();
        if($_POST){
            /*需要存储的数据*/
            $save_data = array(
                'charge_num' => trim($_POST['charge_num']),
                'jiaoban_id' => $_POST['jiaoban_id'],
                'jiaoban_name' => trim($_POST['jiaoban_name']),
                'jieban_id' => $_POST['jieban_id'],
                'jieban_name' => trim($_POST['jieban_name']),
                'start_time' => trim($_POST['start_time']),
                'end_time' => $_POST['end_time'],
                'cash' => trim($_POST['cash']),
                'webpos' => trim($_POST['webpos']),
                'deposit'  => trim($_POST['deposit']),
                'coupon'  => trim($_POST['coupon']),
                'amount' => trim($_POST['amount']),
                'order_nums' => trim($_POST['order_nums']),
            );
            
            $jieban_id = $_POST['jieban_id'];
            $account = app::get('b2c')->model('local_staff')->getList('*',array('staff_id'=>$jieban_id));
            if(trim($_POST['password']) !== $account[0]['login_password']){
                   echo json_encode(array('ret'=>app::get('b2c')->_('交接员工密码错误，请重试!')));
                   return;
            }
              //print_r($save_data);exit;
            if($dailycash->insert($save_data)){
                echo json_encode(array('ret'=>app::get('b2c')->_('交接班成功!')));
                   return;
                
            }else{
                echo json_encode(array('ret'=>app::get('b2c')->_('交接班失败!')));
                   return;
            }
        }
        
    }
    
}
