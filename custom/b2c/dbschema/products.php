<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['products']=array (
  'columns' =>
  array (
    'product_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('货品ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('商品ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'barcode' =>
    array (
      'type' => 'varchar(200)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('条形码'),
    ),
    'title' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'title',
      'label' => app::get('b2c')->_('标题'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'bn' =>
    array (
      'type' => 'varchar(30)',
      'label' => app::get('b2c')->_('货号'),
      'width' => 75,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    'price' =>
    array (
      'type' => 'decimal(20,5)',
      'sdfpath' => 'price/price/price',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('销售价格'),
      'width' => 75,
      'filtertype' => 'number',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    'cost' =>
    array (
      'type' => 'decimal(20,5)',
      'sdfpath' => 'price/cost/price',
      'default' => '0',
      'label' => app::get('b2c')->_('成本价'),
      'required' => true,
      'width' => 110,
      'filtertype' => 'number',
      'editable' => false,
      'in_list' => true,
    ),
    'mktprice' =>
    array (
      'type' => 'decimal(20,5)',
      'sdfpath' => 'price/mktprice/price',
      'label' => app::get('b2c')->_('市场价'),
      'width' => 75,
      'filtertype' => 'number',
      'editable' => false,
      'in_list' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
//      'sdfpath' => 'title',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('货品名称'),
      'width' => 180,
      'searchtype' => 'has',
      'filtertype' => 'custom',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'weight' =>
    array (
      'type' => 'decimal(20,3)',
      'label' => app::get('b2c')->_('单位重量'),
      'width' => 110,
      'filtertype' => 'number',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    'g_weight' =>
    array (
      'type' => 'decimal(20,3)',
      'label' => app::get('b2c')->_('净重'),
      'width' => 110,
      'filtertype' => 'number',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    'unit' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('单位'),
      'width' => 110,
      'filtertype' => 'normal',
      'editable' => false,
      'in_list' => true,
    ),
    'store' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('库存'),
      'default'=>0,
      'width' => 30,
      'filtertype' => 'number',
      'filterdefault' => true,
      'editable' => false,
      'in_list' => true,
    ),
    'store_place' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('库位'),
      'width' => 30,
      'editable' => false,
      'hidden'=>true,
    ),
    'freez' =>
    array (
      'type' => 'number',
      'sdfpath' => 'freez',
      'label' => app::get('b2c')->_('冻结库存'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => true,
    ),

    'goods_type' =>
    array (
      'type' =>
      array (
        'normal' => app::get('b2c')->_('普通商品'),
        'bind' => app::get('b2c')->_('捆绑商品'),
        'gift' => app::get('b2c')->_('赠品'),
      ),
      'sdfpath' => 'goods_type',
      'default' => 'normal',
      'required' => true,
      'label' => app::get('b2c')->_('销售类型'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,

    ),

    'spec_info' =>
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('物品描述'),
      'width' => 110,
      'filtertype' => 'normal',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'searchtype' => 'has',
    ),
    'spec_desc' =>
    array (
      'type' => 'serialize',
      'label' => app::get('b2c')->_('规格值,序列化'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'is_default' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'editable' => false,
    ),
    'qrcode_image_id' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('二维码图片ID'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('录入时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('最后修改时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'marketable' =>
    array (
      'type' => 'bool',
      'sdfpath' => 'status',
      'default' => 'true',
      'required' => true,
      'label' => app::get('b2c')->_('上架'),
      'width' => 30,
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('货品表'),
  'index' =>
  array (
    'ind_goods_id' =>
    array (
      'columns' =>
      array (
        0 => 'goods_id',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_barcode' =>
    array (
      'columns' =>
      array (
        0 => 'barcode',
      ),
    ),
    'ind_bn' =>
    array (
      'columns' =>
      array (
        0 => 'bn',
      ),
      'prefix' => 'UNIQUE',
    ),
    'idx_goods_type' =>
    array(
        'columns' =>
        array(
            0 => 'goods_type',
            ),
        ),
    'idx_store' =>
    array(
        'columns' =>
        array(
            0 => 'store',
            ),
        ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 423763 $',
  'comment' => app::get('b2c')->_('商品货品表'),
);
