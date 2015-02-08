<?php

class b2c_ctl_admin_local_staff extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	function index() {		
		$local_staff = $this->app->model('local_staff');
		$this->finder('b2c_mdl_local_staff',array(
            'title'=>app::get('b2c')->_('门店员工'),
            'allow_detail_popup'=>false,
            'use_buildin_filter'=>false,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
            'actions'=>array(
            	array('label'=>app::get('b2c')->_('添加门店员工'),'href'=>'index.php?app=b2c&ctl=admin_local_staff&act=edit','target'=>'dialog::{width:680,height:350,title:\''.app::get('b2c')->_('添加门店员工').'\'}'),
            )
        ));
	}

        function edit($staff_id=''){           
            if($staff_id){
                $staff=$this->app->model('local_staff')->getList('*',array('staff_id'=>$staff_id));
                
                $this->pagedata['staff']=$staff[0];
            }
            $local_store=app::get('ome')->model('branch')->getList('*',array('disabled'=>'false'));
            //hack by Jason 将新增门店会员变成要从会员列表中选begin
            $obj_member = app::get('b2c')->model('members');
            $aData = $obj_member->getList('member_id',array('member_lv_id'=>'5'));
            $mem_array = array();
            foreach($aData as $k=>$v){
            	$account = app::get('pam')->model('members')->getList('*',array('member_id'=>$v,'login_type'=>'local'));
            	$now_staff=$this->app->model('local_staff')->getList('*',array('member_id'=>$v));
            	if(!$now_staff){
            		$mem_array[]=$account[0];           		
            	}elseif($account[0]['member_id'] == $staff[0]['member_id']){
            		$mem_array[]=$account[0];
            	}       	
            }
            $this->pagedata['memberlist']=$mem_array;
            //hack by Jason 将新增门店会员变成要从会员列表中选end
            $this->pagedata['local']=$local_store;
            $this->display('admin/local/staff.html');
            
        }
        
        function toadd(){
            $this->begin();
            $obj_member = app::get('b2c')->model('members');
            $regtime = $obj_member->getList('regtime',array('member_id'=>$_POST['member_id']));
            $use_pass_data['login_name'] = $_POST['login_name'];
            $use_pass_data['createtime'] = $regtime[0]['regtime'];            
            $login_password = pam_encrypt::get_encrypted_password(trim($_POST['login_password']),'member',$use_pass_data);
            $staff_data=array(
            	'member_id'=>$_POST['member_id'],
                'login_name'=>$_POST['login_name'],
                'staff_name'=>$_POST['staff_name'],
                'login_password'=>$login_password,
                'branch_id'=>$_POST['branch_id'],
                'ctime'=>$use_pass_data['createtime'],
                'disabled'=>$_POST['disabled'],
            );
            if($_POST['staff_id']){
                if($this->app->model('local_staff')->update($staff_data,array('staff_id'=>$_POST['staff_id']))){
                $this->end(true,app::get('b2c')->_('保存成功'));
                } 
                                
            }else{
                $data=$this->app->model('local_staff')->getList('*',array('login_name'=>$_POST['login_name']));
                if($data){
                        $this->end(false,app::get('b2c')->_('用户名已存在'));
                }
                if($this->app->model('local_staff')->insert($staff_data)){
                $this->end(true,app::get('b2c')->_('添加成功'));
                }
                  
            }
           
            
        }


}
