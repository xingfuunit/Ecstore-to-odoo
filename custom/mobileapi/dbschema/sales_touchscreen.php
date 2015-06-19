<?php
$db ['sales_touchscreen'] = array (
		'columns' => array (
				'ad_id' => array (
						'type' => 'number',
						'required' => true,
						'pkey' => true,
						'extra' => 'auto_increment',
						'label' => app::get ( 'b2c' )->_ ( '广告id' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '广告id' ),
						'editable' => false,
						'in_list' => false,
						'default_in_list' => false 
				),
				'ad_name' => array (
						'type' => 'varchar(50)',
						'label' => app::get ( 'b2c' )->_ ( '广告名称' ),
						'width' => 350,
						'is_title' => true,
						'required' => true,
						'comment' => app::get ( 'b2c' )->_ ( '广告名称' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'pos_id' => array (
					'type' => 'number',
					'label' => app::get ( 'b2c' )->_ ( '广告位置id' ),
					'default' => '1',
					'is_title' => true,
					'required' => true,
					'comment' => app::get ( 'b2c' )->_ ( '广告位置id' ),
					'editable' => true
				),
				'pos_name' => array (
					'type' => 'varchar(50)',
					'label' => app::get ( 'b2c' )->_ ( '广告位置' ),
					'width' => 180,
					'default' => '',
					'is_title' => true,
					'required' => true,
					'comment' => app::get ( 'b2c' )->_ ( '广告位置' ),
					'editable' => true,
					'in_list' => true,
					'default_in_list' => true
				),
				'ad_img' => array (
						'type' => 'varchar(255)',
						'comment' => app::get ( 'b2c' )->_ ( '广告图片' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '广告图片' ),
						'in_list' => false,
						'default_in_list' => false 
				),
				'ad_img_w' => array (
						'type' => 'number',
						'comment' => app::get ( 'b2c' )->_ ( '图片宽' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '图片宽' ),
						'in_list' => false,
						'default_in_list' => false 
				),
				
				'ad_img_h' => array (
						'type' => 'number',
						'comment' => app::get ( 'b2c' )->_ ( '图片高' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '图片高' ),
						'in_list' => false,
						'default_in_list' => false 
				),
				'ad_url' => array (
						'type' => 'varchar(255)',
						'label' => app::get ( 'b2c' )->_ ( '链接地址' ),
						'width' => 350,
						'comment' => app::get ( 'b2c' )->_ ( '链接地址' ),
						'editable' => false,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'vodfile' => array (
						'type' => 'varchar(255)',
						'comment' => app::get ( 'b2c' )->_ ( '视频地址' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '视频地址' ),
						'in_list' => false,
						'default_in_list' => false 
				),
				'url_type' => array (
						'type' => array (
								'pic' => app::get ( 'b2c' )->_ ( '图片' ),
								'vod'=> app::get ( 'b2c' )->_ ( '视频' ),
								'bg' => app::get ( 'b2c' )->_ ( '背景' ),
						),
						'default' => 'pic',
						'label' => app::get ( 'b2c' )->_ ( '广告类型' ),
						'width' => 60,
						'comment' => app::get ( 'b2c' )->_ ( '广告类型' ),
						'editable' => true,
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
						'in_list' => false,
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
						'in_list' => true ,
						'default_in_list' => true 
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
		'version' => '$Rev: 40656 $',
		'comment' => app::get ( 'b2c' )->_ ( '门店触屏系统' ) 
);
