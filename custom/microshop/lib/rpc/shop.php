<?php
/**
 * ********************************************
 * Description   : 微店店铺接口
 * Filename      : shop.php
 * Create time   : 2014-06-11 17:58:32
 * Last modified : 2014-06-16 17:48:43
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_rpc_shop {

    function __construct( $app ) {
        $this->app  = $app;
        $this->db   = kernel::database();
    }

    /**
     * 获得微店列表数据
     */
    function index($param = array(), $rpcService) {
        $data       = array ();
        if ( $param['shop_name'] ) {
            $data   = array (
                'shop_name|has' => trim($param['shop_name']),
            );
        }
        if ( $param['agency_id'] ) {
            $data['agency_id']  = intval($param['agency_id']);
        }
        $mdl        = $this->app->model('shop');
        $count      = $mdl->count($data);
        $list       = array();
        if ( $count ) {
            $page       = intval($param['page_no']);
            $page       = $page > 0 ? $page : 1;
            $page_size  = intval($param['page_size']);
            $page_size  = $page_size > 0 ? $page_size : 20;
            $offset     = ($page - 1) * $page_size;
            $order      = trim($param['order']);
            $order      = $order ? $order : 'addtime';
            $order_type = trim($param['order_type']);
            $order_type = $order_type ? $order_type : 'DESC';
            $list       = $mdl->getList('*', $data, $offset, $page_size, $order.' '.$order_type);
            if ( $list ) {
                $m_mdl  = app::get('b2c')->model('members');
                foreach ( $list AS $k => $v ) {
                    $_param = array (
                        'app'   => 'microshop',
                        'ctl'   => 'site_index',
                        'full'  => 1,
                        'act'   => 'detail',
                        'arg0'  => $v['shop_id'],
                    );  
                    $list[$k]['shop_link']  = app::get('site')->router()->gen_url($_param);
                    $m_info = $m_mdl->dump($v['member_id']);
                    $list[$k]['follow_num'] = $m_info['follow_num'];
                    $list[$k]['fans_num']   = $m_info['follow_num'];
                    $list[$k]['cover']      = $m_info['cover'] ? kernel::single('base_storager')->image_path($m_info['cover']) : $this->app->res_url.'/images/top-bg.png';
                    $list[$k]['avatar']     = $m_info['avatar'] ? kernel::single('base_storager')->image_path($m_info['avatar']) : $this->app->res_url.'/images/top-bg.png';
                    $list[$k]['info']       = $m_info['info'];
                }
            }
        }
        $ret        = array (
            'code'          => 1,
            'total_results' => $count,
            'items'         => $list,
        );
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 我推荐的微店
     */
    function recommend( $param = array(), $rpcService ) {
        $ret    = array ();
        if ( $param['agency_id'] ) {
            $ret = $this->index($param, $rpcService);
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('推荐人的ID不合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 获得店铺详细信息
     *
     * @param   array   $param      会员与店铺的相关数据
     * @param   array   $rpcService rpc对象
     */
    function detail( $param = array(), $rpcService ) {
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
        $member_id  = $param['member_id'] ? $param['member_id'] : 0;
        $ret        = array ();
        if ( $shop_id > 0 || $member_id > 0 ) {
            $mdl    = $this->app->model('shop');
            $filter = array();
            if ( $shop_id > 0 ) {
                $filter['shop_id']      = $shop_id;
            }
            if ( $member_id > 0 ) {
                $filter['member_id']    = $member_id;
            }
            $info   = $mdl->getDetail($filter);
            if ( $info ) {
                if ( $info['is_open'] == 1 ) {
                    $ret    = array (
                        'code'  => 1,
                        'data'  => $info,
                        'msg'   => app::get('microshop')->_('会员店铺正常'),
                    );
                } else {
                    $ret    = array (
                        'code'  => -3,
                        'msg'   => app::get('microshop')->_('该会员店铺已关闭'),
                    );
                }
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('该会员无任何微店信息'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('商店ID/会员ID不合法，参数至少两选一'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 修改微店信息
     */
    function edit( $param = array(), $rpcService) {
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
        $shop_name  = trim($param['shop_name']);
        $shop_name  = $shop_name ? $shop_name : '';
        if ( $shop_id > 0 && $shop_name ) {
            $mdl    = $this->app->model('shop');
            $data   = array (
                'shop_name' => $shop_name
            );
            if ( $mdl->update($data, array('shop_id' => $shop_id)) ) {
                $ret    = array (
                    'code'  => 1,
                    'data'  => $data,
                    'msg'   => app::get('microshop')->_('修改成功'),
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('系统错误,请联系管理员'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('商店ID/商店名称不合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }
}
