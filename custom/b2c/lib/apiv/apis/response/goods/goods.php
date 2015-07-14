<?php
class b2c_apiv_apis_response_goods_goods
{
    public function __construct($app)
    {
        $this->app=$app;    
    }
    /**
     * 根据商品id获取商品详情
     * @param int goods_id 商品id
     * @return goods_id 商品的id 
     * @return goods_context 商品的详情 
     */
    public function get_goods_intro($params, &$service)
    {
		$goods_id = intval(''.$params['goods_id']);
        if ($goods_id<1)
        {
            $service->send_user_error(app::get('b2c')->_('商品ID必填'), null);
        }
		
        $_goods = $this->app->model('goods');
        $filter = array('goods_id'=>intval($goods_id));
        $wap_status = app::get('wap')->getConf('wap.status');
        if( $wap_status == 'true' ){
            $intro = $_goods->getList('wapintro,intro',$filter);
        }else{
            $intro = $_goods->getList('intro',$filter);
        }
        $intro = $intro[0];
        $return['goods_id'] = $goods_id;
        if( $intro['wapintro'] ){
            $return['goods_context'] = $intro['wapintro'];
        } else {
            $return['goods_context'] = $intro['intro'];
        }
        return $return;
    }

    /**
     * 获取商品列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function get_goods_detail_list(&$sdf, &$thisObj)
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


        $rs = $db->select("SELECT count(*) as count FROM `sdb_b2c_goods` WHERE 1".$condition);
        if(!$rs[0]['count']){
            return array('total_results'=>0, 'items'=>'[]');            
        }
        if (!$rows = $db->select("SELECT * FROM `sdb_b2c_goods` WHERE 1".$condition." LIMIT ".$page_no.",".$page_size))
        {
            return array('total_results'=>0, 'items'=>'[]');
        }

        /**
         * 得到返回的商品信息
         */
        $sdf_goods = array();
        //$obj_ctl_goods = kernel::single('b2c_ctl_site_product');
        foreach($rows as $arr_row)
        {
            $sdf_goods['item'][] = $this->get_item_detail($arr_row);
        }

        //return array('total_results'=>$rs[0]['count'], 'items'=>json_encode($sdf_goods));
		return array('total_results'=>$rs[0]['count'], 'items'=>$sdf_goods);
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
	 * 根据表名，查询条件，页码，返回总记录数，总分页数
	 * 
     * @param page_no 		当前页码
     * @param page_size		每页的记录数
     * @param sTableName 	表名
     * @param sWhere 		查询条件
	 * @return array
	 */
	private function get_pager($page_no, $page_size, $sTableName, $sWhere='')
	{
        $page_no 	= intval($page_no);
        $page_size 	= intval($page_size);
		$limit		= '';
		
		$rs_count = 0;
		
		$sql = 'select count(*) as c from `'.$sTableName.'` '.$sWhere;

		//-------------------------------------------------
		
		$db = kernel::database();
        $rs = $db->selectrow($sql);
        if ($rs && is_array($rs)){
			$rs_count = intval($rs['c']);
		}
		$str_limit 	= '';
		$offset 	= 0;
		$limit 		= -1;
		$page_count = 0;
		
		if($rs_count>0){
			$page_count	= ceil($rs_count / $page_size);
			if( $page_no < 1 ){
				$page_no = 1;
			}else if( $page_no > $page_count ){
				$page_no = $page_count;
			}
			$offset		= (($page_no-1)* $page_size);
			$limit		= $page_size;
			$str_limit = ' LIMIT '.$offset.','.$limit;
		}else{
			$page_no = 1;
		}
		
		return array(
			'rs_count' 		=> $rs_count,
			'page_count' 	=> $page_count,
			'page_no' 		=> $page_no,
			'page_size' 	=> $page_size,
			'offset' 		=> $offset,
			'limit' 		=> $limit,
			'str_limit' 	=> $str_limit
		);
	}
	
