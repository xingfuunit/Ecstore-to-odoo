<?php
/**
 * july by 2015-06-15
 */

class b2c_ctl_wap_simplesearch extends wap_frontpage{

    public function __construct(&$app) {
        parent::__construct($app);
    }

    public function index(){
		// "/wap/simplesearch.html?find=no"
		$find = ''.$_GET['find'];
		if(strlen($find)>0){
			$find = 'no';
		}
		
		//后台设定的热门搜索词，暂时不用 bySam 20150701
		//$_keywords = kernel::single('mobileapi_rpc_keywords')->get_itmes();
		
		$this->pagedata['find'] = $find;
		
		$_s = $_COOKIE['pz_search_history'];
		$_s = $_s?json_decode($_COOKIE['pz_search_history']):'';

		$i = count($_keywords);
		
		foreach($_s as $k => $v){
			$_keywords[++$i]['name'] = $v;
			$_keywords[$i]['url'] = '/wap/gallery.html?scontent=n,' . urlencode($v);
		}
		
		$keywords = array();
		if($_keywords){
			foreach($_keywords as $v){
				$keywords[$v['name']] = $v;
			}
		}
		
		$this->pagedata['keywords'] = $keywords;
		$this->page('wap/simplesearch/index.html');
    }

}