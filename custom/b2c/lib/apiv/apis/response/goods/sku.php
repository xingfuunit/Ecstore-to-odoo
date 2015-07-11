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
			
			$rows = $_model->getList('spec_id,spec_name,spec_memo,p_order,disabled',array(),$pager['offset'],$pager['limit']);

			if ($rows && is_array($rows)){
				foreach ($rows as $index=>$rs){
					$items[$index] = array(
						'spec_id'	=> $rs['spec_id'],
						'spec_name'	=> $rs['spec_name'],
						'spec_memo'	=> $rs['spec_memo'],
						'p_order'	=> $rs['p_order'],
						'disabled'	=> $rs['disabled']=='true'?'1':'0'
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

}
