<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_apiv_apis_response_goods
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }



    /**
     * 添加一个商品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function add_goods(&$sdf, &$thisObj)
    {
        //请求数据验证合法有效性
        if(!$this->_checkInsertData($sdf,$msg)){
            $thisObj->send_user_error($msg, array('iid'=>''));
        }
		
		//--------------------------------------------------
		$sdf['type_id'] 	= 1;	
		//这个商品类型因为ERP和EC不相同，而且EC存在旧的数据，所以二边数据不用相同
		//这样解决方案是：
		//		从ERP调用 新增商品接口时，全部类型都是固定为 1，然后再人工在EC后台修改相关类型和相关商品网图片，详细介绍等。
		//		从ERP调用 编辑商品接口时，类型这个参数，代码会直接跳过，即不修改类型。
		
		$sdf['is_simple']	= false; //全部统一使用规格商品
		$sdf['approve_status'] = '';	//新增商品，默认全部下架，必须人工到EC后台修改资料后再上架

		//--------------------------------------------------
		
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
		

        //判断简单商品还是多货品商品数据处理
        if($sdf['is_simple'] == true){
            $goods_id = $this->simple_goods_update($sdf,$msg);
        }else{
            $goods_id = $this->normal_goods_update(true,$sdf,$msg);
        }

        if (!$goods_id)
        {
            $db->rollback();
            $thisObj->send_user_error($msg, array('iid'=>''));
        }

        $db->commit($transaction_status);

        /** 得到所有的sku_id **/
        $obj_product = $this->app->model('products');
        $tmp_products = $obj_product->getList('product_id,bn',array('goods_id'=>$goods_id));
        $str_sku_bns = "";
        $str_sku_ids = "";
        foreach ((array)$tmp_products as $arr_product)
        {
            $str_sku_ids .= $arr_product['product_id'] . ",";
            $str_bns .= $arr_product['bn'] . ",";
        }
        if ($str_sku_ids)
            $str_sku_ids = substr($str_sku_ids, 0, strlen($str_sku_ids)-1);
        if ($str_bns)
            $str_bns = substr($str_bns, 0, strlen($str_bns)-1);

        /** 获取商品修改时间 **/
        $obj_goods = $this->app->model('goods');
        $tmp_goods = $obj_goods->getList('last_modify',array('goods_id'=>$goods_id));

        return array('iid'=>$goods_id, 'sku_id'=>$str_sku_ids, 'sku_bn'=>$str_bns, 'created'=>date('Y-m-d H:i:s',$tmp_goods[0]['last_modify']));
    }
	
    /**
     * 编辑一个商品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function update_goods($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
        if (!isset($params['name']) || strlen($params['name'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('name参数为空'), null);
        }
		//--------------------------------------------------
		//update 接口根据 bn 查询 iid
		$params['bn'] = '' . $params['bn'];
		$goods_id = $this->get_goods_id($params['bn']);
		if($goods_id<1){
			$msg = app::get('b2c')->_('没有查找到商品！'.$params['bn']);
			$service->send_user_error($msg, null);
		}
		
		//--------------------------------------------------
		$save_data['name'] = $params['name'];
		if(isset($params['barcode']) && strlen($params['barcode'])>0){
			$save_data['barcode'] = $params['barcode'];
		}

		$save_data['last_modify']  	= time();
		
		$_goods = $this->app->model('goods');
		$isSave = $_goods->update($save_data, array('goods_id' => $goods_id));
		
		if($isSave){
			//--------------------------------------------------
			//同步更新products.name
			$_products	= $this->app->model('products');
			$_products->update(array('name'=>$params['name']), array('goods_id' => $goods_id));
			
			//--------------------------------------------------
			return array(
				'goods_id'	=> ''.$goods_id, 
				'time'		=> date('Y-m-d H:i:s',time())
			);
		}else{
			$msg = app::get('b2c')->_('保存出错');
			$service->send_user_error($msg, null);
		}
	}
	
    /**
     * 编辑一个商品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function update(&$sdf, &$thisObj)
    {
        //请求数据验证合法有效性
        if(!$this->_checkUpdateData($sdf,$msg)){
            $thisObj->send_user_error($msg, array('iid'=>''));
        }
		
		//--------------------------------------------------
		$sdf['type_id'] 	= 1;	
		//这个商品类型因为ERP和EC不相同，而且EC存在旧的数据，所以二边数据不用相同
		//这样解决方案是：
		//		从ERP调用 新增商品接口时，全部类型都是固定为 1，然后再人工在EC后台修改相关类型和相关商品网图片，详细介绍等。
		//		从ERP调用 编辑商品接口时，类型这个参数，代码会直接跳过，即不修改类型。
		
		$sdf['is_simple']	= false; //全部统一使用规格商品
		
		//--------------------------------------------------
		//update 接口根据 bn 查询 iid
		$sdf['bn'] = '' . $sdf['bn'];
		
		$sdf['iid'] = $this->get_goods_id($sdf['bn']);
		if($sdf['iid']<1){
			$msg = app::get('b2c')->_('没有查找到商品编号的资料！'.$sdf['bn']);
			$thisObj->send_user_error($msg, array('iid'=>''));
		}
		
		//--------------------------------------------------
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        //判断简单商品还是多货品商品数据处理
        if($sdf['is_simple'] == true){
            $goods_id = $this->simple_goods_update($sdf,$msg);
        }else{
            $goods_id = $this->normal_goods_update(false,$sdf,$msg);
        }

        if (!$goods_id)
        {
            $db->rollback();
            $thisObj->send_user_error($msg, array('iid'=>$sdf['iid']));
        }

        $db->commit($transaction_status);

        /** 得到所有的sku_id **/
        $obj_product = $this->app->model('products');
        $tmp_products = $obj_product->getList('product_id,bn',array('goods_id'=>$goods_id));
        $str_sku_bns = "";
        $str_sku_ids = "";
        foreach ((array)$tmp_products as $arr_product)
        {
            $str_sku_ids .= $arr_product['product_id'] . ",";
            $str_bns .= $arr_product['bn'] . ",";
        }
        if ($str_sku_ids)
            $str_sku_ids = substr($str_sku_ids, 0, strlen($str_sku_ids)-1);
        if ($str_bns)
            $str_bns = substr($str_bns, 0, strlen($str_bns)-1);

        /** 获取商品修改时间 **/
        $obj_goods = $this->app->model('goods');
        $tmp_goods = $obj_goods->getList('last_modify',array('goods_id'=>$goods_id));

        return array('iid'=>$goods_id,'sku_id'=>$str_sku_ids, 'sku_bn'=>$str_bns, 'modified'=>date('Y-m-d H:i:s',$tmp_goods[0]['last_modify']));
    }

    /**
     * 验证新增商品的数据合理有效性
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkInsertData(&$sdf, &$msg=''){
        if (!$sdf['name']){
            $msg = app::get('b2c')->_('商品名称不能为空，必要参数！');
            return false;
        }
		
        if (!$sdf['bn']){
            $msg = app::get('b2c')->_('商品编码 bn 不能为空，必要参数！');
            return false;
        }
		
		/*统一使用 false,所以不用检查
        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('是否简单商品不能为空，必要参数！');
            return false;
        }
		*/
	
        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }

		/*
		 * 存在问题，禁用
        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }*/
        return true;
    }

    /**
     * 验证更新商品的数据合理有效性
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkUpdateData(&$sdf, &$msg=''){
		/* 使用 bn 代替 iid
        if (!$sdf['iid']){
            $msg = app::get('b2c')->_('商品ID不能为空，必要参数！');
            return false;
        }
		*/
        if (!$sdf['name']){
            $msg = app::get('b2c')->_('商品名称不能为空，必要参数！');
            return false;
        }
		
        if (!$sdf['bn']){
            $msg = app::get('b2c')->_('商品编码 bn 不能为空，必要参数！');
            return false;
        }
		
		/*统一使用 false,所以不用检查
        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('参数是否简单商品不能为空，必要参数！');
            return false;
        }
		*/
	
        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }
		/*
		 * 存在问题，禁用
        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }
		*/
        return true;
    }

    /**
     * 编辑更新简单商品
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function simple_goods_update(&$sdf, &$msg='')
    {
        //格式化传入参数
        $time = time();
        $tmp['bn'] = $sdf['bn'] ? $sdf['bn'] : '';
        $tmp['type_id'] = $sdf['type_id'] ? $sdf['type_id'] : '1';
        $tmp['cat_id'] = $sdf['cat_id'] ? $sdf['cat_id'] : '0';
        $tmp['brand_id'] = $sdf['brand_id'] ? $sdf['brand_id'] : '';
        $tmp['score'] = $sdf['score'] ? $sdf['score'] : '';
        $tmp['unit'] = $sdf['unit'] ? $sdf['unit'] : '';
        $tmp['brief'] = $sdf['brief'] ? $sdf['brief'] : '';
        $tmp['marketable'] = ($sdf['approve_status'] == 'onsale')? 'true' : 'false';
        $tmp['description'] = $sdf['description'] ? $sdf['description'] : '';
        $tmp['store'] = $sdf['num'] ? $sdf['num'] : '';
        $tmp['price'] = $sdf['price'] ? $sdf['price'] : '0.000';
        $tmp['cost'] = $sdf['cost'] ? $sdf['cost'] : '0.000';
        $tmp['mktprice'] = $sdf['mktprice'] ? $sdf['mktprice'] : '0.000';
        $tmp['weight'] = $sdf['weight'] ? $sdf['weight'] : '0.000';

        //组织商品基础数据
        $sdf_goods = array(
            'bn'=>$tmp['bn'],
            'name'=>$sdf['name'],
            'type'=>array(
                'type_id'=>$tmp['type_id'],
            ),
            'category'=>array(
                'cat_id'=>$tmp['cat_id'],
            ),
            'brand'=>array(
                'brand_id'=>$tmp['brand_id'],
            ),
            'uptime'=>$time,
            'last_modify'=>$time,
            'gain_score'=>$tmp['score'],
            'unit'=>$tmp['unit'],
            'brief'=>$tmp['brief'],
            'status'=>$tmp['marketable'],
            'thumbnail_pic'=>'',
            'description'=>$tmp['description'],
            //'store'=>$tmp['store'],
        );

        /** 简单商品生成一个货品信息 **/
            $sdf_goods['product'] = array(
                array(
                    'bn' => $tmp['bn'],
                    'price'=>array(
                            'price'=>array(
                                'price'=>$tmp['price'],
                            ),
                            'cost'=>array(
                                'price'=>$tmp['cost'],
                            ),
                            'mktprice'=>array(
                                'price'=>$tmp['mktprice'],
                            ),
                        ),
                    'name' => $sdf['name'],
                    'weight'=>$tmp['weight'],
                    'unit' => $tmp['unit'],
                    'store'=>$tmp['store'],
                    'freez' => '0',
                    'status'=>$tmp['marketable'],
                    'default'=>'1',
                    'goods_type' => 'normal',
                    'uptime' => $time,
                    'last_modify' => $time,
                ),
            );

        //判断是否是已有商品
        if ($sdf['iid']){
            $sdf_goods['goods_id'] = $sdf['iid'];
            $sdf_goods['product'][0]['goods_id'] = $sdf['iid'];
        }

        /** 图片处理 接收远程img地址进行处理**/
        if ($sdf['image_default_id'])
        {
            $mdl_img = app::get('image')->model('image');
            $image_name = substr($sdf['image_default_id'], strrpos($sdf['image_default_id'],'/')+1);
            $image_id = $mdl_img->store($sdf['image_default_id'],null,null,$image_name);
            $sdf_goods['image_default_id'] = $image_id;
        }

        /** 商品属性列表 根据商品类型获取属性props_id以及属性值props_value_id来设置商品属性**/
        if ($sdf['props'] && $tmp['type_id']>1)
        {
            $arr_props = explode(';',$sdf['props']);
            if (count($arr_props)>0)
            {
                $obj_goods_type_props = $this->app->model('goods_type_props');
                $obj_goods_type_props_value = $this->app->model('goods_type_props_value');
                $p_index = 1;
                foreach ((array)$arr_props as $key=>$value)
                {
                    $tmp_arr_props = explode(':',$value);
                    $tmp_props = $obj_goods_type_props->getList('props_id', array('type_id'=>$tmp['type_id'],'props_id'=>$tmp_arr_props['0']));
                    if (!$tmp_props)
                    {
                        $msg = app::get('b2c')->_('当前添加的商品类型下不存在该商品属性！');
                        return false;
                    }
                    $tmp_props_value = $obj_goods_type_props_value->getList('props_value_id', array('props_id'=>$tmp_arr_props['0'],'props_value_id'=>$tmp_arr_props['1']));
                    if (!$tmp_props_value)
                    {
                        $msg = app::get('b2c')->_('当前添加的商品属性下不存在该商品属性值！');
                        return false;
                    }

                    $sdf_goods['props']['p_'.$p_index]['value'] = $tmp_props_value[0]['props_value_id'];
                    $p_index++;
                }
            }
        }

        /** 用户自行输入的类目属性 **/
        if ($sdf['input_pids'] && $sdf['input_str'] && $tmp['type_id']>1)
        {
            $arr_input_pids = explode(';',$sdf['input_pids']);
            $arr_input_strs = explode(';',$sdf['input_str']);

            if(count($arr_input_pids)>0){
                $obj_goods_type_props = $this->app->model('goods_type_props');

                foreach ((array)$arr_input_pids as $key=>$pid){
                    $tmp_input_props = $obj_goods_type_props->getList('props_id', array('type_id'=>$tmp['type_id'],'props_id'=>$pid));
                    if (!$tmp_input_props)
                    {
                        $msg = app::get('b2c')->_('需要添加的商品类型下不存在该自行输入属性！');
                        return false;
                    }
                }

                if (count($arr_input_pids) == count($arr_input_strs) )
                {
                    $p_input_id = 21;
                    foreach ((array)$arr_input_strs as $key=>$input_value)
                    {
                        $sdf_goods['props']['p_'.$p_input_id]['value'] = $input_value;
                        $p_input_id++;
                    }
                }
            }
        }

        $obj_goods = $this->app->model('goods');
        $goods_id = $obj_goods->save($sdf_goods);
        if (!$goods_id)
        {
            $msg = app::get('b2c')->_('商品添加失败！');
            return false;
        }

        if(!$this->goods_related_items($sdf,$goods_id)){
            $msg = app::get('b2c')->_('商品关联商品信息添加失败！');
            return false;
        }

        if(!$this->goods_keywords($sdf,$goods_id)){
            $msg = app::get('b2c')->_('商品关键字添加失败！');
            return false;
        }

        return $sdf_goods['goods_id'];
    }

    /**
     * 编辑更新多货品商品
     * @param bool new,		true = 新增， false=修改
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function normal_goods_update($new, &$sdf, &$msg='')
    {
        //合计货品库存数量，判断商品库存数量是否等于货品库存之和
        $arr_sku_store = json_decode($sdf['sku_store'],1);
        $goods_store = 0;
        $obj_math = kernel::single('ectools_math');
        foreach ((array)$arr_sku_store as $sku_store)
        {
            //合计商品库存
            $goods_store = $obj_math->number_plus(array($goods_store,$sku_store));
        }

       if($goods_store != $sdf['num']){
            $msg = app::get('b2c')->_('商品库存数量与货品库存之和不等！');
            return false;
       }

        //格式化传入参数
        $time = time();
        $tmp['bn'] = $sdf['bn'] ? $sdf['bn'] : '';
        $tmp['type_id'] = $sdf['type_id'] ? $sdf['type_id'] : '1';
        $tmp['cat_id'] = $sdf['cat_id'] ? $sdf['cat_id'] : '0';
        $tmp['brand_id'] = $sdf['brand_id'] ? $sdf['brand_id'] : '';
        $tmp['score'] = $sdf['score'] ? $sdf['score'] : '';
        $tmp['unit'] = $sdf['unit'] ? $sdf['unit'] : '';
        $tmp['brief'] = $sdf['brief'] ? $sdf['brief'] : '';
        $tmp['marketable'] = ($sdf['approve_status'] == 'onsale')? 'true' : 'false';
        $tmp['description'] = $sdf['description'] ? $sdf['description'] : '';
        $tmp['store'] = $sdf['num'] ? $sdf['num'] : '';

        $obj_goods = $this->app->model('goods');
        if(!empty($tmp['bn'])){
            $tmp_goods_info  = $obj_goods->getList('goods_id', array('bn'=>$tmp['bn']));
        }

        if ($sdf['iid']){
            if($tmp_goods_info && $sdf['iid'] != $tmp_goods_info[0]['goods_id']){
                $msg = app::get('b2c')->_('您所填写的商品编号已被使用，请检查！');
                return false;
            }
        }else{
            if($tmp_goods_info ){
                $msg = app::get('b2c')->_('您所填写的商品编号已被使用，请检查！');
                return false;
            }
        }


        //组织商品基础数据
		if($new){
			$sdf_goods = array(
				'bn'=>$tmp['bn'],
				'barcode'=>$tmp['bn'],		//条形码和bn一样
				'name'=>$sdf['name'],
				'type'=>array(
					'type_id'=>$tmp['type_id'],
				),
				'category'=>array(
					'cat_id'=>$tmp['cat_id'],
				),
				'brand'=>array(
					'brand_id'=>$tmp['brand_id'],
				),
				'uptime'=>$time,
				'last_modify'=>$time,
				'gain_score'=>$tmp['score'],
				'unit'=>$tmp['unit'],
				'brief'=>$tmp['brief'],					
				'status'=>$tmp['marketable'],
				'thumbnail_pic'=>'',
				'description'=>$tmp['description'],
				//'store'=>$tmp['store'],
			);
		}else{
			//ERP对接入来，部分数据不用修改
			 $sdf_goods = array(
				'bn'=>$tmp['bn'],
				'name'=>$sdf['name'],
				'uptime'=>$time,
				'last_modify'=>$time,
				'unit'=>$tmp['unit'],
			);		
		}

        if($sdf['iid']){
            $sdf_goods['goods_id'] = $sdf['iid'];
        }

        $obj_products = $this->app->model('products');

        //组织商品货品信息数据
        if ($sdf['sku_bns']){
            $arr_sku_bns = json_decode($sdf['sku_bns'],1);
            $arr_sku_prices = json_decode($sdf['sku_prices'],1);
            $arr_suk_costs = json_decode($sdf['sku_costs'],1);
            $arr_sku_mktprices = json_decode($sdf['sku_mktprices'],1);
            $arr_sku_weights = json_decode($sdf['sku_weights'],1);

            foreach ((array)$arr_sku_bns as $key=>$bns)
            {

                //根据货号查询是否已经该货号的货品
                $tmp_product = $obj_products->getList('product_id,goods_id', array('bn'=>$bns));

                if ($sdf['iid']){
                    if($tmp_product){
                        if($tmp_product[0]['goods_id'] != $sdf['iid']){
                            $msg = app::get('b2c')->_('您所填写的货号已被使用，请检查！');
                            return false;
                        }
                    }
                }else{
                    if($tmp_product){
                        $msg = app::get('b2c')->_('您所填写的货号已被使用，请检查！');
                        return false;
                    }
                }

                $sdf_goods['product'][$key] = array(
                    'bn'=>$bns,
					'barcode'=>$bns,		//条形码和bn一样
                    'price'=>array(
                        'price'=>array(
                            'price'=>$arr_sku_prices[$key] ? $arr_sku_prices[$key] : '',
                        ),
                        'cost'=>array(
                            'price'=>$arr_suk_costs[$key] ? $arr_suk_costs[$key] : '',
                        ),
                        'mktprice'=>array(
                            'price'=>$arr_sku_mktprices[$key] ? $arr_sku_mktprices[$key] : '',
                        ),
                    ),
                    'name' => $sdf['name'],
                    'weight'=>$arr_sku_weights[$key] ? $arr_sku_weights[$key]  : '',
                    'unit' => $sdf['unit'] ? $sdf['unit'] : '',
                    'store' => $arr_sku_store[$key],
                    'freez' => '0',
                    'status'=>$tmp['marketable'],
                    'goods_type' => 'normal',
                    'uptime' => $time,
                    'last_modify' => $time,
                );

                if($sdf_goods['product'][$key]['product_id']){
                    $sdf_goods['product'][$sdf_goods['product'][$key]['product_id']] = $sdf_goods['product'][$key];
                    unset($sdf_goods['product'][$key]);
                }
            }
        }

        $sdf_goods['product'][0]['default'] = '1';

        /** 商品规格处理 **/
        //if ($sdf['sku_properties'] && $tmp['type_id']>1)
			
		//因为全部默认类型为1，所以上面判断修改为下面的
		if ($sdf['sku_properties'])
        {
            //拆分单个货品的规格组合字符串
            $arr_spec_properties = json_decode($sdf['sku_properties'],1);
            if (count($arr_spec_properties)>0)
            {
                $arr_spec_value = array();
                $arr_spec_value_id = array();
                $arr_private_value_id = array();
                //$arr_spec_image = array();
                //$arr_spec_goods_images = array();

                $obj_specification = $this->app->model('specification');
                $obj_goods_type_spec = $this->app->model('goods_type_spec');
                $obj_spec_value = $this->app->model('spec_values');
                foreach ((array)$arr_spec_properties as $key=>$spec_value)
                {
                    //拆分单个货品下的多个规格spec_id与spec_value_id的组合
                    $each_spec = explode('_',$spec_value);
					
                    $tmp_spec_id = array();
                    if (count($each_spec)>0){
                        foreach((array)$each_spec as $k2=>$spec_str){
                            //拆分spec_id、spec_value_id
                            $tmp_arr_spec_value = explode(':',$spec_str);

							/*
							不判断该规格是否所属类型
                            $tmp_spec = $obj_goods_type_spec->getList('spec_id', array('type_id'=>$tmp['type_id'],'spec_id'=>$tmp_arr_spec_value['0']));
                            if (!$tmp_spec)
                            {
                                $msg = app::get('b2c')->_('当前添加的商品类型下不存在该商品规格！');
                                return false;
                            }
							*/
                            $tmp_spec_value = $obj_spec_value->getList('spec_value,spec_image', array('spec_id'=>$tmp_arr_spec_value['0'],'spec_value_id'=>$tmp_arr_spec_value['1']));
                            if (!$tmp_spec_value)
                            {
                                $msg = app::get('b2c')->_('当前添加的商品规格下不存在该商品规格值！');
                                return false;
                            }
							
                            if(count($tmp_spec_id)>0 && in_array($tmp_arr_spec_value['0'],$tmp_spec_id)){
                                $msg = app::get('b2c')->_('单个货品不能添加相同的规格！');
                                return false;
                            }

                            //临时保存当前货品的上一个规格spec_id
                            $tmp_spec_id = array_merge((array)$tmp_arr_spec_value['0'],$tmp_spec_id);
                            //根据spec_id获取具体规格名称
                            //$tmp_specification = $obj_specification->getList('spec_name',array('spec_id'=>$tmp_arr_spec_value['0']));

                            $arr_spec_value[$tmp_arr_spec_value['0']] = $tmp_spec_value[0]['spec_value'];
                            $arr_spec_value_id[$tmp_arr_spec_value['0']] = $tmp_arr_spec_value['1'];
                            $arr_private_value_id[$tmp_arr_spec_value['0']] = time().$tmp_arr_spec_value['1'];
                            //增加商品规格图标数据
                            //$arr_spec_image[$key] = $tmp_spec_value[0]['spec_image'];
                            //$arr_spec_goods_images[$key] = '';
                            $tmp_goods_spec_desc[$tmp_arr_spec_value['0']][$arr_private_value_id[$tmp_arr_spec_value['0']]]['spec_private_value_id'] = $arr_private_value_id[$tmp_arr_spec_value['0']];
                            $tmp_goods_spec_desc[$tmp_arr_spec_value['0']][$arr_private_value_id[$tmp_arr_spec_value['0']]]['spec_value_id'] = $arr_spec_value_id[$tmp_arr_spec_value['0']];
                            $tmp_goods_spec_desc[$tmp_arr_spec_value['0']][$arr_private_value_id[$tmp_arr_spec_value['0']]]['spec_value'] = $arr_spec_value[$tmp_arr_spec_value['0']];
                            $tmp_goods_spec_desc[$tmp_arr_spec_value['0']][$arr_private_value_id[$tmp_arr_spec_value['0']]]['spec_image'] = $tmp_spec_value[0]['spec_image'];
                            $tmp_goods_spec_desc[$tmp_arr_spec_value['0']][$arr_private_value_id[$tmp_arr_spec_value['0']]]['spec_goods_images'] = '';
                        }

                        $sdf_goods['product'][$key]['spec_desc']['spec_value'] = $arr_spec_value;
                        $sdf_goods['product'][$key]['spec_desc']['spec_private_value_id'] = $arr_private_value_id;
                        $sdf_goods['product'][$key]['spec_desc']['spec_value_id'] = $arr_spec_value_id;

                    }

                }
                $sdf_goods['spec_desc']  = $tmp_goods_spec_desc;
            }
        }
		

		
        /** 图片处理 接收远程img地址进行处理**/
        if ($sdf['image_default_id'])
        {
            $mdl_img = app::get('image')->model('image');
            $image_name = substr($sdf['image_default_id'], strrpos($sdf['image_default_id'],'/')+1);
            $image_id = $mdl_img->store($sdf['image_default_id'],null,null,$image_name);
            $sdf_goods['image_default_id'] = $image_id;
        }

        /** 商品属性列表 根据商品类型获取属性props_id以及属性值props_value_id来设置商品属性**/
        if ($sdf['props'] && $tmp['type_id']>1)
        {
            $arr_props = explode(';',$sdf['props']);
            if (count($arr_props)>0)
            {
                $obj_goods_type_props = $this->app->model('goods_type_props');
                $obj_goods_type_props_value = $this->app->model('goods_type_props_value');
                $p_index = 1;
                foreach ((array)$arr_props as $key=>$value)
                {
                    //拆分props_id与props_value_id
                    $tmp_arr_props = explode(':',$value);
                    $tmp_props = $obj_goods_type_props->getList('props_id', array('type_id'=>$tmp['type_id'],'props_id'=>$tmp_arr_props['0']));
                    if (!$tmp_props)
                    {
                        $msg = app::get('b2c')->_('当前添加的商品类型下不存在该商品属性！');
                        return false;
                    }
                    $tmp_props_value = $obj_goods_type_props_value->getList('props_value_id', array('props_id'=>$tmp_arr_props['0'],'props_value_id'=>$tmp_arr_props['1']));
                    if (!$tmp_props_value)
                    {
                        $msg = app::get('b2c')->_('当前添加的商品属性下不存在该商品属性值！');
                        return false;
                    }

                    $sdf_goods['props']['p_'.$p_index]['value'] = $tmp_props_value[0]['props_value_id'];
                    $p_index++;
                }
            }
        }

        /** 用户自行输入的类目属性 **/
        if ($sdf['input_pids'] && $sdf['input_str'] && $tmp['type_id']>1)
        {
            $arr_input_pids = explode(';',$sdf['input_pids']);
            $arr_input_strs = explode(';',$sdf['input_str']);

            if(count($arr_input_pids)>0){
                $obj_goods_type_props = $this->app->model('goods_type_props');

                foreach ((array)$arr_input_pids as $key=>$pid){
                    $tmp_input_props = $obj_goods_type_props->getList('props_id', array('type_id'=>$tmp['type_id'],'props_id'=>$pid));
                    if (!$tmp_input_props)
                    {
                        $msg = app::get('b2c')->_('需要添加的商品类型下不存在该自行输入属性！');
                        return false;
                    }
                }

                if (count($arr_input_pids) == count($arr_input_strs) )
                {
                    $p_input_id = 21;
                    foreach ((array)$arr_input_strs as $key=>$input_value)
                    {
                        $sdf_goods['props']['p_'.$p_input_id]['value'] = $input_value;
                        $p_input_id++;
                    }
                }
            }
        }
