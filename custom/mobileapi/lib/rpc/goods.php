<?php

/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_goods {
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = app::get("b2c");
    }
    
    /**
     * 相关商品
     * @return array goodslink
     */
    function goodslink() {
    
    	$gid = $_REQUEST['iid'];
    
    	$objGoods = kernel::single("b2c_mdl_goods");
    	$objProduct =  kernel::single("b2c_mdl_products");
    
    	$aLinkId['goods_id'] = array();
    	foreach($objGoods->getLinkList($gid) as $rows){
    		if($rows['goods_1']==$gid){
    			$aLinkId['goods_id'][] = $rows['goods_2'];
    		}else {
    			$aLinkId['goods_id'][] = $rows['goods_1'];
    		}
    	}
    	if(count($aLinkId['goods_id'])>0){
    		$aLinkId['marketable'] = 'true';
    		$goodslink = $objGoods->getList('name,price,goods_id,image_default_id,marketable, view_count',$aLinkId,0,500);
    		foreach ($goodslink as $k => $v){
    			$goodslink[$k]['image_default'] = kernel::single('base_storager')->image_path($v['image_default_id'], 'm');
    		}
    	}
    
    	return $goodslink;
    }
    
    /**
     * 商品评论
     * @return array goods Comment
     */
    function comment() {
    
    	$page = isset($_REQUEST['page_no']) && $_REQUEST['page_no']>0?$_REQUEST['page_no']:1;
    	$limit = isset($_REQUEST['page_size']) && $_REQUEST['page_size']>0?$_REQUEST['page_size']:10;
    	$gid = $_REQUEST['iid'];
    
    	$objdisask = kernel::single('b2c_message_disask');
    	$aComment = $objdisask->good_all_disask($gid,'discuss',$page,null,$limit);
    
    	$objPoint = kernel::single('b2c_mdl_comment_goods_point');
    	$aComment['goods_point'] = $objPoint->get_single_point($gid);
    	$aComment['total_point_nums'] = $objPoint->get_point_nums($gid);
    	$aComment['_all_point'] = $objPoint->get_goods_point($gid);
    
    	return $aComment;
    }
    

    /**
     * 取到所有货品的bn和store
     * @param null
     * @return string json
     */
    public function getAllProductsStore()
    {
        $obj_products = $this->app->model('products');
        $arr_products = $obj_products->getList('bn,store');

        return $arr_products;
    }

    /**
     * 添加一个商品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function add(&$sdf, &$thisObj)
    {
        //请求数据验证合法有效性
        if(!$this->_checkInsertData($sdf,$msg)){
            $thisObj->send_user_error($msg, array('iid'=>''));
        }

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        //判断简单商品还是多货品商品数据处理
        if($sdf['is_simple'] == true){
            $goods_id = $this->simple_goods_update($sdf,$msg);
        }else{
            $goods_id = $this->normal_goods_update($sdf,$msg);
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
    public function update(&$sdf, &$thisObj)
    {
        //请求数据验证合法有效性
        if(!$this->_checkUpdateData($sdf,$msg)){
            $thisObj->send_user_error($msg, array('iid'=>''));
        }

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        //判断简单商品还是多货品商品数据处理
        if($sdf['is_simple'] == true){
            $goods_id = $this->simple_goods_update($sdf,$msg);
        }else{
            $goods_id = $this->normal_goods_update($sdf,$msg);
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

        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('是否简单商品不能为空，必要参数！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }
        return true;
    }

    /**
     * 验证更新商品的数据合理有效性
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkUpdateData(&$sdf, &$msg=''){
        if (!$sdf['iid']){
            $msg = app::get('b2c')->_('商品ID不能为空，必要参数！');
            return false;
        }

        if (!$sdf['name']){
            $msg = app::get('b2c')->_('商品名称不能为空，必要参数！');
            return false;
        }

        if(!isset($sdf['is_simple'])){
            $msg = app::get('b2c')->_('参数是否简单商品不能为空，必要参数！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['brief'] && strlen($sdf['brief'])>210){
            $msg = app::get('b2c')->_('简短的商品介绍,请不要超过70个字！');
            return false;
        }

        if(isset($sdf['brief']) && $sdf['num']>=0){
            $msg = app::get('b2c')->_('商品库存数量必须是大于等于零！');
            return false;
        }
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
     * @param array sdf数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function normal_goods_update(&$sdf, &$msg='')
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
        if ($sdf['sku_properties'] && $tmp['type_id']>1)
        {
            //拆分单个货品的规格组合字符串
            $arr_spec_properties = explode(';',$sdf['sku_properties']);
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
                            $tmp_spec = $obj_goods_type_spec->getList('spec_id', array('type_id'=>$tmp['type_id'],'spec_id'=>$tmp_arr_spec_value['0']));
                            if (!$tmp_spec)
                            {
                                $msg = app::get('b2c')->_('当前添加的商品类型下不存在该商品规格！');
                                return false;
                            }

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
    public function delete(&$sdf, &$thisObj)
    {
        if (!$sdf['iid'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要删除的商品不存在！'), array('iid'=>''));
        }

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        /**
         * 删除商品对应的货品
         */
        $obj_products = $this->app->model('goods');
        $is_delete = $obj_products->delete(array('goods_id'=>$sdf['iid']));

        if (!$is_delete)
        {
            $db->rollback();
            $thisObj->send_user_error(app::get('b2c')->_('删除商品的货品失败！'), array('iid'=>$sdf['goods_id'],'modified'=>time()));
        }

        $db->commit($transaction_status);
        return array('iid'=>$sdf['iid'], 'modified'=>date('Y-m-d H:i:s',time()));
    }

    /**
     * 获取商品列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_all_list(&$sdf, &$thisObj)
    {

        $sdf['page_no'] = $sdf['page_no'] ? max(1,(int)$sdf['page_no']) : '1';
        $sdf['page_size'] = $sdf['page_size'] ? (int)$sdf['page_size'] : '20';

        /** 生成过滤条件 **/
        $db = kernel::database();
        $condition = "";
        if ($sdf['cat_id'])
            $condition .= " AND cat_id=".intval($sdf['cat_id']);
        if ($sdf['cid'])
            $condition .= " AND type_id=".intval($sdf['cid']);
        if ($sdf['brand_id'])
            $condition .= " AND brand_id=".$sdf['brand_id'];

        $page_size = $sdf['page_size'];
        $page_no = ($sdf['page_no'] - 1) * $page_size;

        $start_time = $sdf['start_time'];
        $end_time = $sdf['end_time'];
        if($start_time)
        {
            if(($start_time = strtotime($start_time)) === false || $start_time == -1)
            {
                $thisObj->send_user_error('5001', '开始时间格式不能正确解析!');
            }

            $condition .= " AND last_modify>=".$start_time;
        }

        if($end_time)
        {
            if(($end_time = strtotime($end_time)) === false || $end_time == -1)
            {
                $thisObj->send_user_error('5002', '结束时间格式不能正确解析!');
            }
            $condition .= " AND last_modify<".$end_time;
        }
        
        if ($sdf['search_keyword']){
<<<<<<< HEAD
        	$condition .= " AND ( name LIKE '%".$sdf['search_keyword']."%' OR brief LIKE '%".$sdf['search_keyword']."%' OR bn LIKE '%".$sdf['search_keyword']."%' OR barcode LIKE '%".$sdf['search_keyword']."%')"; 
=======
        	$condition .= " AND ( name LIKE '%".$sdf['search_keyword']."%' OR brief LIKE '%".$sdf['search_keyword']."%' OR bn LIKE '%".$sdf['search_keyword']."%' )"; 
>>>>>>> remotes/qianse/master
        }
        
        
        $orderby = isset($_REQUEST['orderby'])?trim($_REQUEST['orderby']):'';
        $orderby = $orderby?" order by ".$orderby:'';
        
        //虚拟分类
        if(isset($sdf['virtual_cat_id']) && $sdf['virtual_cat_id']){
        	/*
        	$filter = Array
        	(
        			'virtual_cat_id' => $sdf['virtual_cat_id'],
        			'marketable' => 'true',
        			'is_buildexcerpts' => 'true',
        			'pricefrom' =>'',
        			'priceto' =>'',
        			'searchname' =>'',
        			'type_id' => '_ANY_',
        			'cat_id' => Array('_ANY_'),
        			'brand_id' => Array(9),
        			'tag' => Array('_ANY_'),
        	
        	);
        	*/
        	$goodsModel = kernel::single('b2c_mdl_goods');
        	$goodsVcatModel = kernel::single('b2c_mdl_goods_virtual_cat');
        	
        	$vcat = $goodsVcatModel->dump($sdf['virtual_cat_id']);
        	
        	if(!$vcat)return array('total_results'=>0, 'items'=>'[]');
        		
        	parse_str($vcat['filter'],$filter);
        	$filter['marketable'] = 'true';
        	$filter['is_buildexcerpts'] = 'true';
        	
        	$rs[0]['count'] = $goodsModel->count($filter);
        	if(!$rs[0]['count']){
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	
        	$rows = $goodsModel->getList('*',$filter,$page_no,$page_size,$_REQUEST['orderby'],$total=false);
        	
        	
        	
        }else{
        	/*
        	$rs = $db->select("SELECT count(*) as count FROM `sdb_b2c_goods` WHERE 1".$condition);
        	if(!$rs[0]['count']){
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	if (!$rows = $db->select("SELECT * FROM `sdb_b2c_goods` WHERE 1".$condition." ".$orderby." LIMIT ".$page_no.",".$page_size))
        	{
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	*/
        	
        	$filter = Array(
        			'marketable' => 'true',
        			'is_buildexcerpts' => 'true',
        	);
        	
        	if($sdf['cid']) $filter['type_id'] = $sdf['cid'];
        	if($sdf['cat_id']) $filter['cat_id'] = $sdf['cat_id'];
        	if($sdf['brand_id']) $filter['brand_id'] = $sdf['brand_id'];
        	if($sdf['search_keyword']) $filter['search_keywords'][] = $sdf['search_keyword'];
        	
        	$goodsModel = kernel::single('b2c_mdl_goods');
        	
        	$rs[0]['count'] = $goodsModel->count($filter);
        	if(!$rs[0]['count']){
        		return array('total_results'=>0, 'items'=>'[]');
        	}
        	
        	$rows = $goodsModel->getList('*',$filter,$page_no,$page_size,$_REQUEST['orderby'],$total=false);
        	
        }
        

        

        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$obj_ctl_goods = kernel::single('b2c_ctl_site_product');
        foreach($rows as $arr_row)
        {
            $sdf_goods['item'][] = $this->get_item_detail($arr_row, $obj_ctl_goods);
        }
        
        $ret_items = isset($_REQUEST['son_object']) && $_REQUEST['son_object'] == 'json'?$sdf_goods:json_encode($sdf_goods);
        return array('total_results'=>$rs[0]['count'], 'items'=> $ret_items, 'screen' => $screen['screen']);
    }

    public function get_goods_list($params = array()) {
        $cat_id = $params['cat_id'];
        $page = $params['page'] > 0 ? $params['page'] : 1;
        $virtual_cat_id = $params['virtual_cat_id'];
        $filter = $params['filter'];

        $params = $this->filter_decode($filter ,$cat_id,$virtual_cat_id);
        $page = $params['page']?$params['page']:$page;
        $data = $this->get_goods($params['filter'], $page, $params['orderby']);
        $screen = $this->screen($cat_id, null);
        $data['screen'] = $screen['screen'];
        return $data;
    }

    public function get_screen($params) {
        $cat_id = $params['cat_id'];
        $tmp_filter['marketable'] = $params['marketable'] ? $params['marketable'] : 'true';
        if ($params['search_keywords'] || $filter['virtual_cat_id']) {
            $tmp_filter['search_keywords'][] = $params['search_keywords'];

            $goodsModel = app::get('b2c')->model('goods');
            $sfilter = 'select cat_id,count(cat_id) as count from sdb_b2c_goods WHERE ';
            $sfilter .= $goodsModel->_filter($tmp_filter);
            $sfilter .= ' group by cat_id';
            $cats = $goodsModel->db->select($sfilter);
            if($cats){
                foreach($cats as $cat_row){
                    $catCount[$cat_row['cat_id']] = $cat_row['count'];
                }
            }
        }

        //搜索时的分类
        if(!empty($catCount) && count($catCount) != 1){
            arsort($catCount);
            $this->pagedata['show_cat_id'] = key($catCount);
            $this->pagedata['catArr'] = array_keys($catCount);
            $this->catCount = $catCount;
        }else{
            $this->pagedata['show_cat_id'] = @key($catCount);
        }
        
        $screen = $this->screen($cat_id, $tmp_filter);
        foreach ($screen['screen'] as $key => $value) {
            if (is_array($value) && $key != 'tags' ) {
                sort($value);
                $screen['screen'][$key] = $value;
            }
        }
        return $screen['screen'];
    }

    public function filter_decode($params=null,$cat_id,$virtual_cat_id=null){
        if($virtual_cat_id){
            $params['virtual_cat_id']  = $virtual_cat_id;
        }
        $filter['params'] = $params;
        #分类
        $params['cat_id'] = $cat_id ? $cat_id : $params['cat_id'];
        if(!$params['cat_id']) unset($params['cat_id']);

        if($params['search_keywords'][0]){
            $params['orderBy'] = $params['orderBy'] ? $params['orderBy'] : 'view_count desc';
        }elseif($params['scontent']){
            $oSearch = $this->app->model('search');
            $decode = $oSearch->decode($params['scontent']);
            $params['search_keywords'] = $decode['search_keywords'];
            unset($params['scontent']);
        }

        if($params['search_keywords']){
            $params['search_keywords']= str_replace('%xia%','_',$params['search_keywords']);
        }

        #排序
        $orderby = $params['orderBy'];unset($params['orderBy']);

        #分页,页码
        $page= $params['page'];unset($params['page']);

        #商品显示方式
        if($params['showtype']){
            $showtype = $params['showtype'];unset($params['showtype']);
        }else{
            $showtype = app::get('b2c')->getConf('gallery.default_view');
        }

        $params['marketable'] = 'true';
        $params['is_buildexcerpts'] = 'true';
        $tmp_filter = $params;

        #价格区间筛选
        if($tmp_filter['price']){
            $tmp_filter['price'] = explode('~',$tmp_filter['price'][0]);
        }
        #商品标签筛选条件
        if($tmp_filter['gTag']){
            $tmp_filter['tag'] = $tmp_filter['gTag'];unset($tmp_filter['gTag']);
        }

        if($tmp_filter['is_store'] == 'on' || app::get('b2c')->getConf('gallery.display.stock_goods') != 'true'){
            #是否有货
            $is_store = $params['is_store'];
        }
        if($tmp_filter['virtual_cat_id']){
            $tmp_filter = $this->_merge_vcat_filter($tmp_filter['virtual_cat_id'],$tmp_filter);//合并虚拟分类条件
        }

        if($tmp_filter['pTag']){//促销优惠
            $time = time();
            $pTagGoods = app::get('b2c')->model('goods_promotion_ref')->getList('goods_id',array('tag_id'=>$tmp_filter['pTag'],'from_time|sthan'=>$time, 'to_time|bthan'=>$time,'status'=>'true'));
            if($pTagGoods){
                foreach($pTagGoods as $gids){
                    $tmp_filter['goods_id'][] = $gids['goods_id'];
                }
            }
            if(empty($tmp_filter['goods_id']) ){
                $tmp_filter['goods_id'] = array(-1);
            }
            unset($tmp_filter['pTag']);
        }

        $filter['filter'] = $tmp_filter;
        $filter['orderby'] = $orderby;
        $filter['showtype'] = $showtype;
        $filter['is_store'] = $is_store;
        $filter['page'] = $page;
        return $filter;
    }

    /*
     * 将列表页中的搜索条件和虚拟分类条件合并
     *
     * @params int $virtual_cat_id 虚拟分类ID
     * @params array $filter  列表页搜索条件
     * */
    private function _merge_vcat_filter($virtual_cat_id,$filter){
        $virCatObj = $this->app->model('goods_virtual_cat');
        /** 颗粒缓存商品虚拟分类 **/
        if(!cachemgr::get('goods_virtual_cat_'.intval($virtual_cat_id), $vcat)){
            cachemgr::co_start();
            $vcat = $virCatObj->getList('cat_id,cat_path,virtual_cat_id,filter,virtual_cat_name as cat_name',array('virtual_cat_id'=>intval($virtual_cat_id)));
            cachemgr::set('goods_virtual_cat_'.intval($virtual_cat_id), $vcat, cachemgr::co_end());
        }
        $vcat = current( $vcat );
        parse_str($vcat['filter'],$vcatFilters);

        if($filter['cat_id'] && $vcatFilters['cat_id']){
            unset($vcatFilters['cat_id']);
        }
        $filter = array_merge_recursive($filter,$vcatFilters);
        return $filter;
    }

    /* 根据条件返回搜索到的商品
     * @params array $filter 搜索条件
     * @params int   $page   页码
     * @params string $orderby 排序
     * @return array
     * */
    public function get_goods($filter,$page=1,$orderby){
        $goodsObject = kernel::single('b2c_goods_object');
        $goodsModel = app::get('b2c')->model('goods');
        $userObject = kernel::single('b2c_user_object');
        $member_id = $userObject->get_member_id();
        if( empty($member_id) ){
            //$this->pagedata['login'] = 'nologin';
        }

        $page = $page ? $page : 1;
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $data['pager']['pageLimit'] = $pageLimit;
        $goodsData = $goodsModel->getList('*',$filter,$pageLimit*($page-1),$pageLimit,$orderby,$total=false);
        if($goodsData && $total ===false){
           $total = $goodsModel->count($filter);
        }
        $data['pager']['total'] =  $total;
        $pagetotal= $total ? ceil($total/$pageLimit) : 1;
        $max_pagetotal = $this->app->getConf('gallery.display.pagenum');
        $max_pagetotal = $max_pagetotal ? $max_pagetotal : 100;
        $data['pager']['pagetotal'] = $pagetotal > $max_pagetotal ? '1231' : $pagetotal;
        $data['pager']['page'] = $page;
        $gfav = explode(',',$_COOKIE['S']['GFAV'][0]);
        foreach($goodsData as $key=>$goods_row){
            if(in_array($goods_row['goods_id'],$gfav)){
                $goodsData[$key]['is_fav'] = 'true';
            }
            if($goods_row['udfimg'] == 'true' && $goods_row['thumbnail_pic']){
                $goodsData[$key]['image_default_id'] = $goods_row['thumbnail_pic'];
            }
            $gids[$key] = $goods_row['goods_id'];
        }

        if($filter['search_keywords'] || $filter['virtual_cat_id']){
            if(kernel::service('server.search_type.b2c_goods') && $searchrule = searchrule_search::instance('b2c_goods') ){
                if($searchrule){
                    $catCount = $searchrule->get_cat($filter);
                }
            }else{
                $sfilter = 'select cat_id,count(cat_id) as count from sdb_b2c_goods WHERE ';
                $sfilter .= $goodsModel->_filter($filter);
                $sfilter .= ' group by cat_id';
                $cats = $goodsModel->db->select($sfilter);
                if($cats){
                    foreach($cats as $cat_row){
                        $catCount[$cat_row['cat_id']] = $cat_row['count'];
                    }
                }
            }
        }
        //搜索时的分类
        if(!empty($catCount) && count($catCount) != 1){
            arsort($catCount);
            $this->pagedata['show_cat_id'] = key($catCount);
            $this->pagedata['catArr'] = array_keys($catCount);
            $this->catCount = $catCount;
        }else{
            $this->pagedata['show_cat_id'] = @key($catCount);
        }

        //货品
        $goodsData = $this->get_product($gids,$goodsData);

        //促销标签
        $goodsData = $this->get_goods_promotion($gids,$goodsData);

        //商品标签信息
        foreach( kernel::servicelist('tags_special.add') as $services ) {
            if ( is_object($services)) {
                if ( method_exists( $services, 'add') ) {
                    $services->add( $gids, $goodsData);
                }
            }
        }
        $data['goodsData'] = $this->get_goods_point($gids,$goodsData);
        return $data;
    }

    /*
     * 获取搜索到的商品的默认货品数据，并且格式化货品数据(货品市场价，库存等)
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_product($gids,$goodsData){
        $this->pagedata['imageDefault'] = app::get('image')->getConf('image.set');
        $productModel = $this->app->model('products');
        $products =  $productModel->getList('*',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        $show_mark_price = $this->app->getConf('site.show_mark_price');

        #检测货品是否参与special活动
        if($object_price = kernel::service('sepcial_goods_check')){
            $object_price->check_special_goods_list($products);
        }

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
        return $goodsData;
    }

    /*
     * 获取搜索到的商品的促销信息
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_goods_promotion($gids,$goodsData){
        //商品促销
        $time = time();
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $goodsPromotion = app::get('b2c')->model('goods_promotion_ref')->getList('*', array('goods_id'=>$gids, 'from_time|sthan'=>$time, 'to_time|bthan'=>$time,'status'=>'true'),0,-1,$order);
        if($goodsPromotion){
            $black_gid = array();
            foreach($goodsPromotion as $row) {
                if(in_array($row['goods_id'],$black_gid)) continue;
                $tags[] = $row['tag_id'];
                $promotionData[$row['goods_id']][] = $row['tag_id'];
                if( $row['stop_rules_processing']=='true' ){
                    $black_gid[] = $row['goods_id'];
                }
            }
        }
        $tagModel = app::get('desktop')->model('tag');
        $sdf_tags = $tagModel->getList('tag_id,tag_name',array('tag_id'=>$tags));
        foreach($sdf_tags  as $tag_row){
            $tagsData[$tag_row['tag_id']] = $tag_row;
        }
        foreach((array)$promotionData as $gid=>$p_row){
            foreach($p_row as $k=>$tag_id){
                $promotion_tags[$gid][$k] = $tagsData[$tag_id];
            }
        }
        foreach($goodsData as $key=>$goods_row){
            if($goods_row['products']['promotion_type']){
                continue;   
            }
            $goodsData[$key]['promotion_tags'] = $promotion_tags[$goods_row['goods_id']];
        }
        return $goodsData;
    }

    /*
     * 获取搜索到的商品的积分信息
     *
     * @params array $gids 搜索到到的商品ID集合
     * @params array $goodsData 搜索到的商品数据
     * @return array
     * */
    private function get_goods_point($gids,$goodsData){
        $pointModel = $this->app->model('comment_goods_point');
        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $this->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        if($this->pagedata['point_status'] == 'on'){
            $sdf_point = $pointModel->get_single_point_arr($gids);
            foreach($goodsData as $key=>$row){
                $goodsData[$key]['goods_point'] = $sdf_point[$row['goods_id']];
                #$goodsData[$key]['comments_count'] = $sdf_point[$row['goods_id']]['comments_count'];
            }
        }
        return $goodsData;
    }

    /*
     * 前台筛选商品
     * */
    public function filter_get_goods($params){
        $tmp_params = $this->filter_decode($params, $params['cat_id']);
        $params = $tmp_params['filter'];
        $orderby = $tmp_params['orderby'];
        $showtype = 'grid';
        $page = $tmp_params['page'] ? $tmp_params['page'] : 1;
        $goodsData = $this->get_goods($params,$page,$orderby);

        $data = array();
        foreach($goodsData['goodsData'] as $arr_row)
        {
            $data['items']['item'][] = $this->get_item_detail($arr_row, $obj_ctl_goods);
        }
        $data['pager'] = $goodsData['pager'];
        $data['total_results'] = $data['pager']['total'];
        return $data;
    }

    /*
     * 根据分类ID提供筛选条件，并且返回已选择的条件数据
     *
     * @params int $cat_id 分类ID
     * @params array $filter 已选择的条件
     * */
    private function screen($cat_id,$filter){
        if ( empty($cat_id) ) {
            $screen = array();
        }
        $screen['cat_id'] = $cat_id;
        $cat_id = $cat_id ?  $cat_id : $this->pagedata['show_cat_id'];
        //搜索时的分类
        if(!$screen['cat_id'] && count($this->pagedata['catArr']) > 1){
            $searchCat = app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('cat_id'=>$this->pagedata['catArr']));
            $i=0;
            foreach($this->catCount as $catid=>$num){
                $sort[$catid] = $i;
                if($i == 9) break;
                $i++;
            }
            foreach($searchCat as $row){
                $screen['search_cat'][$sort[$row['cat_id']]] = $row;
            }
            ksort($screen['search_cat']);
        }

        //商品子分类
        /*
        $show_cat = $this->app->getConf('site.cat.select');
        if($show_cat == 'true'){
            $sCatData = app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('parent_id'=>$cat_id));
            $screen['cat'] = $sCatData;
        }
        */

        cachemgr::co_start();
        if(!cachemgr::get("goodsObjectCat".$cat_id, $catInfo)){
            $goodsInfoCat = app::get("b2c")->model("goods_cat")->getList('*',array('cat_id'=>$cat_id) );
            $catInfo = $goodsInfoCat[0];
            cachemgr::set("goodsObjectCat".$cat_id, $catInfo, cachemgr::co_end());
        }
        $this->goods_cat = $catInfo['cat_name'];//seo

        cachemgr::co_start();
        if(!cachemgr::get("goodsObjectType".$catInfo['type_id'], $typeInfo)){
            $typeInfo = app::get("b2c")->model("goods_type")->dump2(array('type_id'=>$catInfo['type_id']) );
            cachemgr::set("goodsObjectType".$catInfo['type_id'], $typeInfo, cachemgr::co_end());
        }
        $this->goods_type = $typeInfo['name'];//seo

        if($typeInfo['price'] && $filter['price'][0]){
            $active_filter['price']['title'] = $this->app->_('价格');
            $active_filter['price']['label'] = 'price';
            $active_filter['price']['options'][0]['data'] =  $filter['price'][0];
            foreach($typeInfo['price'] as $key=>$price){
                $price_filter = implode('~',$price);
                if($filter['price'][0] == $price_filter){
                    $typeInfo['price'][$key]['active'] = 'active';
                    $active_arr['price'] = 'active';
                }
                $active_filter['price']['options'][0]['name'] = $filter['price'][0];
            }
        }
        $screen['price'] = $typeInfo['price'];

        if ( $typeInfo['setting']['use_brand'] ){
            $type_brand = app::get('b2c')->model('type_brand')->getList('brand_id',array('type_id'=>$catInfo['type_id']));
            if ( $type_brand ) {
                foreach ( $type_brand as $brand_k=>$brand_row ) {
                    $brand_ids[$brand_k] = $brand_row['brand_id'];
                }
            }
            $brands = app::get('b2c')->model('brand')->getList('brand_id,brand_name',array('brand_id'=>$brand_ids,'disabled'=>'false'));
            //是否已选择
            foreach($brands as $b_k=>$row){
                if(@in_array($row['brand_id'],$filter['brand_id'])){
                    $brands[$b_k]['active'] = 'active';
                    $active_arr['brand'] = 'active';
                    $active_filter['brand']['title'] = $this->app->_('品牌');;
                    $active_filter['brand']['label'] = 'brand_id';
                    $active_filter['brand']['options'][$row['brand_id']]['data'] =  $row['brand_id'];
                    $active_filter['brand']['options'][$row['brand_id']]['name'] = $row['brand_name'];
                }
            }
            $screen['brand'] = $brands;
        }

        //扩展属性
        if ( $typeInfo['setting']['use_props'] && $typeInfo['props'] ){
            foreach ( $typeInfo['props'] as $p_k => $p_v){
                if ( $p_v['search'] != 'disabled' ) {
                    $props[$p_k]['name'] = $p_v['name'];
                    $props[$p_k]['goods_p'] = $p_v['goods_p'];
                    $props[$p_k]['type'] = $p_v['type'];
                    $props[$p_k]['search'] = $p_v['search'];
                    $props[$p_k]['show'] = $p_v['show'];
                    $propsActive = array();
                    if($p_v['options']){
                        foreach($p_v['options'] as $propItemKey=>$propItemValue){
                            $activeKey = 'p_'.$p_v['goods_p'];
                            if($filter[$activeKey] && in_array($propItemKey,$filter[$activeKey])){
                                $active_filter[$activeKey]['title'] = $p_v['name'];
                                $active_filter[$activeKey]['label'] = $activeKey;
                                $active_filter[$activeKey]['options'][$propItemKey]['data'] =  $propItemKey;
                                $active_filter[$activeKey]['options'][$propItemKey]['name'] = $propItemValue;
                                $propsActive[$propItemKey] = 'active';
                            }
                        }
                    }
                    $props[$p_k]['options'] = $p_v['options'];
                    $props[$p_k]['active'] = $propsActive;
                }
            }

            $screen['props'] = $props;
        }

        //规格
        $gType = &$this->app->model('goods_type');
        $SpecList = $gType->getSpec($catInfo['type_id'],1);//获取关联的规格
        if($SpecList){
            foreach($SpecList as $speck=>$spec_value){
                if($spec_value['spec_value']){
                    foreach($spec_value['spec_value'] as $specKey=>$SpecValue){
                        if($spec_value['spec_type'] == "image"){
                            $SpecList[$speck]['spec_value'][$specKey]['spec_image'] = base_storager::image_path($SpecList[$speck]['spec_image'][$specKey]['spec_image'],'s');
                        }
                        $activeKey = 's_'.$speck;
                        if($filter[$activeKey] && in_array($specKey,$filter[$activeKey])){
                            $active_filter[$activeKey]['title'] = $spec_value['name'];
                            $active_filter[$activeKey]['label'] = $activeKey;
                            $active_filter[$activeKey]['options'][$specKey]['data'] =  $specKey;
                            $active_filter[$activeKey]['options'][$specKey]['name'] = $SpecValue['spec_value'];
                            $specActive[$specKey] = 'active';
                        }
                    }
                }
                $SpecList[$speck]['active'] = $specActive;
            }
        }
        $screen['spec'] = $SpecList;

        //排序
        $orderBy = $this->app->model('goods')->orderBy();
        $screen['orderBy'] = $orderBy;

        //标签
        $tagFilter['app_id'][] = 'b2c';
        $giftAppActive = app::get('gift')->is_actived();
        if($giftAppActive){
            $tagFilter['app_id'][] = 'gift';
        }
        $progetcouponAppActive = app::get('progetcoupon')->is_actived();
        if($progetcouponAppActive){
            //$progetcouponAppActive['app_id'][] = 'progetcoupon';
        }
        $tags = app::get('desktop')->model('tag')->getList('*',$tagFilter);
        if($filter['pTag']){
            $active_arr['pTags'] = 'active';
        }
        foreach($tags as $tag_key=>$tag_row){
            if($tag_row['tag_type'] == 'goods'){//商品标签
                if(@in_array($tag_row['tag_id'], $filter['gTag'])){
                    $screen['tags']['goods'][$tag_key]['active'] = 'checked';
                }
                $screen['tags']['goods'][$tag_key]['tag_id'] = $tag_row['tag_id'];
                $screen['tags']['goods'][$tag_key]['tag_name'] = $tag_row['tag_name'];
            }elseif($tag_row['tag_type'] == 'promotion'){//促销标签
                if(@in_array($tag_row['tag_id'],$filter['pTag'])){
                    $screen['tags']['promotion'][$tag_key]['active'] = 'active';
                    $active_filter['pTag']['title'] = $this->app->_('促销商品');;
                    $active_filter['pTag']['label'] = 'pTag';
                    $active_filter['pTag']['options'][$tag_row['tag_id']]['data'] =  $tag_row['tag_id'];
                    $active_filter['pTag']['options'][$tag_row['tag_id']]['name'] = $tag_row['tag_name'];
                }
                $screen['tags']['promotion'][$tag_key]['tag_id'] = $tag_row['tag_id'];
                $screen['tags']['promotion'][$tag_key]['tag_name'] = $tag_row['tag_name'];
            }
        }
        $this->pagedata['active_arr'] = $active_arr;
        $return['screen'] = $screen;
        $return['active_filter'] = $active_filter;
        $return['seo_info'] = $catInfo['seo_info'];
        return $return;
    }

    /**
     * 获取商品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_item(&$sdf, &$thisObj)
    {
        if (!$sdf['iid'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('必填参数不存在！'), array('item'=>''));
        }

        $db = kernel::database();
        if (!$rows = $db->select("SELECT * FROM `sdb_b2c_goods` WHERE `goods_id`=".$sdf['iid']))
        {
            return array('item'=>'');
        }
        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$obj_ctl_goods = kernel::single('b2c_ctl_site_product');
        $sdf_goods['item'] = isset($sdf['son_object']) && $sdf['son_object'] == 'json'?$this->get_item_detail($rows[0], $obj_ctl_goods):json_encode($this->get_item_detail($rows[0], $obj_ctl_goods));

        return array('item'=>$sdf_goods['item']);
    }

    /**
     * 生成商品明细的数组
     * @param mixed 每行商品的数组-数据
     * @param object goods controller
     * @return mixed
     */
    protected function get_item_detail($arr_row, $obj_clt_goods)
    {
        if (!$arr_row)
            return array();
        

        $cnt_props = 20;
        $cn_input_props = 50;

        $detal_url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_row['goods_id']));
        
        
        
        /** props 目前是1-20 **/
        $props = "";
        $props_value_ids = array();
        for ($i=1;$i<=$cnt_props;$i++)
        {
            if ($arr_row['p_'.$i]){
            	$props .= $i.":".$arr_row['p_'.$i].";";
            	$props_value_ids[] = $arr_row['p_'.$i];
            }
                
        }
        if ($props)
            $props = substr($props, 0, strlen($props)-1);
        /** end **/
        
        $db = kernel::database();
        $props_values = '';
        if($props_value_ids){
        	$props_values = $db->select("SELECT p.name as props_name, pv.name as props_value FROM `sdb_b2c_goods_type_props_value` as pv 
        			left join `sdb_b2c_goods_type_props` as p on p.props_id = pv.props_id 
        			where pv.props_value_id in (".implode(',', $props_value_ids).") order by pv.order_by asc;");
        }
        

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
                $str_skus_properties = '';
                $arr_spec_desc = unserialize($arr['spec_desc']);
                if($arr_spec_desc['spec_value_id'])
                {
                	ksort($arr_spec_desc['spec_value_id']);
                	$spec_values = array();
                    foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key =>$arr_value){
                    	$str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
                    	
                    	$spec_v['spec_value_id'] = $arr_value;
                    	$spec_v['spec_value'] = $arr_spec_desc['spec_value'] [$spec_id_key];
                    	$spec_values[] = $spec_v;
                    }
                }
                if ($str_skus_properties)
                    $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);

                
                //是否正在进行组合促销
                $starbuy_products_object = kernel::single("starbuy_special_products");
                $is_starbuy = false;
                if($starbuy_products_object->ifSpecial($arr['product_id']) === 'starbuy'){
                	$is_starbuy = true;
                }
                
                $arr_sdf_skus[] = array(
                    'sku_id'=>$arr['product_id'],
                    'iid'=>$arr['goods_id'],
                    'bn'=>$arr['bn'],
                    'properties'=>$str_skus_properties,
                    'quantity'=>$arr['store'],
                    'weight'=>$arr['weight'],
                    'price'=>$arr['price'],
                    'market_price'=>$arr['mktprice'],
                    'modified'=>$arr['last_modify'],
<<<<<<< HEAD
=======
                    'store'=>$arr['store'],
                    'freez'=>$arr['freez'],
>>>>>>> remotes/qianse/master
                    'cost'=>$arr['cost'],
                	'is_starbuy' => $is_starbuy,
					//'spec_values' => $spec_values,
                );
            }
            $str_skus = isset($_REQUEST['son_object']) && $_REQUEST['son_object'] == 'json'?$arr_sdf_skus:json_encode($arr_sdf_skus);
        }
        
        if($arr_row['udfimg'] == 'true' && $arr_row['thumbnail_pic']){
        	$arr_row['image_default_id'] = $arr_row['thumbnail_pic'];
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
                
        $specification = $db->select("SELECT spec_name, spec_id FROM `sdb_b2c_specification` order by spec_id asc;");
        $specif = array();
        foreach ($specification as $key => $value) {
        	$specif[$value['spec_id']]['spec_id'] = $value['spec_id'];
        	$specif[$value['spec_id']]['spec_name'] = $value['spec_name'];
        }
        
        $spec_desc = @unserialize($arr_row['spec_desc']);
        
        $spec_info = array();
        if($spec_desc){
	        foreach ($spec_desc as $key => $value) {	
	        	$spec_values = array();
	        	foreach ($value as $k => $v) {
	        		$v['spec_goods_images'] = kernel::single('base_storager')->image_path($v['spec_goods_images'], 'm');
	        		$v['spec_image'] = kernel::single('base_storager')->image_path($v['spec_image'], 'm');
	        		$v['properties'] = $key.':'.$v['spec_value_id'].'_'.$v['spec_value'];
	        		
	        		$spec_values[] = $v;
	        	}
	        	$specif[$key]['spec_values'] = $spec_values;
	        	$spec_info[] = $specif[$key];
	        }
        }
        
        
        $objGoods = app::get('b2c')->model('goods');
        $aGoods_list = $objGoods->getList("wapintro",array('goods_id'=>$arr_row['goods_id']));
        
        
        $obj_member = app::get('b2c')->model('member_goods');
        $goods_favorite_count = $obj_member->goods_favorite_count($arr_row['goods_id']);
        
        $userObject = kernel::single('b2c_user_object');
        $siteMember = $userObject->get_current_member();
        
        //促销
        if(empty($siteMember['member_lv'])){
        	$siteMember['member_lv'] = '-1';
        }
        
        $productPromotion= kernel::single('b2c_ctl_site_product')->_get_goods_promotion($arr_row['goods_id'],array(),$siteMember['member_lv']);
		$promotion['goods'] = array();
		$promotion['order'] = array();
		foreach ($productPromotion['goods'] as $pmt){
			$promotion['goods'][] = $pmt;
		}
		foreach ($productPromotion['order'] as $pmt){
			$promotion['order'][] = $pmt;
		}
		
		
        return array(
            'iid'=>$arr_row['goods_id'],
            'title'=>$arr_row['name'],
            'bn'=>$arr_row['bn'],
            'shop_cids'=>$arr_row['cat_id'],
            'cid'=>$arr_row['type_id'],
            'brand_id'=>$arr_row['brand_id'],
            'props'=>$props,
        	'props_values' => $props_values,
            'input_pids'=>$input_pids,
            'input_str'=>$input_str,
            'description'=> $arr_row['intro'],
        	'wapintro'=> !empty($aGoods_list[0]['wapintro']) && strlen($aGoods_list[0]['wapintro']) > 8 ? $aGoods_list[0]['wapintro'] : '', //手机端介绍
            'brief'=>$arr_row['brief'],
            'default_img_url'=>$default_img_url,
            'num'=>$arr_row['store'],
            'weight'=>$arr_row['weight'] ? $arr_row['weight'] : '',
            'score'=>$arr_row['score'] ? $arr_row['score'] : '',
            'price'=>$arr_row['price'],
            'market_price'=>$arr_row['mktprice'],
            'unit'=>$arr_row['unit'],
            'cost_price'=>$arr_row['cost'],
            'marketable'=>$arr_row['marketable'],
<<<<<<< HEAD
=======
            'store'=>$arr_row['store'],
             'freez'=>$arr_row['freez'],
>>>>>>> remotes/qianse/master
            'item_imgs'=>$goods_images,
            'buy_count'=>$arr_row['buy_count'],
            'comments_count'=>$arr_row['comments_count'],
            'modified'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'list_time'=>date('Y-m-d H:i:s',$arr_row['uptime']),
            'delist_time'=>date('Y-m-d H:i:s',$arr_row['downtime']),
            'created'=>date('Y-m-d H:i:s',$arr_row['last_modify']),
            'skus'=>$str_skus,
            'spec_info' => $spec_info,
        	'goods_favorite_count' => $goods_favorite_count,
            'buy_limit' => (int)$arr_row['buy_limit'],
        	'promotion' => $promotion,
        );
    }

    /**
     * 编辑货品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function update_sku(&$sdf, &$thisObj)
    {
        if (!$sdf['iid'] || !$sdf['bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('商品ID或货品bn为空！'), array('item'=>''));
        }

        $obj_products = $this->app->model('products');
        $sdf_products = array();

        if ($sdf['store'])
            $sdf_products['store'] = $sdf['store'];
        if ($sdf['weight'])
            $sdf_products['weight'] = $sdf['weight'];
        if ($sdf['mktprice'])
            $sdf_products['mktprice'] = $sdf['mktprice'];
        if ($sdf['price'])
            $sdf_products['price'] = $sdf['price'];
        if ($sdf['cost'])
            $sdf_products['cost'] = $sdf['cost'];

        $sdf_products['last_modify'] = time();

        if ($sdf_products)
        {
            if (!$obj_products->update($sdf_products, array('goods_id'=>$sdf['iid'],'bn'=>$sdf['bn'])))
            {
                $thisObj->send_user_error(app::get('b2c')->_('货品信息更新失败！'), array('item'=>''));
            }else{
                //如果更新数据有库存信息，则最终重新合计商品库存总数
                if ($sdf['store']){
                     $db = kernel::database();
                     $store_sum = $db->select("SELECT sum(store) as store_sum FROM `sdb_b2c_products` WHERE `goods_id`=".$sdf['iid']);
                     $tmp_goods['store'] = $store_sum[0]['store_sum'];

                     $obj_goods = $this->app->model('goods');
                     $obj_goods->update($tmp_goods, array('goods_id'=>$sdf['iid']));
                }
            }
        }

        $tmp = $obj_products->getList('product_id', array('goods_id'=>$sdf['iid'],'bn'=>$sdf['bn']));
        return array('iid'=>$sdf['iid'],'sku_id'=>$tmp[0]['product_id'],'sku_bn'=>$sdf['bn'],'modified'=>date('Y-m-d H:i:s',$sdf_products['last_modify']));
    }

    /**
     * 获取货品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_sku(&$sdf, &$thisObj)
    {
        if (!$sdf['iid'] || !$sdf['bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('商品ID或货品bn为空！'), array('item'=>''));
        }


        $obj_product = $this->app->model('products');
        $arr_product = $obj_product->getList('*', array('goods_id'=>$sdf['iid'],'bn'=>$sdf['bn']));
        if (!$arr_product)
            return array();
        $arr = $arr_product[0];

        /** 组成返回数组 **/
        $str_skus_properties = '';
        $arr_spec_desc = $arr['spec_desc'];
        if($arr_spec_desc['spec_value_id'])
        {
            foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key => $arr_value)
                $str_skus_properties .= $spec_id_key.":".$arr_value . '_' . $arr_spec_desc['spec_value'] [$spec_id_key]. ";";
        }
        if ($str_skus_properties)
            $str_skus_properties = substr($str_skus_properties, 0, strlen($str_skus_properties)-1);

        return array(
            'sku_id'=>$arr['product_id'],
            'iid'=>$arr['goods_id'],
            'bn'=>$arr['bn'],
            'properties'=>$str_skus_properties,
            'quantity'=>$arr['store'],
            'weight'=>$arr['weight'],
            'price'=>$arr['price'],
            'market_price'=>$arr['mktprice'],
            'modified'=>$arr['last_modify'],
            'cost'=>$arr['cost'],
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
        if(isset($_POST['page_size'])){
        	$arr_goods_cats = $db->select("SELECT * FROM `sdb_b2c_goods_cat` order by p_order asc  LIMIT ".$page_no.",".$page_size." ");
        }else{
        	$arr_goods_cats = $db->select("SELECT * FROM `sdb_b2c_goods_cat` order by p_order asc ");
        }
        if (!$arr_goods_cats)
            return array();
        
        $totalResults = $db->selectRow("SELECT COUNT(*)  as totalResults FROM `sdb_b2c_goods_cat` ");

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
                'picture'=>$img_src = base_storager::image_path($arr_cat['picture']),
            );
        }

        return array('datas'=>$sdf_arr_goods_cats,'totalResults'=>$totalResults['totalResults']);
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
}

?>