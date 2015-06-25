<?php

class b2c_finder_return_goods{
    /**
	 * @var 定义方法名称的变量
	 */
    public $detail_basic = '基本信息';
    
    /**
     * 构造方法，定义全局变量app和状态值
     * @param object app 类
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    /**
    * finder的下拉详细页面
    */
    public function detail_basic($return_id)
    {
        $render = $this->app->render();
        $return_goods = $this->app->model('return_goods');
        $arr_return_product = $return_goods->dump($return_id);
        $return_order_items = $this->app->model('return_order_items');
        if($arr_return_product['order_sn']){
           $row_list = $return_order_items->getList('*',array('order_sn'=>$arr_return_product['order_sn']));
        }
        foreach ( $row_list AS $k => $v ) {
                // 商品单位换算
                if($v['addon'] == "千克" || $v['addon'] == "kg"){
                    $_val = number_format($v['nums']/1000, 3, '.', '');
                    $row_list[$k]['quantity_exchange'] = floatval($_val);
                }
                //print_r($aCart['object']['goods']);exit;
        }
        $render->pagedata['row_list'] = $row_list;
        return $render->fetch('admin/return_goods/goods_list.html');
    }
    
    function searchOptions(){
        $arr = parent::searchOptions();
        return array_merge($arr,array(
                'bn'=>__('货号'),
                'keyword'=>__('商品关键字'),
            ));
    }
    
}