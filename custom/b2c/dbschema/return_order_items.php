<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['return_order_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单明细ID'),
      'label' => app::get('b2c')->_('订单明细ID'),
    ),
    'order_sn' => 
        array (
          'type' => 'bigint(20)',
          'default' => 0,
          'editable' => false,
          'comment' => app::get('b2c')->_('报损/退货单号'),
          'label' => app::get('b2c')->_('报损/退货单号'),
          'in_list' => true,
          'default_in_list' => true,
          'searchtype' => 'has',
          'filtertype' => 'yes',
     ),
    'product_id' => 
    array (
      'type' => 'table:products',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'sdfpath' => 'products/product_id',
      'comment' => app::get('b2c')->_('货品ID'),
      'label' => app::get('b2c')->_('货品ID'),
    ),
    'goods_id' => 
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
      'label' => app::get('b2c')->_('商品ID'),
    ),
    'type_id' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型ID'),
      'label' => app::get('b2c')->_('商品类型ID'),
    ),
    'type' => array (
        'type' => 'int(10)',
        'default' => 0,
        'required' => false,
        'editable' => false,
        'comment' => app::get('b2c')->_('类型 1为退货2为报损'),
        'label' => app::get('b2c')->_('类型 1为退货2为报损'),
        'filtertype' => 'yes',
    ),
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('明细商品的订单号'),
      'label' => app::get('b2c')->_('明细商品的订单号'),
    ),
    'name' => 
    array (
      'is_title' => true,
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品的名称'),
      'label' => app::get('b2c')->_('商品的名称'),
      'width' => 30,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cost' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的成本'),
      'label' => app::get('b2c')->_('明细商品的成本'),
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的销售价(购入价)'),
      'label' => app::get('b2c')->_('明细商品的销售价(购入价)'),
    ),
	'g_price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('会员价原价'),
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品的会员价原价'),
    ),
    'amount' => 
    array (
      'is_title' => true,
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品总额'),
      'label' => app::get('b2c')->_('商品总额'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'score' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('积分'),
      'width' => 30,
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品积分'),
    ),
    'weight' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('明细商品重量'),
      'label' => app::get('b2c')->_('重量'),
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
    'nums' => 
    array (
      'is_title' => true,
      'type' => 'float',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'sdfpath' => 'quantity',
      'comment' => app::get('b2c')->_('商品购买数量'),
      'label' => app::get('b2c')->_('商品购买数量'),
      'in_list' => true,
      'default_in_list' => true, 
    ),
    'create_time' =>
        array (
            'type' => 'time',
            'comment' => app::get('b2c')->_('退货时间'),
            'label' => app::get('b2c')->_('退货时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true, 
            'filterdefault'=>true,
            'filtertype' => 'yes',
    ),
  ),
  'index' => 
  array (
    'ind_item_bn' =>
    array (
        'columns' =>array(
            0 => 'bn',
        ),
        'type' => 'hash',
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44836 $',
  'comment' => app::get('b2c')->_('订单明细表'),
);
