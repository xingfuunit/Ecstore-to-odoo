<?php
/**
 *  集赞形式送 龙虾 活动
 * @author Administrator
 *
 */
class wap_ctl_lobster extends wap_controller{
	

	protected  $lm_model;
	
	protected  $lz_model;
	
	//控制开始结束
	protected  $_is_stop = false;
	
	//微信eid
	protected $_state;
	
	//关注文章地址
	private $_follow_url = 'http://mp.weixin.qq.com/s?__biz=MzAxMjEwMjg2OA==&mid=206625913&idx=1&sn=a21e86c75e22f947ae2e7fb57cf47030#rd';
	
	//礼品最大数量
	private $_gift_max = 3000;
	
	//集赞获奖数
	private $_zan_success_num = 30;
	
	//开始时间
	private $_startdate = '2015-2-10 00:00:01';
	
	//结束时间
	private $_end_date = '2015-3-5 23:59:59';
	
	//活动ID
	private $_active_id = '1';
	
	private $_active_name = '龙虾集赞';
	
	
	function __construct(&$app){
		parent::__construct($app);
		
		//强制结束
		if($this->_is_stop){
			$this->_js_alert($this->_active_name.'活动已结束，更多活动请关注 品珍微信',$this->_follow_url);
			exit;
		}
		
		//判断开始结束
		$time = time();
		if($time < strtotime($this->_startdate)){
			$this->_js_alert($this->_active_name.'活动未开始，请先关注 品珍微信',$this->_follow_url);
			exit;
		}
		if($time > strtotime($this->_end_date)){
			$this->_js_alert($this->_active_name.'活动已结束，更多活动请关注 品珍微信',$this->_follow_url);
			exit;
		}
		
		$this->lm_model = $this->app->model('lobster_member');
		$this->lz_model = $this->app->model('lobster_zlist');
		
		//送完即止
		$gift_count = $this->lm_model->count(array('z_count|than'=>$this->_zan_success_num-1));
		if($gift_count > $this->_gift_max){
			$this->_js_alert('本次活动奖品已派完，敬请期待下期活动！',$this->_follow_url);
			exit;
		}
		
		$shopname = app::get('site')->getConf('site.name');
		
		$this->_state = $_GET['state'];
	}
	
	
	function index(){

		$m_id = $this->_request->get_params();
		$m_id = (int)$m_id[0];
		
		if(empty($m_id)){
			print_r('param error!');exit;
		}

		//分享者信息
		$join_info = $this->lm_model->getrow('*',array('m_id'=>$m_id));
		
		$zan_url = $this->_build_wx_url($this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'post_zan','full'=>1,'args'=>array('m_id'=>$m_id))),0,'snsapi_userinfo');
		
// 		//是否当前参加用户
		$wx_info = $this->_get_wx_info();

		$cur_join_user = $this->lm_model->getrow('*',array('m_openid'=>$wx_info['openid'],'m_id'=>$m_id));
		if($cur_join_user){
			$is_show_share = true;
			$join_href = 'javascript:alert(\'你已参加,不能重复参加！\')';
			$zan_href = $zan_url;
			if($m_id == $cur_join_user['m_id']){
				$zan_href = 'javascript:alert(\'你不能送自己！\')';
			}
			
			//集赞成功  跳到获奖页面
			if($cur_join_user['z_count'] >= $this->_zan_success_num){
				$this->_build_wx_url($this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'member_lobster','full'=>1,'args'=>array('m_id'=>$m_id))),1);
			}
		}
		
		//赞用户
		else{
			$is_show_share = false;
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'join_lobster','full'=>1));
			$join_href = $this->_build_wx_url($url);
			$zan_href = $zan_url;
		}

		//获取赞信息
		$zan_list = $this->lz_model->getlist('*',array('m_id'=>$m_id),0,30,'z_time Desc');

		if($zan_list){
			foreach($zan_list as $k=> $v){
				if($v['z_time']){
					$zan_list[$k]['z_time'] = date('m-d H:i',$v['z_time']);
				}
			}
		}
		
		
		$this->pagedata['join_url'] = $join_href;
		$this->pagedata['zan_url'] = $zan_href;
		$this->pagedata['zan_list'] = $zan_list;
		$this->pagedata['m_info']=$join_info;
		$this->pagedata['lobster_rule'] = $this->_follow_url;
		$this->pagedata['title'] = app::get('b2c')->_('没看错！波士顿大龙虾免费送，只有三千只，快帮我抢~ '.$join_info['m_nick_name']);
		//是否提示分享
		$this->pagedata['is_show_share'] = $is_show_share;
		$this->page('wap/lobster/index.html',true);
	}
	
	/**
	 * 点赞
	 */
	function post_zan(){
		$m_id = $this->_request->get_params();
		$m_id = (int)$m_id[0];
		
		if(empty($m_id)){
			print_r('param error!');exit;
		}
		
		//oauth方式获得 未关注用户信息
		$access_token =parent::$this->accesstoken_oauth2;
		$openid = parent::$this->openid;
		$zinfo = kernel::single('weixin_wechat')->get_basic_userinfo_oauth2($access_token,$openid);
		
		$m_info = $this->lm_model->getrow('*',array('m_id'=>$m_id));
		if(empty($m_info)){
			print_r('没有此参加者');exit;
		}
		
		if($m_info['m_openid'] == $openid){
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_id)));
			$this->_js_alert('你不能送自己！',$this->_build_wx_url($url));
			exit;
		}
		
		if($zinfo['openid']){
			//送过
			$is_zan = $this->lz_model->getrow('*',array('z_openid'=>$zinfo['openid'],'m_id'=>$m_id));
			if($is_zan){
				$url = $this->_build_wx_url($this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_id))));
				$this->_js_alert('你已经送过他了',$url);
				exit;
			}
			else
			{
				$data = array(
						'z_openid'		=>	$zinfo['openid'],	
						'z_nick_name'	=>	$zinfo['nickname'],
						'm_openid'		=>	$m_info['m_openid'],
						'z_time'		=>  time(),
						'z_headimgurl' 	=>	$zinfo['headimgurl'],
						'm_id'	=>	$m_id,
					);
				$this->lz_model->save($data);
				
				//更新赞输
				$zan_data = array('z_count'=>$m_info['z_count']+1);
				$this->lm_model->update($zan_data,array('m_id'=>$m_id));
				
				//赞数达到30 发短信
				if($m_info['z_count']+1  == $this->_zan_success_num){
					$this->_send_success_sms('weixin_success', $m_info['phone']);
				}
			}
			$url = $this->_build_wx_url($this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'zan_success','full'=>1,'args'=>array('m_id'=>$m_id))),1);
