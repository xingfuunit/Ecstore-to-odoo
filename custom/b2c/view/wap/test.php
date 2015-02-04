<?php
class b2c_ctl_wap_test extends wap_frontpage{
   function __construct($app){
        parent::__construct($app);
    }
    
    public function index(){

      $this->set_tmpl_file('articlelist-tehui.html');
      $this->page('wap/tehui/tehui.html');
    }

    public function scroll(){
      
    	$start=$_GET['start'];
    	$limit=5;
    	$goodsModel = app::get('b2c')->model('goods');
        
        //$pageLimit =20;
  
        $goodsData = $goodsModel->getList('*');
        if($goodsData && $total ===false){
           $total = $goodsModel->count($filter);
        }

         foreach($goodsData as $key=>$goods_row){
            if(in_array($goods_row['goods_id'],$gfav)){
                $goodsData[$key]['is_fav'] = 'true';
            }
            if($goods_row['udfimg'] == 'true' && $goods_row['thumbnail_pic']){
                $goodsData[$key]['image_default_id'] = $goods_row['thumbnail_pic'];
            }
            $gids[$key] = $goods_row['goods_id'];
        }
       
        $productModel =app::get('b2c')->model('products');
        $products =  $productModel->getList('*',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        $sdf_product = array();
        foreach($products as $key=>$row){
            $sdf_product[$row['goods_id']] = $row;
        }
        foreach ($goodsData as $gk=>$goods_row){
            $product_row = $sdf_product[$goods_row['goods_id']];
            $goodsData[$gk]['products'] = $product_row;
            //市场价
            if($show_mark_price =='true'){
                if($product_row['mktprice'] == '' || $product_row['mktprice'] == null)
                    $goodsData[$gk]['products']['mktprice'] = $productModel->getRealMkt($product_row['price']);
            }

            //库存
            if($goods_row['nostore_sell'] || $product_row['store'] === null){
                $goodsData[$gk]['products']['store'] = 999999;
            }else{
                $store = $product_row['store'] - $product_row['freez'];
                $goodsData[$gk]['products']['store'] = $store > 0 ? $store : 0;
            }
        }
        $ajaxgoodsdata=array();
       	for($i=$start;$i<($start+$limit);$i++){
       		$ajaxgoodsdata[]= $goodsData[$i];
       	}
        
         $this->pagedata['goodsData'] = $ajaxgoodsdata;
         $html = $this->fetch('wap/tehui/ajax_scroll.html');
         echo $html;

    }
    
   public function wap_demo(){
       print_r("sdad");exit;
   }
}

