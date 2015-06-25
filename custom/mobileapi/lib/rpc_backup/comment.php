<?php

/**
 *
 * @author iegss
 *        
 */
class mobileapi_rpc_comment extends mobileapi_frontpage{
	
	/**
	 */
	function __construct($app) {
		$this->app = $app;
		$this->app->rpcService = kernel::single('base_rpc_service');
	}
	
	/**
	 * 商品评论
	 * @return array goods Comment
	 */
	function goods() {
				
		$page = isset($_REQUEST['page_no']) && $_REQUEST['page_no']>0?$_REQUEST['page_no']:1;
		$limit = isset($_REQUEST['page_size']) && $_REQUEST['page_size']>0?$_REQUEST['page_size']:10;
		$gid = $_REQUEST['iid'];
		
		$objdisask = kernel::single('b2c_message_disask');
		$aComment = $objdisask->good_all_disask($gid,'discuss',$page,null,$limit);
		
		$objPoint = kernel::single('b2c_mdl_comment_goods_point');
        $aComment['goods_point'] = $objPoint->get_single_point($gid);
        $aComment['total_point_nums'] = $objPoint->get_point_nums($gid);
        $aComment['_all_point'] = $objPoint->get_goods_point($gid);
        
        return $aComment;
	}

	/*
     * 评论咨询和回复证码验证
     *
     * @access private
     * @params string $item 验证的类型
     * @params array  $_POST 验证码POST的值
     * @return bool
     * */
    private function _check_vcode($item,$_POST){
        if( app::get('b2c')->getConf('comment.verifyCode') !="on" ){
            return true;;
        }

        $flag = true;
        switch($item){
            case 'ask':
                if(!base_vcode::verify('ASKVCODE',$_POST['askverifyCode'])){
                    $flag = false;
                }
                break;
            case 'discuss':
                if(!base_vcode::verify('DISSVCODE',$_POST['discussverifyCode'])){
                    $flag = false;
                }
                break;
            case 'reply':
                if(!base_vcode::verify('REPLYVCODE',$_POST['replyverifyCode'])){
                    $flag = false;
                }
                break;
        }
        if(!$flag){
            //$this->app->rpcService->send_user_error('4003', app::get('b2c')->_('验证码填写错误'));
        }
        return $flag;
    }//End Function _check_vcode

	/*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        }else{
            $array = strip_tags($array);
        }
        return $array;
    }

	/*
     * 咨询评论提交的数据验证
     * @params array $_POST post和get提交的数据
     * @params string $type 类型discuss(评论)|ask（咨询）
     * @return bool
     * */
    private function _check_post($_POST,$type){
    	$this->verify_member();
        $_POST = $this->check_input($_POST);
        //验证基本参数
        if(!$_POST['goods_id']){
        	 $this->app->rpcService->send_user_error('4003', app::get('b2c')->_('参数错误'));
        }else{
            $goodsData= app::get('b2c')->model('goods')->getList('goods_id',array('goods_id'=>$_POST['goods_id']));
            if(!$goodsData){
                $this->app->rpcService->send_user_error('4003', app::get('b2c')->_('参数错误'));
            }
        }
        if($type == 'discuss' && (!$_POST['product_id'] || !$_POST['order_id']) ){
            $this->app->rpcService->send_user_error('4003', app::get('b2c')->_('参数错误'));
        }
        if(empty($_POST['comment'])){
        	$this->app->rpcService->send_user_error('4003', app::get('b2c')->_('内容不能为空'));
        }

        //验证评论权限
        $disask = kernel::single('b2c_message_disask');
        if(!$disask->toValidate($type,$_POST,$message) ){
            if($message){
                $this->app->rpcService->send_user_error('4003', $message);
            }else{
                $this->app->rpcService->send_user_error('4003', app::get('b2c')->_('权限不足'));
            }
        }
        //验证码验证
        $this->_check_vcode($type,$_POST);

        return true;
    }
	
	/*
     * 发表评论/咨询
     * */
    public function toComment($param = array()){
    	$this->verify_member();

    	$item = $param['type'] ? $param['type'] : 'discuss';
    	$_POST = $param;

        $point_json = $param['point_json'];
        if (is_object(json_decode($point_json))) {
            foreach (json_decode($point_json) as $key => $value) {
                $_POST['point_type'][$key]['point'] = $value;
            }
        }

        $this->_check_post($_POST,$item);

        $member_data = $this->get_current_member();
        $aData['hidden_name'] = $_POST['hidden_name'];
        $aData['gask_type'] = $_POST['gask_type'];
        $aData['goods_point'] = $_POST['point_type'];
        $aData['title'] = $_POST['title'];
        $aData['comment'] = $_POST['comment'];
        $aData['goods_id'] = $_POST['goods_id'];
        $aData['product_id'] = $_POST['product_id'];
        $aData['order_id'] = $_POST['order_id'];
        $aData['object_type'] = $item;
        $aData['author_id'] = $member_data['member_id'] ? $member_data['member_id']:0;
        $aData['author'] = ($member_data['uname'] ? $member_data['uname'] : app::get('b2c')->_('佚名'));
        $aData['contact'] = ($_POST['contact']=='' ? $member_data['email'] : $_POST['contact']);
        $aData['time'] = time();
        $aData['lastreply'] = 0;
        $aData['ip'] = $_SERVER["REMOTE_ADDR"];
        $aData['display'] = (app::get('b2c')->getConf('comment.display')=='soon' ? 'true' : 'false');

        //更新goods表,统计此商品评论，咨询的数量
        $objGoods = app::get('b2c')->model('goods');
        $objGoods->updateRank($_POST['goods_id'], $item,1);

        $objComment = kernel::single('b2c_message_disask');
        if($comment_id = $objComment->send($aData, $item, $message)){
            $comment_display = $this->app->getConf('comment.display');
            if($comment_display == 'soon' && $item == 'discuss' && $aData['author_id']){
                $_is_add_point = app::get('b2c')->getConf('member_point');
                if($_is_add_point){
                    $obj_member_point = $this->app->model('member_point');
                    $obj_member_point->change_point($aData['author_id'],$_is_add_point,$_msg,'comment_discuss',2,$aData['goods_id'],$aData['author_id'],'comment');
                }
            }

            $setting_display = $comment_display ? $comment_display : 'reply';
            if($setting_display == 'soon'){
                $message = $this->app->getConf('comment.submit_display_notice.'.$item);
            }else{
                $message = $this->app->getConf('comment.submit_hidden_notice.'.$item);
            }

            if($item == 'ask'){
                $this->ask_content_data($_POST['goods_id'],$type_id,'tab');
                $this->pagedata['comments']['gask_type'] = $objComment->gask_type($_POST['goods_id']);
                // $data = $this->fetch('wap/product/tab/ask/content.html');
                //$url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_product','act'=>'index', 'arg0'=>$_POST['product_id']));
            }else{
                //$url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'nodiscuss'));
            }
            $message = $message ? $message : app::get('b2c')->_('发表成功');
            return $message;
        }
        else{
            $this->app->rpcService->send_user_error('4003', app::get('b2c')->_('发表失败'));
        }
    }
}

?>