<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['return_goods']=array (
  'columns' =>
  array (
      're_id' => array (
        'type' => 'int(10)',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
        'comment' => app::get('b2c')->_('退货/报损ID'),
      ),
      'type' => array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'comment' => app::get('b2c')->_('类型'),
        'label' => app::get('b2c')->_('类型'),
        'filtertype' => 'yes',
      ),
      'order_sn' => 
        array (
          'type' => 'bigint(20)',
          'default' => 0,
          'editable' => false,
          'comment' => app::get('b2c')->_('退货/报损单号'),
          'label' => app::get('b2c')->_('退货/报损单号'),
          'in_list' => true,
          'default_in_list' => true,
          'searchtype' => 'has',
          'filtertype' => 'yes',
       ),
      'local_id' => array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'comment' => app::get('b2c')->_('门店ID'),
        'label' => app::get('b2c')->_('门店ID'),
        'in_list' => true,
        'default_in_list' => true, 
        'searchtype' => 'has',
        'filtertype' => 'yes',
     ),
     'local_name' => array (
        'is_title' => true,
        'type' => 'varchar(50)',
        'editable' => false,
        'comment' => app::get('b2c')->_('名称'),
        'label' => app::get('b2c')->_('门店名称'),
        'in_list' => true,
        'default_in_list' => true,
        'searchtype' => 'has',
        'filtertype' => 'yes',
      ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('退货的销售总价'),
      'label' => app::get('b2c')->_('退货的销售总价'),
      'in_list' => true,
      'default_in_list' => true, 
    ),
    'create_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('退货时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true, 
            'filterdefault'=>true,
            'filtertype' => 'yes',
            ),
   
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 428536 $',
  'comment' => app::get('b2c')->_('退货/报损表'),
);
