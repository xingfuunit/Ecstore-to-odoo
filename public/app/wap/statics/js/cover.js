/*
 * Date: 2015-05-26
 * */
var cover = {
	call:function(){
		/*
		var ckName  = 'ck-cover';
		var ckVal = $.cookie(ckName);
		if(ckVal != '1'){
			//30 天过期
			$.cookie(ckName,'1',{expires:30,path:'/'});
			this.init();
		};
		*/
	},
	hide:function(){
		top.location = '/';
		/*
		var $cover = $('#cover-body');
		if($cover.is(':visible')){
			$cover.flexslider('stop');
			$cover.fadeOut();	
		};
		*/
	},
	init:function(){
		if(!document.getElementById('cover-body')){
			var sb = [
				'<div class="am-slider-default cover-body" id="cover-body">',
					'<ul class="items am-slides">',
						'<li class="pic1 item"></li>',
						'<li class="pic2 item"></li>',
						'<li class="pic3 item"></li>',
					'</ul>',
				'</div>'
			].join('');
			$('body').append(sb);
			$('body').addClass('cover-fixed');
		};
		//----------------------------------------
		var $cover = $('#cover-body');

		$cover.flexslider({
				animationLoop:false,
				slideshowSpeed:3000,
				keyboard:false,
				directionNav:false,
				before: function(slider){
					//到了最后一页，用户还拖动下一页的情况下，自动关闭
					if(slider.direction=='next' && slider.currentSlide == slider.last){
						cover.hide();
					};
				},
				after: function(slider){
					//最后一页，自动倒计时，关闭
					if(slider.direction=='next' && slider.currentSlide == slider.last){
						setTimeout(function(){
							cover.hide();
						},4000);
					};
				}
			});
		
		//用户点击最后一页，关闭
		$cover.find('.pic3').click(function(){
			cover.hide();
		});
	}
};

$(function(){
	cover.init();
});