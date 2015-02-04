<?php
$db ['keywords'] = array (
		'columns' => array (
				'kw_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'label' => app::get ( 'b2c' )->_ ( '关键字id' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '关键字id' ),
						'editable' => false,
						'in_list' => false,
						'default_in_list' => false 
				),
				'kw_name' => array (
						'type' => 'varchar(50)',
						'label' => app::get ( 'b2c' )->_ ( '名称' ),
						'width' => 180,
						'is_title' => true,
						'required' => true,
						'comment' => app::get ( 'b2c' )->_ ( '关键字名称' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'ordernum' => array (
						'type' => 'number',
						'label' => app::get ( 'b2c' )->_ ( '排序' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '排序' ),
						'editable' => true,
						'in_list' => true 
				),
		),
		'index' => array (
				'ind_disabled' => array (
						'columns' => array (
								0 => 'kw_id' 
						) 
				),
				'ind_ordernum' => array (
						'columns' => array (
								0 => 'kw_id' 
						) 
				) 
		),
		'version' => '$Rev: 40654  $',
		'comment' => app::get ( 'b2c' )->_ ( '移动关键字' ) 
);
