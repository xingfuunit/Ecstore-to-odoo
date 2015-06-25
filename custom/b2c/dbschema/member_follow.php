<?php
$db['member_follow']    = array (
    'columns'           => array (
        'member_follow_id'  => array (
            'type'      => 'int(10)',
            'required'  => true,
            'pkey'      => true,
            'extra'     => 'auto_increment',
            'label'     => app::get('b2c')->_('会员关注ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'member_id'     => array (
            'type'      => 'number',
            'required'  => true,
            'label'     => app::get('b2c')->_('会员ID'),
            'width'     => 80,
            'default'   => 0,
            'editable'  => false,
        ),
        'follow_id'     => array (
            'type'      => 'number',
            'label'     => app::get('b2c')->_('关注会员ID'),
            'width'     => 80,
            'editable'  => false,
        ),
        'addtime'       => array (
            'type'      => 'time',
            'required'  => true,
            'label'     => app::get('b2c')->_('添加时间'),
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
        'follow_id' => array (
            'columns'   => array (
                0   => 'follow_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'comment' => app::get('b2c')->_('微店专辑表'),
);
