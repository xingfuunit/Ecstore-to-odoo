<?php
/**
 * ********************************************
 * Description   : 专辑数据模型
 * Filename      : special.php
 * Create time   : 2014-06-19 17:20:22
 * Last modified : 2014-06-24 14:05:18
 * License       : MIT, GPL
 * ********************************************
 */

class microshop_mdl_special extends dbeav_model {

    function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 获得专辑列表
     * 
     * @param   array   $param          参数
     * @return  array
     */
    function getSpecialList( $param = array() ) {
        $list   = array ();
        $filter = $param['filter'];
        $fields = $param['fields']  ? $param['fields'] : '*';
        $limit  = intval($param['limit']);
        $limit  = $limit > 0 ? $limit : 20;
        $page   = intval($param['page']);
        $page   = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;
        $orderby= $param['orderby'] ? $param['orderby'] : 'addtime DESC';
        $count  = parent::count($filter);
        if ( $count ) {
            $info   = parent::getList($fields, $filter, $offset, $limit, $orderby);
        }
        $platform   = $param['platform'] ? $param['platform'] : 'wap';
        // 获得商品列表
        if ( $info ) {
            $list['total_results'] = $count;
            foreach ( $info AS $k => $v ) {
                $param  = array (
                        'special_id'    => $v['special_id'],
                        'limit'         => 4,
                        'platform'      => $platform,
                        );
                $list['list'][]   = $this->getSpecialInfo($param, 2);
            }
        }
        return $list;
    }

    /**
     * 获得商品列表
     *
     * @param   array   $param          参数
     * @param   number  $update_visit   是否更新访问，1->更新(默认), 其它 -> 不更新
     * @return  array
     */
    function getSpecialInfo( $param = array(), $update_visit = 1 ) {
        $info   = array ();
        if ( $param['special_id'] ) {
            $filter = array (
                    'special_id'=> $param['special_id'],
                    );
            $info   = parent::dump($filter);
            $_param = array (
                'app'   => 'microshop',
                'ctl'   => 'site_index',
                'full'  => 1,
                'act'   => 'special',
                'arg0'  => $param['special_id'],
            );  
            $info['special_url']  = app::get('site')->router()->gen_url($_param);
            // 验证商店是否关闭
            $s_mdl  = $this->app->model('shop');
            $_filter= array (
                'shop_id'   => $info['shop_id']
            );
            $s_info = $s_mdl->getDetail($_filter, 2);
            $info['shop_open']  = $s_info['is_open'];
            if ( $info['shop_open'] != 1 ) {
                return $info;
            }
            // 专辑访问添加
            if ( $update_visit == 1 ) {
                $_info  = array (
                        'see_num'   => $info['see_num'] + 1
                        );
                parent::update($_info, $filter);
            }
            // 获得商品信息
            $mdl        = $this->app->model('special_products');
            $page       = intval($param['page']);
            $page       = $page ? $page : 1;
            $limit      = $param['limit'] ? $param['limit'] : 20;
            $offset     = ($page - 1) * $limit;
            $offset     = $offset > 0 ? $offset : 0;
            $orderby    = $param['orderby'] ? $param['orderby'] : 'addtime DESC';
            $fields     = $param['fields'] ? $param['fields'] : '*';
            $count      = $mdl->count($filter);
            $list       = array();
            if ( $count > 0 ) {
                $list   = $mdl->getList($fields, $filter, $offset, $limit, $orderby);
            }
            $platform   = $param['platform'] ? $param['platform'] : 'wap';
            if ( $list ) {
                $p_mdl      = app::get('b2c')->model('products');
                $g_mdl      = app::get('b2c')->model('goods');
                $p_ids      = array();
                foreach ( $list AS $v ) {
                    $p_ids[]= $v['product_id'];
                }
                if ( $p_ids ) {
                    // 获得产品列表
                    $products   = $p_mdl->getList('*', array('product_id' => $p_ids, 'marketable' => 'true'));
                    if ( $products ) {
                        $g_ids  = array();
                        foreach ( $products AS $k => $v) {
                            $g_ids[$v['goods_id']]      = $v['goods_id'];
                        }
                        $good_ids   = array_values($g_ids);
                        // 获得商品图片
                        $img_arr    = $g_mdl->getList('image_default_id, goods_id', array('goods_id' => $good_ids));
                        // 构造产品图片数据
                        foreach ( $products AS $k => $v ) {
                            $products[$k]['title']  = $v['name'];
                            foreach ( $img_arr AS $val ) {
                                if ( $val['goods_id'] == $v['goods_id'] ) {
                                    $img_id = $val['image_default_id'];
                                    $products[$k]['images']   = array (
                                        'big_url'       => kernel::single('base_storager')->image_path($img_id, 'l'),
                                        'thisuasm_url'  => kernel::single('base_storager')->image_path($img_id, 'm'),
                                        'small_url'     => kernel::single('base_storager')->image_path($img_id, 's'),
                                    );
                                    $products[$k]['image_default_id']   = $img_id;
                                }
                            }
                        }
                        // 获得商品收藏
                        $fav_arr    = app::get('b2c')->model('member_goods')->getList('goods_id', array('goods_id' => $good_ids, 'type' => 'fav'));
                        // 构造产品收藏数据
                        foreach ( $products AS $k => $v ) {
                            $products[$k]['fav_num']   = $products[$k]['fav_num'] ? $products[$k]['fav_num'] : 0;
                            foreach ( $fav_arr AS $val ) {
                                if ( $val['goods_id'] == $v['goods_id'] ) {
                                    $products[$k]['fav_num']   += 1;
                                }
                            }
                        }
                        // 将产品数据放入到列表中
                        foreach ( $list AS $k => $v ) {
                            foreach ( $products AS $val ) {
                                if ( $val['product_id'] == $v['product_id'] ) {
                                    $list[$k]['detail']   = $val;
                                }
                            }
                            $v['platform']          = $platform;
                            $list[$k]['buy_url']    = $this->getBuyUrl($v);
                            $list[$k]['buy_code']   = $this->getBuyCode($v);
                        }
                    }
                }
            }
            $info['products']       = $list;
            $info['products_count'] = $count;
        }
        return $info;
    }

    /**
     * 获得商品购买链接
     * 
     * @param   array   $param      专辑商品数据
     * @return  string
     */
    function getBuyUrl($param) {
        $url    = '';
        if ( $param['product_id'] ) {
            $_param = array (
                'app'   => 'b2c',
                'ctl'   => 'wap_product',
                'full'  => 1,
                'act'   => 'index',
                'arg0'  => $param['product_id'],
                'arg1'  => $this->getBuyCode($param)
            );
            $url    = app::get('wap')->router()->gen_url($_param);
        }
        return $url;
    }

    /**
     * 获得商品推广代码
     *
     * @param   array   $param  专辑商品数据
     * @return  string
     */
    function getBuyCode( $param ) {
        $code = $param['special_id'].'_'.$param['platform'];
        return $code;
    }

}
