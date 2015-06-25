<?php
$db ['local_staff'] = array (
		'columns' => array (
				'staff_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'editable' => false ,
                                                'label' => '员工ID' ,
                                                'in_list' => true,
						'default_in_list' => true,
				),
				'member_id' => array (
						'type' => 'number',
						'required' => true,
						'editable' => false ,
						'comment' => app::get('b2c')->_('会员id'),
                        'label' => app::get('b2c')->_('会员id'),
				),
                                'login_name'=>array(
                                        'type'=>'varchar(100)',
                                        'is_title'=>true,
                                        'required' => true,
                                        'searchtype' => 'has',
										'filtertype' => 'normal',
										'filterdefault' => true,
                                        'comment' => app::get('b2c')->_('登录名'),
                                        'label' => app::get('b2c')->_('登录名'),
                                        'in_list' => true,
					'default_in_list' => true,
                                    
                                ),
                                'login_password'=>array(
                                        'type'=>'varchar(32)',
                                       // 'required' => true,
                                        'comment' => app::get('b2c')->_('登录密码'),
                                        'label' => app::get('b2c')->_('登录密码'),
                                        
                                ),
								'over_password'=>array(
										'type'=>'varchar(32)',
										// 'required' => true,
										'comment' => app::get('b2c')->_('交班密码'),
										'label' => app::get('b2c')->_('交班密码'),
				
								),
                                'branch_id'=>array(
                                        'type'=>'number',
                                        'required' => true,
                                        'comment' => app::get('b2c')->_('仓库id'),
                                         'label' => app::get('b2c')->_('仓库id'),
                                         'in_list' => true,
					'default_in_list' => true,
                                       
                                ),
				'staff_name' => array (
						'type' => 'varchar(200)',
						'required' => true,
						'is_title' => true,
						'searchtype' => 'has',
						'filtertype' => 'normal',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'width' => 130,
                                                'comment' => app::get('b2c')->_('员工姓名'),
						'label' => app::get('b2c')->_('员工姓名'),
				),
                                 'logintime'=>array(
                                        'type'=>'time',
                                       // 'required' => true,
                                        'comment' => app::get('b2c')->_('登陆时间'),
                                        'label' => app::get('b2c')->_('登陆时间'),
                                        'in_list' => true,
					'default_in_list' => true,
                                ),
                                 'logouttime'=>array(
                                        'type'=>'time',
                                       // 'required' => true,
                                        'comment' => app::get('b2c')->_('退出时间'),
                                         'label' => app::get('b2c')->_('退出时间'),
                                         'in_list' => true,
					'default_in_list' => true,
                                ),
                                 'ctime'=>array(
                                        'type'=>'time',
                                        'required' => true,
                                        'comment' => app::get('b2c')->_('创建时间'),
                                         'label' => app::get('b2c')->_('创建时间'),
                                         'in_list' => true,
					'default_in_list' => true,
                                ),
			
				'disabled' => array (
						'type' => 'bool',
						'required' => true,
						'editable' => false,
						'default' => 'false' ,
                                                'label' => app::get('b2c')->_('是否失效'),
                                                'in_list' => true,
                                                'default_in_list' => true,
				),
				
		),
		
		'index' => array (
				'login_name' => array (
						'columns' => array (
								0 => 'login_name' 
						),
						'prefix' => 'unique' 
				) ,
                                'branch_id' => array (
						'columns' => array (
								0 => 'branch_id' 
						),
						 
				)
		),
		'engine' => 'innodb',
		'version' => '$Rev: 13212514' 
);
