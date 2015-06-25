<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db ['member_withdrawal'] = array (
		'columns' => array (
				'id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						//'label' => 'ID',
						'width' => 80,
						'editable' => false,
						'in_list' => true,
						'default_in_list' => true 
				),
				
				'member_id' => array (
						'type' => 'number',
						'label' => app::get ( 'b2c' )->_ ( '用户ID' ),
						'required' => true,
						'default_in_list' => true,
						'in_list' => true,
				),
				'amount' => array (
						'type' => 'money',
						'label' => app::get ( 'b2c' )->_ ( '申请提现金额' ),
						'in_list' => true,
						'is_title' => true,
						'default_in_list' => true,
						'required' => true 
				),
				
				'alipay_user' => array (
						'type' => 'varchar(225)',
						'default' => '',
						'width' => 105,
						'label' => app::get ( 'b2c' )->_ ( '提现支付宝帐号' ),
						'in_list' => true
				),
				
				'remark' => array (
						'label' => app::get ( 'b2c' )->_ ( '备注' ),
						'type' => 'text',
						'default' => '',
						'width' => 105,
						'in_list' => true 
				),
				'create_time' => array (
						'type' => 'time',
						'label' => app::get ( 'b2c' )->_ ( '申请时间' ),
						'width' => 140,
						'editable' => false,
						'filtertype' => 'time',
						'filterdefault' => true,
						'in_list' => true 
				),
				'has_op' => array (
						'type' => 'bool',
						'label' => app::get ( 'b2c' )->_ ( '是否处理' ),
						'default' => 'false',
						'in_list' => true,
						'default_in_list' => true 
				) 
		),
		'index'  => array (
				'ind_member_id' => array (
						'columns'   => array (
								0   => 'member_id',
						),
				),
		),
		'engine' => 'innodb',
		'version' => '$Rev 2343$',
		'comment' => app::get ( 'b2c' )->_ ( '提现申请表' ) 
);
