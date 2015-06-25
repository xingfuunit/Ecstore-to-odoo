<?php
/**
 * ********************************************
 * Description   : 专辑接口
 * Filename      : special.php
 * Create time   : 2014-06-11 17:58:32
 * Last modified : 2014-06-11 17:58:32
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_rpc_special {

    function __construct( $app ) {
        $this->app  = $app;
        $this->db   = kernel::database();
    }

    /**
     * 添加专辑
     */
    function add($request, $rpcService) {
        $ret    = array ();
        if ( $request['special_name'] ) {
            $mdl    = $this->app->model('special');
            $data   = array (
                'member_id'     => $request['member_id'],
                'shop_id'       => $request['shop_id'],
                'special_name'  => $request['special_name'],
                'addtime'       => time(),
            );
            $shop_status    = $this->checkMemberShop($data, $rpcService);
            if ( $mdl->save($data) ) {
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('专辑添加成功'),
                    'data'  => $data,
                );
            } else {
                $ret    = array (
                    'code'  => -4,
                    'msg'   => app::get('microshop')->_('专辑添加失败'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -4,
                'msg'   => app::get('microshop')->_('专辑名称不能为空'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 往专辑中添加商品
     */
    function add_product($request, $rpcService) {
        $ret    = array ();
        if ( $request['member_id'] > 0 && $request['special_id'] > 0 && $request['product_id'] > 0 ) {
            $data   = array (
                'member_id'     => $request['member_id'],
                'special_id'    => $request['special_id'],
                'product_id'    => $request['product_id'],
                'addtime'       => time(),
            );
            $mdl    = $this->app->model('special_products');
            $info   = $mdl->getList('*', array (
                                                'member_id'     => $data['member_id'],
                                                'special_id'    => $data['special_id'],
                                                'product_id'    => $data['product_id']
                                            )
                                        );
            if ( $info ) {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('该产品已添加过了'),
                );
            } else {
                if ( $mdl->save($data) ) {
                    $ret    = array (
                        'code'  => 1,
                        'msg'   => app::get('microshop')->_('产品添加成功'),
                        'data'  => $data,
                    );
                } else {
                    $ret    = array (
                        'code'  => -3,
                        'msg'   => app::get('microshop')->_('产品添加失败'),
                    );
                }
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保会员ID/专辑ID/产品ID都合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 删除专辑中的商品
     *
     */
    function del_product( $request, $rpcService ) {
        $ret    = array();
        if ( $request['member_id'] > 0 && $request['special_products_id'] ) {
            $data   = array (
                'member_id'             => $request['member_id'],
                'special_products_id'   => explode(',', $request['special_products_id']),
            );
            $mdl    = $this->app->model('special_products');
            if ( $mdl->delete($data) ) {
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('删除成功'),
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('删除失败'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保会员ID/专辑产品ID都合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 删除专辑
     */
    function del( $request, $rpcService ) {
        $ret    = array();
        if ( $request['member_id'] > 0 && $request['special_id'] ) {
            $data   = array (
                'member_id'     => $request['member_id'],
                'special_id'    => explode(',', $request['special_id']),
            );
            $mdl    = $this->app->model('special');
            // 删除专辑
            if ( $mdl->delete($data) ) {
                $mdl    = null;
                $mdl    = $this->app->model('special_products');
                // 删除专辑中的产品
                $mdl->delete($data);
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('删除成功'),
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('删除失败'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保会员ID/专辑ID都合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 修改专辑
     */
    function edit( $request, $rpcService ) {
        $ret    = array();
        if ( $request['special_name'] && $request['special_id'] > 0 && $request['member_id'] > 0 ) {
            $mdl    = $this->app->model('special');
            $data   = array (
                'special_name'  => $request['special_name'],
            );
            if ( $mdl->update($data, array('special_id' => $request['special_id'], 'member_id' => $request['member_id'])) ) {
                $ret    = array (
                    'code'  => 1,
                    'msg'   => app::get('microshop')->_('专辑修改成功'),
                    'data'  => $data,
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('专辑修改失败'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保专辑名称/专辑ID/会员ID都合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 读取专辑列表
     */
    function index( $request, $rpcService ) {
        $ret    = array();
        $page   = intval($request['page_no']);
        $page   = $page > 0 ? $page : 1;
        $mdl    = $this->app->model('special');
        $data   = array (
                'shop_id'       => $request['shop_id'],
                );
        // 验证会员店铺
        $shop_info  = $this->checkMemberShop($data, $rpcService, $page);
        $count      = $mdl->count($data);
        $list       = array();
        if ( $count ) {
            $page_size  = intval($request['page_size']);
            $page_size  = $page_size > 0 ? $page_size : 20;
            $order      = trim($request['order']);
            $order      = $order ? $order : 'addtime';
            $order_type = trim($request['order_type']);
            $order_type = $order_type ? $order_type : 'DESC';
            $orderby    = $order.' '.$order_type;
            $platform   = trim($request['platform']);
            $platform   = $platform ? $platform : 'wap';
            $param      = array (
                'filter'    => $data,
                'limit'     => $page_size,
                'page'      => $page,
                'orderby'   => $orderby,
                'platform'  => $platform,
            );
            $list       = $mdl->getSpecialList($param);
        }
        $ret    = array(
            'code'          => 1,
            'total_results' => $count,
            'items'         => $list,
            'shop_info'     => $shop_info['data'],
        );
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 获得专辑中的商品
     */
    function get_products( $request, $rpcService ) {
        $ret    = array();
        if ( $request['special_id'] > 0 ) {
            $page   = intval($request['page_no']);
            $page   = $page > 0 ? $page : 1;
            $limit  = $request['page_size'] ? $request['page_size'] : 20;
            $data           = array (
                'special_id'=> $request['special_id'],
                'page'      => $page,
                'limit'     => $limit,
                'platform'  => $request['platform'] ? $request['platform'] : 'wap',
            );
            $mdl            = $this->app->model('special');
            $special_info   = $mdl->getSpecialInfo($data);
            if ( $special_info['special_id'] ) {
                $ret        = array (
                    'code'  => 1,
                    'special_info'  => $special_info,
                );
            } else {
                $ret    = array (
                    'code'  => -2,
                    'msg'   => app::get('microshop')->_('专辑信息不存在'),
                );
            }
        } else {
            $ret    = array (
                'code'  => -1,
                'msg'   => app::get('microshop')->_('请确保专辑ID合法'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

    /**
     * 验证会员店铺
     *
     * @param   array   $param      会员与店铺的相关数据
     * @param   array   $rpcService rpc对象
     */
    function checkMemberShop( $param = array(), $rpcService, $type = 2 ) {
        $member_id  = $param['member_id'] ? $param['member_id'] : 0;
        $shop_id    = $param['shop_id'] ? $param['shop_id'] : 0;
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
            $info   = $mdl->getDetail($filter, $type);
            if ( $info ) {
                if ( $info['is_open'] == 1 ) {
                    $ret    = array (
                        'code'  => 1,
                        'data'  => $info,
                        'msg'   => app::get('microshop')->_('会员店铺正常'),
                    );
                    // 会员ID > 0，则验证会员是否与用户一致
                    if ( $member_id > 0 && $member_id != $info['member_id'] ) {
                        $ret['code']    = -4;
                        $ret['msg']     = app::get('microshop')->_('微店所属用户验证失败');
                    }
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
                'msg'   => app::get('microshop')->_('很抱歉，本帐户不允许开设微店和创建专辑'),
            );
        }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        }
        return $ret;
    }

}
