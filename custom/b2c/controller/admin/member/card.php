<?php

class b2c_ctl_admin_member_card extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	function index() {		
		$card_model = $this->app->model('member_card');
		$this->finder('b2c_mdl_member_card',array(
            'title'=>app::get('b2c')->_('会员卡'),
            'actions'=>array(
            	array('label'=>app::get('b2c')->_('添加会员卡'),'href'=>'index.php?app=b2c&ctl=admin_member_card&act=edit','target'=>'dialog::{width:680,height:350,title:\''.app::get('b2c')->_('添加会员卡').'\'}'),
            )
        ));
	}
	
	function edit($card_id=''){
		if($card_id){
			$card=$this->app->model('member_card')->getList('*',array('card_id'=>$card_id));	
			$this->pagedata['card']=$card[0];
		}
		$member_lv=$this->app->model("member_lv");
		foreach($member_lv->getMLevel() as $row){
			$options[$row['member_lv_id']] = $row['name'];
		}
		$a_mem['lv']['options'] = is_array($options) ? $options : array(app::get('b2c')->_('请添加会员等级')) ;
		$this->pagedata['mem'] = $a_mem;
		$this->display('admin/member/card.html');
	}

	function toadd(){
		$this->begin();
		$card_data=array(
				'card_number'=>$_POST['card_number'],
				'card_password'=>$_POST['card_password'],
				'card_lv_id'=>$_POST['card_lv_id'],
				'card_advance'=>$_POST['card_advance'],
				'card_point'=>$_POST['card_point'],
				'branch_id'=>$_POST['branch_id'],
				'card_etc'=>$_POST['card_etc'],
		);
		if($_POST['card_id']){
			if($this->app->model('member_card')->update($card_data,array('card_id'=>$_POST['card_id']))){
				$this->end(true,app::get('b2c')->_('保存成功'));
			}
	
		}else{
			$data=$this->app->model('member_card')->getList('*',array('card_number'=>$_POST['card_number']));
			if($data){
				$this->end(false,app::get('b2c')->_('卡号已存在已存在'));
			}
			if($this->app->model('member_card')->insert($card_data)){
				$this->end(true,app::get('b2c')->_('添加成功'));
			}
	
		}		 	
	}
}
