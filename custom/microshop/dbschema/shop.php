<?php
$db['shop']             = array (
    'columns'           => array (
        'shop_id'       => array (
            'type'      => 'number',
            'required'  => true,
            'pkey'      => true,
            'extra'     => 'auto_increment',
            'label'     => app::get('microshop')->_('微店ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'shop_name'     => array (
            'type'      => 'char(40)',
            'required'  => true,
            'label'     => app::get('microshop')->_('微店名称'),
            'width'     => 100,
            'in_list'   => true,
            'searchtype'=> 'has',
            'default_in_list'   => true,
            'filtertype'        => 'normal',
            'filterdefault'     => 'true',
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('所属用户'),
            'width'     => 80,
            'default'   => 0,
            'editable'  => false,
        ),
        'agency_id'     => array (
            'type'      => 'number',
            'label'     => app::get('microshop')->_('经销商ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'see_num'       => array (
            'type'      => 'number',
            'label'     => app::get('microshop')->_('查看数'),
            'width'     => 110,
            'default'   => 0,
            'hidden'    => true,
            'in_list'   => true,
            'default_in_list'   => true,
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
        'is_open'       => array (
            'type'      => 'tinyint(1)',
            'label'     => app::get('microshop')->_('微店开启状态'),
            'default'   => 1,
        ),
    ),
    'index'  => array (
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
        'agency_id' => array (
            'columns'   => array (
                0   => 'agency_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'comment' => app::get('microshop')->_('微店专辑表'),
);
