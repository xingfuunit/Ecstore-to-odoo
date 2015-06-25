<?php
$db['special_products']    = array (
    'columns'       => array (
        'special_products_id'   => array (
            'type'      => 'int(10)',
            'required'  => true,
            'pkey'      => true,
            'extra'     => 'auto_increment',
            'label'     => app::get('microshop')->_('专辑商品自增ID'),
        ),
        'special_id'    => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('专辑ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('会员ID'),
            'width'     => 80,
        ),
        'product_id'    => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('商品ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'addtime'       => array (
            'type'      => 'time',
            'required'  => true,
            'label'     => app::get('microshop')->_('添加时间'),
            'width'     => 70,
            'editable'  => false,
            'in_list'   => true,
            'default_in_list'   => true,
            'filtertype'        => 'time',
            'filterdefault'     => true,
        ),
    ),
    'index'  => array (
        'special_id' => array (
            'columns'   => array (
                0   => 'special_id',
            ),
        ),
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
     ),
    'engine' => 'innodb',
    'comment' => app::get('microshop')->_('微店专辑商品表'),
);
