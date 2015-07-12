<?php

class b2c_apiv_apis_response_goods_specs{

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
     * 取得规格列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_spec_list(&$sdf, &$thisObj)
    {
        $sdf['page_no'] 	= $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] 	= $sdf['page_size'] ? $sdf['page_size'] : '20';
		
		$_model = app::get('b2c')->model('specification');
		
		$pager	= $this->get_pager($sdf['page_no'], $sdf['page_size'], $_model->table_name(1));
		
		$items 	= array();
		if( $pager['rs_count']>0 ){
			
			$rows = $_model->getList('spec_id,spec_name,spec_memo,p_order,disabled',array(),$pager['offset'],$pager['limit']);
				
			/*
			$sql  = 'SELECT `spec_id`,`spec_name`,`spec_memo`,`p_order`,`disabled` FROM `sdb_b2c_specification` '.$pager['str_limit'];
			$db = kernel::database();
			$rows = $db->select($sql);
			*/
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
     * 取得一个规格的信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_spec_item(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }
		
		$item 	= array();
		
		$_model = app::get('b2c')->model('specification');
		$rs  = $_model->getRow('spec_id,spec_name,spec_memo,p_order,disabled', array('spec_id' => $sdf['spec_id']));
		if ($rs && is_array($rs)){
			$item = array(
				'spec_id'	=> $rs['spec_id'],
				'spec_name'	=> $rs['spec_name'],
				'spec_memo'	=> $rs['spec_memo'],
				'p_order'	=> $rs['p_order'],
				'disabled'	=> $rs['disabled']=='true'?'1':'0'
			);
		}

        return $item;
	}
	
    /**
     * 取得一个规格的信息
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_spec_item_value(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }
		
		return $this->get_spec_item_value2($sdf['spec_id']);
	}
	
	/**
	 * 根据
	 * @return int
	 */
	private function get_spec_item_value2($spec_id)
	{
		$items 	= array();
		
		/*
		$sql  = 'SELECT `spec_value_id`,`spec_id`,`spec_value`,`p_order` FROM `sdb_b2c_spec_values` where `spec_id`='.$spec_id;
		$db = kernel::database();
		$rows = $db->select($sql);
		*/
	
		$_model = app::get('b2c')->model('spec_values');
		$rows = $_model->getList('spec_value_id,spec_id,spec_value,p_order',array('spec_id'=>$spec_id));
			
		if ($rows && is_array($rows)){
			foreach ($rows as $index=>$rs){
				$items[$index] = array(
					'spec_value_id'	=> $rs['spec_value_id'],
					'spec_id'		=> $rs['spec_id'],
					'spec_value'	=> $rs['spec_value'],
					'p_order'		=> $rs['p_order']
				);
			}
		}
		
        return $items;	
	}
	
