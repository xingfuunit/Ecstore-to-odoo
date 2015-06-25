<?php
/**
 * ********************************************
 * Description   : 组合促销接口
 * Filename      : index.php
 * Create time   : 2014-10-28 15:34:24
 * Last modified : 2014-10-28 17:06:31
 * License       : MIT, GPL
 * ********************************************
 */

class starbuy_rpc_index {

    function __construct( $app ) {
        $this->app  = $app;
        $this->mdl_special_goods = app::get('starbuy')->model('special_goods');
        $this->special_pro  = kernel::single('starbuy_special_products');
        $this->team_ctl     = kernel::single('starbuy_ctl_site_team');
    }

    /**
     * 获得促销场次列表
     */
    public function getGroup( $request, $rpcService ) {
        $ret    = array();
        $mdl    = $this->app->model('special');
        $type_id= intval($request['type_id']);
        if ( $type_id ) {
            $order      = trim($request['order']);
            $order      = $order ? $order : 'begin_time';
            $order_type = trim($request['order_type']);
            $order_type = $order_type ? $order_type : 'DESC';
            $orderby    = $order.' '.$order_type;
            $platform   = trim($request['platform']);
            $platform   = $platform ? $platform : 'web';
            $filter     = array (
                    'status'    => 'true',
                    'type_id'   => $type_id,
                    );
            $list   = $mdl->getList('*', $filter, 0, -1, $orderyby);
            $ret    = array (
                'code'  => 1,
                'list'  => $list,
                'system_time'   => (string)time(),
            );
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('starbuy')->_('请确保促销类型合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 组合促销场次商品列表
     */
    public function getList( $request, $rpcService ) {
        $ret    = array();
        $page   = intval($request['page_no']);
        $page   = $page > 0 ? $page : 1;
        $mdl    = $this->app->model('special_goods');
        $spec_id= intval($request['special_id']);
        if ( $spec_id ) {
            $filter = array (
                    'status'    => 'true',
                    'special_id'=> $spec_id,
                    );
            $count  = $this->mdl_special_goods->count($filter);
            $list   = array();
            if ( $count ) {
                $page_size  = intval($request['page_size']);
                $page_size  = $page_size > 0 ? $page_size : 20;
                $order      = trim($request['order']);
                $order      = $order ? $order : 'begin_time';
                $order_type = trim($request['order_type']);
                $order_type = $order_type ? $order_type : 'DESC';
                $orderby    = $order.' '.$order_type;
                $platform   = trim($request['platform']);
                $platform   = $platform ? $platform : 'web';
                $arr        = $this->mdl_special_goods->getList('*', $filter, ($page - 1) * $page_size, $page_size, $orderby);
                foreach ( $arr as $k => $v ) {
                    $_list  = $this->special_pro->getParams($v);
                    $_list['image_default_url'] = array (
                            'l' => base_storager::image_path($_list['image_default_id'], 'l'),
                            'm' => base_storager::image_path($_list['image_default_id'], 'm'),
                            's' => base_storager::image_path($_list['image_default_id'], 's'),
                            );
                    $list[] = $_list;
                }
            }
            $ret    = array(
                    'code'          => 1,
                    'total_results' => $count,
                    'items'         => $list,
                    'system_time'   => (string)time(),
                    );
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('starbuy')->_('请确保促销产品ID合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 获得组合购详情
     */
    public function getDetail( $request, $rpcService ) {
        $ret    = array();
        $pid    = intval($request['product_id']);
        if ( $pid > 0 ) {
        	
        	$filter['product_id'] = $pid;
        	$filter['begin_time|sthan']=time();
        	$filter['end_time|bthan']=time();
            $special_goods  = $this->mdl_special_goods->getRow('*', $filter, 'begin_time asc');
            $goodsdata      = $this->special_pro->getdetailParams($special_goods);
            //没有默认货品图片则显示商品所有图片，否则显示关联货品图片
            $img    = array();
            if ( empty( $goodsdata['images'] ) ) {
                $goodsImages    = $this->team_ctl->_get_goods_image($goodsdata['goods']['goods_id']);
                $img['images']  = $goodsImages;//商品图片
                $img['image_default_id']    = $goodsdata['goods']['image_default_id'];//商品图片
                $img['image_default_url']   = $goodsdata['goods']['image_default_url'];//商品图片
            } else {
                $img['images']  = $goodsdata['images'];
                $img['image_default_id']    = $goodsdata['images'][0]['image_id'];//商品图片
                $img['image_default_url']   = array (
                    'l' => base_storager::image_path($img['image_default_id'], 'l'),
                    'm' => base_storager::image_path($img['image_default_id'], 'm'),
                    's' => base_storager::image_path($img['image_default_id'], 's'),
                );
            }
            if ( $img['images'] ) {
                foreach ( $img['images'] AS &$v ) {
                    $v['image_url'] = array (
                            'l' => base_storager::image_path($v['image_id'], 'l'),
                            'm' => base_storager::image_path($v['image_id'], 'm'),
                            's' => base_storager::image_path($v['image_id'], 's'),
                            ); 
                }
            }
            if ( $goodsdata ) {
            	
            	$goods_f = array('iid' => $goodsdata['goods_id'],'son_object' => 'json');
            	$goods_info = kernel::single('mobileapi_rpc_goods')->get_item($goods_f,kernel::single('base_rpc_service'));
            	
                $ret        = array (
                    'code'          => 1,
                    'info'          => $goodsdata,
                	'goods'  => $goods_info['item'],
                    'images'        => $img,
                    'system_time'   => (string)time(),
                );
                //自提时间
                $specialModel = $this->app->model('special');
                $sRow = $specialModel->getRow('from_extract', array('special_id' => $special_goods['special_id']));
                $from_extract = $sRow['from_extract'] ? explode(',', $sRow['from_extract']) : null;
                if (count($from_extract) > 0) {
                    $ret['from_extract'] = $from_extract;
                }
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('starbuy')->_('该产品目前无促销信息'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('starbuy')->_('请确保促销产品ID合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

}
