<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_giftcardlog']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'int(11)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'rule_id' => 
    array (
      'type' => 'int(11)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'op_id' => 
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('b2c')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'op_name' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('操作人名称'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'alttime' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('操作时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
    ),
	'd_valid' =>
	array(		
		'label' => app::get('b2c')->_('有效截止日期'),
		'type' => 'time',
      'default' => 0,
      'required' => true,      
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'in_list' => true,
      'default_in_list' => true,
      'order' => '30',
	),
    'nums' =>
    array(
        'type' => 'number',
        'label' => app::get('b2c')->_('生成数量'),
        'width'=> 110,
        'searchtype' => 'has',
        'filtertype' => false,
        'filterdefault' => 'true',
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,        
    ),
    'c_token' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('生成记录标识'),
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'log_text' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
);