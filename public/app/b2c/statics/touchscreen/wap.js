/*
 * touchscreen
 * date: 2015-06-01
 * */
var touchscreen = {
	conf:{
		delay	: 1000 * 60 * 1,
		isInit	: false,
		isPic	: true,	//true = pic , false =  video
		urls	: {
			sid	:'',
			base:'',
			//base:'http://release.ecstore.pinzhen365.com/',
			apijson:'/wap/touchscreen.html?key=',
		},
		apiKey	: '',
		data	: []
	},
	getKey:function(val){
		if(val == ''){
			var key = '';
			if(touchscreen.conf.isInit){
				key = ''+touchscreen.conf.apiKey;
			};
			if(key.length != 32){
				key = 'json';
			};
			return key;
		}else{
			touchscreen.conf.apiKey = val;
			return true;
		};
	},
	getData:function(){
		var key = touchscreen.getKey(''),
			url = touchscreen.conf.urls.base + touchscreen.conf.urls.apijson + key;
		
		$.ajax({
			type: 'get',
			url : url,
			cache:false,
			dataType:'json',
			success: function (rs) {
				if(rs.act != '1' ){
					//write apiKey
					touchscreen.getKey(rs.key);
					touchscreen.run(rs.data);
				}else{
					//alert(rs.act);
				};
				
			},
			error:function(XMLHttpRequest, textStatus, errorThrown){
				//console.log('error='+errorThrown);
				alert('网络不通，\r\nkey='+key+'\r\nurl='+url);
			}
		});
	},
	run:function(json){
		if(json.length<1){
			alert('缺少数据！');
			return false;
		};
		
		//--------------------------------------
		touchscreen.resize();
		touchscreen.conf.$loading.show();
		
		//--------------------------------------
		touchscreen.conf.data[1] = [];
		
		for (var x in json){
			var o = json[x];
			//1=pic,2=footer,3=vod,4=bg
			if(o['type']=='1'){
				touchscreen.conf.data[1].push(o);
			}else{
				touchscreen.conf.data[parseInt(o['type'])] = o;
			}
		}
		//--------------------------------------

		touchscreen.init_footer();
		

		if( touchscreen.conf.data[1].length>0 ){
			touchscreen.conf.isPic  = true;
			touchscreen.init_pic();
		}else{
			touchscreen.conf.isPic  = false;
			touchscreen.init_vod();
		}
		

		touchscreen.conf.isInit = true;
		touchscreen.conf.$loading.hide();
	},
	init_footer:function(){
		if( typeof touchscreen.conf.data[2] == 'undefined'){
			return;
		}
		
		var sb = '',
			o = touchscreen.conf.data[2];
			
		if(o['img'].length > 5){
			if (o['url'].length > 5) {
				sb = '<a href="' + o['url'] + '" target="_blank"><img src="' + o['img'] + '" /></a>';
			}else{
				sb = '<img src="' + o['img'] + '" />';	
			}
		}
		touchscreen.conf.$footer.html(sb);
	},
	init_pic:function(){
		if( typeof touchscreen.conf.data[1] == 'undefined'){
			return;
		}

		var css = 'width:'+touchscreen.conf.width+'px;height:'+touchscreen.conf.height+'px',
			arr = touchscreen.conf.data[1];

		var sb = [];
		var sb2 = [];
		for (var x in arr) {
			var o = arr[x];
			
			if (o['url'].length > 5) {
				if(appconf.conf.isTest){
					sb.push('<div class="item" style="' + css + '">');
					sb.push('<a href="' + o['url'] + '" target="_blank"><img src="' + o['img'] + '" /></a>');
				}else{
					sb.push('<div class="item" style="' + css + '" onclick="touchscreen.openurl(\''+o['url']+'\')">');
					sb.push('<img src="' + o['img'] + '" />');
				};
			} else {
				sb.push('<div class="item" style="' + css + '">');
				sb.push('<img src="' + o['img'] + '" />');
			};
			sb.push('</div>');
			//---------------------------------------------
			
			if(x==0){
				sb2.push('<a class="active"></a>');
			}else{
				sb2.push('<a></a>');	
			};
		};

		var html = [
			'<div class="sliderBox">',
				'<div class="sliderPagerWrap">',
					'<div class="sliderPager" id="sliderPager">',
						sb2.join(''),
					'</div>',
				'</div>',
				'<div class="slider" id="slider" style="'+css+'">',
					'<div class="items">',
						sb.join(''),
					'</div>',
					'<div class="fc"></div>',
				'</div>',
			'</div>'
		].join('');
		touchscreen.conf.$banner.html(html);
		
		//---------------------------------------------
		$sliderPager = $('#sliderPager');
		var slider = document.getElementById('slider');
		if(slider && arr.length>1){
			window.mySwipe = new Swipe(slider, {
				startSlide:0,
				speed:1000,
				auto:3000,
				callback: function(index, elem){
					$sliderPager.find('.active').removeClass('active');
					$sliderPager.find('a').eq(index).addClass('active');
				}
			  // continuous: true,
			  // disableScroll: true,
			  // stopPropagation: true,
			  // callback: function(index, element) {},
			  // transitionEnd: function(index, element) {}
			});
		};
	},
	init_vod:function(){

		//---------------------------------------------
		//bg
		if( typeof touchscreen.conf.data[4] !== 'undefined'){
			var o = touchscreen.conf.data[4];
			if(o['img'].length > 5){
				touchscreen.conf.$banner.html('<img src="' + o['img'] + '" />');	
			};
		};

		//---------------------------------------------
		if( typeof touchscreen.conf.data[3] == 'undefined'){
			return;
		};

		var o = touchscreen.conf.data[3],
			vod = touchscreen.conf.urls.base + o['vod'],
			url = ''+o['url'];
			
		var html = [
			'<div id="container">',
				'<video id="video1" class="video" preload="none" autoplay="true" loop="loop" controls="false">',
				'<source src="',vod,'" type="video/mp4">',
				'</video>',
			'</div>'
		].join('');

		touchscreen.conf.$vodbox.html(html).show(function(){
			var $video = $('#video1');
			if($video.size()){
				$video.css('top',parseInt(touchscreen.conf.height -  $video.height()/2));
			};
		});
		
		var video1 = document.getElementById("video1"); 
		if(video1){
			video1.loop = false;

			$('#video1').on('ended.touchscreen.video',function(){
				video1.currentTime = 0.1;
				video1.play();
			});
/*
			video1.addEventListener('ended', function () {
				video1.currentTime = 0.1;
				video1.play();
			}, false);
*/
		};
		
		if(url.length>5){
			$('#container').on('click.touchscreen.video',function(){
				if($('#video1').size()){
					$('#video1').get(0).pause();	
				};
				self.location = url;
				return false;
			});	
		};
	},
	resize:function(){
		touchscreen.conf.width = $('#banner').width();
		touchscreen.conf.height = $('#banner').height();

		if(touchscreen.conf.isInit){
			//pic
			if(touchscreen.conf.isPic){
				touchscreen.conf.$banner.find('.item').css({
						width:touchscreen.conf.width,
						height:touchscreen.conf.height
				});
				
				var $slider = $('#slider');
				if($slider.size()){
					$slider.css({
							width:touchscreen.conf.width,
							height:touchscreen.conf.height
					});
					//$slider.data('scrollable').begin();
					if(window.mySwipe){
						window.mySwipe.setup();	
					};
				};
			}else{
				//vod
				var $video = $('#video1');
				if($video.size()){
					$video.css('top',parseInt(touchscreen.conf.height -  $video.height()/2));
				};
			};
		};
	},
	init:function(){
		this.conf.$main = $('#main');
		this.conf.$banner = $('#banner');
		this.conf.$footer = $('#footer');
		this.conf.$vodbox = $('#vodbox');
		this.conf.$msgbox = $('#msgbox');
		
		this.conf.$loading = $('#loading');
		this.conf.width = $('#banner').width();
		this.conf.height = $('#banner').height();
		
		this.getData();
		this.conf.oInter = setInterval(this.getData,this.conf.delay);
		$(window).on('resize.touchscreen', touchscreen.resize);
	}
};

$(function(){
	touchscreen.init();
});