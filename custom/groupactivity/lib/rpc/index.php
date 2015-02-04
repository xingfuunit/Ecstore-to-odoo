<?php
/**
 * ********************************************
 * Description   : 团购接口
 * Filename      : index.php
 * Create time   : 2014-07-17 11:01:36
 * Last modified : 2014-07-17 11:57:09
 * License       : MIT, GPL
 * ********************************************
 */

class groupactivity_rpc_index  extends mobileapi_frontpage{ 

    public $app = null;

    public $db = null;

    function __construct( $app ) {
    	parent::__construct($app);
    	
        $this->app  = $app;
        $this->db   = kernel::database();
        
    }

    public function get_list( $param = array(), $rpcService ) {
        $ret    = array();
        $state  = intval($param['state']);
        if ( $state > 0 && $state < 6 ) {
            $filter = array (
                'state'     => $state,
                'act_open'  => 'true'
            );
            $mdl        = $this->app->model('purchase');
            $count      = $mdl->count($filter);
            $list       = array();
            if ( $count ) {
                $page       = intval($param['page_no']);
                $page       = $page > 0 ? $page : 1;
                $page_size  = intval($param['page_size']);
                $page_size  = $page_size > 0 ? $page_size : 20;
                $offset     = ($page - 1) * $page_size;
                $order      = trim($param['order']);
                $order      = $order ? $order : 'end_time';
                $order_type = trim($param['order_type']);
                $order_type = $order_type ? $order_type : 'ASC';
                $list       = $mdl->getList('*', $filter, $offset, $page_size, $order.' '.$order_type);
                if ( $list ) {
                    $p_mdl  = app::get('b2c')->model('products');
                    $g_mdl  = app::get('b2c')->model('goods');
                    $now_time   = time();
                    foreach ( $list AS $k => $v ) {
                        $p_info = $p_mdl->dump($v['pid'], '*');
                        $g_info = $g_mdl->dump($p_info['goods_id'], '*');
                        $img_id = $g_info['image_default_id'];
                        // 图片
                        $list[$k]['images']   = array (
                                'big_url'       => kernel::single('base_storager')->image_path($img_id, 'l'),
                                'thisuasm_url'  => kernel::single('base_storager')->image_path($img_id, 'm'),
                                'small_url'     => kernel::single('base_storager')->image_path($img_id, 's'),
                                );
                        // 产品信息
                        $list[$k]['product']    = $p_info;
                        // iid
                        $list[$k]['iid']         = $p_info['iid'];
                        // 剩余时间
                        $list[$k]['remaining_time'] = $v['end_time'] - $v['start_time'];
                        // 原价
                        $list[$k]['old_price']  = $p_info['price']['price']['price'];
                        // 折扣
                        $list[$k]['discount']   = round($v['price'] / $list[$k]['old_price'], 2) * 10;
                    }
                }
            }
            $ret        = array (
                'code'  => 1,
                'items' => $list,
                'total_results' => $count,
            );
        } else {
            $ret    = array (
                'code'  => -1, 
                'msg'   => $this->app->_('团购状态码不合法'),
            );
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 团购详情
     */
    public function get_detail( $param = array(), $rpcService ) {
        $ret    = array();
        $act_id = intval($param['act_id']);
        if ( $act_id ) {
            $mdl    = $this->app->model('purchase');
            $info   = $mdl->dump($act_id);
            if ( $info) {
                $p_mdl  = app::get('b2c')->model('products');
                // 读取产品数据
                $p_info = $p_mdl->dump($info['pid'], '*');
                // 剩余时间
                $info['remaining_time'] = $info['end_time'] - $info['start_time'];
                // 原价
                $info['old_price']  = $p_info['price']['price']['price'];
                // 折扣
                $info['discount']   = round($info['price'] / $info['old_price'], 2) * 10;
                // iid
                $info['iid']        = $p_info['goods_id'];
                // 商品
                $info['product']    = $p_info;
                $ret    = array (
                        'code'  => 1,
                        'data'  => $info,
                        'msg'   => $this->app->_('获得详情成功'),
                        );
            } else {
                $ret    = array (
                        'code'  => -2, 
                        'msg'   => $this->app->_('团购未开启'),
                        );
            }
        } else {
            $ret    = array (
                    'code'  => -1, 
                    'msg'   => $this->app->_('团购活动ID不合法'),
                    );
        }
        if ( $ret['code'] < 0 ) { 
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }
    
    
    /*
     * 团购商品checkout
    */
    public function buy($post, $rpcService)
    {
    	
    	$this->verify_member();
    	
    	$tmp = app::get('b2c')->model('products')->getList( '*',array('product_id'=>$post['product_id']) );
    	if(!$tmp){
    		$ret    = array (
    				'code'  => -1,
    				'msg'   => '团购活动产品不存在',
    		);
    		$rpcService->send_user_error($ret['code'], $ret['msg']);
    	}
    	reset( $tmp );
    	$tmp = current( $tmp );
    	
    	$data['group'] = 1;
    	$data['goods'] = array(
    			'goods_id' => $tmp['goods_id'],
    			'product_id' => $tmp['product_id'],
    	);
    	$data['goods']['num'] = $post['num']?$post['num']:1;
    	
    	
    	$o = kernel::single("groupactivity_cart_object_purchase");
    	$type = $o->get_type();
    	
    	if(!empty($_SESSION['groupactivity-redirect'])) unset($_SESSION['groupactivity-redirect']);
    	$o->set_session( array($type=>$data) );
    
    	if( !$o->_check() ) {
    		$ret    = array (
    				'code'  => -1,
    				'msg'   => $o->error_html,
    		);
    		$rpcService->send_user_error($ret['code'], $ret['msg']);
    	}else{
    		$ret    = array (
    				'code'  => 1,
    				'order' => $o->getAll(),
    				'msg'   => '团购购买添加成功',
    		);
    		return $ret;
    	}
    }

}
