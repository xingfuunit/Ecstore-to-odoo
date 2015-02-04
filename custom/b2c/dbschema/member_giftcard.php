<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_giftcard'] =array (
  'columns' => 
  array (
    'gcard_id' => 
    array (
      'type' => 'int(11)',
      'required' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'editable' => false,
    ),
    'gcard_code' => 
    array (
      'type' => 'varchar(50)',
      'pkey' => true,
      'label' => app::get('b2c')->_('兑换卡'),
      'width' => 180,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '11',
    ),
    'uname' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('用户名'),
      'width' => 110,
      'searchtype' => 'head',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
      'order' => '70',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'rule_id' => 
    array (
      'type' => 'number',
      'label' => __('规则ID'),
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'rule_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('规则名称'),
      'width' => 180,
      //'searchtype' => 'has',
      'filtertype' => true,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '10',
    ),
    'c_token' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('生成记录标识'),
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'gcard_money' => 
    array (
      'label' => app::get('b2c')->_('面额'),
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'width' => 70,
      'searchable' => true,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '20',
    ),
    'status' =>array (
      'type' =>array (
        'active' =>app::get('b2c')->_('正常'),
        'dead' => app::get('b2c')->_('作废'),
      ),
      'default' => 'active',
      'required' => true,
      'label' => app::get('b2c')->_('兑换卡状态'),
      'width' => 70,
      'editable' => false,
      'filtertype' => 'has',
      'in_list'=>true,
      'default_in_list'=>true,
      'order' => '40',
    ),
    'start_time'=>array(
        'type'=>'time',
        'label'=>app::get('b2c')->_('生成时间'),
        'editable' => false,
        'in_list'=>true,
        'width' => 180,
        'default_in_list'=>true,
        'order' => '30',
        'filtertype' => 'time',
        'filterdefault' => true,
    ),
    'end_time'=>array(
        'type'=>'time',
        'label'=>app::get('b2c')->_('有效截止日期'),
        'editable' => false,
        'in_list'=>true,
        'width' => 180,
        'default_in_list'=>true,
        'order' => '31',
        'filtertype' => 'time',
        'filterdefault' => true,
    ),
    'is_overdue'=>array(
        'type' => 'bool',
        'default' => 'false',
        'label'=>app::get('b2c')->_('过期状态'),
        'editable' => false,
        'required' => false,
         'width' => 70,
        'in_list'=>true,
        'default_in_list'=>true,
        'filtertype' => 'yes',
        'order' => '50',
    ),
    'used_status'=>array(
        'type' => 'bool',
        'default' => 'false',
        'label'=>app::get('b2c')->_('使用状态'),
        'editable' => false,
        'required' => false,
         'width' => 70,
        'in_list'=>true,
        'filtertype' => 'yes',
        'default_in_list'=>true,
        'order' => '60',
    ), 
    'used_time'=>array(
        'type'=>'time',
        'label'=>app::get('b2c')->_('使用时间'),
        'editable' => false,
        'in_list'=>true,
        'width' => 180,
        'default_in_list'=>true,
        'order' => '80',
        'filtertype' => 'time',
        'filterdefault' => true,
    ),        
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('兑换卡信息表'),
  'index' => 
  array (
    'ind_giftcard' => 
    array (
      'columns' => 
      array (
        0 => 'gcard_id',
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