// 			$this->_js_alert('你已成功送出一只龙虾',$url);
		}
	}
	
	/**
	 * 赞成功
	 */
	function zan_success(){
		$this->pagedata['title'] = app::get('b2c')->_('我要免费吃龙虾，快来支持我！ &nbsp; - 品珍鲜活');
		
		$m_id = $this->_request->get_params();
		$m_id = (int)$m_id[0];
		
		$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'want_join_btn','full'=>1));
		$this->pagedata['want_join_btn'] =  $this->_build_wx_url($url);
		$this->page('wap/lobster/zan_success.html',true);
	}
	
	/**
	 * 我也要参加 - 提交按钮
	 */
	function want_join_btn(){
		$wx_info = $this->_get_wx_info();
		if(empty($wx_info)){
			$this->_js_alert('你未关注品珍鲜活，请先关注',$this->_follow_url);
			exit;
		}
		
		$m_user = $this->lm_model->getrow('*',array('m_openid'=>$wx_info['openid']));
		if($m_user){
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_user['m_id'])));
			$this->_js_alert('你已参加活动',$this->_build_wx_url($url));
			exit;
		}
		
		//跳转参加页
		$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'join_lobster','full'=>1));
		$this->_build_wx_url($url,1);
		exit;
	}
	
	
	/**
	 * 活动个人主页
	 */
	function member_lobster(){
		$this->pagedata['title'] = app::get('b2c')->_($this->_active_name.'活动  - 品珍鲜活');
		
		$wx_info = $this->_get_wx_info();
		$m_info = $this->lm_model->getrow('*',array('m_openid'=>$wx_info['openid']));
		
		if(empty($m_info)){
			$url = $url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'join_lobster','full'=>1));
			$this->_js_alert('你未参加活动',$this->_build_wx_url($url));
			exit;
		}
		
		//显示获取数
		if($m_info['z_count'] < $this->_zan_success_num){
			
			$lost = $this->_zan_success_num- $m_info['z_count'];
			$this->pagedata['lost'] = $lost;
			$this->pagedata['count'] = $m_info['z_count'];
			
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_info['m_id'])));
			$this->pagedata['index_url'] = $this->_build_wx_url($url);
			$this->page('wap/lobster/member_lobster.html',true);
		}
		//获取奖品
		else{
			$this->page('wap/lobster/member_win.html',true);
		}
		
	}
	
	/**
	 * 参加页面
	 */
	function join_lobster(){
		$this->pagedata['title'] = app::get('b2c')->_('欢迎参加'.$this->_active_name.'活动  - 品珍鲜活');
		
		$user_info = $this->_get_wx_info();
		
		if ($user_info == false) {
			$this->_js_alert('你未关注品珍鲜活，请先关注！',$this->_follow_url);
		}
		
		$lm_model = $this->app->model('lobster_member');
		$join_user = $lm_model->getlist('*',array('m_openid'=>$user_info['openid']));
		
		if($join_user){
			//跳转到用户页面
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'member_lobster','full'=>1,'args'=>array('m_id'=>$join_user[0]['m_id'])));
			$this->_js_alert('你已经参加活动',$this->_build_wx_url($url));
		}
		
		$this->pagedata['wx_info'] = $user_info;
		$this->pagedata['gift_list'] = $this->lm_model->_gift_list;
		$this->pagedata['area_list'] = $this->lm_model->_area_list;
		$this->pagedata['post_url'] = $this->_build_url_state(array('app'=>'wap','ctl'=>'lobster','act'=>'post_join'));
		
		
		$this->page('wap/lobster/join_lobster.html',true);
		
	}
	
	/**
	 *  参加提交
	 */
	public function post_join(){
		
		if(empty($_POST)){
			header('Content-Type:text/html; charset=utf-8');
			print_r('参数错误，请稍后再试');
			exit;
		}
		
		$msg = $this->_vali_post_data($_POST);
		if($msg){
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'join_lobster','full'=>1));
			$this->_js_alert($msg,$this->_build_wx_url($url));
			exit;
		}
		
		
		$m_user  =$this->lm_model->getlist('*',array('m_openid'=>$_POST['m_openid']));
		if($m_user){
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'member_lobster','full'=>1,'args'=>array('m_id'=>$join_user['m_id'])));
			$this->_js_alert('你已经参加活动',$this->_build_wx_url($url));
		}
		
		$re = $this->lm_model->save($_POST);
		
		if($re){
			$m_user  =$this->lm_model->getlist('*',array('m_openid'=>$_POST['m_openid']));
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_user[0]['m_id'])));
			$this->_build_wx_url($url,1);
