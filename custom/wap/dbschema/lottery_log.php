<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['lottery_log']=array (
  'columns' =>
  array (
    'lotterylog_id' =>
    array (
      'type' => 'number',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => app::get('b2c')->_('微信抽奖ID'),
    ),
    
    'dateline'=>
    array(
        'type'=>'time',
       'in_list' => true,
        'comment'=>app::get('b2c')->_('抽奖时间'),
        'label' => app::get('b2c')->_('抽奖时间'),
      	'default_in_list' => true,
    ),
    
    'member_id' =>
    array (
      'type' => 'number',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('会员ID'),
      'label' => app::get('b2c')->_('会员ID'),
      'default_in_list' => true,
    ),
    
    'login_account' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('会员名'),
      'label' => app::get('b2c')->_('会员名'),
      'default_in_list' => true,
    ),
    
    'gift_name' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('中奖奖品名'),
      'label' => app::get('b2c')->_('中奖奖品名'),
      'default_in_list' => true,
    ),
    	
    'gift_type' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => false,
      'comment' => app::get('b2c')->_('奖品类型'),
      'label' => app::get('b2c')->_('奖品类型'),
      'default_in_list' => false,
    ),
    
    'cpns_prefix' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
       'in_list' => true,
      'comment' => app::get('b2c')->_('优惠卷号码'),
      'label' => app::get('b2c')->_('优惠卷号码'),
      'default_in_list' => true,
    ),
    	
  ),
  'comment' => app::get('b2c')->_('微信抽奖'),
 
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);
