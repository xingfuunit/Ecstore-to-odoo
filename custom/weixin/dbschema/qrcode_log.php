<?php

$db['qrcode_log']=array (
  'columns' =>
  array (
    'log_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true, 
      'extra' => 'auto_increment',
      'label' => 'logID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'log_type' =>
    array (
      'type' => 'int(10)',
      'label' => app::get('b2c')->_('log类型'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true, 
    ),
  	'qrcode_id' =>
  	array (
  		'type' => 'int(10)',
  		'label' => app::get('b2c')->_('二维码id'),
  		'width' => 75,
  		'editable' => true,
  		'hidden' => true,
  		'filtertype' => 'yes',
  		'filterdefault' => true,
  		'in_list' => true,
  	),
    'tousername' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('公众号'),
      'is_title' => true,
      'width' => 310,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'1',
    ),
     'from_openid' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('关注openid'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
     'createtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('创建时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),

    
  ),

  'engine' => 'innodb',
  'version' => '$Rev: 4451552 $',
  'comment' => app::get('b2c')->_('二维码表'),
);
