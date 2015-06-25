<?php
$db['special']    = array (
    'columns'       => array (
        'special_id'  => array (
            'type'      => 'number',
            'required'  => true,
            'pkey'      => true,
            'extra'     => 'auto_increment',
            'label'     => app::get('microshop')->_('专辑ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('microshop')->_('所属用户'),
            'width'     => 80,
            'default'   => 0,
            'editable'  => false,
        ),
        'shop_id'       => array (
            'type'      => 'number',
            'required'  => true,
            'lable'     => app::get('microshop')->_('微店ID'),
            'default'   => 0,
        ),
        'special_name'  => array (
            'type'      => 'char(40)',
            'label'     => app::get('microshop')->_('专辑名称'),
            'width'     => 80,
            'editable'  => false,
            'in_list'   => true,
            'searchtype'=> 'has',
            'default_in_list'   => true,
            'filtertype'        => 'normal',
            'filterdefault'     => 'true',
        ),
        'see_num'       => array (
            'type'      => 'number',
            'label'     => app::get('microshop')->_('查看数'),
            'width'     => 110,
            'default'   => 0,
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
    ),
    'index'  => array (
        'member_id' => array (
            'columns'   => array (
                0   => 'member_id',
            ),
        ),
     ),
    'engine' => 'innodb',
    'comment' => app::get('microshop')->_('微店专辑表'),
);
