<?php
$db ['promotion_into_logs'] = array (
		'columns' => array (
				'log_id' => array (
					      'type' => 'number',
					      'required' => true,
					      'pkey' => true,
					      'extra' => 'auto_increment',
					      'label' => app::get('b2c')->_('日志id'),
					      'width' => 110,
					      'comment' => app::get('b2c')->_('日志id'),
					      'editable' => false,
					      'hidden' => true,
					      'in_list' => true,
					      'default_in_list' => true,
				),
									
				'pri_id' => array (
						'type' => 'number',
						'label' => '分成ID' ,
						'width' => 80,
						'editable' => false,
						'searchtype'=> 'has',
						'in_list' => true,
						'default_in_list' => true,
				),
				
				'member_id' => array (
						'type' => 'number',
						'default' => '0',
						'required' => true,
						'label' => '用户ID',
						'width' => 80,
						'default' => 0,
						'editable' => false,
						'in_list' => true,
				),
				
				'money' =>  array (
				      'type' => 'money',
				      'required' => true,
				      'default' => 0,
				      'label' => app::get('b2c')->_('分成金额'),
				      'width' => 110,
				      'comment' => app::get('b2c')->_('分成金额'),
				      'editable' => false,
				      'in_list' => true,
				   ),

				'pri_type' =>
				array (
						'type' =>
						array (
								'0' => app::get('b2c')->_('微店推广'),
								'1' => app::get('b2c')->_('经销商推广'),
								'2' => app::get('b2c')->_('发货分成'),
						),
						'default' => '0',
						'label' => app::get('b2c')->_('分成方式'),
						'width' => 75,
						'editable' => false,
						'in_list'   => true,
						'default_in_list'   => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				),
				
				'money_status' =>
				array (
						'type' =>
						array (
								'0' => app::get('b2c')->_('已存入预存款'),
						),
						'default' => '0',
						'required' => true,
						'label' => app::get('b2c')->_('处理状态'),
						'width' => 75,
						'editable' => false,
						'in_list' => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				
				),
				    
				    'mtime' => 
				    array (
				      'type' => 'time',
				      'required' => true,
				      'default' => 0,
				      'label' => app::get('b2c')->_('交易时间'),
				      'width' => 75,
				      'comment' => app::get('b2c')->_('交易时间'),
				      'editable' => false,
				      'in_list' => true,
				    ),
		)
		,
		'index' => array (
				'member_id' => array (
						'columns' => array (
								0 => 'member_id' 
						) 
				) 
		),
		'engine' => 'innodb',
		'version' => '$Rev: 42376 $',
		'comment' => '推广分成记录表' 
);
