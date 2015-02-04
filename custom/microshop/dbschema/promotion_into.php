<?php
$db ['promotion_into'] = array (
		'columns' => array (
				'pri_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'label' => '分成ID' ,
						'width' => 80,
						'editable' => false,
						'in_list'   => true,
				),
				'pro_member_id' => array (
						'type' => 'number',
						'default' => '0',
						'required' => true,
						'label' => '推广用户ID(微店)',
						'width' => 80,
						'default' => 0,
						'editable' => false,
						'in_list' => true,
				),
				
				'agency_id'     => array (
						'type'      => 'number',
						'default' => 0,
						'label'     => app::get('microshop')->_('经销商ID'),
						'width'     => 80,
						'editable'  => false,
						'in_list' => true,
				),
				
				'ship_members' => array (
						'type' => 'serialize',
						'default' => '',
						'required' => true,
						'label' => '发货商会员列表',
						'width' => 80,
						'default' => 0,
						'editable' => false
				),

				'order_id' => array (
						'type' => 'varchar(30)',
						'required' => true,
						'default' => 0,
						'editable' => false,
						'label' => '订单号',
						'searchtype'=> 'has',
						'in_list'   => true,
						'default_in_list'   => true,
						'filtertype'        => 'normal',
				),
				
				'ext_order_id' => array (
						'type' => 'varchar(30)',
						'required' => true,
						'default' => '',
						'editable' => false,
						'label' => '外部订单号',
						'in_list'   => true,
				),
				
				'from_system' => array (
						'type' => 'varchar(30)',
						'required' => true,
						'default' => 'ecstore',
						'editable' => false,
						'label' => '来源平台',
						'in_list'   => true,
				),
				
				'from_client' => array (
						'type' => 'varchar(30)',
						'required' => true,
						'default' => '',
						'editable' => false,
						'label' => '来源终端',
						'in_list'   => true,
				),
				
				'special_id' => array (
						'type' => 'number',
						'default' => '0',
						'required' => true,
						'label' => '所属专辑ID' ,
						'width' => 80,
						'editable' => false 
				),
				
				'special_name'  => array (
						'type'      => 'char(40)',
						'default' => '',
						'label'     => app::get('microshop')->_('专辑名称'),
						'width'     => 80,
						'editable'  => false,
						'searchtype'=> 'has',
						'in_list'   => true,
						'default_in_list'   => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				),
				
				'product_id' => array (
						'type' => 'number',
						'default' => '0',
						'required' => true,
						'label' => '商品ID' ,
						'width' => 80,
						'editable' => false 
				),
				'bn' => array (
						'type' => 'varchar(40)',
						'editable' => false,
						'is_title' => true,
						'label' => '商品编号',
						'searchtype'=> 'has',
						'in_list'   => true,
						'default_in_list'   => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				),
				'name' => array (
						'type' => 'varchar(200)',
						'editable' => false,
						'label' => '商品名称',
						'searchtype'=> 'has',
						'in_list'   => true,
						'default_in_list'   => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				),
				'cost' => array (
						'type' => 'money',
						'default' => '0',
						'editable' => false,
						'label' => '商品成本' ,
						'in_list'   => true,
				),
				'price' => array (
						'type' => 'money',
						'default' => '0',
						'required' => true,
						'editable' => false,
						'label' => '商品售出价',
						'in_list'   => true,
				),
				'nums' =>
				array (
						'type' => 'number',
						'default' => 1,
						'required' => true,
						'editable' => false,
						'label' => '商品购买数量',
						'in_list'   => true,
				),
				
				'pri_status' =>
				array (
						'type' =>
						array (
								'0' => app::get('b2c')->_('未处理'),
								'1' => app::get('b2c')->_('已处理'),
						),
						'default' => '0',
						'required' => true,
						'label' => app::get('b2c')->_('分成状态'),
						'width' => 75,
						'editable' => false,
						'in_list' => true,
						'filtertype'        => 'normal',
						'filterdefault'     => 'true',
				
				),
				
				'addtime' => array (
						'type' => 'time',
						'required' => true,
						'label' => '添加时间' ,
						'width' => 70,
						'editable' => false,
						'in_list' => true,
						'default_in_list' => true,
						'filtertype' => 'time',
						'filterdefault' => true 
				) 
		)
		,
		'index' => array (
				'member_id' => array (
						'columns' => array (
								0 => 'pri_id' 
						) 
				) 
		),
		'engine' => 'innodb',
		'version' => '$Rev: 42377 $',
		'comment' => '推广分成表' 
);
