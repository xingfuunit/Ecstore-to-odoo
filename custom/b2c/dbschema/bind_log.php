<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
*/
/* WEBPOS --> */
$db['bind_log']=array (
  'columns' =>
  array (
      'log_id' => array (
        'type' => 'int(10)',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
        'comment' => app::get('b2c')->_('会员绑定log主键'),
        'label' => app::get('b2c')->_('会员绑定log主键'),
        'in_list' => true,
        'default_in_list' => true,
      ),
      'to_member_id' => array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'comment' => app::get('b2c')->_('绑定去了哪个账号ID'),
        'label' => app::get('b2c')->_('绑定去了哪个账号ID'),
      ),
      'to_account' =>
       array (
          'is_title' => true,
          'type' => 'varchar(100)',
          'editable' => false,
          'comment' => app::get('b2c')->_('绑定去了哪些账号'),
          'label' => app::get('b2c')->_('绑定去了哪些账号'),
          'in_list' => true,
          'default_in_list' => true,
        ),
        'from_member_id' => array (
          'type' => 'int(10)',
          'default' => 0,
          'required' => false,
          'editable' => false,
          'comment' => app::get('b2c')->_('来自哪个账号ID'),
          'label' => app::get('b2c')->_('来自哪个账号ID'),
        ),
      'from_account' => array (
        'is_title' => true,
        'type' => 'varchar(100)',
        'editable' => false,
        'comment' => app::get('b2c')->_('来自哪些账号'),
        'label' => app::get('b2c')->_('来自哪些账号'),
        'in_list' => true,
        'default_in_list' => true,
      ),   
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 427533 $',
  'comment' => app::get('b2c')->_('门店地址表'),
);
