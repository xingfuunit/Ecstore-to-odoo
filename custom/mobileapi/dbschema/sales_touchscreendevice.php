<?php
$db ['sales_touchscreendevice'] = array (
		'columns' => array (
				'device_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'label' => app::get ( 'b2c' )->_ ( 'id' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( 'id' ),
						'editable' => false,
						'in_list' => false,
						'default_in_list' => false 
				),
				'device_name' => array (
						'type' => 'varchar(250)',
						'label' => app::get ( 'b2c' )->_ ( '设备ID' ),
						'width' => 350,
						'is_title' => true,
						'required' => true,
						'comment' => app::get ( 'b2c' )->_ ( '设备ID' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'branch_bn' => array (
						'type' => 'varchar(32)',
						'default' => '',
						'label' => app::get ( 'b2c' )->_ ( '门店编号' ),
						'width' => 100,
						'comment' => app::get ( 'b2c' )->_ ( '门店编号' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => false 
				),
				'branch_name' => array (
						'type' => 'varchar(200)',
						'default' => '',
						'label' => app::get ( 'b2c' )->_ ( '门店名称' ),
						'width' => 200,
						'comment' => app::get ( 'b2c' )->_ ( '门店名称' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'disabled' => array (
						'type' => 'bool',
						'default' => 'false',
						'comment' => app::get ( 'b2c' )->_ ( '失效' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '失效' ),
						'width' => 60,
						'in_list' => true,
						'deny_export' => true,
						'default_in_list' => true 
				),
				'ordernum' => array (
						'type' => 'number',
						'default' => '0',
						'label' => app::get ( 'b2c' )->_ ( '排序' ),
						'width' => 60,
						'comment' => app::get ( 'b2c' )->_ ( '排序' ),
						'editable' => true,
						'in_list' => false ,
						'default_in_list' => false 
				),
				'last_modify' => array (
						'type' => 'last_modify',
						'label' => app::get ( 'b2c' )->_ ( '更新时间' ),
						'width' => 110,
						'editable' => false,
						'in_list' => true,
						'orderby' => true
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
		'version' => '$Rev: 40657 $',
		'comment' => app::get ( 'b2c' )->_ ( '门店触屏设备' ) 
);
