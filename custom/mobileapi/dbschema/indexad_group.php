<?php
$db ['indexad_group'] = array (
		'columns' => array (
				'group_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'label' => app::get ( 'b2c' )->_ ( '分组ID' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '分组ID' ),
						'editable' => false,
						'in_list' => false,
						'default_in_list' => false 
				),
				'group_name' => array (
						'type' => 'varchar(50)',
						'default' => '',
						'label' => app::get ( 'b2c' )->_ ( '分组名' ),
						'width' => 180,
						'is_title' => true,
						'required' => true,
						'comment' => app::get ( 'b2c' )->_ ( '分组名' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'group_code' => array (
						'type' => 'varchar(50)',
						'default' => '',
						'label' => app::get ( 'b2c' )->_ ( '分组代码' ),
						'width' => 180,
						'is_title' => true,
						'comment' => app::get ( 'b2c' )->_ ( '分组代码' ),
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => false 
				),
				'showtype' => array (
						'type' => array (
								'top_ad' => app::get ( 'b2c' )->_ ( '顶部广告' ),
								'second_ad' => app::get ( 'b2c' )->_ ( '秒杀' ),
								'groupbuy_ad' => app::get ( 'b2c' )->_ ( '团购' ),
								'normal' => app::get ( 'b2c' )->_ ( '通用' )
						),
						'default' => 'normal',
						'label' => app::get ( 'b2c' )->_ ( '展示类型' ),
						'width' => 60,
						'comment' => app::get ( 'b2c' )->_ ( '展示类型' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true
				),
				
				'begin_time' => array (
						'type' => 'time',
						'default' => '0',
						'width' => 110,
						'editable' => false,
						'in_list' => true,
						'label' => app::get ( 'b2c' )->_ ( '开始时间' ),
						'comment' => app::get ( 'b2c' )->_ ( '开始时间' ),
				),
				
				'end_time' => array (
						'type' => 'time',
						'default' => '0',
						'width' => 110,
						'editable' => false,
						'in_list' => true,
						'label' => app::get ( 'b2c' )->_ ( '结束时间' ),
						'comment' => app::get ( 'b2c' )->_ ( '结束时间' ),
				),

				'pubtime' => array (
						'type' => 'time',
						'default' => '0',
						'width' => 110,
						'editable' => false,
						'in_list' => true,
						'label' => app::get ( 'b2c' )->_ ( '发布时间' ),
						'comment' => app::get ( 'b2c' )->_ ( '发布时间' ),
				),
				
				'column_size' => array (
						'type' => 'int(3)',
						'default' => '50',
						'label' => app::get ( 'b2c' )->_ ( '每行图片数' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '每行图片数' ),
						'editable' => true,
						'in_list' => true
				),
				
				'disabled' => array (
						'type' => 'bool',
						'default' => 'false',
						'comment' => app::get ( 'b2c' )->_ ( '失效' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '失效' ),
						'in_list' => true,
						'deny_export' => true
				),
				
				'ordernum' => array (
						'type' => 'number',
						'default' => '50',
						'label' => app::get ( 'b2c' )->_ ( '排序' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '排序' ),
						'editable' => true,
						'in_list' => true 
				)
		),
		'index' => array (
				'ind_disabled' => array (
						'columns' => array (
								0 => 'disabled' 
						) 
				),
				'ind_ordernum' => array (
						'columns' => array (
								0 => 'ordernum' 
						) 
				) 
		),
		'version' => '$Rev: 121231 $',
		'comment' => app::get ( 'b2c' )->_ ( '广告分组' ) 
);
