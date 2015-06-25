<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class groupactivity_delete_check
{
    
    
    function __construct( &$app ) {
        $this->app = $app;
        $this->o_purchase = $this->app->model('purchase');
    }
    
    
    /**
     * 
     * @params $gid 商品id
     * @params $pid 货品id
     * @return bool
     **/
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        
        #if( $pid )
        #    $filter['product_id'] = $pid;
        
        if( $gid ) 
            $filter['gid'] = $gid;
        if( !$filter ) return true;

        $objProducts =  app::get('b2c')->model('products');
        $ret = $objProducts->getList('product_id', array('goods_id'=> $gid));
        foreach ($ret as $val) {
            $product_ids[] = $val['product_id'];
        }
        $db = kernel::database();
        $count = $db->select('SELECT pid FROM  `sdb_groupactivity_purchase` where pid in('.implode(',', $product_ids).')');

        //$count = $this->o_purchase->dump( $filter );
        if( count($count) ) {
            $this->error_msg = '该商品在团购中存在！无法删除！';
            return false;
        }
        return true;
    }
}