<?php
/**
 * 获得商品数据类
 * @author tntppa@163.com
 * @Date 2013-11-10
 *
 */
class groupactivity_mdl_getgoodsdata{


    /*
     * get goods data
     * @parm $filer 获取条件
     */
    public function get_goods_data($filter){

        if($filter['goodsNum'] == 1) {

            $returnGoodsData = $this->get_one_goods_data($filter['goodsId'][0]);
            return $returnGoodsData;
        }
    }

    //获取一条商品数据
    public function get_one_goods_data($filter){

        //实例化商品model
        $goodsModel = app::get('b2c')->model('goods');
        $goodsData = $goodsModel->getList('name,price,cat_id,image_default_id,mktprice',array('goods_id'=>$filter),0,-1,'');
        //new goods_cat
        $goodsCatModel = app::get('b2c')->model('goods_cat');

        //use cat_id get cat_name
        $catId = $goodsData[0]['cat_id'];
        $goodsCatData = $goodsCatModel->getList('cat_name',array('cat_id'=>$catId),0,-1,'');

        //get goods image url
        $imageId = $goodsData[0]['image_default_id'];
        $imageModel = app::get('image')->model('image');
        $imageUrl = $imageModel->getList('l_url,m_url,s_url',array('image_id'=>$imageId),0,-1,'');

        //组织返回数据
        $goodsCombinationData = array(
            'goodsId'=>$filter,
            'goodsData'=>$goodsData[0],
            'goodsCatData'=>$goodsCatData[0],
            'imageUrl'=>$imageUrl[0],
        );
        $returnGoodsData = $this->process_return_data($goodsCombinationData);

        return $returnGoodsData;

    }

    //组织返回数据
    public function process_return_data($goodsCombinationData){
        //goodsLink
        $goodsLink = $_SERVER['SCRIPT_NAME'].'/product-'.$goodsCombinationData['goodsId'].'.html';

        $return_tmp_data = array(

            'goodsId'=>$goodsCombinationData['goodsId'],//goodsid
            'goodsName'=>$goodsCombinationData['goodsData']['name'],//商品名称
            'goodsCategory'=>$goodsCombinationData['goodsCatData']['cat_name'],//商品分类
            'goodsPicL'=>$goodsCombinationData['imageUrl']['l_url'],//商品大图
            'goodsPicM'=>$goodsCombinationData['imageUrl']['m_url'],//商品中图
            'goodsPicS'=>$goodsCombinationData['imageUrl']['s_url'],//商品小图
            'goodsMarketPrice'=>$goodsCombinationData['goodsData']['mktprice'],//市场价
            'goodsSalePrice'=>$goodsCombinationData['goodsData']['price'],//销售价
            //'goodsMemberPrice'=>$goodsCombinationData['goodsCatData']['cat_name'],//会员价
            //'goodsDiscount'=>$goodsCombinationData['goodsCatData']['cat_name'],//折扣
            //'goodsIntro'=>$goodsCombinationData['goodsData']['intro'],//商品简介
            'goodsLink'=>$goodsLink,//商品链接
        );

        //group widgets out data
        $return_data = array();
        $return_data['goodsRows'][$goodsCombinationData['goodsId']] = $return_tmp_data;

        return $return_data;
    }
}

