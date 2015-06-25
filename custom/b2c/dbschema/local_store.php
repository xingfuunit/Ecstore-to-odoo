<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
*/
/* WEBPOS --> */
$db['local_store']=array (
  'columns' =>
  array (
      'local_id' => array (
        'type' => 'int(10)',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
        'comment' => app::get('b2c')->_('门店ID'),
        'label' => app::get('b2c')->_('门店ID'),
        'in_list' => true,
        'default_in_list' => true,
      ),
      'member_id' => array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'comment' => app::get('b2c')->_('会员ID'),
        'label' => app::get('b2c')->_('会员ID'),
      ),
      'name' =>
       array (
          'is_title' => true,
          'type' => 'varchar(50)',
          'editable' => false,
          'comment' => app::get('b2c')->_('地址名称'),
          'label' => app::get('b2c')->_('地址名称'),
          'in_list' => true,
          'default_in_list' => true,
        ),
      'local_name' => array (
        'is_title' => true,
        'type' => 'varchar(50)',
        'editable' => false,
        'comment' => app::get('b2c')->_('名称'),
        'label' => app::get('b2c')->_('门店名称'),
        'in_list' => true,
        'default_in_list' => true,
      ),
    'lastname' => array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('姓名'),
      'label' => app::get('b2c')->_('姓名'),
      'in_list' => false,
      'default_in_list' => false,
    ),
    'firstname' => array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('姓名'),
      'label' => app::get('b2c')->_('姓名'),
      'in_list' => false,
      'default_in_list' => false,
    ),
    'area' => array (
      'type' => 'region',
      'editable' => false,
      'comment' => app::get('b2c')->_('地区'),
      'label' => app::get('b2c')->_('地区'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'addr' => array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => app::get('b2c')->_('地址'),
      'label' => app::get('b2c')->_('详细地址'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'zip' => array (
      'type' => 'varchar(20)',
      'sdfpath'=>'zipcode',
      'editable' => false,
      'comment' => app::get('b2c')->_('邮编'),
      'label' => app::get('b2c')->_('邮编'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'tel' => array (
      'type' => 'varchar(50)',
      'sdfpath' => 'phone/telephone',
      'editable' => false,
      'comment' => app::get('b2c')->_('电话'),
      'label' => app::get('b2c')->_('电话'),
      'in_list' => true,
      'default_in_list' => false,
    ),
    'mobile' => array (
        'type' => 'varchar(50)',
        'sdfpath' => 'phone/mobile',
        'editable' => false,
        'comment' => app::get('b2c')->_('手机'),
        'label' => app::get('b2c')->_('手机'),
        'in_list' => true,
        'default_in_list' => true,
    ),
    'day' => array(
        'type'=>'varchar(255)',
        'default' => app::get('b2c')->_('任意日期'),
        'comment' => app::get('b2c')->_('上门日期'),
        'label' => app::get('b2c')->_('任意日期'),
        'in_list' => false,
        'default_in_list' => false,
    ),
    'time'=> array(
        'type'=>'varchar(255)',
        'default' => app::get('b2c')->_('任意时间段'),
        'comment' => app::get('b2c')->_('上门时间'),
        'label' => app::get('b2c')->_('任意时间段'),
        'in_list' => false,
        'default_in_list' => false,
    ),
   
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 427533 $',
  'comment' => app::get('b2c')->_('门店地址表'),
);
