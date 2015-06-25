<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['special']  = array(
    'columns'=>array(
        'special_id'=>array(
            'type'=>'number',
            'pkey'=>true,
            'required' => true,
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => false,
            'label'=>app::get('starbuy')->_('规则id'),
            'comment'=>app::get('starbuy')->_('规则id'),
        ),
        'name'=>array(
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            'label'=>app::get('starbuy')->_('规则名称'),
            'comment'=>app::get('starbuy')->_('规则名称'),
            'order' => 5,
            'width' => 100,
        ),
        'description'=>array(
            'type'=>'text',
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('规则描述'),
            'comment'=>app::get('starbuy')->_('规则描述'),
            'order'=>15,
            'width' => 100,
        ),
        'from_extract'=>array(
            'type'=>'text',
            'required' => false,
            'default' => '',
            'editable' => false,
            'in_list' => false,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('自提时间'),
            'comment'=>app::get('starbuy')->_('自提时间'),
            'order'=>16,
            'width' => 100,
        ),
        'release_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('发布时间'),
            'comment'=>app::get('starbuy')->_('发布时间'),
            'order'=>6,
        ),
        'begin_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('开始时间'),
            'comment'=>app::get('starbuy')->_('开始时间'),
            'order'=>7,
        ),
        'end_time'=>array(
            'type'=>'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label'=>app::get('starbuy')->_('结束时间'),
            'comment'=>app::get('starbuy')->_('结束时间'),
            'order'=>8,
        ),
        'status'=>array(
            'type'=>'bool',
            'default' => 'false',
            'in_list' => true,
            'editable' => false,
            'filterdefault'=>true,
            'default_in_list' => true,
            'label'=>app::get('starbuy')->_('状态'),
            'comment'=>app::get('starbuy')->_('状态'),
            'order'=>9,
            'width' => 50,
        ),
        'remind_way'=>array(
            'type'=>'serialize',
            'default_in_list'=>false,
            'in_list'=>false,
            'label'=>app::get('starbuy')->_('提醒方式'),
            'comment'=>app::get('starbuy')->_('提醒方式'),
        ),
        'remind_time'=>array(
            'type'=>'int(10) unsigned',
            'default' => 0,
            'default_in_list'=>true,
            'in_list'=>true,
            'label'=>app::get('starbuy')->_('提前提醒时间'),
            'comment'=>app::get('starbuy')->_('提前提醒时间'),
            'order'=>10,
            'width' => 100,
        ),
        'limit'=>array(
            'type'=>'int(10) unsigned',
            'default' =>0,
            'default_in_list'=>false,
            'in_list'=>true,
            'label'=>app::get('starbuy')->_('限购数量'),
            'comment'=>app::get('starbuy')->_('限购数量'),
            'width' => 100,
        ),
        'cdown'=>array(
            'type'=>'bool',
            'default' =>'true',
            'default_in_list'=>false,
            'in_list'=>false,
            'label'=>app::get('starbuy')->_('是否显示倒计时'),
            'comment'=>app::get('starbuy')->_('是显示否倒计时'),
        ),
        /*
        'mprice'=>array(
            'type'=>'bool',
            'default' =>'true',
            'default_in_list'=>false,
            'in_list'=>true,
            'label'=>app::get('starbuy')->_('是否显示市场价'),
            'comment'=>app::get('starbuy')->_('是否显示市场价'),
        ),
        'vcode' =>array (
            'type' => 'bool',
            'default'=>'false',
            'default_in_list'=>false,
            'in_list'=>true,
            'label' => app::get('b2c')->_('是否开启验证机制'),
            'comment' => app::get('b2c')->_('是否开启验证机制'),
        ),
         */
        'timeout'=>array(
            'type'=>'int(10) unsigned',
            'default' =>0,
            'label' => app::get('b2c')->_('超时时间'),
            'comment' => app::get('b2c')->_('超时时间'),
        ),
        'initial_num'=>array(
            'type'=>'int(10) unsigned',
            'default'=>0,
            'label' => app::get('b2c')->_('初始销售量'),
            'comment' => app::get('b2c')->_('初始销售量'),
        ),

        'type_id'=>array(
            'type'=>'table:promotions_type',
            'default_in_list'=>true,
            'in_list'=>true,
            'label' => app::get('b2c')->_('促销类型 '),
            'comment' => app::get('b2c')->_('促销类型'),
            'order' => 4,
            'width' => 80,
        ),
        'promotion_pro'=>array(
            'type'=>'serialize',
            'default_in_list'=>false,
            'in_list'=>false,
            'label'=>app::get('starbuy')->_('促销商品组合'),
            'comment'=>app::get('starbuy')->_('促销商品组合 '),
        ),
    ),

    'index' => array (
                'ind_special_id' => array (
                        'columns' => array (
                                0 => 'special_id' 
                        ),
                ) 
        ),
    'engine' => 'innodb',
    'version' => '$Rev: 519916' 
);
