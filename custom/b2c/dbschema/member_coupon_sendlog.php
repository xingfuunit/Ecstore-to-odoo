<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_coupon_sendlog']=array (
  'columns' =>
  array (
    'sendlog_id' =>
    array (
      'type' => 'number',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => app::get('b2c')->_('优惠卷发送记录ID'),
    ),
    
    'sendtime'=>
    array(
        'type'=>'time',
       'in_list' => true,
        'comment'=>app::get('b2c')->_('发送时间'),
        'label' => app::get('b2c')->_('发送时间'),
    ),
    
    'member_list' =>
    array (
      'type' => 'longtext',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('会员列表'),
      'label' => app::get('b2c')->_('会员列表'),
    ),
    	
    'cpns_name' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('优惠卷名称'),
      'label' => app::get('b2c')->_('优惠卷名称'),
    ),
    	
    'cpns_prefix' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('优惠卷代号'),
      'label' => app::get('b2c')->_('优惠卷代号'),
    ),
    	
    'code_list' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('发出的优惠卷code列表'),
    ),
    	
  ),
  'comment' => app::get('b2c')->_('发送优惠卷表'),
 
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);
