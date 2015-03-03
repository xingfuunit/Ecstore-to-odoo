<?php
/*基础配置项*/
$setting['author']='清风创科设计团队';
$setting['version']='v1.0';
$setting['name']='商品展示挂件';
$setting['order']=0;
$setting['stime']='2012-08';
$setting['catalog']='商品相关';
$setting['description'] = '本挂件用于商品展示、销售排行等产品调用，并且可以选择产品缩略图的常用规格，更多ecstore模板和挂件请访问<a href="http://www.hnqss.cn" target="_blank">清风创科设计团队官网</a>';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
	'default.html'=>app::get('b2c')->_('默认')
);
/*初始化配置项*/
$setting['selector'] = 'filter';
?>