    /**
     * 取得一个规格的信息，包括所属的全部规格值
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_spec_info(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }
		
		$item 	= array();
		$_model = app::get('b2c')->model('specification');
		$rs  = $_model->getRow('spec_id,spec_name,spec_memo,p_order,disabled', array('spec_id' => $sdf['spec_id']));
		if ($rs && is_array($rs)){
			
			$item = array(
				'spec_id'	=> $rs['spec_id'],
				'spec_name'	=> $rs['spec_name'],
				'spec_memo'	=> $rs['spec_memo'],
				'p_order'	=> $rs['p_order'],
				'disabled'	=> $rs['disabled']=='true'?'1':'0',
				'items'		=> $this->get_spec_item_value2($rs['spec_id'])
			);
		}

        return $item;
	}
	
    /**
     * 新增一个规格
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function add_spec(&$sdf, &$thisObj)
    {
        if (!isset($sdf['spec_name']) || strlen($sdf['spec_name'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格名称为空！'), null);
        }
		
        if (!isset($sdf['spec_memo']) || strlen($sdf['spec_memo'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格备注为空！'), null);
        }
		
		//-------------------------------------------
        $_model = app::get('b2c')->model('specification');
		
		//检查备注是否重复
		$tmp  = $_model->getRow('spec_id', array('spec_memo' => $sdf['spec_memo']));
		if($tmp){
            $thisObj->send_user_error(app::get('b2c')->_('规格备注已经存在，请检查！'), null);
		}

		//-------------------------------------------
        $sdf['p_order'] 	= $sdf['p_order'] ? $sdf['p_order'] : '1';
        $sdf['p_order'] 	= intval($sdf['p_order']);

		
		//-------------------------------------------
		if(isset($sdf['disabled'])){
			$sdf['disabled'] = intval($sdf['disabled'])==1?'true':'false';
		}else{
			$sdf['disabled'] = 'false';
		}
		//-------------------------------------------
        $save_data = array(
            'spec_name' 		=> $sdf['spec_name'],
            'spec_show_type' 	=> 'flat',
            'spec_type' 		=> 'text',
            'spec_memo' 		=> $sdf['spec_memo'],
            'p_order' 			=> $sdf['p_order'],
            'disabled' 			=> $sdf['disabled'],
            'alias' 			=> ''
        );
		

        $rs = $_model->insert($save_data);
        if( $rs ){
            return array(
				'spec_id' => $rs,
				'time' => date('Y-m-d H:i:s',time()),
			);
        }else{
			$thisObj->send_user_error(app::get('b2c')->_('保存出错'), null);
        }	
	}
	
    /**
     * 修改一个规格
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function update_spec(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }
		
        if (!isset($sdf['spec_name']) || strlen($sdf['spec_name'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格名称为空！'), null);
        }
		
        if (!isset($sdf['spec_memo']) || strlen($sdf['spec_memo'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格备注为空！'), null);
        }
		
		//-------------------------------------------
        $_model = app::get('b2c')->model('specification');
		
		$old  = $_model->getRow('*', array('spec_id'=>$sdf['spec_id']));
		if(!$old){
            $thisObj->send_user_error(app::get('b2c')->_('规格不存在，请检查！'), null);
		}
		
		/*
		 * 检查备注是否重复
		 * 
		$tmp  = $_model->getRow('spec_id', array(
			'spec_memo'			=> $sdf['spec_memo'],
			'spec_id|noequal'	=> $sdf['spec_id'] 
		));
		if($tmp){
            $thisObj->send_user_error(app::get('b2c')->_('规格备注已经存在，请检查！'), null);
		}
		*/
		//-------------------------------------------
		if(isset($sdf['p_order']) && intval($sdf['p_order'])>0){
			$old['p_order'] = intval($sdf['p_order']);
		}
		
		//-------------------------------------------
		if(isset($sdf['disabled'])){
			$old['disabled'] = intval($sdf['disabled'])==1?'true':'false';
		}
		//-------------------------------------------
        $save_data = array(
            'spec_name' 		=> $sdf['spec_name'],
            'spec_show_type' 	=> $old['spec_show_type'],
            'spec_type' 		=> $old['spec_type'],
            'spec_memo' 		=> $sdf['spec_memo'],
            'p_order' 			=> $old['p_order'],
            'disabled' 			=> $old['disabled'],
            'alias' 			=> $old['alias']
        );
		
        $rs = $_model->update($save_data,array(
			'spec_id'=>$sdf['spec_id'] 
		));
		
