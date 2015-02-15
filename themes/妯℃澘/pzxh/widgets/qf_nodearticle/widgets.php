<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='清风设计';
$setting['name']='★带有更多的文章调用';
$setting['version']='1.0.0';
$setting['stime']='2013-7-05';
$setting['catalog']='文章挂件';
$setting['usual'] = '0';
$setting['description']='可以调用相应的文章栏目，并且默认倒序排列，有更多';
$setting['userinfo']='您只需输入文字及链接，即可。';
$setting['tag'] = '230';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
						
/*首次默认配置项*/
$setting['lv'] = 2;             //深度
$setting['styleart'] = 0;       //文章样式统一
$setting['node_id']  = 1;       //默认节点
$selectmaps = kernel::single('content_article_node')->get_selectmaps();
//array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---所有---')));
$setting['selectmaps'] = $selectmaps;
$setting['select_order']['order_type'] = array('pubtime'=>'发布时间');
$setting['select_order']['order'] = array('asc'=>'升序','desc'=>'降序');
$setting['showuptime'] = 0; //是否显示文章最后更新时间
$setting['title']  = "";
?>
