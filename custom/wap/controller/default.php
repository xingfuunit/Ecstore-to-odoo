<?php
class wap_ctl_default extends wap_controller{
    

     function index(){
//千色修改的代码，现在发现 是没用的
//         kernel::single('base_session')->start();
//         if( !empty($_GET['signature']) &&  !empty($_GET['openid']) ){
//             $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['u_eid'],'status'=>'active'));
//             $flag = kernel::single('weixin_object')->check_wechat_sign($_GET['signature'], $_GET['openid']);
//             if( $flag && !empty($bind)){
//                 $openid = $_GET['openid'];
//             }
//         }elseif( !empty($_GET['code']) && !empty($_GET['state']) ){
//             $bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
//             if( !empty($bind) &&  kernel::single('weixin_wechat')->get_oauth2_accesstoken($bind['id'],$_GET['code'],$result) ){
//                 $openid = $result['openid'];
//             }
//         }
          
//         if( $openid ){
//             $bindTagData = app::get('pam')->model('bind_tag')->getRow('*',array('open_id'=>$openid));
//             if( $bindTagData ){
//                 $_SESSION['weixin_u_nickname'] = $bindTagData['tag_name'];
//             }else{
//                 $res = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
//                 $_SESSION['weixin_u_nickname'] = $res['nickname'];
//             }
//             $_SESSION['weixin_u_openid'] = $openid;
//             $_SESSION['is_bind_weixin'] = false;
            
//             //一键登陆
//             $deed = $_SESSION['weixin_u_nickname'];
//             $pam_members_model = app::get('pam')->model('members');
//             $flag = $pam_members_model->getList('member_id',array('login_account'=>trim($deed)));
//             if($flag[0]['member_id'] == ''){
//                 $this->create($deed);
//             }
//             $userData = array(
//                 'login_account' => $deed,
//                 'login_password' => "123456"
//             );
//             $this->weixinObject = kernel::single('weixin_object');
//             $this->userObject = kernel::single('b2c_user_object');
//             $member_id = $this->login($userData,'',$msg);
//             $this->userObject->set_member_session($member_id);
//         }
        
        
        if($this->openid){
        	//判断微信是否第一次进入
        	if(!isset($_COOKIE['ck-cover'])){
        		setcookie('ck-cover',1,time()+3600*24);		//30天
        		$this->redirect(array('app'=>'wap','ctl'=>'default','act'=>'wepcover'),1);
        	}
        }
        
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('wap')->_('首页'),'link'=>kernel::base_url(1));
        $this->set_tmpl('index');
        $this->title=app::get('wap')->getConf('wap.shopname');
        $this->title=$this->title ? $this->title : app::get('site')->getConf('site.name');
        if(in_array('index', $this->weixin_share_page)){
            $this->pagedata['from_weixin'] = $this->from_weixin;
            $this->pagedata['weixin']['appid'] = $this->weixin_a_appid;
            $this->pagedata['weixin']['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
            $this->pagedata['weixin']['linelink'] = app::get('wap')->router()->gen_url(array('app'=>'wap','ctl'=>'default', 'full'=>1));
            $this->pagedata['weixin']['shareTitle'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_name');
            $this->pagedata['weixin']['descContent'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_brief');
        }
        
         
        $goodsModel = app::get('b2c')->model('goods');
        
        $params=$this->_request->get_params();
        $pageLimit =20;
        $this->pagedata['pageLimit'] = $pageLimit;
        $page = $params['page']?$params['page']:1;     
        $goodsData = $goodsModel->getList('*',$filter,$pageLimit*($page-1),$pageLimit,$orderby,$total=false);
        if($goodsData && $total ===false){
           $total = $goodsModel->count($filter);
        }
        $this->pagedata['total'] =  $total;
         $pagetotal= $this->pagedata['total'] ? ceil($this->pagedata['total']/$pageLimit) : 1;
         $this->pagedata['pagetotal'] =$pagetotal;
        $this->pagedata['page'] = $page;
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>$this->pagedata['pagetotal'],
            'link' =>$this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_gallery','act'=>'ajax_get_goods')),
        );
        
        foreach($goodsData as $key=>$goods_row){
            if(in_array($goods_row['goods_id'],$gfav)){
                $goodsData[$key]['is_fav'] = 'true';
            }
            if($goods_row['udfimg'] == 'true' && $goods_row['thumbnail_pic']){
                $goodsData[$key]['image_default_id'] = $goods_row['thumbnail_pic'];
            }
            $gids[$key] = $goods_row['goods_id'];
        }
       
        $productModel =app::get('b2c')->model('products');
        $products =  $productModel->getList('*',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        $sdf_product = array();
        foreach($products as $key=>$row){
            $sdf_product[$row['goods_id']] = $row;
        }
        foreach ($goodsData as $gk=>$goods_row){
            $product_row = $sdf_product[$goods_row['goods_id']];
            $goodsData[$gk]['products'] = $product_row;
            //市场价
            if($show_mark_price =='true'){
                if($product_row['mktprice'] == '' || $product_row['mktprice'] == null)
                    $goodsData[$gk]['products']['mktprice'] = $productModel->getRealMkt($product_row['price']);
            }

            //库存
            if($goods_row['nostore_sell'] || $product_row['store'] === null){
                $goodsData[$gk]['products']['store'] = 999999;
            }else{
                $store = $product_row['store'] - $product_row['freez'];
                $goodsData[$gk]['products']['store'] = $store > 0 ? $store : 0;
            }
        }
        
        $this->_new_index_data();
        
        $this->pagedata['goodsData'] = $goodsData;
        $this->pagedata['title'] = '品珍鲜活';
        $this->page('index.html');
    }

    //验证码组件调用
    function gen_vcode($key='vcode',$len=4){
        $vcode = kernel::single('base_vcode');
        $vcode->length($len);
        $vcode->verify_key($key);
        $vcode->display();
    }
    
    /**
     * 新首页获取数据
     */
    private function _new_index_data(){
    	$goodsModel = app::get('b2c')->model('goods');
    	$catModel = app::get('b2c')->model('goods_cat');
    	$filter =array(
    			'wap_recommend'=>1,
    			'is_line'=>1,
   				'marketable'=>"true",
   				'is_buildexcerpts'=>"true",
    			); 
    	
    	//品珍鲜果
    	$this->pagedata['pz_xg'] = $goodsModel->get_good_list_by_cat_catname('时令水果',$filter);
    	$this->pagedata['pz_xg_cat']= $catModel->getRow('cat_id,cat_name', array('cat_name' => "时令水果"));
    	
    	//品珍海鲜
    	$this->pagedata['pz_hs'] = $goodsModel->get_good_list_by_cat_catname('鲜活海鲜',$filter);
    	$this->pagedata['pz_hs_cat']= $catModel->getRow('cat_id,cat_name', array('cat_name' => "鲜活海鲜"));
    	
    	//品珍鲜肉
    	$this->pagedata['pz_xr'] = $goodsModel->get_good_list_by_cat_catname('精品肉类',$filter);
    	$this->pagedata['pz_xr_cat']= $catModel->getRow('cat_id,cat_name', array('cat_name' => "精品肉类"));
    	
    	//品珍精选
    	$this->pagedata['pz_jx'] = $goodsModel->get_good_list_by_cat_catname('品珍精选',$filter);
    	$this->pagedata['pz_jx_cat']= $catModel->getRow('cat_id,cat_name', array('cat_name' => "品珍精选"));
    	
//     	print_r($this->pagedata['pz_hs']);exit;

    	$ads_model = app::get('mobileapi')->model('sales_ads');
    	//首页滚动广告
    	$this->pagedata['roll_ads'] = $ads_model->get_sales_ads('index_roll_banner');
    	//首页优惠券 广告
    	$this->pagedata['coup_ads'] = $ads_model->get_sales_ads('index_coup_banner');
    	//促销图片广告
    	$this->pagedata['pic_ads'] = $ads_model->get_sales_ads('index_pic_banner');
    	//送免邮券广告
    	$this->pagedata['mianyou_ads'] = $ads_model->get_sales_ads('index_coup_mianyou');
    	
    	//滚动文字
    	$this->pagedata['roll_text'] = $ads_model->get_sales_ads('index_roll_text');

    	//商品分类
    	$objCat = app::get('b2c')->model('goods_cat');
//     	$catlist = $objCat->getList('*', array('parent_id' => $cat_id), $offset=0, $limit=-1, 'p_order desc');

    	//购物车 是否有商品
    	$oCart = app::get('b2c')->model("cart_objects");
    	$arr = array();
    	$aData = $oCart->setCartNum( $arr );
    	$this->pagedata['cartCount'] = $aData['CART_COUNT'];
    	
    	$catmap = $objCat->getmap();
    	$this->pagedata['catmap'] = $catmap;
    	
    }
    
    
    /**
     * 手机首次进入封面页
     */
    public function wepcover(){
    	$this->page('wap/cover.html');
    }
    
    /**
     * 公司简介页
     */
    public function company(){
    	$this->page('wap/company.html');
    }
    
    /**
     * 微信入口页面
     */
    public function enterpage(){
    	echo $this->fetch('wap/enterpage.html');
//     	$this->page('wap/enterpage.html');
    }

}