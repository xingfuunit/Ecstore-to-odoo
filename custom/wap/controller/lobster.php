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
	protected  $_is_stop = true;
	
	//微信eid
	protected $_state;
	
	//关注文章地址
	private $_follow_url = 'http://mp.weixin.qq.com/s?__biz=MzAxMjEwMjg2OA==&mid=206625913&idx=1&sn=a21e86c75e22f947ae2e7fb57cf47030#rd';
	
	//活动结束文章
	private $_end_url = 'http://mp.weixin.qq.com/s?__biz=MzAxMjEwMjg2OA==&mid=206627978&idx=3&sn=5e1b720986f1d88024ea513a3d0e4076#rd';
	
	//礼品最大数量
	private $_gift_max = 3000;
	
	//开始时间
	private $_startdate = '2015-2-10 00:00:01';
	
	//结束时间
	private $_end_date = '2015-3-5 23:59:59';
	
	//活动ID
	private $_active_id = '1';
	
	private $_active_name = '龙虾';
	
	
	function __construct(&$app){
		parent::__construct($app);
		
		$this->lm_model = $this->app->model('lobster_member');
		$this->lz_model = $this->app->model('lobster_zlist');

// 		//判断开始结束
// 		$time = time();
// 		if($time < strtotime($this->_startdate)){
// 			$this->_js_alert($this->_active_name.'活动未开始，请先关注 品珍微信',$this->_follow_url);
// 			exit;
// 		}
// 		if($time > strtotime($this->_end_date)){
// 			$this->_js_alert($this->_active_name.'活动已结束，更多活动请关注 品珍微信',$this->_follow_url);
// 			exit;
// 		}
		
// 		//送完即止
// 		$gift_count = $this->lm_model->count(array('z_count|than'=>$this->lm_model->_zan_success_num-1));
// 		if($gift_count > $this->_gift_max){
// 			$this->_js_alert('本次活动奖品已派完，敬请期待下期活动！',$this->_follow_url);
// 			exit;
// 		}
		
		$this->_state = $_GET['state'];
	}
	
	
	function index(){
		//判断活动是否结束
		$this->_is_stop();

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
			if($cur_join_user['z_count'] >= $this->lm_model->_zan_success_num){
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
		$zan_list = $this->lz_model->getlist('*',array('m_id'=>$m_id),0,100,'z_time Desc');

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
		
		//判断活动是否结束
		$this->_is_stop();
		
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
				if($m_info['z_count']+1  == $this->lm_model->_zan_success_num){
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
		
		//判断活动是否结束
		$this->_is_stop();
		
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
		
		//判断活动是否结束
		$this->_is_stop();
		
		$this->pagedata['title'] = app::get('b2c')->_($this->_active_name.'活动  - 品珍鲜活');
		
		$wx_info = $this->_get_wx_info();
		$m_info = $this->lm_model->getrow('*',array('m_openid'=>$wx_info['openid']));
		
		if(empty($m_info)){
			$url = $url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'join_lobster','full'=>1));
			$this->_js_alert('你未参加活动',$this->_build_wx_url($url));
			exit;
		}
		
		//显示获取数
		if($m_info['z_count'] < $this->lm_model->_zan_success_num){
			
			$lost = $this->lm_model->_zan_success_num- $m_info['z_count'];
			$this->pagedata['lost'] = $lost;
			$this->pagedata['count'] = $m_info['z_count'];
			
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'index','full'=>1,'args'=>array('m_id'=>$m_info['m_id'])));
			$this->pagedata['index_url'] = $this->_build_wx_url($url);
			$this->page('wap/lobster/member_lobster.html',true);
		}
		//获取奖品
		else{
			$this->pagedata['count'] = $m_info['z_count'];
			$this->page('wap/lobster/member_win.html',true);
		}
		
	}
	
	/**
	 * 参加页面
	 */
	function join_lobster(){
		
		//判断活动是否结束
		$this->_is_stop();
		
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
	 * 
	 * no_reg=1 返回给微信接口  参加活动的不注册
	 */
	protected function  _build_wx_url($url,$is_jump=0,$scope='snsapi_base'){
		$bind = app::get('weixin')->model('bind')->getRow('*',array('eid'=>$this->_state,'status'=>'active'));
		$path1 = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$bind['appid']}&redirect_uri=";
		$path2 = "?no_reg=1&response_type=code&scope={$scope}&state={$this->_state}&connect_redirect=1#wechat_redirect";
		
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
	
	/**
	 * 活动结束
	 */
	public function end_lobster(){
		$m_id = $this->_request->get_params();
		$m_id = (int)$m_id[0];
		
		if(empty($m_id)){
			print_r('param error!');exit;
		}
		
		//分享者信息
		$join_info = $this->lm_model->getrow('*',array('m_id'=>$m_id));
		
		//获取赞信息
		$zan_list = $this->lz_model->getlist('*',array('m_id'=>$m_id),0,200,'z_time Desc');
		
		if($zan_list){
			foreach($zan_list as $k=> $v){
				if($v['z_time']){
					$zan_list[$k]['z_time'] = date('m-d H:i',$v['z_time']);
				}
			}
		}

		$this->pagedata['end_url'] = $this->_end_url;
		$this->pagedata['zan_list'] = $zan_list;
		$this->pagedata['m_info']=$join_info;
		$this->pagedata['lobster_rule'] = $this->_follow_url;
		$this->pagedata['title'] = app::get('b2c')->_($this->_active_name.'活动'.$join_info['m_nick_name']);
		$this->page('wap/lobster/end_lobster.html',true);
	}

	/**
	 * 活动结束
	 */
	function _is_stop(){
	
		if($this->_is_stop){
			// 			$this->_js_alert('3000只龙虾已经全部送完啦~品珍鲜活感谢您的支持与关注，敬请期待下一期活动~',$this->_follow_url);
			$m_id = $this->_request->get_params();
			$m_id = (int)$m_id[0];
			
			$url = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'end_lobster','full'=>1,'args'=>array('m_id'=>$m_id)));
			$this->redirect($url);
		}
	}
	

