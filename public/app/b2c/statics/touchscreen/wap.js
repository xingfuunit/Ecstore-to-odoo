/*
 * touchscreen
 * date: 2015-06-01
 * */
function Random(n) { return (Math.floor(Math.random() * n)); };
function AjaxRnd() { return new Date().getTime() + '' + Random(10000); };

var touchscreen = {
	conf:{
		delay	: 1000 * 60 * 1,
		ckNameKey	: 'touchscreen_key',
		isInit	: false,
		isPic	: true,	//true = pic , false =  video
		urls	:{
			base:'/',
			//base:'http://release.ecstore.pinzhen365.com/',
			apijson:'wap/touchscreen.html?key=',
		},
		apiKey	: ''
	},
	getKey:function(val){
		if(val == ''){
			var key = '';
			if(touchscreen.conf.isInit){
				key = ''+$.cookie(touchscreen.conf.ckNameKey);
			};
			if(key.length != 32){
				key = 'json';
			};
			return key;
		}else{
			//cookie expires 30 day
			$.cookie(touchscreen.conf.ckNameKey, val, 30);	
			return true;
		};
	},
	getData:function(){
		var key = touchscreen.getKey('');
		var url = touchscreen.conf.urls.base + touchscreen.conf.urls.apijson + key + '&a=' + AjaxRnd();
		$.ajax({
			type: 'get',
			url : url,
			cache:false,
			dataType:'json',
			success: function (rs) {
				if(rs.act != '1' ){
					//write cookie key
					touchscreen.getKey(rs.key);
					touchscreen.run(rs.data);
				}else{
					//alert(rs.act);
				};
				
			},
			error:function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	},
	run:function(json){
		touchscreen.resize();
		touchscreen.conf.$loading.show();
		
		var o = json[0];
		
		if(o['type']=='pic'){
			touchscreen.init_pic(json);
		}else{
			touchscreen.init_vod(json);	
		};
		touchscreen.conf.isInit = true;
		touchscreen.conf.$loading.hide();
	},
	init_pic:function(json){
		var css = 'width:'+touchscreen.conf.width+'px;height:'+touchscreen.conf.height+'px';
		
		var sb = [];
		for (var x in json) {
			var o = json[x];
			sb.push('<div class="item" style="' + css + '">');
			if (o['url'].length > 5) {
				sb.push('<a href="' + o['url'] + '" target="_blank"><img src="' + o['img'] + '" /></a>');
			} else {
				sb.push('<img src="' + o['img'] + '" />');
			};
			sb.push('</div>');
		};
		var html = [
			'<div class="sliderBox">',
				'<div class="sliderPagerWrap">',
					'<div class="sliderPager" id="sliderPager"></div>',
				'</div>',
				'<div class="slider" id="slider" style="'+css+'">',
					'<div class="items">',
					sb.join(''),
					'</div>',
					'<div class="fc"></div>',
				'</div>',
			'</div>'
		].join('');

		touchscreen.conf.$main.html(html);

		$('#slider').scrollable({circular:true}).navigator({navi:'#sliderPager',indexed:false}).autoscroll({
			interval: 2000,autoplay:true,autopause:false
		});

		//$('#slider').scrollable({circular:true}).navigator({navi:'#sliderPager',indexed:true});	
	},
	init_vod:function(json){
		touchscreen.conf.isPic  = false;
		
		var o = json[0],
			vod = touchscreen.conf.apiUrlBase + o['vod'],
			url = ''+o['url'],
			css = 'width:'+touchscreen.conf.width+'px;height:'+touchscreen.conf.height+'px';

		var html = [
			'<div id="container">',
				'<div id="videocover">&nbsp;</div>',
				'<video id="video" class="video" preload="metadata" src="',vod,'" autoplay="true" loop="loop" controls="false" style="',css,'"></video>',
			'</div>'
		].join('');

		touchscreen.conf.$main.html(html);
		
		if(url.length>5){
			$('#container').on('click.touchscreen.video',function(){
				if($('#video').size()){
					$('#video').get(0).pause();	
				};
				self.location = url;
				return false;
			});	
		};
	},
	resize:function(){
		touchscreen.conf.width = $('body').width();
		touchscreen.conf.height = $('body').height();
		
		if(touchscreen.conf.isInit){
			//Pictures
			if(touchscreen.conf.isPic){
				touchscreen.conf.$main.find('.item').css({
						width:touchscreen.conf.width,
						height:touchscreen.conf.height
				});
				
				
				var $slider = $('#slider');
				if($slider.size()){
					$slider.css({
							width:touchscreen.conf.width,
							height:touchscreen.conf.height
					});
					$slider.data('scrollable').begin();
				};
			}else{
				//video
				var $video = $('#video');
				if($video.size()){
					$video.css({
							width:touchscreen.conf.width,
							height:touchscreen.conf.height
					});
				};
			};

		};
	},
	init:function(){
		this.conf.$main = $('#main');
		this.conf.$loading = $('#loading');
		this.conf.width = $('body').width();
		this.conf.height = $('body').height();
		
		this.getData();
		this.conf.oInter = setInterval(this.getData,this.conf.delay);
		$(window).on('resize.touchscreen', touchscreen.resize);
	}
};

$(function(){
	touchscreen.init();
});