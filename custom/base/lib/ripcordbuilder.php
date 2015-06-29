<?php
/**
 * 
 * @author Administrator
 *
 */

class ripcord_builder{
	
	
	var $url;

	var $db;
	
	var $username;
	
	var $password;
	
	function __construct(
			require('ripcord/ripcord.php');
			
			$this->url = ODOO_URL;
			$this->db = ODOO_DB;
			$this->username = ODOO_USERNAME;
			$this->password = ODOO_PASSWORD;
		)
	
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
	function calling_methods($post_param,$methods="ecstore_notice"){
		$post_data['Obj'] = 'ecstore_obj';
		$post_data['Method'] = 'ecstore_notice';
		$post_data['Args'] = $post_param;
		
		$uid = $this->logging_in();
		$models = ripcord::client("$this->url/xmlrpc/2/object");
		$re = $models->execute_kw($this->db, $uid, $this->password,'res.partner', $methods,$post_data, array('raise_exception' => false));
		
		return $re;
	}
}