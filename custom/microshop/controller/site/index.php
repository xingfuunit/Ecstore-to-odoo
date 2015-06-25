<?php
/**
 * ********************************************
 * Description   : 微店列表
 * Filename      : index.php
 * Create time   : 2014-06-19 14:06:12
 * Last modified : 2014-06-24 14:04:46
 * License       : MIT, GPL
 * ********************************************
 */
class microshop_ctl_site_index extends b2c_frontpage {

    function __construct( $app ) {
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->shopname = app::get('site')->getConf('site.name');
        if( isset($this->shopname) ) {
            $this->title        = app::get('microshop')->_('微店').'_'.$this->shopname;
            $this->keywords     = app::get('microshop')->_('微店').'_'.$this->shopname;
            $this->description  = app::get('microshop')->_('微店').'_'.$this->shopname;
        }    
        $cur = app::get('ectools')->model('currency');
        //货币格式输出
        $ret = $cur->getFormat();
        $ret =array(
            'decimals'                  => app::get('b2c')->getConf('system.money.decimals'),
            'dec_point'                 => app::get('b2c')->getConf('system.money.dec_point'),
            'thousands_sep'             => app::get('b2c')->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'     => app::get('b2c')->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'   => app::get('b2c')->getConf('system.money.decimals'),
            'sign'                      => $ret['sign']
        );   
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['headers'][] = '<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />';
        $this->pagedata['headers'][] = '<meta content="telephone=no" name="format-detection" />';
        $this->pagedata['headers'][] = '<meta name="apple-mobile-web-app-capable" content="yes" />';
        $this->pagedata['headers'][] = '<meta name="apple-mobile-web-app-status-bar-style" content="black" />';
    }

    function index() {
        echo '微店列表';
    }

    function detail() {
        $_getParams = $this->_request->get_params();
        $shop_id    = intval($_getParams[0]);
        if ( $shop_id ) {
            $mdl        = $this->app->model('shop');
            $filter     = array (
                'shop_id'   => $shop_id
            );
            $info       = $mdl->getDetail($filter);
            $this->pagedata['title']    = $info['shop_name'].'_'.$this->shopname;
            if ( $info['is_open'] == 1 ) {
                // 用户信息
                /*
                $m_mdl  = app::get('b2c')->model('members');
                $m_info = $m_mdl->dump($info['member_id']);
                $info['follow_num'] = $m_info['follow_num'];
                $info['fans_num']   = $m_info['follow_num'];
                $info['bg_url']     = $m_info['cover'] ? $m_info['cover'] : $this->app->res_url.'/images/top-bg.png';
                $info['avatar']     = $m_info['avatar'] ? $m_info['avatar'] : $this->app->res_url.'/images/top-bg.png';
                */
                
                // 专辑列表
                $s_mdl  = $this->app->model('special');
                $filter = array (
                    'shop_id'   => $shop_id,
                );
                $param      = array (
                    'filter'    => $filter,
                    'page'      => 1,
                    'limit'     => 50,
                    'orderby'   => 'addtime DESC'
                );
                $spec_list  = $s_mdl->getSpecialList($param);
                $this->pagedata['spec_list']    = $spec_list;
                $img_set= app::get('image')->getConf('image.set');
                $big_w  = 138;
                $big_h  = intval($img_set['M']['height'] * $big_w / $img_set['M']['width']);
                $small_w= 44;
                $small_h= intval($img_set['S']['height'] * $small_w / $img_set['S']['width']);
                $this->pagedata['img_size'] = array (
                        'big_w'     => $big_w,
                        'big_h'     => $big_h,
                        'small_w'   => $small_w,
                        'small_h'   => $small_h,
                        );
            } else {
                $info['shop_name']      = app::get('microshop')->_('该微店已关闭');
            }
            $this->pagedata['info']     = $info;
        }
        $this->display('site/detail.html');
    }

    /**
     * 专辑详情
     */
    function special() {
        $_getParams = $this->_request->get_params();
        $spec_id    = intval($_getParams[0]);
        $data       = array (
                    'special_id'=> $spec_id,
                    'limit'     => -1,
                    );  
        $mdl    = $this->app->model('special');
        $info   = $mdl->getSpecialInfo($data);
        if ( $info['shop_open'] == 1 ) {
            // 用户信息
            $s_mdl  = $this->app->model('shop');
            $s_info = $s_mdl->getDetail($info);
            $info['follow_num'] = $s_info['follow_num'];
            $info['fans_num']   = $s_info['fans_num'];
            $info['bg_url']     = $s_info['cover'];
            $info['avatar']     = $s_info['avatar'];
            $info['cover']      = $s_info['cover'];
            $info['info']       = $s_info['info'];
            $info['shop_name']  = $s_info['shop_name'];
            $img_set    = app::get('image')->getConf('image.set');
            $big_w      = 148;
            $big_h      = intval($img_set['M']['height'] * $big_w / $img_set['M']['width']);
            $this->pagedata['img_size'] = array (
                'big_w'     => $big_w,
                'big_h'     => $big_h,
            );
        } else {
            $info['shop_name']  = app::get('microshop')->_('该微店已关闭');
        }
        $this->pagedata['info'] = $info;
        $this->pagedata['title']= $info['special_name'].'_'.$this->shopname;
        $this->display('site/special.html');
    }
    
}