    /**
     * 根据筛选条件查询商品
     * @param int page_num 页码
     * @param int page_size 每页的容量
     * @param int cat_id 商品分类 
     * @param string brand_id array(int)数组的json(品牌id)
     * @param string search_keywords 关键词（根据名字搜索）
     * @param string specs array(int=>array(int))格式的json 商品规格array(规格id=>array(规格值id))
     * @param string props array(int=>array(int))格式的json props 商品属性id
     * @param string orderBy 排序方式，默认为按时间排序
     * @return goods 商品
     * @return count 商品条目数
     */
    public function get_goods_base_list($params, &$service)
    {
        $params['page_no'] 		= $params['page_no'] ? $params['page_no'] : '1';
        $params['page_size'] 	= $params['page_size'] ? $params['page_size'] : '10';
		
        $_goods = $this->app->model('goods');

        //排序
        if(isset($params['orderBy_id']) && $params['orderBy_id']>0 && $params['orderBy_id'] <11)
        {
            $order = $_goods->orderBy($params['orderBy_id']);
            $orderBy = $order['sql'];
        }
		
		$pager	= $this->get_pager($params['page_no'], $params['page_size'], $_goods->table_name(1));

		//----------------------------------------------------
		//组织数据
		$items 	= array();
		if( $pager['rs_count']>0 ){
			$field = 'goods_id,bn,name,price,type_id,cat_id,brand_id,marketable,store,uptime,downtime,'
			.'last_modify,p_order,score,cost,mktprice,weight,unit,brief,goods_type,nostore_sell,goods_setting,spec_desc,params,disabled';
			
			$filter = array();
			$items = $_goods->getList($field,$filter,$pager['offset'],$pager['limit'],$orderBy);
		}
		
        return array(
			'rscount'	=> $pager['rs_count'],
			'pageno'	=> $pager['page_no'],
			'pageszie'	=> $pager['page_size'],
			'pagecount'	=> $pager['page_count'],
			'items'		=> $items
		);
    }
	
