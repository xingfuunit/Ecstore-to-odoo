<?php
$db ['branch'] = array ( 
		'columns' => array (
				'branch_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'editable' => false 
				),
				'wms_id' => array (
						'type' => 'number',
						'editable' => false 
				),
				'branch_bn' => array (
						'type' => 'varchar(32)',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '仓库编号' 
				),
				'storage_code' => array (
						'type' => 'varchar(32)',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '库内存放点编号' 
				),
				'name' => array (
						'type' => 'varchar(200)',
						'required' => true,
						'editable' => false,
						'is_title' => true,
						'searchtype' => 'has',
						'filtertype' => 'normal',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'width' => 130,
						'label' => '仓库名' 
				),
				'name_b' => array (
						'type' => 'varchar(200)',
						'required' => true,
						'editable' => false,						
						'searchtype' => 'has',
						'filtertype' => 'normal',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'width' => 130,
						'label' => '仓库前端显示名', 
						'comment' => '仓库前端显示名',
				),
				'parent_id' => array (
						'type' => 'number',
						'default' => 0 
				),
				'type' => array (
						'type' => array (
								'main' => '主仓',
								'aftersale' => '售后仓',
								'damaged' => '残损仓' 
						),
						'in_list' => true,
						'default_in_list' => true,
						'width' => 130,
						'label' => '仓库类型',
						'required' => true,
						'default' => 'main' 
				),
				'disabled' => array (
						'type' => 'bool',
						'required' => true,
						'editable' => false,
						'default' => 'false' 
				),
				'is_show' => array (
						'type' => 'bool',
						'editable' => false,
						'default' => 'false',
						'label' => '前端显示',
						'in_list' => true,
						'default_in_list' => true
				),
				'area' => array (
						'type' => 'region',
						'editable' => false,
						'comment' => app::get ( 'b2c' )->_ ( '地区' ),
						'label' => app::get ( 'b2c' )->_ ( '地区' ),
						'in_list' => true,
						'default_in_list' => true 
				),
				'address' => array (
						'type' => 'varchar(200)',
						'editable' => false,
						'label' => '联系人地址',
						'in_list' => true 
				),
				'region_id' =>
				array (
						'type' => 'int(8)',
						'required' => false,
						'editable' => false,
						'comment' => app::get ( 'b2c' )->_ ( '父级ID 废弃' ),
				),
				'region_path' =>
				array (
						'type' => 'varchar(255)',
						'width'=>300,
						'editable' => false,
						'comment' => app::get ( 'b2c' )->_ ( '地址全路径' ),
				),
				'zip' => array (
						'type' => 'varchar(20)',
						'editable' => false,
						'label' => '联系人邮编',
						'in_list' => true 
				),
				'phone' => array (
						'type' => 'varchar(100)',
						'editable' => false,
						'label' => '联系人电话',
						'in_list' => true,
						'default_in_list' => true 
				),
				'uname' => array (
						'type' => 'varchar(100)',
						'editable' => false,
						'label' => '联系人姓名',
						'in_list' => true,
						'default_in_list' => true 
				),
				'mobile' => array (
						'type' => 'varchar(100)',
						'editable' => false,
						'label' => '联系人手机',
						'in_list' => true,
						'default_in_list' => true 
				),
				'sex' => array (
						'type' => array (
								'male' => '男',
								'female' => '女' 
						),
						'default' => 'male',
						'editable' => false,
						'label' => '性别',
						'in_list' => true 
				),
				'memo' => array (
						'type' => 'text',
						'editable' => false,
						'in_list' => true,
						'label' => '备注' 
				),
				'stock_threshold' => array (
						'type' => 'number',
						'editable' => false,
						'default' => 1 
				),
				'stock_safe_type' => array (
						'type' => array (
								'supplier' => '供应商补货水平',
								'branch' => '仓库设置' 
						),
						'editable' => false,
						'label' => '安全库存计算类型',
						'default' => 'branch' 
				),
				'stock_safe_day' => array (
						'type' => 'mediumint unsigned',
						'editable' => false,
						'default' => 7 
				),
				'stock_safe_time' => array (
						'type' => 'mediumint unsigned',
						'editable' => false,
						'default' => 0 
				),
				'attr' => array (
						'type' => array (
								'true' => '线上',
								'false' => '线下' 
						),
						'editable' => false,
						'default' => 'true',
						'label' => '仓库属性' 
				),
				'online' => array (
						'type' => array (
								'true' => '电子商务仓',
								'false' => '传统业务仓' 
						),
						'editable' => false,
						'default' => 'true',
						'label' => '仓库类型' 
				),
				'weight' => array (
						'type' => 'number',
						'editable' => false,
						'in_list' => true,
						'default' => 0,
						'label' => '权重' 
				),
				'defaulted' => array (
						'type' => 'bool',
						'required' => true,
						'editable' => false,
						'default' => 'false' 
				),
				'area_conf' => array (
						'type' => 'longtext',
						'editable' => false 
				),
				'is_deliv_branch' => array (
						'type' => array (
								'true' => '发货仓库',
								'false' => '备货仓库' 
						),
						'label' => '发货属性',
						'in_list' => true,
						'default_in_list' => true,
						'default' => 'true' 
				),
				'bind_conf' => array (
						'type' => 'longtext',
						'editable' => false,
						'label' => '发货仓绑定配置' 
				),
				'owner' => array (
						'type' => array (
								'1' => '自建仓库',
								'2' => '第三方仓库' 
						),
						'label' => '仓库归属',
						'default' => '1',
						'in_list' => true,
						'default_in_list' => true,
						'filtertype' => true 
				),
				'nostore_sell' =>
				array (
						'type' => 'bool',
						'default' => 'true',
						'required' => true,
						'label' => app::get('b2c')->_('是否允许无库存销售'),
						'width' => 30,
						'filtertype' => 'yes',
						'editable' => false,
						'in_list' => true,
				),
		),
		
		'index' => array (
				'ind_branch_bn' => array (
						'columns' => array (
								0 => 'branch_bn' 
						),
						'prefix' => 'unique' 
				) 
		),
		'engine' => 'innodb',
		'version' => '$Rev: 51996' 
);