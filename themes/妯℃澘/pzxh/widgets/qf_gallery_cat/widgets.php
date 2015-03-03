<?php
$setting['author']='hnqss.cn';
$setting['version']='v1.0';
$setting['name']='清风左侧商品分类挂件';
$setting['order']=4;
$setting['stime']='2014-10-28';
$setting['catalog']='商品分类挂件';
$setting['description'] = '可选择显示方式（0.显示全部分类，1.自动判断（根据当前分类id显示），2.指定分类id显示（可指定多个）） 以及显示深度（1.显示一级分类，2.显示二级分类，3.显示三级分类）';
$setting['usual']    = '1';
$setting['vary']    = '*';
$setting['template'] = array(
	 'default.html'=>app::get('b2c')->_('默认')
);
?>