    /**
     * 根据筛选条件查询商品
     * @param int page_num 页码
     * @param int page_size 每页的容量
     * @param int cat_id 商品分类 
     * @param string search_keywords 关键词（根据名字搜索）
     * @param string brand_id array(int)数组的json(品牌id)
     * @param string specs array(int=>array(int))格式的json 商品规格array(规格id=>array(规格值id))
     * @param string props array(int=>array(int))格式的json props 商品属性id
     * @return goods 商品
     * @return count 商品条目数
     */
    public function search_properties_goods($params, &$service)
    {
        //json转array
        $params['brand_id'] = $params['brand_id'] ? json_decode($params['brand_id'],true) : null;
        $params['specs'] = $params['specs'] ? json_decode($params['specs'],true) : null;
        $params['props'] = $params['props'] ? json_decode($params['props'],true) : null;
        //分类、品牌、关键词必须要有一个才可以查询
        if( $params['cat_id'] == null && $params['search_keywords'] == null && $params['brand_id'] == null)
        {
            return array('status'=>'error','message'=>'分类、品牌、关键词至少有一项需要填写');
        }

        $obj_goods = $this->app->model('goods');
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $offset = $params['page_num'] ? (($params['page_num']-1) * $limit) : 0;

        //根据分类查询
        if(isset($params['cat_id']) && $params['cat_id']!=null)
        {
            $obj_cat = $this->app->model('goods_cat');
            $cat_data = $obj_cat->getList('cat_id',array('parent_id|in'=>$params['cat_id']));
            foreach($cat_data as $value)
            {
                $cat_filter[$value['cat_id']] = $value['cat_id'];
            }
            $cat_filter[$params['cat_id']] = $params['cat_id'];
            $filter['cat_id|in'] = $cat_filter;
        }

        //根据关键字查询
        if(isset($params['search_keywords']) && $params['search_keywords']!=null)
        {
            $filter['search_keywords'] = array($params['search_keywords']);
        }

        //根据品牌查询
        if(isset($params['brand_id']) && count($params['brand_id'])>0)
        {
            $filter['brand_id'] = $params['brand_id'];
        }

        //根据属性查询
        if(isset($params['props']) && count($params['props'])>0)
        {
            foreach($params['props'] as $prop_id=>$prop)
            {
                $prop_ids[$prop_id] = $prop_id;
            }

            $obj_props = $this->app->model('goods_type_props');
            $props = $obj_props->getList('props_id,goods_p',array('props_id|in'=>$prop_ids));
            foreach($props as $prop)
            {
                $filter['p_'.$prop['goods_p'].'|in'] = $params['props'][$prop['props_id']];
            }
        }

        //根据规格查询
        if(isset($params['specs']) && count($params['specs'])>0)
        {
            foreach($params['specs'] as $spec_id=>$spec)
            {
                $filter['s_'.$spec_id] = $spec;
            }
        }

        //排序
        if(isset($params['orderBy_id']) && $params['orderBy_id']>0 && $params['orderBy_id'] <11)
        {
            $order = $obj_goods->orderBy($params['orderBy_id']);
            $orderBy = $order['sql'];
        }
        $filter['marketable'] = 'true';
        $data = $obj_goods->getList('marketable,goods_id,bn,name,brief,image_default_id,comments_count,nostore_sell',$filter,$offset,$limit,$orderBy);

        foreach($data as $key=>$goods)
        {
            $fmt_use_for_img[$goods['goods_id']] = $goods['image_default_id'];
            $gids[$goods['goods_id']] = $goods['goods_id'];
        }
        $fmt_image = $this->get_images_by_ids($fmt_use_for_img);
        //拉取默认货品
        $obj_product = $this->app->model('products');
        $products = $obj_product->getList('product_id,goods_id,price,mktprice,store,freez',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        foreach($products as $key=>$value)
        {
            $fmt_products[$value['goods_id']] = $value;
        }
        //组织数据
        foreach($data as $key=>$goods)
        {
            $data[$key]['default_product_id'] = $fmt_products[$goods['goods_id']]['product_id'];
            $data[$key]['price'] = $fmt_products[$goods['goods_id']]['price'];
            $mktprice = $fmt_products[$goods['goods_id']]['mktprice'];
            if($mktprice == '' || $mktprice == null)
                $data[$key]['mktprice'] = $obj_product->getRealMkt($fmt_products[$goods['goods_id']]['price']);
            else
                $data[$key]['mktprice'] = $mktprice;
            $store = $fmt_products[$goods['goods_id']]['store'];
            $freez = $fmt_products[$goods['goods_id']]['freez'];
            if($goods['nostore_sell'] || $store == null)
                $data[$key]['store'] = 999999;
            else
                $data[$key]['store'] = $store - $freez;
            $data[$key]['image'] = $fmt_image[$goods['image_default_id']];
            unset($data[$key]['nostore_sell']);
        }
        $return['goods']=$data;
        //获取总条数
        $count = $obj_goods->countGoods($filter);
        $return['count']=$count;
        return $return;
    }
	
	/**
	 * 根据 product，返回 get_sku_detail
	 * 
     * @param rs_product 	product
     * @param rs_goods 		goods
	 * @return array
	 */
	private function get_response_goods_detail($rs_product,$rs_goods)
    {

        //拉取库存
        if($rs_goods['nostore_sell'] == '1' || $rs_product['store'] == null)
        {
            $store = '999999';
        }else{
            $store = ''.($rs_product['store'] - $rs_product['freez']);
        }

		//-----------------------------------------------
        //拉取类型type
        $_type = $this->app->model('goods_type');
        $rs_type = $_type->getRow('type_id,name',$rs_goods['type']);

        //拉取分类
        $_cat = $this->app->model('goods_cat');
        $rs_cat = $_cat->getRow('cat_id,cat_name',$rs_goods['category']);

        //拉取品牌
        $_brand = $this->app->model('brand');
        $rs_brand = $_brand->getRow('brand_id,brand_name',$rs_goods['brand']);

        //拉取促销
        $promotion = @$this->get_promotion_by_goods_id($rs_goods['goods_id']);
		

        //处理规格(并拉取图片)
        $spec = $rs_goods['spec'];

		if($spec){
			foreach($spec as $spec_key=>$spec_value)
			{
				foreach($spec_value['option'] as $spec_option_key=>$spec_option_value)
				{
					$image_ids[$spec_option_value['spec_image']] = $spec_option_value['spec_image'];
					$spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] = explode(',',$spec_option_value['spec_goods_images']);
					foreach($spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] as $image_id)
					{
						$image_ids[$image_id] = $image_id;
					}
				}
			}
		}

        $_image_attach = app::get("image")->model("image_attach");
        $image_data_ids = $_image_attach->getList("attach_id,image_id",array("target_id"=>intval($rs_goods['goods_id']),'target_type'=>'goods'));

