<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * b2c order interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_goods_store
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->objMath = kernel::single("ectools_math");
    }
	
    /**
     * 取到所有货品的bn和store
     * @param null
     * @return string json
     */
    public function get_all_products_store()
    {
        $obj_products = $this->app->model('products');
        $arr_products = $obj_products->getList('bn,store');

        return $arr_products;
    }
	
    /**
     * 根据货号更新一个货品的库存
     * @param String bn
     * @return  array();
     */
    public function get_store($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
		//-----------------------------------------------
        $obj_products = $this->app->model('products');

        $rs_product = $obj_products->getRow('product_id,goods_id,bn,store,freez',array('bn'=>$params['bn']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
		}

        return $this->response_product_store($rs_goods, $rs_product);
    }
	
    /**
     * 根据货品ID查询一个货品库存
     * @param String product_id 货品ID
     * @return  array()
     */
    public function get_store_id($params, &$service)
    {
		$params['product_id'] = intval(''.$params['product_id']);
        if ($params['product_id']<1)
        {
            $service->send_user_error(app::get('b2c')->_('product_id参数为空'), null);
        }

		//-----------------------------------------------
        $obj_products = $this->app->model('products');

        $rs_product = $obj_products->getRow('product_id,goods_id,bn,store,freez',array('product_id'=>$params['product_id']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'));
		}

        return $this->response_product_store($rs_goods, $rs_product);
    }

	
    /**
     * 根据多个货品ID查询多个货品库存
     * @param String product_ids 货品ID数组的json，最大一次20个  [21,22,23]
     * @return  array(货品ID=>库存) goods_num 商品库存
     */
    public function get_store_id_json($params, &$service)
    {
        $product_ids = $params['product_ids'];
        if (!isset($product_ids) || strlen($product_ids)==0)
        {
			$service->send_user_error(app::get('b2c')->_('product_ids参数为空'), null);
        }
        $jsonProductId = json_decode($product_ids,true);

        if(count($jsonProductId)>20 || count($jsonProductId) < 1)
        {
			$service->send_user_error(app::get('b2c')->_('product_ids参数错误'), null);
        }
		
		//-----------------------------------------------
        $obj_products = $this->app->model('products');
        $products_filter = array('product_id'=>$jsonProductId);
        $arr_products = $obj_products->getList('product_id,goods_id,bn,store,freez',$products_filter);
        if(count($arr_products) == 0)
		{
            return array();//没有查询到任何内容
		}
		
		//-----------------------------------------------
        foreach($arr_products as $key=>$product)
        {
            $fmt_products[$product['product_id']] = $product;
            $goods_ids[$key] = $product['goods_id'];
        }

        $obj_goods = $this->app->model('goods');
        $goodsdata = $obj_goods->getList('goods_id,nostore_sell,store',array('goods_id'=>$goods_ids));
        foreach($goodsdata as $goodsRow)
        {
            $fmt_goods[$goodsRow['goods_id']] = $goodsRow;
        }

        foreach($fmt_products as $fmt_product)
        {
			//不算库存的情况
            if( $fmt_goods[$fmt_product['goods_id']] && ($fmt_goods[$fmt_product['goods_id']]['nostore_sell'] || $fmt_goods[$fmt_product['goods_id']]['store']==null))
			{
				$fmt_product['nostore'] 	= '1'; 		//1=无库存销售,0=必须有库存
				$fmt_product['usestore'] 	= '999999'; //可用库存

			} else{
				$fmt_product['nostore'] 	= '0'; 		//1=无库存销售,0=必须有库存
				$fmt_product['usestore'] 	= ''.($fmt_product['store'] - $fmt_product['freez']); //可用库存
			}
			
			$return[] = $fmt_product;
        }
        return $return;

    }
	
	/**
	 * 根据goods_id 合计全部 products.store，并更新goods.store 字段
	 * @param int goods_id
	 * @return int 返回更新后的合计数
	 */
	private function update_goods_store($goods_id)
	{
		$_products = $this->app->model('products');
		$sql  = 'select sum(store) from '.$_products->table_name(1).' where goods_id='.$goods_id;
		
		//-----------------------------------------------
		$db = kernel::database();
		$rs = $db->selectrow($sql);
		$store = intval($rs[0]);

		$save_data['store']  		= $store;
		$save_data['last_modify']  	= time();
		
		$_goods = $this->app->model('goods');
		$_goods->update($save_data, array('goods_id' => $goods_id));

		return $store;
	}
	
	/**
	 * 根据goods_id 合计全部 products.store，并更新goods.store 字段
	 * @param objcet rs_goods
	 * @param objcet rs_goods
	 * @return array
	 */
	private function response_product_store($rs_goods,$rs_product)
	{
		//-----------------------------------------------
		//不算库存的情况
		if($rs_goods['nostore_sell']=='1' || $rs_goods['store']==null)
		{
			$rs_product['nostore'] 	= '1'; 		//1=无库存销售,0=必须有库存
			$rs_product['usestore'] = '999999'; //可用库存

		} else{
			$rs_product['nostore'] 	= '0'; 		//1=无库存销售,0=必须有库存
			$rs_product['usestore'] = ''.($rs_product['store'] - $rs_product['freez']); //可用库存
		}
        return $rs_product;
	}
	
    /**
     * 根据货号更新一个货品的库存
     * @param String bn
     * @return  array();
     */
    public function update_store($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
		if(!isset($params['qty']) || !is_numeric($params['qty']))
		{
            $service->send_user_error(app::get('b2c')->_('qty参数格式不对'), null);
		}
		
		$params['qty'] = intval(''.$params['qty']);
		if($params['qty']<0){
			$params['qty'] = 0;
		}

		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs_product = $_products->getRow('product_id,goods_id,bn,store,freez',array('bn'=>$params['bn']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
		}
		//-----------------------------------------------
		//更新库存
		$rs_product['store'] = ''.$params['qty'];
		
		$save_data['store']  		= $params['qty'];
		$save_data['last_modify']  	= time();
		
		$isSave = $_products->update($save_data, array('product_id' => $rs_product['product_id']));
		//-----------------------------------------------
		//同步更新 goods 表
		$this->update_goods_store($goods_id);
		
        return $this->response_product_store($rs_goods, $rs_product);
    }

    /**
     * 根据货品ID更新一个货品的库存
     * @param String product_id 货品ID
     * @return  array()
     */
    public function update_store_id($params, &$service)
    {
		$params['product_id'] = intval(''.$params['product_id']);
        if ($params['product_id']<1)
        {
            $service->send_user_error(app::get('b2c')->_('product_id参数为空'), null);
        }
		
		if(!isset($params['qty']) || !is_numeric($params['qty']))
		{
            $service->send_user_error(app::get('b2c')->_('qty参数格式不对'), null);
		}
		
		$params['qty'] = intval(''.$params['qty']);
		if($params['qty']<0){
			$params['qty'] = 0;
		}
		
		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs_product = $_products->getRow('product_id,goods_id,bn,store,freez',array('product_id'=>$params['product_id']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
		}
		//-----------------------------------------------
		//更新库存
		$rs_product['store'] = ''.$params['qty'];
		
		$save_data['store']  		= $params['qty'];
		$save_data['last_modify']  	= time();
		
		$isSave = $_products->update($save_data, array('product_id' => $rs_product['product_id']));
		//-----------------------------------------------
		//同步更新 goods 表
		$this->update_goods_store($goods_id);
		
        return $this->response_product_store($rs_goods, $rs_product);
    }
	
    /**
     * 根据货号更新一个货品的冻结库存
     * @param String bn
     * @return  array();
     */
    public function update_freezstore($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
		if(!isset($params['qty']) || !is_numeric($params['qty']))
		{
            $service->send_user_error(app::get('b2c')->_('qty参数格式不对'), null);
		}
		
		$params['qty'] = intval(''.$params['qty']);
		if($params['qty']<0){
			$params['qty'] = 0;
		}

		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs_product = $_products->getRow('product_id,goods_id,bn,store,freez',array('bn'=>$params['bn']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
		}
		//-----------------------------------------------
		//更新库存
		$rs_product['freez'] = ''.$params['qty'];
		
		$save_data['freez']  		= $params['qty'];
		$save_data['last_modify']  	= time();
		
		$isSave = $_products->update($save_data, array('product_id' => $rs_product['product_id']));
		
		
        return $this->response_product_store($rs_goods, $rs_product);
    }
	
    /**
     * 根据货品ID更新一个货品的冻结库存
     * @param String product_id 货品ID
     * @return  array()
     */
    public function update_freezstore_id($params, &$service)
    {
		$params['product_id'] = intval(''.$params['product_id']);
        if ($params['product_id']<1)
        {
            $service->send_user_error(app::get('b2c')->_('product_id参数为空'), null);
        }
		
		if(!isset($params['qty']) || !is_numeric($params['qty']))
		{
            $service->send_user_error(app::get('b2c')->_('qty参数格式不对'), null);
		}
		
		$params['qty'] = intval(''.$params['qty']);
		if($params['qty']<0){
			$params['qty'] = 0;
		}
		
		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs_product = $_products->getRow('product_id,goods_id,bn,store,freez',array('product_id'=>$params['product_id']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
        $rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
        if(count($rs_goods) == 0)
		{
            $service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
		}
		//-----------------------------------------------
		//更新库存
		$rs_product['freez'] = ''.$params['qty'];

		$save_data['freez']  		= $params['qty'];
		$save_data['last_modify']  	= time();
		
		$isSave = $_products->update($save_data, array('product_id' => $rs_product['product_id']));
		
        return $this->response_product_store($rs_goods, $rs_product);
    }
	
    /**
     * 批量修改库存量
     * @param json
     * @return  array()
     */
    public function updateStore($params, &$service)
    {
        if (!isset($params['list_quantity']) || !$params['list_quantity'])
        {
            $service->send_user_error(app::get('b2c')->_('缺少参数 list_quantity'), null);
        }
		$arr_store = json_decode($params['list_quantity'], true);
		
		//---------------------------------------------------
		
		$_products = $this->app->model('products');

		//---------------------------------------------------
		
		if (isset($arr_store) && is_array($arr_store))
		{
			foreach ($arr_store as $info)
			{
				if ($info['bn'] && is_numeric($info['quantity']))
				{
					if (!$_products->dump(array('bn' => $info['bn'])))
					{
						$service->send_user_error(app::get('b2c')->_('该货品不存在'.$info['bn']), null);
						return;	
					}
				}else{
					$service->send_user_error(app::get('b2c')->_('参数json转换出错！请检查 bn 和 quantity！'), null);
					return;	
				}
			}
		}else{
            $service->send_user_error(app::get('b2c')->_('参数json转换出错！请检查！'), null);
		}
		
		//---------------------------------------------------
		$_goods = $this->app->model('goods');
		//---------------------------------------------------
		foreach ($arr_store as $info)
		{
			if ($info['bn'] && is_numeric($info['quantity']))
			{
				$rs_product = $_products->getRow('product_id,goods_id,bn,store,freez',array('bn' => $info['bn']));
				if(count($rs_product) == 0)
				{
					$service->send_user_error(app::get('b2c')->_('该货品不存在'.$info['bn']), null);
				}
				//-----------------------------------------------
				$goods_id = $rs_product['goods_id'];

				$rs_goods = $_goods->getRow('nostore_sell,store',array('goods_id'=>$goods_id));
				if(count($rs_goods) == 0)
				{
					$service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'), null);
				}
				
				//-----------------------------------------------
				//更新库存
				$rs_product['store'] = ''.$info['quantity'];
				
				$save_data['store']  		= $info['quantity'];
				$save_data['last_modify']  	= time();
				
				$isSave = $_products->update($save_data, array('product_id' => $rs_product['product_id']));
				
				//-----------------------------------------------
				//同步更新 goods 表
				$this->update_goods_store($goods_id);
			}
		}
		
		return 'true';
	}
	
	
    /**
     * 库存修改
     * @param array sdf
     * @return boolean success of failure
     */
    public function updateStore2(&$sdf, $thisObj)
    {

        if (!isset($sdf['list_quantity']) || !$sdf['list_quantity'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要更新的货品的库存不存在！'), null);
        }
        else
        {
            $has_error = false;
            $arr_store = json_decode($sdf['list_quantity'], true);

            $product = $this->app->model('products');
            $obj_goods = $this->app->model('goods');
            $fail_products = array();
            $db = kernel::database();
            if (isset($arr_store) && $arr_store)
            {
                foreach ($arr_store as $arr_product_info)
                {
                    if ($arr_product_info['bn'] && is_numeric($arr_product_info['quantity']))
                    {
                        $arr_product = $product->dump(array('bn' => $arr_product_info['bn']));
                        if ($arr_product)
                        {
                            $store_increased = $this->objMath->number_minus(array(intval($arr_product_info['quantity']), intval($arr_product['store'])));
                            $arr_goods = $db->selectrow('SELECT store,goods_id from sdb_b2c_goods where goods_id ='.$arr_product['goods_id']);


                            $goods_store = $this->objMath->number_plus(array($arr_goods['store'],$store_increased,$arr_product['freez']));
                            $arr_goods['store'] = ($goods_store == '0') ? 0:$goods_store;
                            $arr_product['store'] = $this->objMath->number_plus(array($arr_product_info['quantity'],$arr_product['freez']));
                            $arr_product['last_modify'] = time();
                            $storage_enable = $this->app->getConf('site.storage.enabled');
                            if (!is_null($arr_product['store']) && $storage_enable != 'true')
                            {
                                $is_save = $product->save($arr_product);
                                if($is_save){

                                    $obj_goods->update($arr_goods, array('goods_id' => $arr_goods['goods_id']));
                                }
                            }
                            else
                            {
                                $is_save = true;
                            }

                            if (!$is_save)
                            {
                                $msg = $this->app->_('商品库存更新失败！');
                                $has_error = true;

                                $fail_products[] = $arr_product_info['bn'];

                                continue;
                            }
                        }
                        else
                        {
                            $has_error = true;

                            $fail_products[] = $arr_product_info['bn'];

                            continue;
                        }
                    }
                    else
                    {
                        $has_error = true;
                        continue;
                    }
                }

                if (!$has_error)
                    return true;
                else
                {
                    // 更新部分失败.
                    $fail_products = array('error_response' => $fail_products);
                    $thisObj->send_user_error(app::get('b2c')->_('更新库存部分失败！'), $fail_products);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('更新的商品的库存信息不存在！'), null);
            }
        }
    }

    /**
     * 冻结库存请0
     * @param array sdf
     * @return boolean success of failure
    */
    private function updateFreezStore(&$sdf, $thisObj)
    {

        if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            trigger_error(app::get('b2c')->_('订单标号不存在！'), E_USER_ERROR);
        }
        else
        {
            $obj_orders = $this->app->model('orders');
            $goods = $this->app->model('goods');
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_orders->dump($sdf['order_bn'], '*', $subsdf);
            $stock_freez_time = $this->app->getConf('system.goods.freez.time');

            if ($stock_freez_time == '1')
            {
                // 清除预占库存
                if ($sdf_order['order_objects'])
                {
                    foreach ($sdf_order['order_objects'] as $arr_sdf_objs)
                    {
                        if ($arr_sdf_objs['order_items'])
                        {
                            foreach ($arr_sdf_objs['order_items'] as $arr_sdf_items)
                            {
                                $goods->unfreez($arr_sdf_items['products']['goods_id'], $arr_sdf_items['products']['product_id'], $arr_sdf_items['quantity']);
                            }
                        }
                    }
                }
            }

            return array('tid'=>$sdf['order_bn']);
        }
    }
}
