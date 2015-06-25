<?php
$setting['author'] = 'hnqss.cn';
$setting['version'] = '1.0.0';
$setting['order'] = 17;
$setting['name'] = app::get( "b2c" )->_( "带左右按钮的轮播广告" );
$setting['catalog'] = app::get( "b2c" )->_( "广告挂件" );
$setting['description'] = app::get( "b2c" )->_('本挂件适合用于放置全屏自适应宽度广告，将本广告调用在100%宽度的div结构中。作者：<{$setting.author}>,<a href="http://www.hnqss.com/code/widefocus/demo.html" target="_blank">示例</a>');
$setting['usual']  = '1';
$setting['stime'] = '2012-07-27';
$setting['template'] = array(
	"default.html" => app::get( "b2c" )->_( "默认" )
);
?>

