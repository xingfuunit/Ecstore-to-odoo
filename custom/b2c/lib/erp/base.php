<?php
/**
 * ********************************************
 * Description   : erp 同步基础类
 * Filename      : base.php
 * Create time   : 2014-07-11 15:17:07
 * Last modified : 2014-07-11 15:37:19
 * License       : MIT, GPL
 * ********************************************
 */
class b2c_erp_base {

    public $erp_base_url    = 'http://pinzhenerp.qsit.com.cn/';

    /**
     * 获得erp签名
     * 
     * @param   array   $param  要签名的参数
     * @return  string
     */
    public function getSign( $param ) {
        $_param = array (
            'url'   => $this->getSignUrl(),
            'data'  => $param
        );
        return $this->send($_param);
    }

    /**
     * 获得签名的URL
     *
     * @return string
     */
    public function getSignUrl() {
        return $this->erp_base_url.'index.php/index.php?app=ome&ctl=services&act=get_sign';
    }

    /**
     * 获得API URL
     *
     * @return string
     */
    public function getAPIUrl() {
        return $this->erp_base_url.'index.php/api';
    }

    /**
     * 发送请求
     *
     * @param   array   $param  参数
     * @return  string
     */
    public function send($param) {
        $type   = $param['type'] ? $param['type'] : 'post';
        $url    = $param['url'];
        $data   = $param['data'];
        $ret    = kernel::single('base_curl')->action($type, $url, '', '', $data);
        return $ret;
    }

    /**
     * 同步
     *
     * @param   array   $param  需要同步的数据
     * @return  mix
     */
    public function sync( $param ) {
        // 获得店铺绑定情况
        $cnt    = kernel::single('b2c_shop')->getShopBindStatus();
        $ret    = false;
        if ( $cnt > 0 ) {
            $param['sign']  = $this->getSign($param);
            $_param = array (
                    'url'   => $this->getAPIUrl(),
                    'data'  => $param
                    );
            $ret = $this->send($_param);
        }
        return $ret;
    }

}
