<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_card']=array (
  'columns' =>
  array (
    'card_id' =>
    array (
      'type' => 'number',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => app::get('b2c')->_('会员卡主键'),      
    ),
  	'card_number' =>
  	array (
  	  'type' => 'varchar(50)',
  	  'label' => app::get('b2c')->_('会员卡卡号'),
  	  'width' => 75,
  	  'sdfpath' => 'contact/name',
      'searchtype' => 'has',
  	  'editable' => true,
      'filtertype' => 'normal',
  	  'filterdefault' => 'true',
  	  'in_list' => true,
  	  'is_title'=>true,
  	  'default_in_list' => false,
  	),
  	'card_password'=>
  	array(
  	  'type'=>'varchar(32)',
  	  'required' => true,
  	  'comment' => app::get('pam')->_('会员卡密码'),
  	),
    'card_lv_id' =>
    array (
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('会员卡等级'),
      'sdfpath' => 'member_lv/member_group_id',
      'width' => 75,
      'order' => 40,
      'type' => 'table:member_lv',
      'editable' => true,
      'filtertype' => 'bool',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'card_advance' =>
    array (
      'type' => 'decimal(20,3) unsigned',
      'default' => '0.00',
      'required' => true,
      'label' => app::get('b2c')->_('会员卡预存款'),
      'sdfpath' => 'advance/total',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'comment' => app::get('b2c')->_('会员卡余额'),
    ),
    'card_point' =>
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'sdfpath' => 'score/total',
      'label' => app::get('b2c')->_('会员卡积分'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'card_etc' =>
    array (
      'type'=>'varchar(32)',
      'label' => app::get('b2c')->_('会员卡批次'),
      'comment' => app::get('b2c')->_('会员卡批次'),
    ),
    'card_state' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('会员卡状态'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'comment' => app::get('b2c')->_('会员卡状态'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
  'comment' => app::get('b2c')->_('会员卡主表'),
);
