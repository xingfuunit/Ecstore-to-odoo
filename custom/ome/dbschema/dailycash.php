<?php
$db ['dailycash'] = array ( 
		'columns' => array (
				'cash_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'editable' => false ,
                                                'label' => '记录ID' ,
                                                'in_list' => true,
						'default_in_list' => true,
                                                'width'=>80,

				),
				'branch_id' => array (
						'type' => 'number',
                                                'required' => true,
                                                'label' => '仓库门店ID' ,
                                                'comment'=>'仓库门店ID',
                                                'in_list' => true,
						'default_in_list' => false,
						
				),
                                'branch_name' => array (
						'type' => 'varchar(64)',
                                                'required' => true,
                                                'label' => '仓库名称' ,
                                                'comment'=>'仓库名称',
                                                'in_list' => true,
						'default_in_list' => false,
						
				),
                                'staff_id' => array (
						'type' => 'number',
                                                'required' => true,
                                                'label' => '录入人员工ID' ,
                                                 'comment'=>'录入人员工ID',
                                                'in_list' => true,
						'default_in_list' => true,
						'editable' => false 
				),
                                'staff_name' => array (
						'type' => 'varchar(64)',
                                                'required' => true,
                                                'label' => '录入人' ,
                                                 'comment'=>'录入人',
                                                'in_list' => true,
						'default_in_list' => true,
						'editable' => false 
				),
                                'bank_name' => array (
						'type' => 'varchar(64)',
                                                'required' => true,
                                                'label' => '存入银行名称' ,
                                                 'comment'=>'存入银行名称',
                                                'in_list' => true,
						'default_in_list' => true,
						 'width'=>150,
				),
                                'bank_num' => array (
						'type' => 'varchar(32)',
                                                'label' => '存入银行账户' ,
                                                 'comment'=>'存入银行账户',
                                                'in_list' => true,
						'default_in_list' => true,
						 'width'=>150,
				),
				'import_money' => array (
						'type' => 'money',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '存入金额' ,
                                                 'comment'=>'存入金额',
				),
                                'import_money_upper' => array (
						'type' => 'varchar(255)',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '存入金额大写' ,
                                                 'comment'=>'存入金额大写',
				),
				'cash_amount' => array (
						'type' => 'money',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '现金期初余额',
                                                'comment'=>'现金期初余额',
				),
                                'cash_amount_upper' => array (
						'type' => 'varchar(255)',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '现金期初余额大写' ,
                                                 'comment'=>'现金期初余额大写',
				),
				'cash_balance' => array (
						'type' => 'money',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'width' => 130,
						'label' => '门店现金结余',
                                                'comment'=>'门店现金结余',
				),
                                'cash_balance_upper' => array (
						'type' => 'varchar(255)',
						'required' => true,
						'in_list' => true,
						'default_in_list' => true,
						'label' => '门店现金结余大写' ,
                                                'comment'=>'门店现金结余大写',
				),
                                'ctime'     =>      array(
                                                'type' => 'time',
						'in_list' => true,
						'label' => '记录时间' ,
                                                'comment'=>'记录时间',
                                ),
                              
				'memo' => array (
						'type' => 'text',
						'editable' => false,
						'in_list' => true,
						'label' => '备注' ,
                                                'comment'=>'备注',
				),

		),
		
		'index' => array (
				'branch_id' => array (
						'columns' => array (
								0 => 'branch_id' 
						),
				) ,
                                'staff_id' => array (
						'columns' => array (
								0 => 'staff_id' 
						),
				) 
		),
		'engine' => 'innodb',
		'version' => '$Rev: 5465131' ,
                'comment' => app::get ( 'ome' )->_ ( '门店现金日结记录' ) 
);