//	================================================= 特殊操作   ============================================

// 	12号会员数 2119-1726+159=552
// 	11号会员数54856-54844+8186=8198
// 	10号会员数 986-979+125=132
// 	虚增数：49079

	//较验密码
	public $_del_pwd = 'zxcvbnm';
	
	//每次处理个数
	public $_pre_exc_num = '50';
	
	//临时操作，写死需要绑定的微信帐号
	public $_weixin_account = 'pz0086';
	
	/**
	 * 清除龙虾活动多余会员数据
	 */
	function del_exc_member(){
		
		$start = isset($_POST['start']) ? $_POST['start'] : 1;
		$exc_count = isset($_POST['exc_count']) ? $_POST['exc_count'] : 0;
		
		$start_time = '2015-2-10 00:00:01';
		$end_time = '2015-2-12 23:59:59';
		
		$start_time = '2015-2-10 00:00:01';
		$end_time = '2015-2-12 23:59:59';
		
		$pam_member_model = app::get('pam')->model('members');
		$pam_bind_tag_model = app::get('pam')->model('bind_tag');
		
		//开头为 o1a的是来自微信的数据
		$filter = array('createtime|than'=>strtotime($start_time),'createtime|lthan'=>strtotime($end_time),'login_account|head'=>'o1');

		$count = $pam_member_model->count($filter);
		
		if($_POST){
			if(md5($_POST['pwd']) == md5($this->_del_pwd)){
				
				$member_list = $pam_member_model->getList('*',$filter,$start,$this->_pre_exc_num,'member_id ASC');
				
				if($member_list){
					//记录处理数
					$exc_num = 0;
					
					foreach($member_list  as $k => $v){
						
						$weixin_info = $pam_bind_tag_model->getrow('*',array('member_id'=>$v['member_id']));
						
						//未关注微信用户 且 没参加活动 且 没有订单优惠券数据  就删除
						if(!$this->del_member_is_join_lobster($weixin_info['open_id'], $this->_weixin_account)  && !$this->del_member_is_bind($weixin_info['open_id'], $this->_weixin_account)  && !$this->del_member_is_used($v['member_id'])){
							$this->_del_exc($v['member_id'],$weixin_info['open_id']);
							$exc_count ++;
							$exc_num ++;
						}
					}
					$msg  ='处理完成：'.$exc_num;
					echo json_encode(array(
										'status'=>1,
										'msg'=>$msg,
										'start'=> $start + $this->_pre_exc_num,
										'limit'=> $this->_pre_exc_num,
										'pwd'=>$_POST['pwd'],
										'exc_count'=>$exc_count,
										'post_url' => $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'del_exc_member','full'=>1,'args'=>array('start'=>$start))),
										'exc_num'=>$exc_num,
									)
							);
					exit;
					
				}else{
					$msg = '处理已完成 或 未找到处理数据';
					echo json_encode(array('status'=>2,'msg'=>$msg,'exc_count'=>$exc_count));
					exit;
				}
				
			}else{
				$msg = '校验码不正确';
				echo json_encode(array('status'=>0,'msg'=>$msg));
				exit;
			}
		}
		
		$this->pagedata['count'] = $count;
		$this->pagedata['limit'] = $this->_pre_exc_num;
		$this->pagedata['post_url'] = $this->gen_url(array('app'=>'wap','ctl'=>'lobster','act'=>'del_exc_member','full'=>1,'args'=>array('start'=>$start)));
		$this->page('wap/lobster/del_exc_member.html',true);
	}
	
	/**
	 * 删除操作  根据members_id  删除 pam_bind_tag ,pam_members,b2c_members 3个表的 
	 * @param unknown_type $member_id
	 * @param unknown_type $account
	 */
	private function _del_exc($member_id,$account){
		if($member_id){
			$pam_members_model = app::get('pam')->model('members');
			$members_model = app::get('b2c')->model('members');
			$pam_bind_model = app::get('pam')->model('bind_tag');
			
			$filter = array('member_id'=>$member_id);
			
			$pam_members_model->delete($filter);
			$members_model->delete($filter);
			$pam_bind_model->delete($filter);
			
			//写入log文件
			$content = 'member_id:'.$member_id.'  account:'.$account."\r\n";
			file_put_contents('l_weixin_del_member.log',$content,FILE_APPEND);
		}
		
		
	}
	
	
	/**
	 * 	open_id 是否关注  
	 */
	private function del_member_is_bind($openid,$weixin_account){
		$bind_model = app::get('weixin')->model('bind');
		$bind = $bind_model->getrow('id',array('weixin_account'=>$weixin_account));
		$uinfo = kernel::single('weixin_wechat')->get_basic_userinfo($bind['id'],$openid);
		
		if($uinfo){
			//写入log文件
			$content = 'openid:'.$openid."\r\n";
			file_put_contents('l_member_bind.log',$content,FILE_APPEND);
		}else{
			//写入log文件
			$content = 'openid:'.$openid."\r\n";
			file_put_contents('l_member_no_bind.log',$content,FILE_APPEND);
		}
		if ($uinfo['subscribe']) {
			return true;
		}
		else{
			return false;
		}
	}
	
	//判断用户 是否有订单或者 优惠券
	private function del_member_is_used($member_id){
		$is_used = false;
		
		if($member_id){
			$orders_model = app::get('b2c')->model('orders');
			if($orders_model->count(array('member_id'=>$member_id))>0){
				$is_used  = true;
			}
			
			$member_coupon_model = app::get('b2c')->model('member_coupon');
			if($member_coupon_model->count(array('member_id'=>$member_id))>0){
				$is_used = true;
			}
		}
		
		return $is_used;
	}
	
	/**
	 * 判断 是否参与 龙虾集赞活动，参加了就不删除 （参加的包含点赞的）
	 */
	private function del_member_is_join_lobster($openid,$weixin_account){
		
		$lobster_member_model = app::get('wap')->model('lobster_member');
		$re = $lobster_member_model->getrow(' * ',array('m_openid'=>$openid));

// 		$sql = "SELECT * FROM sdb_wap_lobster_member WHERE LCASE(m_openid) = '$openid'";
// 		$re = $lobster_member_model->db->select($sql);
// 		$re = $re[0];
		
		if($re){
			//写入log文件
			$content = 'login_account:'.$openid.'    openid:'.$re['m_openid']."\r\n";
			file_put_contents('l_member_join_lobster.log',$content,FILE_APPEND);
		}else{
			//写入log文件
			$content = 'login_account:'.$openid."\r\n";
			file_put_contents('l_member_no_join_lobster.log',$content,FILE_APPEND);
		}
		
		if($re){
			return true;
		}
		else{
			return false;
		}
	}
	
// 	================================================= 特殊操作  ============================================
}
?>