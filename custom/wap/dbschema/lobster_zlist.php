<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 
 * 龙虾集赞活动  - 赞列表
 */
$db['lobster_zlist']=array (
  'columns' =>
  array (
    'z_id' =>
	    array (
	      'type' => 'number',
	      'extra' => 'auto_increment',
	      'pkey' => true,
	      'label' => app::get('b2c')->_('赞id'),
	    ),

  	'z_openid' =>
  		array (
  			'type' => 'varchar(200)',
  			'editable' => false,
  			'in_list' => true,
  			'comment' => app::get('b2c')->_('赞者openid'),
  			'label' => app::get('b2c')->_('赞者openid'),
  			'default_in_list' => true,
  		),
  		
  	'z_nick_name' =>
  		array (
  				'type' => 'varchar(200)',
  				'editable' => false,
  				'in_list' => true,
  				'comment' => app::get('b2c')->_('赞者微信昵称'),
  				'label' => app::get('b2c')->_('赞者微信昵称'),
  				'default_in_list' => true,
  		),
  	'z_headimgurl'=>
  		array(
  				'type' => 'varchar(255)',
  				'editable' => false,
  				'in_list' => true,
  				'comment' => app::get('b2c')->_('赞者微信昵称'),
  				'label' => app::get('b2c')->_('赞者微信昵称'),
  				'default_in_list' => true,
  		),
  	'm_openid' =>
  		array (
  				'type' => 'varchar(200)',
  				'editable' => false,
  				'in_list' => true,
  				'comment' => app::get('b2c')->_('参与者微信openid'),
  				'label' => app::get('b2c')->_('参与者微信openid'),
  				'default_in_list' => true,
  		),
  		
  	'm_id'=>
  		array(
	  		'type' => 'number',
	  		'editable' => false,
	  		'in_list' => true,
	  		'width' => 10,
	  		'comment' => app::get('b2c')->_('参与者id'),
	  		'label' => app::get('b2c')->_('参与者id'),
	  		'default_in_list' => true,
  		),
  	
  	'z_time'=>
  		array(
  			'type' => 'time',
	        'comment' => app::get('b2c')->_('添加时间'),
	  		'label' => app::get('b2c')->_('添加时间'),
	        'width' => 110,
	        'editable' => false,
	        'in_list' => true,
	        'default_in_list' => true,
  		),
  	'active_id'=>
  		array(
	  		'type' => 'number',
	  		'editable' => false,
	  		'in_list' => true,
	  		'width' => 10,
	  		'comment' => app::get('b2c')->_('活动id'),
	  		'label' => app::get('b2c')->_('活动id'),
	  		'default_in_list' => true,
  		),
  		
    	
  ),
  'comment' => app::get('b2c')->_('集赞参与者表'),
 
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);