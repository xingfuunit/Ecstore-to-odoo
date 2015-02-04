<?php

class mobileapi_ctl_admin_indexad_group extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';
    function index(){
        $this->finder('mobileapi_mdl_indexad_group',array(
            'title'=>app::get('b2c')->_('广告分组'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_indexad_group&act=create','target'=>'_blank'),

            )
            ));
    }

    function create(){
    	$group = array(
    			"group_code" => "group_",
    			"disabled" => "false",
    			'begin_time' => time(),
    			'end_time' => time(),
                'pubtime' => time(),
    			"ordernum" => "50",
    	);
    	$this->pagedata['group'] = $group;
    	$this->singlepage('admin/indexad/group_detail.html');
    }

    function save(){
    	$this->begin('');
    	$objAd = $this->app->model('indexad_group');
    	
    	$data = $_POST;
    	
    	$hour = $param['_DTIME_']['H'];
    	$begin_h = $hour['begin_time'];
    	$end_h = $hour['end_time'];
    	
    	// 开始时间&结束时间
    	foreach ($data['_DTIME_'] as $val) {
    		$temp['begin_time'][] = $val['begin_time'];
    		$temp['end_time'][] = $val['end_time'];
            $temp['pubtime'][] = $val['pubtime'];
    	}
    	$data['begin_time'] = strtotime($data['begin_time'].' '. implode(':', $temp['begin_time']));
    	$data['end_time'] = strtotime($data['end_time'].' '. implode(':', $temp['end_time']));
        $data['pubtime'] = strtotime($data['pubtime'].' '. implode(':', $temp['pubtime']));
    	
    	unset($data['_DTYPE_TIME']);
    	unset($data['_DTIME_']);

    	
    	if ($objAd->save($data)) {
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }
   

    function edit($group_id){
    	header("Cache-Control:no-store");
    	
        $this->path[] = array('text'=>app::get('b2c')->_('广告分组编辑'));
        $group_obj = $this->app->model('indexad_group');
        $this->pagedata['group'] = $group_obj->dump($group_id);

        $this->singlepage('admin/indexad/group_detail.html');
    }

}
