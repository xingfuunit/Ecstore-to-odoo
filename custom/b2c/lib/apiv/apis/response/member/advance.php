<?php
/**
 * add by Nick
 */
class b2c_apiv_apis_response_member_advance
{
	
	/**
	 * 构造方法
	 * @param object app
	 */
	public function __construct($app)
	{
		$this->app = $app;
		$this->objMath = kernel::single('ectools_math');
	}
	
	/**
	 * 获得时间段内
	 * @param unknown_type $params
	 * @param unknown_type $service
	 * @return multitype:unknown multitype:multitype:string unknown
	 */
	public function get_advance_list( $params, &$service )
	{
		//校验参数
		if( !( $start_time = $params['start_time'] ) )
			$service->send_user_error('开始时间不能为空！', null);
		if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
			$service->send_user_error('开始时间不合法！', null);
	
		if( !( $end_time = $params['end_time'] ) )
			$service->send_user_error('结束时间不能为空！');
		if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
			$service->send_user_error('结束时间不合法！', null);
	
		
		//-------------------------------------------------------------
		$params['page_no'] 		= $params['page_no'] ? $params['page_no'] : '1';
		$params['page_size'] 	= $params['page_size'] ? $params['page_size'] : '20';
	
		//-------------------------------------------------------------
		$where = '';
		if( $start_time != '' )
			$where .= "AND mtime > '" . $start_time . "' ";
		if( $end_time != '' )
			$where .= "AND mtime <= '" . $end_time . "' ";
		if( $where != '' )
			$where = ' WHERE ' . substr($where, 4);
		
		//-------------------------------------------------------------
		$_model = app::get('b2c')->model('member_advance');
		$pager	= $this->get_pager($params['page_no'], $params['page_size'], $_model->table_name(1),$where);
	
		//-------------------------------------------------------------
		$items 	= array();
		if( $pager['rs_count']>0 ){
			$sql = 'select  *  from '
					.$_model->table_name(1)
					.$where
					.' ORDER BY mtime ASC';
	
			$rows = $_model->db->selectLimit( $sql, $pager['limit'], $pager['offset'] );
			if ($rows && is_array($rows)){
				foreach ($rows as $index=>$rs){
					$items[$index] = array(
							
					);
				}
			}
		}
		
		return array(
				'rscount'	=> $pager['rs_count'],
				'pageno'	=> $pager['page_no'],
				'pageszie'	=> $pager['page_size'],
				'pagecount'	=> $pager['page_count'],
				'items'		=> $rows,
		);
	}
	
	
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
	
// 	private function get_member_name($member_id){
// 		$model = app::get('b2c')->model('member');
// 		return  $model->get_
// 	}
	
}

