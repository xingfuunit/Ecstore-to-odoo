<?php

/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_article {
	
	/**
	 */
	function __construct($app) {
		$this->app = $app;
	}
	
	/**
	 * 相关商品
	 * @return array goodslink 
	 */
	function get_detail() {
				
		$article_id = $_REQUEST['article_id'];
		
		$detail = kernel::single('content_article_detail')->get_detail($article_id, true);
		
		return $detail;
	}
	

	function help_item(){
		$data = array(
			0 => array(
				'title' => '使用帮助',
				'type' => 'node',
				'ids' => '23',
			),
			1 => array(
				'title' => '自提帮助',
				'type' => 'node',
				'ids' => '24'
			),
			2 => array(
				'title' => '关于',
				'type' => 'article',
				'ids' => '65'
			)
		);

		foreach ($data as $key =>$value) {
			if ($value['type'] != 'node') continue;
			if ($childrens = app::get('content')->model('article_nodes')->get_childrens_id($value['ids'])) {
				foreach ($childrens as $k => $v) {
					if($v == $value['ids'] && count($childrens) > 1) unset($childrens[$k]);
				}
				$data[$key]['ids'] = implode(',', $childrens);
			}
			
		}
		return $data;
	}

	function get_article_list($params, &$service){
		if (!$params['ids']) $service->send_user_error('error', '参数错误');
		$arr_articles = kernel::database()->select("SELECT * FROM `sdb_content_article_indexs` WHERE 1 AND `sdb_content_article_indexs`.node_id in (".$params['ids'].")  ORDER BY pubtime DESC");

		$node  = kernel::database()->select('SELECT node_id, node_name FROM `sdb_content_article_nodes` WHERE node_id IN('.$params['ids'].')');
		$arr = array();
		foreach ($node as $key => $value) {
			foreach ($arr_articles as $v) {
				if ($value['node_id'] == $v['node_id']) {
					$arr[$key]['article'][] = $v;
				}
			}
			if ($arr[$key]) $arr[$key]['node_name'] =$value['node_name'];
		}
		return $arr;
	}
}

?>