/*
		print_r($sdf_goods);
		print_r('==================================');
		exit();
*/
        $goods_id = $obj_goods->save($sdf_goods);

        if (!$goods_id)
        {
            $msg = app::get('b2c')->_('商品添加失败！');
            return false;
        }

        if(!$this->goods_related_items($sdf,$goods_id)){
            $msg = app::get('b2c')->_('商品关联商品信息添加失败！');
            return false;
        }

        if(!$this->goods_keywords($sdf,$goods_id)){
            $msg = app::get('b2c')->_('商品关键字添加失败！');
            return false;
        }

        return $sdf_goods['goods_id'];
    }

    /**
     * 商品关联商品信息处理
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    private function goods_related_items(&$sdf, $goods_id){
        // 生成推荐商品
        if ($sdf['related_items'])
        {
            $arr_related_items = json_decode($sdf['related_items'], 1);
            if ($arr_related_items)
            {
                $obj_goods_rate = $this->app->model('goods_rate');
                foreach ((array)$arr_related_items as $related_item)
                {
                    $filter = array(
                        'filter_sql'=>'(`goods_1`="'.$goods_id.'" AND `goods_2`="'.$related_item.'") OR (`goods_1`="'.$related_item.'" AND `goods_2`="'.$goods_id.'")',
                    );
                    $tmp = $obj_goods_rate->getList('*',$filter);
                    if (count($tmp) == 2)
                    {
                        // 当前商品与关联商品已经存在相互绑定，不做任何处理.
                    }
                    elseif (count($tmp) ==1)
                    {
                        //当查询结果只有一条时，判断是否是当前商品发起的商品关联，有就跳出不做处理，没有就设置双向绑定
                        if ($tmp[0]['goods_1'] == $goods_id)
                            continue;

                        $filter = array(
                            'goods_1'=>$tmp[0]['goods_1'],
                            'goods_2'=>$tmp[0]['goods_2'],
                        );
                        $is_save = $obj_goods_rate->update(array('manual'=>'both'),$filter);
                    }
                    else
                    {
                        //如果之前没有关联数据，则创建单向关联关系
                        $data = array(
                            'goods_1'=>$goods_id,
                            'goods_2'=>$related_item,
                            'manual'=>'left',
                        );
                        $is_save = $obj_goods_rate->insert($data);
                    }
                }
            }
            return $is_save;
        }
        return true;
    }

        /**
     * 商品关键字信息处理
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    private function goods_keywords(&$sdf, $goods_id){
        /** 商品关键字 **/
        if ($sdf['keywords'])
        {
            $arr_keywords = json_decode($sdf['keywords'], 1);
            if ($arr_keywords)
            {
                $obj_goods_keywords = $this->app->model('goods_keywords');
                foreach ((array)$arr_keywords as $str_keywords)
                {
                    $data = array(
                        'goods_id'=>$goods_id,
                        'keyword'=>$str_keywords,
                        'res_type'=>'goods',
                    );
                    $is_save = $obj_goods_keywords->insert($data);
                }
            }
            return $is_save;
        }
        return true;
    }

    /**
     * 删除一个商品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function del_goods(&$sdf, &$thisObj)
    {
		//--------------------------------------------------
		//根据 bn 查询 iid
		$sdf['bn'] = '' . $sdf['bn'];
		
		$goods_id = $this->get_goods_id($sdf['bn']);
		if($goods_id<1){
			$msg = app::get('b2c')->_('没有查找到商品编号的资料！'.$sdf['bn']);
			$thisObj->send_user_error($msg, null);
		}

		//-----------------------------------------------------
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        /**
         * 删除商品对应的货品
         */
        $_goods = $this->app->model('goods');
        $is_delete = $_goods->delete(array('goods_id'=>$goods_id));

        if (!$is_delete)
        {
            $db->rollback();
            $thisObj->send_user_error(app::get('b2c')->_('删除商品的货品失败！'),null);
        }

        $db->commit($transaction_status);

        return array(
			'goods_id'	=> ''.$goods_id, 
			'time'		=> date('Y-m-d H:i:s',time())
		);
    }
	

    /**
     * 检查商品编号是否存在
     * @param mixed sdf结构
     * @param object handle object
     * @return 如果返回'true',如果不存在返回'false'
     */
    public function get_goods_check_bn(&$sdf, &$thisObj)
    {
		$goods_id = $this->get_goods_id('' . $sdf['bn']);
		return ($goods_id>0)?'true':'false';
    }

	
    /**
     * 获取商品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_goods_detail(&$sdf, &$thisObj)
    {
        if (!isset($sdf['bn']) || strlen($sdf['bn'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('货品bn为空！'), array('item'=>''));
        }
		//--------------------------------------------------
		//根据 bn 查询 iid
		$sdf['bn'] = '' . $sdf['bn'];
		
		$goods_id = $this->get_goods_id($sdf['bn']);
		if($goods_id<1){
			$msg = app::get('b2c')->_('没有查找到商品编号的资料！'.$sdf['bn']);
			$thisObj->send_user_error($msg, array('item'=>''));
		}
		//--------------------------------------------------
        $_goods = $this->app->model('goods');

        $rs = $_goods->getRow('*',array('goods_id'=>$sdf['goods_id']));
        if(count($rs) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该商品不存在'.$sdf['goods_id']), null);
		}
		

		return $this->get_item_detail($rs);
    }
	
    /**
     * 获取商品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_goods_detail_id(&$sdf, &$thisObj)
    {
		$goods_id = intval(''.$sdf['goods_id']);
        if ($goods_id<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('goods_id参数为空'), null);
        }

		//--------------------------------------------------
        $_goods = $this->app->model('goods');

        $rs = $_goods->getRow('*',array('goods_id'=>$goods_id));
        if(count($rs) == 0)
		{
			$thisObj->send_user_error(app::get('b2c')->_('该商品不存在'.$goods_id), null);
		}
		
		return $this->get_item_detail($rs);
    }

    /**
     * 生成商品明细的数组
     * @param mixed 每行商品的数组-数据
     * @param object goods controller
     * @return mixed
     */
    private function get_item_detail($arr_row)
    {
        if (!$arr_row)
            return array();

        $cnt_props = 20;
        $cn_input_props = 50;

        $detal_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_row['goods_id']));
        /** props 目前是1-20 **/
        $props = "";
        for ($i=1;$i<=$cnt_props;$i++)
        {
            if ($arr_row['p_'.$i])
                $props .= $i.":".$arr_row['p_'.$i].";";
        }
        if ($props)
            $props = substr($props, 0, strlen($props)-1);
        /** end **/

        /** input props 21-50 **/
        $input_pids = "";
        $input_str = "";
        for ($i=$cnt_props+1;$i<=$cn_input_props;$i++)
        {
            if ($arr_row['p_'.$i])
            {
                $input_pids .= $i.",";
                $input_str .= $arr_row['p_'.$i].";";
            }
        }
        if ($input_pids)
            $input_pids = substr($input_pids, 0, strlen($input_pids)-1);
        if ($input_str)
            $input_str = substr($input_str, 0, strlen($input_str)-1);
        /** end **/
        $db = kernel::database();
		
        $arr_skus = $db->select("SELECT * FROM `sdb_b2c_products` WHERE `goods_id`=".$arr_row['goods_id']);
		
        $arr_sdf_skus = array();
        $str_skus = "";
        if ($arr_skus)
        {
            foreach ($arr_skus as $arr)
            {
                //$str_skus_properties = '';
				$arr_specs = array();
                $arr_spec_desc = unserialize($arr['spec_desc']);
                if($arr_spec_desc['spec_value_id'])
                {
					foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key => $arr_value){
						$arr_specs[] = array(
							'spec_id' 		=> ''.$spec_id_key,
							'spec_value_id' => ''.$arr_value,
							'spec_value' 	=> $arr_spec_desc['spec_value'] [$spec_id_key],
						);
					}
					
					/*
                    foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key =>$arr_value)
                        $str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
					*/
                }
				/*
                if ($str_skus_properties)
                    $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);
				*/
                $arr_sdf_skus[] = array(
                    'product_id'=>$arr['product_id'],
                    'goods_id'=>$arr['goods_id'],
                    'bn'=>$arr['bn'],
					'specs'=>$arr_specs,
                    //'properties'=>$str_skus_properties,
                    'store'=>$arr['store'],
                    'weight'=>$arr['weight'],
                    'price'=>$arr['price'],
                    'mktprice'=>$arr['mktprice'],
                    'cost'=>$arr['cost']
                );
            }
            $str_skus = json_encode($arr_sdf_skus);
        }
        $default_img_url = kernel::single('base_storager')->image_path($arr_row['image_default_id']);


        $goods_images  = array();
        //$arr_goods_images = $db->select("SELECT b.`image_id`, b.`l_url`, b.`m_url`, b.`s_url`  FROM `sdb_image_image_attach` a LEFT JOIN `sdb_image_image` b ON a.image_id=b.image_id WHERE `target_type`='goods' and `target_id`=".$arr_row['goods_id']);
        $arr_goods_images = $db->select("SELECT `image_id` FROM `sdb_image_image_attach` WHERE `target_type`='goods' and `target_id`=".$arr_row['goods_id']);
        if($arr_goods_images)
        {
            foreach($arr_goods_images as $single_row)
            {
                $goods_images[] = array(
                    'image_id'=>$single_row['image_id'],
                    'big_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'l'),
                    'thisuasm_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 'm'),
                    'small_url'=>kernel::single('base_storager')->image_path($single_row['image_id'], 's'),
                    'is_default'=>'false',
                    );
            }
        }
        //$goods_images = json_encode($goods_images);

        return array(
            'goods_id'=>$arr_row['goods_id'],
            'name'=>$arr_row['name'],
            'goods_bn'=>$arr_row['bn'],
            'cat_id'=>$arr_row['cat_id'],
            'type_id'=>$arr_row['type_id'],
            'brand_id'=>$arr_row['brand_id'],
            //'props'=>$props,
            //'input_pids'=>$input_pids,
            //'input_str'=>$input_str,
            //'description'=>$arr_row['intro'],			//商品详细介绍不要
            //'default_img_url'=>$default_img_url,
            'store'=>$arr_row['store'],
            'weight'=>$arr_row['weight'] ? $arr_row['weight'] : '',
            'score'=>$arr_row['score'] ? $arr_row['score'] : '',
            'price'=>$arr_row['price'],
            'mktprice'=>$arr_row['mktprice'],
	        'cost'=>$arr_row['cost'],
            'unit'=>$arr_row['unit'],
            'marketable'=>$arr_row['marketable'],
            //'item_imgs'=>$goods_images,
            'last_modify'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'uptime'=>date('Y-m-d H:i:s',$arr_row['uptime']),			//上架时间
            'downtime'=>date('Y-m-d H:i:s',$arr_row['downtime']),		//下架时间
            //'skus'=>$str_skus,
			'skus'=>$arr_sdf_skus
        );
    }


    /**
     * 获取商品类型
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_types(&$sdf, &$thisObj)
    {
        $sdf['page_no'] = $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] = $sdf['page_size'] ? $sdf['page_size'] : '20';

        $page_no = intval($sdf['page_no']) - 1;
        $page_size = intval($sdf['page_size']);

        $db = kernel::database();
        $arr_goods_types = $db->select("SELECT * FROM `sdb_b2c_goods_type` LIMIT ".$page_no.",".$page_size."");
        if (!$arr_goods_types)
            return array();

        $arr_sdf_goods_types = array();
        foreach ($arr_goods_types as $index=>$arr)
        {
            $arr_sdf_goods_types[$index] = array(
                'type_id'=>$arr['type_id'],
                'name'=>$arr['name'],
                'brands'=>$arr_brand_ids,
                'setting'=>unserialize($arr['setting']),
            );

            /** brand id array **/
            $arr_brand_ids = $db->select("SELECT `brand_id` FROM `sdb_b2c_type_brand` WHERE `type_id`=".$arr['type_id']);
            $arr_sdf_goods_types[$index]['brands'] = $arr_brand_ids;
            /** properties array **/
            $arr_type_props = $db->select("SELECT `props_id`,`type`,`name` FROM `sdb_b2c_goods_type_props` WHERE `type_id`=".$arr['type_id']);
            $arr_prop_props_ids = array();
            $arr_prop_names = array();
            $arr_prop_types = array();
            $arr_prop_options = array();
            $arr_prop_value_ids = array();
            foreach ($arr_type_props as $arr_prop)
            {
                $arr_prop_props_ids[] = $arr_prop['props_id'];
                $arr_prop_names[] = $arr_prop['name'];
                $arr_prop_types[] = $arr_prop['type'];
                $arr_props_value = $db->select("SELECT `props_value_id`,`name` FROM `sdb_b2c_goods_type_props_value` WHERE `props_id`=".$arr_prop['props_id']);
                foreach($arr_props_value as $arr_prop_value){
                    $arr_prop_value_ids[] = $arr_prop_value['props_value_id'];
                    $arr_prop_options[] = $arr_prop_value['name'];
                }

            }
            $arr_sdf_goods_types[$index]['props'] = array(
                'props_id'=>$arr_prop_props_ids,
                'name'=>$arr_prop_names,
                'type'=>$arr_prop_types,
                'props_value_id'=>$arr_prop_value_ids,
                'options'=>$arr_prop_options,
            );
            /** specialist array **/
            $arr_specifications = $db->select("SELECT sbsf.`spec_id`,sbsf.`spec_type` FROM `sdb_b2c_specification` as sbsf
                                                                left join sdb_b2c_goods_type_spec as sbgts on sbgts.spec_id = sbsf.spec_id
                                                                WHERE sbgts.`type_id`=".$arr['type_id']."");
            $arr_spec_ids = array();
            $arr_spec_types = array();
            $arr_spec_value_ids = array();
            $arr_spec_values = array();
            foreach ((array)$arr_specifications as $arr_specs)
            {
                $arr_spec_ids[] = $arr_specs['spec_id'];
                $arr_spec_types[] = $arr_specs['spec_type'];
                $arr_specs_values = $db->select("SELECT `spec_value_id`,`spec_value` FROM `sdb_b2c_spec_values` WHERE `spec_id`=".$arr_specs['spec_id']);
                foreach((array)$arr_specs_values as $arr_spec_value){
                    $arr_spec_value_ids[] = $arr_spec_value['spec_value_id'];
                    $arr_spec_values[] = $arr_spec_value['spec_value'];
                }
            }
            $arr_sdf_goods_types[$index]['spec'] = array(
                'id'=>$arr_spec_ids,
                'type'=>$arr_spec_types,
                'value_id'=>$arr_spec_value_ids,
                'value_name'=>$arr_spec_values,
            );
            /** params array **/
            $arr_goods_type_params = unserialize($arr['params']);
            $arr_goods_type_param_groups = array();
            $arr_goods_type_param_names = array();
            foreach ((array)$arr_goods_type_params as $key=>$arr_goods_type_param)
            {
                $arr_goods_type_param_groups[] = $key;
                $arr_keys = array_keys((array)$arr_goods_type_param);
                $arr_goods_type_param_names[] = $arr_keys[0];
            }
            $arr_sdf_goods_types[$index]['params'] = array(
                'group'=>$arr_goods_type_param_groups,
                'name'=>$arr_goods_type_param_names,
            );
            /** minfo 商品必填项 **/
            $arr_minfos_labels = array();
            $arr_minfos_types = array();
            $arr_minfos_options = array();
            $arr_goods_type_minfos = unserialize($arr['minfo']);
            foreach ((array)$arr_goods_type_minfos as $key=>$arr_goods_type_minfo)
            {
                $arr_minfos_labels[] = $arr_goods_type_minfo['label'];
                $arr_minfos_types[] = $arr_goods_type_minfo['type'];
                if ($arr_goods_type_minfo['type'] == 'select')
                {
                    $arr_minfos_options[] = $arr_goods_type_minfo['options'];
                }
            }
            $arr_sdf_goods_types[$index]['minfo'] = array(
                    'label'=>$arr_minfos_labels,
                    'type'=>$arr_minfos_types,
                    'options'=>$arr_minfos_options,
            );
        }

        return array('datas'=>$arr_sdf_goods_types,'totalResults'=>count($arr_sdf_goods_types));
    }

    /**
     * 获取商品分类 - 直连方法
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_cat(&$sdf, &$thisObj)
    {
        $sdf['page_no'] = $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] = $sdf['page_size'] ? $sdf['page_size'] : '20';

        $page_no = intval($sdf['page_no']) - 1;
        $page_size = intval($sdf['page_size']);

        $db = kernel::database();
        $arr_goods_cats = $db->select("SELECT * FROM `sdb_b2c_goods_cat` LIMIT ".$page_no.",".$page_size."");
        if (!$arr_goods_cats)
            return array();

        $sdf_arr_goods_cats = array();
        foreach ($arr_goods_cats as $index=>$arr_cat)
        {
            $arr_goods_type =  $db->selectrow("SELECT `name` FROM `sdb_b2c_goods_type` WHERE `type_id`=".$arr_cat['type_id']);
            $sdf_arr_goods_cats[$index] = array(
                'cat_name'=>$arr_cat['cat_name'],
                'cat_id'=>$arr_cat['cat_id'],
                'pid'=>$arr_cat['parent_id'],
                'type'=>$arr_cat['type_id'],
                'type_name'=>$arr_goods_type['name'],
                'p_order'=>$arr_cat['p_order'],
                'cat_path'=>$arr_cat['cat_path'],
                'is_leaf'=>$arr_cat['is_leaf'],
            );
        }

        return array('datas'=>$sdf_arr_goods_cats,'totalResults'=>count($sdf_arr_goods_cats));
    }

    /**
     * 获取品牌 - 直连方法
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_brands(&$sdf, &$thisObj)
    {
        $sdf['page_no'] = $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] = $sdf['page_size'] ? $sdf['page_size'] : '20';

        $page_no = intval($sdf['page_no']) - 1;
        $page_size = intval($sdf['page_size']);

        $db = kernel::database();
        $arr_goods_brands = $db->select("SELECT `brand_id`,`brand_name`,`brand_url` FROM `sdb_b2c_brand` LIMIT ".$page_no.",".$page_size."");
        if (!$arr_goods_brands)
            return array();

        $sdf_arr_goods_brands = array();
        foreach ($arr_goods_brands as $index=>$arr_brand)
        {
            $sdf_arr_goods_brands[$index] = array(
                'brand_id'=>$arr_brand['brand_id'],
                'brand_name'=>$arr_brand['brand_name'],
                'brand_url'=>$arr_brand['brand_url'],
            );
        }

        return array('datas'=>$sdf_arr_goods_brands,'totalResults'=>count($sdf_arr_goods_brands));
    }

    /**
     * 获取规格 - 直连方法
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_specs(&$sdf, &$thisObj)
    {
        $sdf['page_no'] = $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] = $sdf['page_size'] ? $sdf['page_size'] : '20';

        $page_no = intval($sdf['page_no']) - 1;
        $page_size = intval($sdf['page_size']);

        $db = kernel::database();
        $arr_goods_specifications = $db->select("SELECT * FROM `sdb_b2c_specification` LIMIT ".$page_no.",".$page_size."");
        if (!$arr_goods_specifications)
            return array();

        $sdf_arr_goods_specifications = array();
        foreach ($arr_goods_specifications as $index=>$arr_spec)
        {
            $arr_goods_spec_values = $db->select("SELECT * FROM `sdb_b2c_spec_values` WHERE `spec_id`=".$arr_spec['spec_id']);
            $arr_value_ids = array();
            $arr_values = array();
            $arr_value_imgs = array();
            foreach ($arr_goods_spec_values as $arr)
            {
                $arr_value_ids[] = $arr['spec_value_id'];
                $arr_values[] = $arr['spec_value'];
                $arr_value_imgs[] = $arr['spec_image'];
            }
            $sdf_arr_goods_specifications[$index] = array(
                'spec_id'=>$arr_spec['spec_id'],
                'spec_name'=>$arr_spec['spec_name'],
                'spec_show_type'=>$arr_spec['spec_show_type'],
                'spec_type'=>$arr_spec['spec_type'],
                'val'=>$arr_value_ids,
                'spec_value'=>$arr_values,
                'spec_image'=>$arr_value_imgs,
            );
        }

        return array('datas'=>$sdf_arr_goods_specifications,'totalResults'=>count($sdf_arr_goods_specifications));
    }
	

	/**
	 * 根据 bn 查找 iid (新增)
	 * @param string bn
	 * @return int 0 =未找到,>0找到iid
	 */
	private function get_goods_id($bn)
	{
		if (strlen($bn)==0) {
			return 0;
		}
		
		
		$obj_goods = $this->app->model('goods');
		$tmp_goods_info  = $obj_goods->getList('goods_id', array('bn'=>$bn));

		if(!$tmp_goods_info){
			return 0;
		}
		
		$iid = $tmp_goods_info[0]['goods_id'];
		return intval($iid);
	}
}
