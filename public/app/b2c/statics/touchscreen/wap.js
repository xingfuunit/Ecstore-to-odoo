/*
 * touchscreen
 * date: 2015-06-01
 * */
function Random(n) { return (Math.floor(Math.random() * n)); };
function AjaxRnd() { return new Date().getTime() + '' + Random(10000); };

var touchscreen = {
	conf:{
		delay	: 1000 * 60 * 5,
		ckNameKey	: 'touchscreen_key',
		isInit:false,
		apiUrl	: '/wap/touchscreen.html?key=',
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
		
		$.ajax({
			type: 'get',
			url : touchscreen.conf.apiUrl + key + '&a=' + AjaxRnd(),
			cache:false,
			dataType:'json',
			success: function (rs) {
				if(rs.act != '1' ){
					//write cookie key
					touchscreen.getKey(rs.key);
					touchscreen.conf.isInit = true;
					touchscreen.run(rs.data);

				};
			},
			error:function(){
			}
		});
	},
	run:function(json){
		this.conf.$loading.show();
		var sb = [];
		
		for(var x in json){
			var o  = json[x];
			sb.push('<div class="item" style="'+touchscreen.conf.itemStyle+'"><img src="'+o['img']+'" /></div>');
		};
		var html = [
			'<div class="sliderBox">',
				'<div class="sliderPagerWrap">',
					'<div class="sliderPager" id="sliderPager"></div>',
				'</div>',
				'<div class="slider" id="slider" style="'+touchscreen.conf.itemStyle+'">',
					'<div class="items">',
					sb.join(''),
					'</div>',
					'<div class="fc"></div>',
				'</div>',
			'</div>'
		].join('');
		
		touchscreen.conf.$main.html(html);


		$("#slider").scrollable({circular:true}).navigator({navi: "#sliderPager",indexed:true}).autoscroll({
			interval: 2000,autoplay:true
		});

		//$("#slider").scrollable({circular:true}).navigator({navi: "#sliderPager",indexed:true});
		this.conf.$loading.hide();
	},
	resize:function(){
		this.conf.width = $('body').width();
		this.conf.height = $('body').height();
		this.conf.itemStyle = 'width:'+this.conf.width+'px;height:'+this.conf.height+'px';
	},
	init:function(){
		this.conf.$main = $('#main');
		this.conf.$loading = $('#loading');
		this.resize();
		this.getData();
		this.conf.oInter = setInterval(this.getData,this.conf.delay);
	}
};

 $(function(){
	touchscreen.init();
 });