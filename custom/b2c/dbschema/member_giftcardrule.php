<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_giftcardrule']=array (
  'columns' => 
  array (
    'rule_id' => 
    array (
      'type' => 'int(11)',
      'pkey' => true,
      'label' => ID,
      'required' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'rule_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('规则名称'),
      'width' => 110,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      //'order' => 20,
    ),
    'gcard_money' => 
    array (
      'label' => app::get('b2c')->_('兑换卡面值'),
      'type' => 'money',
      'default' => '0.00',
      'required' => true,
      'width' => 110,
      'searchable' => true,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
       //'order' => 30,
    ),
    'gcard_prefix' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('兑换卡前缀'),
      'width' => 75,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
      //'order' => 40,
    ),
    'd_valid' => 
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('默认有效期'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
      'order' => 110,
    ),
    'count' =>
    array(
        'type' => 'number',
        'label' => app::get('b2c')->_('生成总数'),
        'width'=> 110,
        'searchtype' => 'has',
        'filtertype' => false,
        'filterdefault' => 'true',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,  
        'order' => 120      
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('兑换卡规则表'),
  'index' => 
  array (
    'ind_rule' => 
    array (
      'columns' => 
      array (
        0 => 'rule_name',
      ),
    ),
     'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);
