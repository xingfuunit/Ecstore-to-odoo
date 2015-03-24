<?php

$db['qrcode']=array (
  'columns' =>
  array (
    'code_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => '二维码ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'code_group' =>
    array (
      'type' => 'int(10)',
      'label' => app::get('b2c')->_('组'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true, 
    ),
    'code_name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('二维码名称'),
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
     'code_key' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('二维码key'),
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
    'bind_id' =>
    array (
      'type' => 'int(10)',
      'label' => app::get('b2c')->_('绑定id'),
      'width' => 75,
      'editable' => true,
      'hidden' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    /*
  	'scan_count' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('扫描统计'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'follow_count' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('关注统计'),
      'default'=>0,
      'width' => 30,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
    ),
    */
  	'code_url'=>
	  	array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('二维码解析后的url'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
    
  ),

  'engine' => 'innodb',
  'version' => '$Rev: 4451552 $',
  'comment' => app::get('b2c')->_('二维码表'),
);
