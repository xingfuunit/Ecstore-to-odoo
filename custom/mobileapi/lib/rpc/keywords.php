<?php
/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_keywords {
	
	/**
	 */
	function __construct($app) {
		$this->app = $app;
	}
	
	function get_all_list() {
		
		
		$db = kernel::database();
		$sql = "select kw_name  FROM `sdb_mobileapi_keywords` order by ordernum asc;";
		$keywords = $db->select($sql);
		
		$ret['items'] = $keywords?$keywords:array();
		$ret['show_num'] = count($keywords) > 10 ? 10: count($keywords);
		return $ret;		
	}
	
	function get_itmes($count=10) {
		$ww = '';
		if(isset($count) && $count>0){
			$ww = ' limit '.$count;
		}

		$db = kernel::database();
		$sql = "select kw_name as name,kw_url as url FROM `sdb_mobileapi_keywords` order by ordernum desc ".$ww;
		$rs = $db->select($sql);
		
		if(isset($rs) && is_array($rs)){
			foreach($rs as $k => $v){
				if(strlen($v['url'])<5){
					$rs[$k]['url'] = '/wap/gallery.html?scontent=n,' . urlencode($v['name']);
				}
			}
		}
		
		return $rs;
	}
	
	
	public function associate(){
		$words = $_POST['words'];
		$searchrule = searchrule_search::instance('search_associate');
		if($searchrule && !empty($words)){
			$result = $searchrule->get_woreds($words);
			return $result;
		}else{
			return array();
		}
	}
}

?>