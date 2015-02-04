<?php
$setting['author']='清风设计';
$setting['version']='1.0';
$setting['name']=app::get('b2c')->_('清风自定义链接');
$setting['catalog']=app::get('b2c')->_('其它挂件');
$setting['stime']='2011-04-24';
$setting['usual']    = '1';
$setting['description']    =''.app::get('b2c')->_('本挂件需要配合CSS使用，挂件定制<a href=http://www.hnqss.cn/ target=_blank>清风设计官网</a>');
$setting['template'] = array(
	'default.html'=>app::get('b2c')->_('默认')
);
?>
