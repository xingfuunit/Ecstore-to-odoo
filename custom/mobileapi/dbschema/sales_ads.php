<?php
$db ['sales_ads'] = array (
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
						'width' => 180,
						'is_title' => true,
						'required' => true,
						'comment' => app::get ( 'b2c' )->_ ( '广告名称' ),
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				'ad_position' => array (
					'type' => 'varchar(50)',
					'label' => app::get ( 'b2c' )->_ ( '广告位置' ),
					'width' => 180,
					'default' => '1',
					'is_title' => true,
					'required' => true,
					'comment' => app::get ( 'b2c' )->_ ( '广告位置' ),
					'editable' => true,
					'searchtype' => 'has',
					'in_list' => false,
					'default_in_list' => false
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
						'type' => 'varchar(12)',
						'comment' => app::get ( 'b2c' )->_ ( '图片宽' ),
						'editable' => false,
						'label' => app::get ( 'b2c' )->_ ( '图片宽' ),
						'in_list' => false,
						'default_in_list' => false 
				),
				
				'ad_img_h' => array (
						'type' => 'varchar(12)',
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
						'editable' => true,
						'searchtype' => 'has',
						'in_list' => true,
						'default_in_list' => true 
				),
				
				'url_type' => array (
						'type' => array (
								'goods' => app::get ( 'b2c' )->_ ( '产品' ),
								'cat' => app::get ( 'b2c' )->_ ( '分类' ),
								'virtual_cat' => app::get ( 'b2c' )->_ ( '虚拟分类' ),
								'article' => app::get ( 'b2c' )->_ ( '文章' ),
								'none' => app::get ( 'b2c' )->_ ( '不链接' ) 
						),
						'default' => 'goods',
						'label' => app::get ( 'b2c' )->_ ( '链接类型' ),
						'width' => 60,
						'comment' => app::get ( 'b2c' )->_ ( '链接类型' ),
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
						'in_list' => true,
						'deny_export' => true 
				),
				'ordernum' => array (
						'type' => 'number',
						'label' => app::get ( 'b2c' )->_ ( '排序' ),
						'width' => 150,
						'comment' => app::get ( 'b2c' )->_ ( '排序' ),
						'editable' => true,
						'in_list' => true 
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
		'comment' => app::get ( 'b2c' )->_ ( '移动首页广告' ) 
);
