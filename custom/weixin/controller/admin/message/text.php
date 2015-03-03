<?php
class weixin_ctl_admin_message_text extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
    }//End Function

    //文字消息列表
    public function index(){
        $this->finder(
            'weixin_mdl_message_text',
            array(
                'title'=>app::get('weixin')->_('文字消息列表'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('添加文字消息'),'href'=>'index.php?app=weixin&ctl=admin_message_text&act=text_view','target'=>'dialog::{title:\''.app::get('weixin')->_('添加文字消息').'\',width:600,height:500}','icon'=>'sss.ccc'),
                ),
                'use_buildin_recycle'=>true,
            )
        );
    }

    //添加文字消息
    public function text_view($id){
        $data = app::get('weixin')->model('message_text')->getList('id,content,name,is_check_bind',array('id'=>intval($id) ));
        $this->pagedata['data'] = $data[0] ? $data[0] : array();
        $page_view = 'admin/message/text.html';
        $this->display($page_view);
    }

    //保存文字回复
    public function save(){
        $this->begin();
        if( empty($_POST['content']) ){
            $this->end(false, app::get('weixin')->_('操作失败!内容不能为空'));
        }
        if( empty($_POST['name']) ){
            $this->end(false, app::get('weixin')->_('操作失败!消息名称不能为空'));
        }
        $_POST['content'] = trim($_POST['content']);
        if( strlen($_POST['content']) > 1200 ){
            $this->end(false, app::get('weixin')->_('操作失败!内容不能超出1200字符'));
        }

        if($row=app::get('weixin')->model('message_text')->getList('id',array('name'=>$_POST['name'])) ){
            if( !$_POST['id'] || $row[0]['id'] != intval($_POST['id']) ){
                $this->end(false, app::get('weixin')->_('操作失败!消息名称已存在'));
            }
        }

        $data = array(
            'content' => trim(str_replace('&nbsp;',' ',$_POST['content'])), 
            'name' => trim($_POST['name']),
            'is_check_bind' => $_POST['is_check_bind'],
        );
        if( isset($_POST['id']) && intval($_POST['id']) ){
            $data['id'] = intval($_POST['id']);
        }
        if (app::get('weixin')->model('message_text')->save($data) ){
            $this->end(true, app::get('weixin')->_('添加成功！'));
        }else{
            $this->end(true, app::get('weixin')->_('添加失败！'));
        }
    }
    
    
    /**
     * 发送消息
     */
	 
	/*
    public function send_message(){
    	

		
    	$bind_id = '7';
    	
    	$coupons_id = 349;
    	
    	$log_file_name = 'my';
    	
    	$gift_id = '4';
    	
    	$message = '感谢您参与本次品珍鲜活推出的送龙虾活动，20元话费将于7个工作日内存入您预留的手机号。';
    	
    	$lm_model = app::get('wap')->model('lobster_member');
    	$pm_model =  app::get('pam')->model('members');
    	$member_coupon_sendlog = app::get('b2c')->model('member_coupon_sendlog');
    	
    	$start = '0';
    	$limit = '250';
    	
    	$list = $lm_model->getlist('*',array('z_count|than'=>29'),$start,$limit,'m_id ASC');

    	foreach($list as $k=>$v){
    		
    		$member_id = $pm_model->getrow('member_id',array('login_account'=>$v['m_openid']));
    		
    		//发送优惠卷
//     		$send_coupon = $this->_send_coupon($coupons_id,$member_id);
    		
    		//优惠券log
//     		if($send_coupon){
//     			$coupons_arr = app::get('b2c')->model('coupons')->getList("*",array('cpns_id'=>$coupons_id));
//     			$data = array();
//     			$data['sendlog_id'] = '';
//     			$data['sendtime'] = time();
//     			$data['member_list'] = $member_list;
//     			$data['cpns_name'] = $coupons_arr[0]['cpns_name'];
//     			$data['cpns_prefix'] = $coupons_arr[0]['cpns_prefix'];
//     			$data['code_list'] = $send_coupon;
//     			$member_coupon_sendlog->insert($data);
    			
//     			//写入 log文件 （my.html）
//     			file_put_contents($log_file_name.'_con.html', '{member_id:'.$member_id['member_id'].'}&nbsp;{'.$send_coupon.'}&nbsp;{'.$v['m_openid'].'} {'.$k.'}<br>',FILE_APPEND);
    			 
    			//微信通知
    			
    			$re = kernel::single('weixin_wechat')->send_message($bind_id,$v['m_openid'],$message);
    			 
    			if($re){
    				//写入 log文件 （my.html免邮）
    				file_put_contents($log_file_name.'_wx.html', '{m_id:'.$v['m_id'].'}&nbsp;'.$v['m_openid'].'&nbsp;{'.$k.'}<br>',FILE_APPEND);
    			}
    			 
    			echo $v['m_id'].'<br>';
//     		}

    	}
    	
    	echo 'success'.'<br>';

    }
    
    //发送优惠卷
    function _send_coupon($cpns_id,$member_ids) {
    	//从下载接口拿优惠卷
    	$coupons = app::get('b2c')->model('coupons');
    	$coupons_num = $coupons->downloadCoupon($cpns_id,count($member_ids));
    	$new_time = time();
    	if (empty($coupons_num)) {
    		return false;
    	}
    	$member_coupon = app::get('b2c')->model('member_coupon');
    	foreach ($member_ids as $key=>$value) {
    		$num = current($coupons_num);
    		next($coupons_num);
    			
    		$data = array(
    				'memc_code'=>$num,
    				'cpns_id'=>$cpns_id,
    				'member_id'=>$value,
    				'memc_source'=>'a',
    				'memc_enabled'=>'true',
    				'memc_used_times'=>0,
    				'memc_gen_time'=>$new_time,
    				'disabled'=>'false',
    				'memc_isvalid'=>'true',
    		);
    		$member_coupon->insert($data);
    	}
    
    	$coupons_txt = implode(',',$coupons_num);
    	return $coupons_txt;
    }

	*/
	
}