//			$this->_js_alert('请点击右上角分享',$this->_build_wx_url($url));
		}
		
		exit;
	}
	
	
	
	
	/**
	 * 构造  微信 使用的url
	 * @param unknown_type $url
	 * @param is_jump 是否跳转
	 * snsapi_userinfo
	 */
	protected function  _build_wx_url($url,$is_jump=0,$scope='snsapi_base'){
		$bind = app::get('weixin')->model('bind')->getRow('*',array('eid'=>$this->_state,'status'=>'active'));
		$path1 = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$bind['appid']}&redirect_uri=";
		$path2 = "&response_type=code&scope={$scope}&state={$this->_state}&connect_redirect=1#wechat_redirect";
		
		$url = $path1.$url.$path2;
		
		if($is_jump){
			$this->redirect($url,$is_jump);
		}else{
			return $url;
		}
	}
	
	/**
	 * 为每个自定义 跳转 连接添加  state参数
	 */
	protected function _build_url_state($params=array()){
		return $this->gen_url($params).'?state='.$this->_state;
	}
	
	
	//判断是否关注
	protected function _is_bind() {
		$openid = parent::$this->openid;
		$bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
		$uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
		//var_dump($uinfo['subscribe']);exit;
		if ($uinfo['subscribe']) {
			return true;
		} else {
			return false;
		}
		
	}
	
// 	 获得用户信息 没有返回false
	protected function _get_wx_info(){
		$openid = parent::$this->openid;
		$bind = app::get('weixin')->model('bind')->getRow('id',array('eid'=>$_GET['state'],'status'=>'active'));
		$uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
		
		if ($uinfo['subscribe']) {
			return $uinfo;
		} else {
			return false;
		}
	}	
	
	/**
	 * 提示跳转
	 * @param unknown_type $msg
	 */
	protected function _js_alert($msg,$jump_url=false){
		header('Content-Type:text/html; charset=utf-8');
		
		$html ="<script>
				var j_url = '$jump_url';
				alert('".$msg."');
				if(j_url){
					window.location.href='$jump_url';
				}
			</script>";
		
		echo $html;
		exit;

	}
	
	/**
	 * 提示并返回上一页
	 * @param unknown_type $msg
	 */
	protected function _js_alert_back($msg,$back=true){
		header('Content-Type:text/html; charset=utf-8');
		
		$html ="<script>
				var j_url = '$back';
				alert('".$msg."');
				if(j_url){
					window.history.go(-1);
				}
			</script>";
		
		echo $html;
		exit;

	}
	
	/**
	 * 验证提交数据
	 */
	protected function _vali_post_data($post){
		$msg = false;
		if(empty($post['gift_id'])){
			return $msg = '请选择礼品';
		}
		if(empty($post['area_id'])){
			return $msg = '请选择地区';
		}
		if (!preg_match("/^[1][358]\d{9}$/", $post['phone'])) {
			return $msg = '手机号码不正确';
		}
	}
	
	/**
	 * 集赞成功 发送短信
	 * 
	 * （短信内容直接添加到数据库）
	 * 恭喜您成功召集30个好友支持，成功获取本次品珍鲜活送龙虾活动奖品~奖品将于活动结束后统一发放，领奖方式请参照活动细则。如有疑问，可微信联系客服或拨打：400-930-9303
	 */
	protected function _send_success_sms($tmpl,$mobile,$data=''){
		$messengerModel = app::get('b2c')->model('member_messenger');
		$actions = $messengerModel->actions();
		$level = $actions[$tmpl]['level'];
		$sendType = $actions[$tmpl]['sendType'];
		
		$sender = 'b2c_messenger_sms';
		$tmpl_name = 'messenger:b2c_messenger_sms/'.$tmpl;

		if(!$level < 10){ //队列
			$messengerModel->addQueue($sender,$tmpl_name,(string)$mobile,$data,$tmpl,$sendType);
		}else{ //直接发送 print
			$re = $messengerModel->_send($sender,$tmpl_name,(string)$mobile,$data,$tmpl,$sendType);
		}
		
		return true;
	} 

}
?>