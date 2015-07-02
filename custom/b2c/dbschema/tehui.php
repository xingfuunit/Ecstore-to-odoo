<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['tehui']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('活动特惠ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),

    'tehui_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' => app::get('b2c')->_('活动特惠名称'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  		'alt_name' =>
  		array (
  				'type' => 'varchar(100)',
  				'required' => true,
  				'is_title' => true,
  				'default' => '',
  				'label' => app::get('b2c')->_('活动标题'),
  				'width' => 110,
  				'editable' => false,
  				'in_list' => true,
  				'default_in_list' => true,
  		),
  	/* 	'current' =>
  		array (
      'type' => 'bool',
      'required' => true,
      'label' => app::get('b2c')->_('当前使用'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'true',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ), */
    'start_time'=>
    array(
    'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('开始时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
    'end_time'=>
    array(
    		'type' => 'varchar(100)',
    		'label' => app::get('b2c')->_('结束时间'),
    		'width' => 110,
    		'editable' => false,
    		'filtertype' => 'time',
    		'filterdefault' => true,
    		'in_list' => true,
    		'default_in_list' => true,
    		'orderby' => true,
    ),
    'img_url' => array (
    		'type' => 'varchar(255)',
    		'comment' => app::get ( 'b2c' )->_ ( '广告图片' ),
    		'editable' => false,
    		'label' => app::get ( 'b2c' )->_ ( '广告图片' ),
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
),
  'version' => '$Rev: 41329 $',
  'comment' => app::get('b2c')->_('类别属性值有限表'),
);