        if( $rs ){
            return array(
				'spec_id' => ''.$sdf['spec_id'],
				'time' => date('Y-m-d H:i:s',time()),
			);
        }else{
			$thisObj->send_user_error(app::get('b2c')->_('保存出错'), null);
        }
	}
	
    /**
     * 删除一个规格（会把相关规格值也一起删除）
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function del_spec(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
		
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }

		//-------------------------------------------
		if (app::get('b2c')->model('goods_spec_index')->dump(array('spec_id'=>$sdf['spec_id'])))
		{
            $thisObj->send_user_error(app::get('b2c')->_('规格值已被商品使用'), null);
		}

		//-------------------------------------------
        $_model = app::get('b2c')->model('specification');
		$rs  = $_model->getRow('spec_id', array('spec_id'=>$sdf['spec_id']));
		if ($rs && is_array($rs))
		{
			if($_model->delete(array('spec_id'=>$sdf['spec_id']))){

				$_model2 = app::get('b2c')->model('spec_values');
				$_model2->delete(array('spec_id'=>$sdf['spec_id']));
					
				return array(
					'spec_id' => ''.$sdf['spec_id'],
					'time' => date('Y-m-d H:i:s',time()),
				);
			}else{
				$thisObj->send_user_error(app::get('b2c')->_('sql执行出错'), null);
			}
		}else{
			$thisObj->send_user_error(app::get('b2c')->_('没有相关数据'), null);
		}

	}
	
    /**
     * 取得规格值列表
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_specvalue_list(&$sdf, &$thisObj)
    {
        $sdf['page_no'] 	= $sdf['page_no'] ? $sdf['page_no'] : '1';
        $sdf['page_size'] 	= $sdf['page_size'] ? $sdf['page_size'] : '20';
		
		$_model = app::get('b2c')->model('spec_values');
		$pager	= $this->get_pager($sdf['page_no'], $sdf['page_size'], $_model->table_name(1));
		
		$items 	= array();
		if( $pager['rs_count']>0 ){
			$rows = $_model->getList('spec_value_id,spec_id,spec_value,p_order',array(),$pager['offset'],$pager['limit']);
			
			/*
			$sql  = 'SELECT `spec_value_id`,`spec_id`,`spec_value`,`p_order` FROM `sdb_b2c_spec_values` '.$pager['str_limit'];
			$db = kernel::database();
			$rows = $db->select($sql);
			*/
			if ($rows && is_array($rows)){
				foreach ($rows as $index=>$rs){
					$items[$index] = array(
						'spec_value_id'	=> $rs['spec_value_id'],
						'spec_id'		=> $rs['spec_id'],
						'spec_value'	=> $rs['spec_value'],
						'p_order'	=> $rs['p_order'],
					);
				}
			}	
		}


        return array(
			'rscount'	=> $pager['rs_count'],
			'pageno'	=> $pager['page_no'],
			'pageszie'	=> $pager['page_size'],
			'pagecount'	=> $pager['page_count'],
			'items'		=> $items,
		);
    }
	

	
    /**
     * 取得一个规格对应的全部规格值
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function get_specvalue_item(&$sdf, &$thisObj)
    {
		$sdf['spec_value_id'] = intval(''.$sdf['spec_value_id']);
        if ($sdf['spec_value_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格值编码为空！'), null);
        }
		
		$item 	= array();
		
			
		$_model = app::get('b2c')->model('spec_values');
		$rs = $_model->getRow('spec_value_id,spec_id,spec_value,p_order',array('spec_value_id'=>$sdf['spec_value_id']));
		
		/*
		$sql  = 'SELECT `spec_value_id`,`spec_id`,`spec_value`,`p_order` FROM `sdb_b2c_spec_values` where `spec_value_id`='.$sdf['spec_value_id'];
		$db = kernel::database();
		$rs = $db->selectrow($sql);
		*/
		if ($rs && is_array($rs)){
			$item = array(
				'spec_value_id'	=> $rs['spec_value_id'],
				'spec_id'		=> $rs['spec_id'],
				'spec_value'	=> $rs['spec_value'],
				'p_order'		=> $rs['p_order'],
			);
		}


        return $item;
	}
	
		
    /**
     * 新增一个规格值
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function add_specvalue(&$sdf, &$thisObj)
    {
		$sdf['spec_id'] = intval(''.$sdf['spec_id']);
        if ($sdf['spec_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格编码为空！'), null);
        }
		
        if (!isset($sdf['spec_value']) || strlen($sdf['spec_value'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格值为空！'), null);
        }
		

		//-------------------------------------------
        $_model = app::get('b2c')->model('spec_values');

		$tmp  = $_model->getRow('spec_value_id', array(
			'spec_value'=>$sdf['spec_value'],
			'spec_id'	=>$sdf['spec_id']
		));
		if($tmp){
            $thisObj->send_user_error(app::get('b2c')->_('规格值已经存在，请检查！'), null);
		}

		//-------------------------------------------
		//注意：规格值，必须 p_order 字段有值，并检查不能重复，所以不能通过接口传，只能程序自增
		$sql  = 'SELECT max(`p_order`) as c FROM `sdb_b2c_spec_values` where `spec_id`='.$sdf['spec_id'];
		$max = kernel::database()->selectrow($sql);
		if($max){
			$sdf['p_order'] = intval(''.$max['c'])+1;
		}else{
			$sdf['p_order'] = 1;
		}

		//-------------------------------------------
        $save_data = array(
            'spec_id' 		=> $sdf['spec_id'],
            'spec_value' 	=> $sdf['spec_value'],
            'alias' 		=> '',
            'spec_image' 	=> '',
            'p_order' 		=> $sdf['p_order']
        );

        $rs = $_model->insert($save_data);
        if( $rs ){
            return array(
				'spec_value_id' => $rs,
				'time' => date('Y-m-d H:i:s',time()),
			);
        }else{
			$thisObj->send_user_error(app::get('b2c')->_('保存出错'), null);
        }
	}
	
	
	
    /**
     * 修改一个规格值
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function update_specvalue(&$sdf, &$thisObj)
    {
		$sdf['spec_value_id'] = intval(''.$sdf['spec_value_id']);
        if ($sdf['spec_value_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格值编码为空！'), null);
        }

        if (!isset($sdf['spec_value']) || strlen($sdf['spec_value'])==0)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格值为空！'), null);
        }
		
		//-------------------------------------------
        $_model = app::get('b2c')->model('spec_values');
		
		$old  = $_model->getRow('*', array('spec_value_id'=>$sdf['spec_value_id']));
		if(!$old){
            $thisObj->send_user_error(app::get('b2c')->_('规格值不存在，请检查！'), null);
		}
		$spec_id = intval($old['spec_id']);
		
		$tmp  = $_model->getRow('spec_value_id', array(
			'spec_value'	=> $sdf['spec_value'],
			'spec_id'		=> $sdf['spec_id'],
			'spec_value_id|noequal'=>$sdf['spec_value_id'] 
		));
		if($tmp){
            $thisObj->send_user_error(app::get('b2c')->_('规格值已经存在，请检查！'), null);
		}

		//-------------------------------------------
        $save_data = array(
            'spec_id' 		=> $old['spec_id'],
            'spec_value' 	=> $sdf['spec_value'],
            'alias' 		=> $old['alias'],
            'spec_image' 	=> $old['spec_image']
        );
		
        $rs = $_model->update($save_data,array(
			'spec_value_id'=>$sdf['spec_value_id'] 
		));
		
        if( $rs ){
            return array(
				'spec_value_id' => ''.$sdf['spec_value_id'],
				'time' => date('Y-m-d H:i:s',time()),
			);
        }else{
			$thisObj->send_user_error(app::get('b2c')->_('保存出错'), null);
        }
	}
	
	
    /**
     * 删除一个规格值
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回结果
     */
    public function del_specvalue(&$sdf, &$thisObj)
    {
		$sdf['spec_value_id'] = intval(''.$sdf['spec_value_id']);
        if ($sdf['spec_value_id']<1)
        {
            $thisObj->send_user_error(app::get('b2c')->_('规格值编码为空！'), null);
        }
		
		//-------------------------------------------
		if (app::get('b2c')->model('goods_spec_index')->dump(array('spec_value_id'=>$sdf['spec_value_id'])))
		{
            $thisObj->send_user_error(app::get('b2c')->_('规格值已被商品使用'), null);
		}

		//-------------------------------------------
        $_model = app::get('b2c')->model('spec_values');
		$rs  = $_model->getRow('spec_value_id', array('spec_value_id'=>$sdf['spec_value_id']));
		if ($rs && is_array($rs))
		{
			if($_model->delete(array('spec_value_id'=>$sdf['spec_value_id']))){

				return array(
					'spec_id' => ''.$sdf['spec_value_id'],
					'time' => date('Y-m-d H:i:s',time()),
				);
			}else{
				$thisObj->send_user_error(app::get('b2c')->_('sql执行出错'), null);
			}
		}else{
			$thisObj->send_user_error(app::get('b2c')->_('没有相关数据'), null);
		}
		
	}
}
