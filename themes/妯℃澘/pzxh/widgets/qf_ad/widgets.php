<?php
$setting['author']='清风设计';
$setting['version']='1.0';
$setting['name']=app::get('b2c')->_('清风广告挂件');
$setting['stime']='2012-05-15';
$setting['catalog']=app::get('b2c')->_('广告挂件');
$setting['usual']    = '1';
$setting['description']    ='本版块有三种广告投放方式：图片广告，flash广告和自定义广告。<br>注意：如果选择图片广告，则链接地址的格式一定要填写完整，不要漏掉“http://”，如果漏写，则广告地址无效。<br>自定义广告一般用于投放广告代码。';
$setting['template'] = array(
	'default.html'=>app::get('b2c')->_('默认')
);
?>