        foreach($image_data_ids as $images)
        {
            $image_ids[$images['image_id']] = $images['image_id'];
        }
        $fmt_images = $this->get_images_by_ids($image_ids);
		if($spec){
			foreach($spec as $spec_key=>$spec_value)
			{
				foreach($spec_value['option'] as $spec_option_key=>$spec_option_value)
				{
					$spec[$spec_key]['option'][$spec_option_key]['spec_image'] = $fmt_images[$spec_option_value['spec_image']];
					foreach($spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] as $image_key=>$image_id)
					{
						$spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'][$image_key] = $fmt_images[$image_id];
					}
				}
			}
		}
        foreach($image_data_ids as $goods_image)
        {
            $image[$goods_image['image_id']] = $fmt_images[$goods_image['image_id']];
        }

        //获取商品属性
        $props = $rs_goods['props'];
        foreach($props as $p_id=>$value_id)
        {
            $props_value_ids[$p_id] = $value_id['value'];
        }
        $_props_value = $this->app->model('goods_type_props_value');
        $props_value = $_props_value->getList('props_value_id,props_id,name,alias',array('props_value_id'=>$props_value_ids));
        foreach($props_value as $value)
        {
            $fmt_props_value[$value['props_value_id']] = $value;
            $props_ids[$value['props_id']] = $value['props_id'];
        }
        $_props = $this->app->model('goods_type_props');
        $props_sdf = $_props->getList('props_id,name,goods_p',array('props_id'=>$props_ids));
		if(is_array($props_sdf) && count($props_sdf)>0){

			foreach($props_sdf as $pp)
			{
				$fmt_props['p_'.$pp['goods_p']] = $pp;
			}
			foreach($fmt_props as $key=>$value)
			{
				$use_props[$key]['props'] = $fmt_props[$key];
				$use_props[$key]['props_value'] = $fmt_props_value[$props[$key]['value']];
			}	
		}

        //组织数据
        $return['goods_id'] 	= $rs_product['goods_id'];
        $return['product_id'] 	= $rs_product['product_id'];
        $return['product_bn'] 	= $rs_product['bn'];
        $return['unit'] 		= $rs_product['unit'];
        $return['price'] 		= $rs_product['price'];
        $return['mktprice'] 	= $rs_product['mktprice'] ? $rs_product['mktprice'] : $_product->getRealMkt($rs_product['price']);
		
        $return['product_marketable'] 	= $rs_product['marketable'];
        //$return['goods_marketable'] 	= $rs_goods['maketable'];
		 $return['goods_marketable'] 	= $rs_goods['status'];

        $return['title'] = $rs_goods['name'];
        $return['brief'] = $rs_goods['brief'];
		
        $return['type_name'] 	= $rs_type['name'];
        $return['store'] 		= $store;
        $return['cat_name']		= $rs_cat['cat_name'];
        $return['brand'] 		= $rs_brand;
        $return['spec'] 		= $spec;
        $return['promotion'] 	= $promotion;
        $return['props'] 		= $use_props;
        $return['image'] 		= $image;
		
		return $return;
	}


		
    /**
     * 根据货号取得一个货品的详细信息
     * @param String bn
     * @return  array();
     */
    public function get_goods_detail_sku_bn($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
		//-----------------------------------------------
        $obj_products = $this->app->model('products');

        $rs_product = $obj_products->getRow('*',array('bn'=>$params['bn']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		
		//-----------------------------------------------
		$goods_id = intval($rs_product['goods_id']);
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
		$rs_goods = $_goods->dump($goods_id);
        if($rs_goods == null)
		{
			$service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'));
		}

        return $this->get_response_goods_detail($rs_product,$rs_goods);
    }
	
    /**
     * 根据货品ID取得一个货品的详细信息
     * @param String product_id 货品ID
     * @return  array()
     */
    public function get_goods_detail_sku_id($params, &$service)
    {
		$params['product_id'] = intval(''.$params['product_id']);
        if ($params['product_id']<1)
        {
            $service->send_user_error(app::get('b2c')->_('product_id参数为空'), null);
        }

		//-----------------------------------------------
        $obj_products = $this->app->model('products');

        $rs_product = $obj_products->getRow('*',array('product_id'=>$params['product_id']));
        if(count($rs_product) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('没有查询到任何货品的内容'), null);
		}
		//-----------------------------------------------
		$goods_id = $rs_product['goods_id'];
		
		//-----------------------------------------------
        $_goods = $this->app->model('goods');
		$rs_goods = $_goods->dump($goods_id);
        if($rs_goods == null)
		{
			$service->send_user_error(app::get('b2c')->_('未找到该货品对应的商品'));
		}

        return $this->get_response_goods_detail($rs_product,$rs_goods);
    }
	
	
    private function get_promotion_by_goods_id($goods_id)
    {
        $goodsPromotion = kernel::single('b2c_goods_object')->get_goods_promotion($goods_id);
        $productPromotion = array();
        $giftId = array();
        //商品促销
        foreach($goodsPromotion['goods'] as $row){
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            if(key($temp) == 'gift_promotion_solutions_gift'){
                $giftId = array_merge($giftId,$temp['gift_promotion_solutions_gift']['gain_gift']);
                continue;
            }

            if(isset($same_rule[key($temp)]) && $same_rule[key($temp)]){
                continue;
            }else{
                $same_rule[key($temp)] = true; 
            }

            $ruleData = app::get('b2c')->model('sales_rule_goods')->getList('name',array('rule_id'=>$row['rule_id']));
            $productPromotion['goods'][$row['rule_id']]['name'] = $ruleData[0]['name'];
            $productTag = kernel::single(key($temp))->get_desc_tag();
            $productPromotion['goods'][$row['rule_id']]['tag'] = $productTag['name'];
            if(strpos($row['member_lv_ids'],$member_lv_id) === false){
                $productPromotion['goods'][$row['rule_id']]['use'] = 'false';
            }else{
                $productPromotion['goods'][$row['rule_id']]['use'] = 'true';
            }
        }

        //订单促销
        $giftCartObject = kernel::single('gift_cart_object_goods');
        foreach($goodsPromotion['order'] as $row){
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            if(key($temp) == 'gift_promotion_solutions_gift'){
                $gain_gift = $temp['gift_promotion_solutions_gift']['gain_gift'];
                $giftId = array_merge($giftId,$gain_gift);
                if(!$giftCartObject->check_gift($giftId)){
                    continue;
                }
            }
            $productTag = kernel::single(key($row['action_solution']))->get_desc_tag();
            $productPromotion['order'][$row['rule_id']]['name'] = $row['name'];
            $productPromotion['order'][$row['rule_id']]['tag'] = $productTag['name'];
        }
        //赠品
        if($giftId){
            $giftRef = app::get('gift')->model('ref')->getList('*',array('product_id'=>$giftId,'marketable'=>'true'));
            if($giftRef){
                foreach($giftRef as $key=>$row){
                    if($row['marketable'] == 'false') continue;
                    if($row['cat_id']){
                        $giftCat = app::get('gift')->model('cat')->getList('*',array('cat_id'=>$row['cat_id']));
                        if($giftCat[0]['ifpub'] == 'false') continue;
                    }
                    $newGiftId[] = $row['product_id'];
                }
            }

            $aGift = app::get('b2c')->model('products')->getList('goods_id,product_id,name,store,freez',array('product_id'=>$newGiftId,'marketable='=>'true') );
            foreach($aGift as $key=>$row){
                $arrGoodsId[$key] = $row['goods_id'];
                if(is_null($row['store'])){
                    $aGift[$key]['store'] = 999999;
                }
            }
            $image = app::get('b2c')->model('goods')->getList('image_default_id,goods_id,nostore_sell,marketable',array('goods_id'=>$arrGoodsId) );
            sort($image);
            foreach($aGift as $key=>$row){
                if($image[$key]['marketable'] == 'false'){
                    unset($aGift[$key]);continue;
                }
                $aGift[$key]['image_default_id'] = $image[$key]['image_default_id'];
                if($row['nostore_sell']){
                    $aGift[$key]['store'] = 999999;
                }
            }
            $productPromotion['gift'] = $aGift;
        }
        return $productPromotion;
    }

    private function get_images_by_ids($image_ids)
    {
        $obj_image = app::get('image')->model('image');
        $image_from_db = $obj_image->getList('image_id,storage,l_url,m_url,s_url',array('image_id|in'=>$image_ids));
        foreach($image_from_db as $imageRow)
        {
            $image_id = $imageRow['image_id'];
            $fmt_image[$image_id]['image_id'] = $image_id;
            $fmt_image[$image_id]['s_url'] = $imageRow['s_url'] ? $imageRow['s_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['s_url'] &&!strpos($fmt_image[$image_id]['s_url'],'://')){
                $fmt_image[$image_id]['s_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['s_url'];
            }
            $fmt_image[$image_id]['m_url'] = $imageRow['m_url'] ? $imageRow['m_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['m_url'] &&!strpos($fmt_image[$image_id]['m_url'],'://')){
                $fmt_image[$image_id]['m_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['m_url'];
            }
            $fmt_image[$image_id]['l_url'] = $imageRow['l_url'] ? $imageRow['l_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['l_url'] &&!strpos($fmt_image[$image_id]['l_url'],'://')){
                $fmt_image[$image_id]['l_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['l_url'];
            }
        } 
        return $fmt_image;
    }
	
	

}
