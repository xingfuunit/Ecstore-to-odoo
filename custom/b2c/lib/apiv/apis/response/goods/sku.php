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
class b2c_apiv_apis_response_goods_sku
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
     * 根据筛选条件取得货品列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_sku_list($params, &$service)
    {
        $sdf['page_no'] 	= $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] 	= $sdf['page_size'] ? $sdf['page_size'] : '20';
		
		$_model = app::get('b2c')->model('products');
		
		$pager	= $this->get_pager($sdf['page_no'], $sdf['page_size'], $_model->table_name(1));
		
		$items 	= array();
		if( $pager['rs_count']>0 ){
			
			$rows = $_model->getList('*',array(),$pager['offset'],$pager['limit']);

			if ($rows && is_array($rows)){
				foreach ($rows as $index=>$rs){
					$items[$index] = array(
						'product_id'	=> $rs['product_id'],
						'goods_id'		=> $rs['goods_id'],
						'bn'			=> $rs['bn'],
						'name'			=> $rs['name'],
						'price'			=> $rs['price'],
						'mktprice'		=> $rs['mktprice'],
						'cost'			=> $rs['cost'],
						'specs'			=> $this->get_sku_specs($rs['spec_desc']),
						'store'			=> $rs['store'],
						'weight'		=> $rs['weight'],
						'goods_type'	=> $rs['goods_type'],
						'last_modify'	=> $rs['last_modify']
					);
				}
			}	
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
	 * 根据products.spec_desc，返回 spec数组
	 * 
     * @param arr_spec_desc 		products.spec_desc
	 * @return array
	 */
	private function get_sku_specs($arr_spec_desc)
	{
        /** 组成返回数组 **/
        $spacs = array();
        if($arr_spec_desc['spec_value_id'])
        {
            foreach ($arr_spec_desc['spec_value_id'] as $spec_id_key => $arr_value){
					$spacs[] = array(
						'spec_id'		=> ''.$spec_id_key,
						'spec_value_id'	=> ''.$arr_value,
						'spec_value'	=> $arr_spec_desc['spec_value'] [$spec_id_key],
					);
			}
        }

		return $spacs;
	}
	
    /**
     * 根据货号取得一个货品的基础信息
     * @param String bn
     * @return  array();
     */
    public function get_sku_base($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('bn参数为空'), null);
        }
		
		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs = $_products->getRow('*',array('bn'=>$params['bn']));
        if(count($rs) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		
        return array(
            'product_id'	=> $rs['product_id'],
            'goods_id'		=> $rs['goods_id'],
            'bn'			=> $rs['bn'],
            'name'			=> $rs['name'],
            'price'			=> $rs['price'],
            'mktprice'		=> $rs['mktprice'],
            'cost'			=> $rs['cost'],
            'specs'			=> $this->get_sku_specs($rs['spec_desc']),
            'store'			=> $rs['store'],
            'weight'		=> $rs['weight'],
            'goods_type'	=> $rs['goods_type'],
            'last_modify'	=> $rs['last_modify']
        );
    }
	
    /**
     * 根据货品ID取得一个货品的基础信息
     * @param String product_id 货品ID
     * @return  array()
     */
    public function get_sku_base_id($params, &$service)
    {
		$params['product_id'] = intval(''.$params['product_id']);
        if ($params['product_id']<1)
        {
            $service->send_user_error(app::get('b2c')->_('product_id参数为空'), null);
        }

		//-----------------------------------------------
        $_products = $this->app->model('products');

        $rs = $_products->getRow('*',array('product_id'=>$params['product_id']));
        if(count($rs) == 0)
		{
			$service->send_user_error(app::get('b2c')->_('该货品不存在'), null);
		}
		
        return array(
            'product_id'	=> $rs['product_id'],
            'goods_id'		=> $rs['goods_id'],
            'bn'			=> $rs['bn'],
            'name'			=> $rs['name'],
            'price'			=> $rs['price'],
            'mktprice'		=> $rs['mktprice'],
            'cost'			=> $rs['cost'],
            'specs'			=> $this->get_sku_specs($rs['spec_desc']),
            'store'			=> $rs['store'],
            'weight'		=> $rs['weight'],
            'goods_type'	=> $rs['goods_type'],
            'last_modify'	=> $rs['last_modify']
        );
    }
	
    /**
     * 新增货品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function add_sku($params, &$service)
    {
        if (!isset($params['goods_bn']) || strlen($params['goods_bn'])==0){
            $service->send_user_error(app::get('b2c')->_('商品编号为空！'), null);
        }
		
        if (!isset($params['bn']) || strlen($params['bn'])==0){
            $service->send_user_error(app::get('b2c')->_('货品货号为空！'), null);
        }
		
		$params['spec_id'] = intval(''.$params['spec_id']);
        if ($params['spec_id']<0) {
            $service->send_user_error(app::get('b2c')->_('规格id为空！'), null);
        }

		$params['spec_value_id'] = intval(''.$params['spec_value_id']);
        if ($params['spec_value_id']<0){
            $service->send_user_error(app::get('b2c')->_('规格值id为空！'), null);
        }

		//--------------------------------------------------
		//检查 goods 是否存在
        $_goods 	= $this->app->model('goods');
		$rs_goods  	= $_goods->getRow('goods_id,name,type_id', array('bn'=>$params['goods_bn']));

		if(!is_array($rs_goods) || count($rs_goods)==0){
			$msg = app::get('b2c')->_('没有查找到商品id的资料！'.$params['goods_id']);
			$service->send_user_error($msg, null);
		}
		
		//--------------------------------------------------
		//检查 bn 是否存在
		$_products	= $this->app->model('products');
		$tmp  = $_products->getRow('product_id', array('bn' => $params['bn']));
		if($tmp){
			$msg = app::get('b2c')->_('该bn已存在'.$params['bn']);
            $service->send_user_error($msg, null);
		}
		//--------------------------------------------------
		//检查 规格
		$specs = $this->get_spec_desc($params['spec_id'],$params['spec_value_id'],$params['goods_id']);

		if(strlen($specs['msg'])>0){
            $service->send_user_error(app::get('b2c')->_($specs['msg']), null);
		}

		//--------------------------------------------------
		$params['store'] = intval(''.$params['store']);
        if ($params['store']<0){
            $params['store'] = 0;
        }
		
		$params['price'] = floatval(''.$params['price']);
        if ($params['price']<0){
            $params['price'] = 0;
        }

		$params['cost'] = floatval(''.$params['cost']);
        if ($params['cost']<0){
            $params['cost'] = 0;
        }
		
		$params['mktprice'] = floatval(''.$params['mktprice']);
        if ($params['mktprice']<0){
            $params['mktprice'] = 0;
        }

		
		$params['barcode'] = ''.$params['barcode'];

		$params['weight'] = floatval(''.$params['weight']);
        if ($params['weight']<0){
            $params['weight'] = 0;
        }
		
		$params['unit'] = ''.$params['unit'];
		
		$params['marketable'] = intval(''.$params['marketable']);

		//--------------------------------------------------
        $save_data = array();

		$params['marketable'] = ($params['marketable']==1)?'true':'false';
		
		$time = time();
		//-------------------------------------------
        $save_data = array(
            'goods_id' 		=> $params['goods_id'],
            'barcode' 		=> $params['barcode'],
            'title' 		=> $params['title'],
            'bn' 			=> $params['bn'],
			
            'price' 		=> $params['price'],
            'cost' 			=> $params['cost'],
            'mktprice' 		=> $params['mktprice'],
			
            'name' 			=> $rs_goods['name'],
            'weight' 		=> $params['weight'],
            'unit' 			=> $params['unit'],
			
            'store' 		=> $params['store'],
            'store_place' 	=> '0',
            'freez' 		=> '0',
			
            'goods_type' 	=> 'normal',		//enum('normal','bind','gift')
			
            'spec_info' 	=> $specs['spec_info'],
            'spec_desc' 	=> $specs['spec_desc'],
			
            'is_default' 	=> 'false',			//enum('true','false')
			
            'qrcode_image_id' 	=> '',
			
            'uptime' 		=> $time,
            'last_modify' 	=> $time,
			
            'disabled' 		=> 'false',			//enum('true','false')
            'marketable' 	=> $params['marketable']
        );

		//-----------------------------------------
        $product_id = $_products->insert($save_data);
        if( $product_id ){
			//-----------------------------------------
			//同步更新 goods_spec_index
			$_goods_spec_index	= $this->app->model('goods_spec_index');
			
			$save_goods_spec_index = array(
				'type_id' 		=> $rs_goods['type_id'],
				'spec_id' 		=> $params['spec_id'],
				'spec_value_id' => $params['spec_value_id'],
				'goods_id' 		=> $params['goods_id'],
				'product_id' 	=> $product_id,
				'last_modify' 	=> $time
			);
			$_goods_spec_index->insert($save_goods_spec_index);
			

			//-----------------------------------------
			//同步更新 goods.spec_desc
			$this->update_goods_spec_desc($params['goods_id']);
			
			//同步更新 goods.store
			$this->update_goods_store($params['goods_id']);
			
			//-----------------------------------------
			//同步更新商品二维码
			kernel::single('weixin_qrcode')->update_goods_qrcode($params['goods_id']);
			
			//-----------------------------------------
            return array(
				'goods_id' 		=> ''.$params['goods_id'],
				'product_id' 	=> ''.($product_id),
				'bn'			=> $params['bn'],
				'time' 			=> date('Y-m-d H:i:s',$time),
			);
        }else{
			$service->send_user_error(app::get('b2c')->_('保存出错'), null);
        }
    }
	
	/**
	 * 根据规格id和规格值id，返回 
	 * array(
	 * 		'spec_info'	=> '规格名：规格值名称'
	 * 		'spec_desc'	=> json
	 * 		'msg'		=> '信息'
	 * );
	 * 
     * @param spec_id 			规格
     * @param spec_value_id		规格值id
     * @param goods_id			商品id
     * @param product_id		货品id
	 * @return array
	 */
	private function get_spec_desc($spec_id, $spec_value_id, $goods_id, $product_id = 0)
	{
		$ret = array('msg'=>'');
		
		$_specification = $this->app->model('specification');
		$_spec_value = $this->app->model('spec_values');
		
		$rs_specification  	= $_specification->getRow('spec_name', array('spec_id'=>$spec_id));
		if(!$rs_specification){
			$ret['msg'] = '规格不存在';
            return $ret;
		}
		
		$rs_spec_value = $_spec_value->getRow('spec_value,spec_image', array('spec_id'=>$spec_id,'spec_value_id'=>$spec_value_id));
		if (!$rs_spec_value){
			$ret['msg'] = '规格值和规格不相对应';
            return $ret;
		}
		
		//--------------------------------------------------
		//检查 规格id是否和商品绑定了，不然就是商品的规格和货品的规格不对
		$_goods_spec_index	= $this->app->model('goods_spec_index');
		

		//--------------------------------------------------
		//检查 规格是否重复
		$where = array(
			'goods_id' => $goods_id,
			'spec_value_id' => $spec_value_id
		);
		
		if($product_id>0){
			$where['product_id|noequal'] = $product_id;
		}
		
		$tmp  = $_goods_spec_index->getRow('product_id', $where);
		if($tmp){
			$ret['msg'] = '该商品已经存在这个规格的货品,spec_value_id='.$spec_value_id;
            return $ret;
		}
		
		//--------------------------------------------------
        $arr_spec_value[$spec_id] 		= $rs_spec_value['spec_value'];
        $arr_spec_value_id[$spec_id] 	= $spec_value_id;
        $arr_private_value_id[$spec_id] = time().$spec_value_id;
/*

products.spec_desc

Array
(
    [spec_value] => Array
        (
            [124] => 测试规格
        )

    [spec_private_value_id] => Array
        (
            [124] => 1436347366860
        )

    [spec_value_id] => Array
        (
            [124] => 860
        )
)
*/
		
		$ret['spec_info'] = $rs_specification['spec_name'] .'：'.$rs_spec_value['spec_value'];
		$ret['spec_desc'] = array(
			'spec_private_value_id' => $arr_private_value_id,
			'spec_value' 			=> $arr_spec_value,
			'spec_value_id' 		=> $arr_spec_value_id
		);
		

		$ret['spec_desc'] = $ret['spec_desc'];
		return $ret;
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
		$data['store'] = intval($rs[0]);

		$_goods = $this->app->model('goods');
		$_goods->update($data, array('goods_id' => $goods_id));

		return $store;
	}
	
    /**
     * 新增货品 和 修改货品后，同步更新 goods.spec_desc 字段
     * @param goods_id 
     * @return
     */
	private function update_goods_spec_desc($goods_id)
	{
		if(intval($goods_id)<1){return;}
/*
goods.spec_desc
Array
(
    [66] => Array
        (
            [14153325871] => Array
                (
                    [private_spec_value_id] => 14153325871
                    [spec_value] => 250g/袋
                    [spec_value_id] => 470
                    [spec_image] => 
                    [spec_goods_images] => 
                )

            [14153325882] => Array
                (
                    [private_spec_value_id] => 14153325882
                    [spec_value] => 500g/2袋
                    [spec_value_id] => 471
                    [spec_image] => 
                    [spec_goods_images] => 
                )

        )

)
 * */
		$spec_goods = array();
		$_products	= $this->app->model('products');
		$rows = $_products->getList('spec_desc', array('goods_id'=>$goods_id));
		if(is_array($rows) && count($rows)>0){
			foreach ($rows as $i=>$rs){
				if(strlen(''.$rs['spec_desc'])>0){
					if(is_string($rs['spec_desc'])){
						$spec_products = unserialize($rs['spec_desc']);	
					}else{
						$spec_products = $rs['spec_desc'];	
					}

					
					if(is_array($spec_products['spec_value'])){
						foreach ($spec_products['spec_value'] as $k=>$v){
							$spec_goods[$k] = array($spec_products['spec_private_value_id'][$k]=>array(
								'private_spec_value_id' => $spec_products['spec_private_value_id'][$k],
								'spec_value' 			=> $spec_products['spec_value'][$k],
								'spec_value_id' 		=> $spec_products['spec_value_id'][$k],
								'spec_image'			=> '',
								'spec_goods_images'		=> '',
							));
						}
					}
				}
			}
		}
		
		$_goods = $this->app->model('goods');
		$_goods->update(array('spec_desc' => $spec_goods), array('goods_id'=>$goods_id));
		
	}
	
    /**
     * 编辑货品
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回增加的结果
     */
    public function update_sku($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0){
            $service->send_user_error(app::get('b2c')->_('货品货号为空！'), null);
        }
		
		$params['spec_id'] = intval(''.$params['spec_id']);
        if ($params['spec_id']<0) {
            $service->send_user_error(app::get('b2c')->_('规格id为空！'), null);
        }

		$params['spec_value_id'] = intval(''.$params['spec_value_id']);
        if ($params['spec_value_id']<0){
            $service->send_user_error(app::get('b2c')->_('规格值id为空！'), null);
        }


		//--------------------------------------------------
		//检查 货品 是否存在
		$_products	= $this->app->model('products');
		$rs_products  = $_products->getRow('*', array('bn' => $params['bn']));
		if(!$rs_products){
			$msg = app::get('b2c')->_('该货品不存在'.$params['bn']);
            $service->send_user_error($msg, null);
		}
		
		//--------------------------------------------------
		//检查 bn 是否重复
		if($rs_products['bn'] != $params['bn']){
			$tmp  = $_products->getRow('product_id', 
				array(
				'bn' => $params['bn'],
				'product_id|noequal' => $params['product_id'],
				)
			);
			if($tmp){
				$msg = app::get('b2c')->_('该bn已存在'.$params['bn']);
				$service->send_user_error($msg, null);
			}	
		}


		//--------------------------------------------------
		//检查 goods 是否存在
        $_goods 	= $this->app->model('goods');
		$rs_goods  	= $_goods->getRow('goods_id,name,type_id', array('goods_id'=>$rs_products['goods_id']));

		if(!is_array($rs_goods) || count($rs_goods)==0){
			$msg = app::get('b2c')->_('没有查找到相关商品的资料！'.$rs_products['goods_id']);
			$service->send_user_error($msg, null);
		}
		
		//--------------------------------------------------
		$save_data = array();

		//检查 规格是否要修改
		$specs = $this->get_spec_desc($params['spec_id'],$params['spec_value_id'],$params['goods_id'],$rs_products['product_id']);
		if(strlen($specs['msg'])>0){
			$service->send_user_error(app::get('b2c')->_($specs['msg']), null);
		}

		$save_data['spec_info'] = $specs['spec_info'];
		$save_data['spec_desc'] = $specs['spec_desc'];

		//--------------------------------------------------
		if(isset($params['price'])){
			$save_data['price'] = floatval(''.$params['price']);
			if ($save_data['price']<0){
				$save_data['price'] = 0;
			}	
		}
		
		if(isset($params['cost'])){
			$save_data['cost'] = floatval(''.$params['cost']);
			if ($save_data['cost']<0){
				$save_data['cost'] = 0;
			}	
		}
		
		if(isset($params['mktprice'])){
			$save_data['mktprice'] = floatval(''.$params['mktprice']);
			if ($save_data['mktprice']<0){
				$save_data['mktprice'] = 0;
			}	
		}
		
		if(isset($params['barcode'])){
			$save_data['barcode'] = ''.$params['barcode'];
		}
		
		if(isset($params['weight'])){
			$save_data['weight'] = floatval(''.$params['weight']);
			if ($save_data['weight']<0){
				$save_data['weight'] = 0;
			}	
		}
		
		if(isset($params['unit'])){
			$save_data['unit'] = ''.$params['unit'];
		}
		
		if(isset($params['marketable'])){
			$params['marketable'] 		= intval(''.$params['marketable']);
			$save_data['marketable']  	= ($params['marketable']==1)?'true':'false';
		}
		
		//--------------------------------------------------
		$time = time();
		$save_data['last_modify']  	= $time;

		//--------------------------------------------------
        $save = $_products->update($save_data,array(
			'product_id'=>$rs_products['product_id'] 
		));
		
        if( $save ){
			//-----------------------------------------
			//同步更新 goods_spec_index
			$_goods_spec_index	= $this->app->model('goods_spec_index');
			
			$save_goods_spec_index = array(
				'spec_id' 		=> $params['spec_id'],
				'spec_value_id' => $params['spec_value_id'],
				'last_modify' 	=> $time
			);
			
			$_goods_spec_index->update($save_goods_spec_index, array(
				'goods_id'=>$rs_goods['goods_id'],
				'product_id'=>$rs_products['product_id'] 
			));
			

			//-----------------------------------------
			//同步更新 goods.spec_desc
			$this->update_goods_spec_desc($rs_goods['goods_id']);

			//--------------------------------------------------
			#sphinx delta
			if(kernel::single('b2c_search_goods')->is_search_status()){
				$delta = array('id'=>$goods_id,'index_name'=>'b2c_goods');
				app::get('search')->model('delta')->save($delta);
			}

			//-----------------------------------------
            return array(
				'goods_id' 		=> ''.$rs_goods['goods_id'],
				'product_id' 	=> ''.($rs_products['product_id']),
				'bn'			=> $params['bn'],
				'time' 			=> date('Y-m-d H:i:s',$time),
			);
        }else{
			$service->send_user_error(app::get('b2c')->_('保存出错'), null);
        }
    }

	
    /**
     * 删除货品信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function del_sku($params, &$service)
    {
        if (!isset($params['bn']) || strlen($params['bn'])==0)
        {
            $service->send_user_error(app::get('b2c')->_('货品bn为空！'), null);
        }
		
		//--------------------------------------------------
		//检查 货品 是否存在
		$_products	= $this->app->model('products');
		$rs_products  = $_products->getRow('product_id,goods_id,bn', array('bn' => $params['bn']));
		if(!$rs_products){
			$msg = app::get('b2c')->_('该货品不存在'.$params['bn']);
            $service->send_user_error($msg, null);
		}
		
		//--------------------------------------------------
		//执行删除操作
		$product_id = $rs_products['product_id'];
		$goods_id = $rs_products['goods_id'];
		
		
		$filter['product_id'] = $product_id;

		//--------------------------------------------------
		//删除 货品 记录

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $is_delete = $_products->delete($filter);

        if (!$is_delete)
        {
            $db->rollback();
            $service->send_user_error(app::get('b2c')->_('删除货品失败'),null);
        }

        $db->commit($transaction_status);
		

		//--------------------------------------------------
		//同步删除 goods_spec_index 记录
		$oSpecIndex = $this->app->model('goods_spec_index');
		$oSpecIndex->delete($filter);
		
		//--------------------------------------------------
		//同步更新 goods.spec_desc
		$this->update_goods_spec_desc($goods_id);
		
		//--------------------------------------------------
		//同步更新 goods.store
		$this->update_goods_store($goods_id);
			
		//--------------------------------------------------
        #sphinx delta
        if(kernel::single('b2c_search_goods')->is_search_status()){
            $delta = array('id'=>$goods_id,'index_name'=>'b2c_goods');
            app::get('search')->model('delta')->save($delta);
        }
		
		//--------------------------------------------------
		//清空缓存
		base_kvstore::instance('b2c_goods')->delete('b2c_goods_'.$goods_id);
		
		//-----------------------------------------
		return array(
			'product_id' 	=> strval($product_id),
			'bn'			=> $rs_products['bn'],
			'time' 			=> date('Y-m-d H:i:s',time()),
		);
	}
	
}
