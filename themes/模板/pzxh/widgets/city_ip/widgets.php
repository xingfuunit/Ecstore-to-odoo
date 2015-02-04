<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/*基础配置项*/
$setting['author']='zinkwind@gmail.com';
$setting['version']='v1.0';
$setting['name']='地址控件';
$setting['order']=0;
$setting['stime']='2014-04';
$setting['catalog']='地址控件';
$setting['description'] = '显示各地区详细信息';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

$setting['limit'] = 6;
?>
