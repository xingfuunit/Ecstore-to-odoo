<?php
$db['sales_goods'] = array(
    'columns' =>
    array (
        'rule_id' =>
        array (
            'type' => 'int(8)',
            'required' => true,
            'pkey' => true,
            'label' => app::get('b2c')->_('id'),
            'editable' => false,
            'extra' => 'auto_increment',
            ),
        'name' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'label' => app::get('b2c')->_('促销名称'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            ),
        'description' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('促销描述'),
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => true,
            'filterdefault'=>true,
            ),
        'create_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('修改时间'),
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault'=>true,
            ),
        'from_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('起始时间'),
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            ),
        'to_time' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('截止时间'),
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            ),
        'status' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('b2c')->_('开启状态'),
            'in_list' => true,
            'editable' => false,
            'filterdefault'=>true,
            'default_in_list' => true,
            ),
        'conditions' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'required' => true,
            'label' => app::get('b2c')->_('促销条件'),
            'editable' => false,
            ),
        'sort_order' =>
        array (
            'type' => 'int(10) unsigned',
            'default' => '0',
            'required' => true,
            'label' => app::get('b2c')->_('优先级'),
            'in_list' => true,
            'editable' => true,
            'default_in_list' => true,
            ),
        ),
    'engine' => 'innodb',
    'version' => '$Rev: 44515 $', 
    'comment' => app::get('b2c')->_('商品促销规则'),
    );