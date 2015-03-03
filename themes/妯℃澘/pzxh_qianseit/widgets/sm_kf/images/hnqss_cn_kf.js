var gotop=new Element('a', {
	'class': 'qf_backtop',
	'styles': {
		cursor: 'pointer', 
		zIndex:10,
		display: 'none'
	},
	'events': {	
		'click': function(e){ 
			//document.body.scrollTo(0); 
			//不带缓动效果
			if(document.body.scrollTop){
			  document.body.scrollTo(0);
			}
			else{
			  document.documentElement.scrollTop = 0;
			}
			//new Fx.Scroll(window,{wait: false,duration: 1000}).toTop();//带缓动效果，1000=1秒
			this.setStyle('display', 'none');
			return false;
		} 
	}
});

var kfbox = $('qf_kfbox');
var btn = $('qf_kfbox').getElements('.kf_floor');
var backTopLeft=function(){
	var btLeft = window.getWidth()/2+610;
	if(window.ie6) gotop.setStyle('position','absolute');
	if(btLeft <= 1210){
		kfbox.setStyle('left',1210);
	}else{
		kfbox.setStyle('right',10);
	}
};

window.addEvents({
	'domready': function(){
		kfbox.setStyles({
			'cursor': 'pointer', 
			'position':'fixed',
			'z-index':9999,
			'bottom':10,
			'display':'none'
		});
	    gotop.inject('kf_wrap','bottom');
		backTopLeft();
	},
	'scroll': function(){
		if(window.getScrollTop()<=100){
			gotop.setStyle('display', 'none');
			kfbox.setStyle('display','none')
			
		}else{
			kfbox.setStyle('display','block')
			gotop.setStyle('display', 'block');
			if(window.ie6)
				gotop.setStyle('top', window.getScrollTop()+window.getHeight()-150);
		}
	},	'resize':function(){
		backTopLeft();
	}
})

