<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['vipday']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('会员日版本ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'vipday_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' => app::get('b2c')->_('会员日活动名称'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  		'current' =>
  		array (
      'type' => 'bool',
      'default' => 'false',
      'sdfpath' => 'status',
      'required' => true,
      'label' => app::get('b2c')->_('当前使用'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
),
  'version' => '$Rev: 41329 $',
  'comment' => app::get('b2c')->_('类别属性值有限表'),
);
