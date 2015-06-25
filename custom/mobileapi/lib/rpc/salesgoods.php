<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class mobileapi_rpc_salesgoods extends mobileapi_rpc_goods
{
    /**
     * 构造方法
     * @param object application
     */
    public function __construct(&$app)
    {
        $this->app = $app;
    }

    public function sales_list()
    {
        $arr = $this->app->model('sales_goods')->getList( '*',array('status'=>'true','from_time|lthan'=> time(),'to_time|than'=>time()));
        if(is_array($arr) && $arr[0]['conditions']['goods_select'] != ''){
            $arr[0]['goods'] = $this->get_sales_goods(array('ids'=>$arr[0]['conditions']['goods_select']));
        }
        return $arr;
    }

    public function get_sales_goods($params = array())
    {
        $sids = $params['ids'];
        if(!$sids) return;
        $rows = app::get('b2c')->model('goods')->getList('*',array('goods_id|in'=>explode(',', $sids),'marketable'=>'true'));
        $sdf_goods = array();
        foreach($rows as $arr_row){
            $sdf_goods[] = $this->get_item_detail($arr_row, '');
        }
        return $sdf_goods;
    }

    function get_sales_ads($params = array()) {
        $ad_position = $params['ad_position'] ? $params['ad_position'] : 1;
        $db = kernel::database();
        $sql = "select *  FROM `sdb_mobileapi_sales_ads` where disabled = 'false' and ad_position = '".$ad_position."' order by ordernum asc;";
        $indexads = $db->select($sql);
        
        $groupad = array();
        foreach ($indexads as $key => $v){
            $v['ad_img'] = base_storager::image_path($v['ad_img']);
            $groupad[$v['group_code']]['items'][] = $v;
        }
        
        $res = array();
        foreach ($groupad as $key => $value) {
            $res[] = $value;
        }
        
        return $res;
        
        //print_r($res);exit;
    }
    

}
