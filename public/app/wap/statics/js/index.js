/*
 * wap/index.js
 * date: 2015-06-09
 * */
$(function(){
	 indexMarquee_Call();
});

function indexMarquee_Call(){
	var $indexMarquee = $('#index-marquee');
	
	if($indexMarquee.size()){
		var itemLen = $indexMarquee.find('.item').size();
		if(itemLen>0){
			if(itemLen>1){
				$indexMarquee.find('.itemsWrap').scrollable({circular:true,vertical:true}).autoscroll({
						interval: 2000,autoplay:true
				});
			};
			$icoVolDown = $indexMarquee.find('.am-icon-volume-down');
			$icoVolUp = $indexMarquee.find('.am-icon-volume-up');
			$icoShowState = 0;
			setInterval(function(){
				if($icoShowState==1){
					$icoShowState = 0;
					$icoVolDown.hide();
					$icoVolUp.show();
				}else{
					$icoShowState = 1;
					$icoVolDown.show();
					$icoVolUp.hide();
				};
			},500);	
		}else{
			$indexMarquee.hide();	
		};
	};
};