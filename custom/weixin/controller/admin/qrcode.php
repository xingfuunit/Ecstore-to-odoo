<?php
/**
 * 微信自定义二维码
 * @author Administrator
 *
 */
class weixin_ctl_admin_qrcode extends desktop_controller{
	
	var $_qrcode_model;
	
	var $_wx_showcode_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

	function __construct($app)
	{
		parent::__construct($app);
		$this->_request = kernel::single('base_component_request');
		$this->_response = kernel::single('base_component_response');
		
		$this->_qrcode_model = $this->app->model('qrcode');
	}
	
	
	function index(){
		
		$bind_id = $this->_request->get_get('bind_id');
		$group_id = $this->_request->get_get('group_id') ? $this->_request->get_get('group_id') : 0;
		
		if(!$bind_id){
			$onebindid = app::get('weixin')->model('bind')->getList('id',array('appid|noequal'=>''),0,1,'id ASC');
			$bind_id = $onebindid[0]['id'];
		}
		
		$filter = array('bind_id'=>$bind_id);
		if($group_id){
			$filter['code_group'] = $group_id;
		}
		$code_list = $this->_qrcode_model->getlist('*',$filter);
		
		// 公众账号
		$publicNumbers = app::get('weixin')->model('bind')->getList('id,name',array('appid|noequal'=>''));
		$publicNumbers_options = array();
		foreach($publicNumbers as $row){
			$publicNumbers_options[$row['id']] = $row['name'];
		}
		$this->pagedata['publicNumber'] = $publicNumbers_options;
		
		
		//添加一个全部分组,便于 搜索
		$code_group = $this->_qrcode_model->getGroup();
		array_unshift($code_group,'全部分组');
		
		$this->pagedata['bind_id'] = $bind_id;
		$this->pagedata['group_id'] =  $group_id;
		$this->pagedata['code_group'] = $code_group;
		$this->pagedata['showcode_url'] = $this->_wx_showcode_url;
		$this->pagedata['list'] = $code_list;
		$this->page("admin/qrcode.html");
	}
	
	//添加
	function add(){
		// 公众账号
		$publicNumbers = app::get('weixin')->model('bind')->getList('id,name',array('appid|noequal'=>''));
		$publicNumbers_options = array();
		foreach($publicNumbers as $row){
			$publicNumbers_options[$row['id']] = $row['name'];
		}
		
		$this->pagedata['publicNumber'] = $publicNumbers_options;
		$this->pagedata['group_list'] = $this->_qrcode_model->getGroup();
		$this->page("admin/qrcode/edit.html");
	}
	
	//编辑
	function edit(){
		
		$code_id = $this->_request->get_get('code_id');
		
		// 公众账号
		$publicNumbers = app::get('weixin')->model('bind')->getList('id,name',array('appid|noequal'=>''));
		$publicNumbers_options = array();
		foreach($publicNumbers as $row){
			$publicNumbers_options[$row['id']] = $row['name'];
		}
		
		$this->pagedata['code'] = $this->_qrcode_model->getRow('*',array('code_id'=>$code_id));
		$this->pagedata['publicNumber'] = $publicNumbers_options;
		$this->pagedata['group_list'] = $this->_qrcode_model->getGroup();
		$this->page("admin/qrcode/edit.html");
	}
	
	//保存
	function save(){
		$post = $this->_request->get_post();
		
		$this->begin();
		$refreshUrl = "index.php?app=weixin&ctl=admin_qrcode&act=index";
		
		if($post['code_id']){
			$data =array(
					'code_group' 	=> $post['code_group'],
					'code_name'		=> $post['code_name'],
					'createtime'	=> time(),
					'bind_id'		=> $post['bind_id']
			);
			if($this->_qrcode_model->update($data,array('code_id'=>$post['code_id']))){
				$this->end(true, app::get('weixin')->_('保存二维码成功!'). $msg, $refreshUrl);
			}
			else{
				$this->end(false, app::get('weixin')->_('保存二维码失败!'). $msg, $refreshUrl);
			}
			
		}else{
			//生成二维码
			$data =array(
					'code_group' 	=> $post['code_group'],
					'code_name'		=> $post['code_name'],
					'createtime'	=> time(),
					'bind_id'		=> $post['bind_id'],
			);
			
			 $code_id = $this->_qrcode_model->insert($data);
			 //微信接口生成 二维码
			 $code_data = kernel::single('weixin_wechat')->create_qr_code($post['bind_id'],$code_id);
			 
			 if($code_data){
				 $up_data = array(
				 		'code_key'=> $code_data['ticket'],
				 		'code_url'=> $code_data['url'],
				 	);

				if($this->_qrcode_model->update($up_data,array('code_id'=>$code_id))){
					$this->end(true, app::get('weixin')->_('生成二维码成功!'). $msg, $refreshUrl);
				}
			}
			//生成失败删除之前insert的数据
			$this->_qrcode_model->delete(array('code_id'=>$code_id));
			$this->end(false, app::get('weixin')->_('生成二维码失败!'). $msg, $refreshUrl);
		}
	}
	
	/**
	 * 每个 
	 */
	public function show_count(){
		$get = $this->_request->get_get();
		$model_qrcode_log = $this->app->model('qrcode_log');
		$filter['qrcode_id'] = $get['code_id'];
		$filter['log_type'] = $model_qrcode_log->get_type($get['log_type']);
		
		$this->finder(
				'weixin_mdl_qrcode_log',
				array(
						'title'=>app::get('weixin')->_('自定义二维码统计'),
						'base_filter'=>$filter,
						'actions'=>array(
								array('label'=>app::get('b2c')->_('返回'),'href'=>'index.php?app=weixin&ctl=admin_qrcode&act=index&bind_id='.$get['bind_id'],'target'=>'','icon'=>'sss.ccc'),
							
						),
						'use_buildin_recycle'=>false,
						'use_buildin_filter'=>true,
				)
		);
	}


}