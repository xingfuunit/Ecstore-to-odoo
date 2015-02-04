<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='清风设计';
$setting['name']='★文章调用';
$setting['version']='1.0.0';
$setting['stime']='2013-7-05';
$setting['catalog']='文章挂件';
$setting['usual'] = '0';
$setting['description']='可以调用相应的文章栏目，并且默认倒序排列';
$setting['userinfo']='您只需输入文字及链接，即可。';
$setting['tag'] = '230';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
						
/*首次默认配置项*/
$setting['limit'] 		= 5;
$setting['id']		= 11;	
$setting['title']  = "输入标题";
?>
