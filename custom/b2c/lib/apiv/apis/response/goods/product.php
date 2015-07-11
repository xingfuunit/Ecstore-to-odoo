<?php
class b2c_apiv_apis_response_goods_product
{
    //公开的构造函数
    public function __construct($app)
    {
        $this->app=$app;
    }    


    /**
     * 根据货品ID,查询相应的会员价格
     * @param String product_id 货品ID数组的json
     * @return array(product_id=>array(会员等级=>会员价)) goods_num 商品库存
     */
    public function get_lv_price($params, &$service)
    {
        $jsonProductId = $params['product_id'];
        $productIds = json_decode($jsonProductId,true);
        if(count($productIds)>20 || count($productIds) < 1)
        {
            return $service->send_user_error('参数错误，请提交正确的参数');
        }
        $obj_products = $this->app->model('products');
        $obj_goods_lv_price = $this->app->model('goods_lv_price');
        $obj_member_lv = $this->app->model('member_lv');

        //获取货品你价格
        $filter1=array('product_id'=>$productIds);
        $product_ids=$obj_products->getList('product_id,price',$filter1);
        foreach($product_ids as $product_id)
        {
            $fmt_product_ids[$product_id['product_id']] = $product_id['product_id'];
            $fmt_product_prices[$product_id['product_id']] = $product_id['price'];
        }
        //获取会员详情
        $member_lvs = $obj_member_lv->getList('member_lv_id,name,dis_count');
        foreach($member_lvs as $member_lv)
        {
            $member_lv_id = $member_lv['member_lv_id'];
            $fmt_member_lv[$member_lv_id] = $member_lv;
        }
        //获取会员价
        $filter2 = array('product_id'=>$fmt_product_ids);
        $price_datas = $obj_goods_lv_price->getList('product_id,level_id,price', $filter2);
        foreach($price_datas as $price_data)
        {
            $fmt_lv_price[$price_data['product_id']][$price_data['level_id']] = $price_data['price'];
        }
        //组织数据
        foreach($fmt_product_ids as $product_id)
        {
            foreach($fmt_member_lv as $member_lv_id=>$member_lv)
            {
                if(isset($fmt_lv_price[$product_id][$member_lv_id]) && $fmt_lv_price[$product_id][$member_lv_id])
                {
                    $temp = $fmt_lv_price[$product_id][$member_lv_id];
                    $goods_member_price[$product_id][$member_lv['name']] = $temp;
                }else{
                    $temp = $fmt_product_prices[$product_id] * $member_lv['dis_count'];
                    $goods_member_price[$product_id][$member_lv['name']] = $temp;
                }
            }
        }
        $return['prices'] = $goods_member_price;
        return $return;
    }
}
