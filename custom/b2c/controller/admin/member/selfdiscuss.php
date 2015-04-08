<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_member_selfdiscuss extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
		
		if($_POST){
			$aData['goods_id'] = $_POST['goods_id'];
			
			$aData['author_id'] = $_POST['author_id'];

			$aData['product_id'] = $_POST['product_id'];
			$aData['author'] = $_POST['author'];

			$aData['comment'] = trim($_POST['comment']);
			if($_POST['time'])
				$aData['time'] = strtotime($_POST['time']);
			else
				$aData['time'] = time();

			$aData['ip'] = $_SERVER['REMOTE_ADDR'];
			$aData['display'] = true;
			if($_POST['hidden_name']==1)
				$aData['hidden_name'] = true;
			
			$item = 'discuss';		
			$aData['goods_point'] = $_POST['point_type'];

			$objComment = kernel::single('b2c_message_disask');
			$aComment = $objComment->send($aData,$item);
			
		}
		$this->page ( 'admin/member/selfdiscuss.html' );
	}
	
	function getUser(){
		if($_GET['author_id'])
			$sql = "select member_id,login_account from sdb_pam_members where member_id ='{$_GET['author_id']}'";
		if($_GET['author'])
			$sql = "select member_id,login_account from sdb_pam_members where login_account = '{$_GET['author']}'";
		$member = kernel::database()->select($sql);
		$member = $member[0];
		
		
		echo (json_encode($member));
	}
	function getProduct(){
		if($_GET['product_bn'])
			$sql = "select product_id,goods_id,bn,name from sdb_b2c_products where bn ='{$_GET['product_bn']}'";
		$product = kernel::database()->select($sql);
		$product = $product[0];
		
		
		echo (json_encode($product));
	}
}