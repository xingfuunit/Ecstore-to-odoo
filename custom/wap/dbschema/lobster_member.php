<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 
 * 龙虾集赞活动 -参与者表
 */
$db['lobster_member']=array (
  'columns' =>
  array (
    'm_id' =>
	    array (
	      'type' => 'number',
	      'extra' => 'auto_increment',
	      'pkey' => true,
	      'label' => app::get('b2c')->_('参与者id'),
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
  		
  	'm_nick_name' =>
  		array (
  				'type' => 'varchar(200)',
  				'editable' => false,
  				'in_list' => true,
  				'comment' => app::get('b2c')->_('参与者微信昵称'),
  				'label' => app::get('b2c')->_('参与者微信昵称'),
  				'default_in_list' => true,
  		),
  		
  	'phone' =>
  		array (
  				'type' => 'varchar(100)',
  				'editable' => false,
  				'in_list' => true,
  				'comment' => app::get('b2c')->_('手机号码'),
  				'label' => app::get('b2c')->_('手机号码'),
  				'default_in_list' => true,
  		),
  	
  	'gift_id' =>
  		array (
  			'type' => 'number',
  			'editable' => false,
  			'in_list' => true,
  			'width' => 10,
  			'comment' => app::get('b2c')->_('奖品id'),
  			'label' => app::get('b2c')->_('奖品id'),
  			'default_in_list' => true,
  		),
    
  	'area_id' => 
  		array(
  			'type' => 'number',
  			'editable' => false,
  			'in_list' => true,
  			'width' => 10,
  			'comment' => app::get('b2c')->_('地区id'),
  			'label' => app::get('b2c')->_('地区id'),
  			'default_in_list' => true,
  		),
  	
  	'z_count'=>
  		array(
	  		'type' => 'number',
	  		'label' => app::get('b2c')->_('统计赞数'),
	  		'default'=>0,
	  		'width' => 20,
	  		'editable' => false,
	  		'filterdefault' => true,
	  		'in_list' => true,
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