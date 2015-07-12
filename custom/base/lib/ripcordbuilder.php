<?php
/**
 * 
 * @author Administrator
 *
 */

class base_ripcordbuilder{
	

	var $url;

	var $db;
	
	var $username;
	
	var $password;
	
	public function __construct(){
			require('ripcord/ripcord.php');
			
			$this->url = ODOO_URL;
			$this->db = ODOO_DB;
			$this->username = ODOO_USERNAME;
			$this->password = ODOO_PASSWORD;
	}
	
	/**
	 *  登录
	 */
	function logging_in(){
		$common = ripcord::client("$this->url/xmlrpc/2/common");
		$common->version();
		return $uid = $common->authenticate($this->db, $this->username, $this->password, array());
	}
	
	/**
	 *调用方法
	 */
	function calling_methods($post_param,$model="eshop.to.odoo"){
		$uid = $this->logging_in();
		$models = ripcord::client("$this->url/xmlrpc/2/object");
		$re = $models->execute_kw($this->db, $uid, $this->password,$model, $post_param['method'],array(array(json_encode($post_param)),array()));
		return $re;
	}
}