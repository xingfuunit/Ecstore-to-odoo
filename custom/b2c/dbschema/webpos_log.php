<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db ['webpos_log'] = array (
		'columns' => array ( 
				'log_id' => array ( 
						'type' => 'number',
						'order'=>'1',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'editable' => false,
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '操作日志ID' ) 
				),
				'order_id' => array (
						'type' => 'varchar(100)',
						'required' => true,
						'order'=>'2',
						'default' => 0,
						'editable' => false,
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '订单ID' ) 
				),
				'member_id' => array (
						'type' => 'number', // 'table:users@desktop',
						'label' => app::get ( 'b2c' )->_ ( '会员ID' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'normal',
						'in_list' => true,
						'default_in_list' => true,
						'order'=>'3',
						'comment' => app::get ( 'b2c' )->_ ( '会员ID' ) 
				),
				'op_id' => array (
						'type' => 'number', // 'table:users@desktop',
						'label' => app::get ( 'b2c' )->_ ( '操作员' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'normal',
						'in_list' => true,
						'order'=>'4',
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '操作员ID' ) 
				),
				'op_name' => array (
						'type' => 'varchar(100)',
						'label' => app::get ( 'b2c' )->_ ( '操作人名称' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'normal',
						'filterdefault' => true,
						'default_in_list' => true,
						'order'=>'5',
						'in_list' => true 
				),
				'op_branch_id' => array (
						'type' => 'number', // 'table:users@desktop',
						'label' => app::get ( 'b2c' )->_ ( '操作门店ID' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'normal',
						'in_list' => true,
						'default_in_list' => true,
						'order'=>'6',
						'comment' => app::get ( 'b2c' )->_ ( '操作门店ID' ) 
				),
				'op_time' => array (
						'type' => 'time',
						'label' => app::get ( 'b2c' )->_ ( '操作时间' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'time',
						'orderby' => true,
						'filterdefault' => true,
						'in_list' => true,
						'order'=>'7',
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '操作时间' ) 
				),
				'op_type' => array (
						'type' => array (
								'order_creat' => app::get ( 'b2c' )->_ ( 'webpos生成订单' ),
								'recharge' => app::get ( 'b2c' )->_ ( '预存款充值' ),
								'giftcard' => app::get ( 'b2c' )->_ ( '充值券充值' ) 
						),
						'default' => 'order_creat',
						'required' => true,
						'label' => app::get ( 'b2c' )->_ ( '操作类型' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'yes',
						'order'=>'8',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '操作类型' ) 
				),
				'money' => array (
						'type' => 'varchar(100)',
						'label' => app::get ( 'b2c' )->_ ( '金额' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'normal',
						'filterdefault' => true,
						'order'=>'9',
						'default_in_list' => true,
						'in_list' => true 
				),
				'pay_way' => array (
						'type' => 'varchar(100)',
						'required' => true,
						'label' => app::get ( 'b2c' )->_ ( '支付方式' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'yes',
						'order'=>'10',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '支付方式' ) 
				),
				'result' => array (
						'type' => array (
								'SUCCESS' => app::get ( 'b2c' )->_ ( '成功' ),
								'FAILURE' => app::get ( 'b2c' )->_ ( '失败' ) 
						),
						'required' => true,
						'label' => app::get ( 'b2c' )->_ ( '操作结果' ),
						'width' => 110,
						'editable' => false,
						'filtertype' => 'yes',
						'order'=>'11',
						'filterdefault' => true,
						'in_list' => true,
						'default_in_list' => true,
						'comment' => app::get ( 'b2c' )->_ ( '日志结果' ) 
				), 
				'log_text' => array (
						'type' => 'longtext',
						'label' => app::get ( 'b2c' )->_ ( '操作内容' ),
						'editable' => false,
						'in_list' => true,
						'default_in_list' => true,
						'order'=>'12',
						'comment' => app::get ( 'b2c' )->_ ( '操作内容' ) 
				),
				'addon' => array (
						'type' => 'longtext',
						'label' => app::get ( 'b2c' )->_ ( '序列化数据' ),
						'editable' => false,
						'in_list' => true,
						'default_in_list' => true,
						'order'=>'13',
						'comment' => app::get ( 'b2c' )->_ ( '序列化数据' ) 
				) 
		),
		'engine' => 'innodb',
		'version' => '$Rev: 46974 $',
		'comment' => app::get ( 'b2c' )->_ ( 'webpos操作日志表' ) 
);
