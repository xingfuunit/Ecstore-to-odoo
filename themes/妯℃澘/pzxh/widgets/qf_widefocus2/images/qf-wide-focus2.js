/**
 * @Author zm && www.hnqss.cn
 * @Date 2012-11-11
 * @Description 轮播图片效果
 * @Version 2.0
 * @2012-7-27 加入左右滚动按钮的渐隐渐显效果
 * @2012-11-11 加入上下滚动效果
 * 鼠标移动到按钮上也触发了动画容器的mouseleave事件，所以采取延迟消失的策略(鼠标移动到按钮清除timer)实现，默认100毫秒，可自行配置
 */
var qfFocus = new Class({
	Implements: [Events, Options],
	options: {
		'duration': 1000,
		'button': {
			'left': 'moveLeft',
			'right': 'moveRight',
			'delay': 100
		},
		'pagebtns': {
			'id': 'slide-number',
			'selected-class': 'active'
		},
		'periodical': 5000
	},

	initialize: function(el, options) {
		this.setOptions(options);
		this.handle = $(el);
		this.index = 0;
		this.lastIndex = 0;
		this.bound = {
			'enter': this.enter.bind(this),
			'leave': this.leave.bind(this),
			'clear': this.clear.bind(this)
		}
		this.lock = false;
		this.lastPageBtn = null;
		this.render();
	},

	init: function() {
		this.periodical = this.focus.periodical(this.options.periodical, this);
	},

	render: function() {
		var ops = this.options, _self = this, hd = this.handle;
		this.items = hd.getElements('li');
		this.pageContainer = document.id(ops.pagebtns.id);
        this.pageBtns = this.pageContainer.getElements('a');

		var items = this.items, btns = this.pageBtns;
		if (items.length != btns.length) {
			alert("分页按钮数目和图片数目不一致，请检查！");
			return;
		}

		items.each(function(item, index){
			(index > 0) && item.setStyle('opacity', 0);
			item.setStyle('position', "absolute");
			item.set('tween',{
				'duration': ops.duration,
				onComplete: function(){
					this.lock = false;
				}.bind(this)
			})
		},this);

		this.leftBtn = document.id(ops.button.left).setStyle('opacity', 0);
		this.rightBtn = document.id(ops.button.right).setStyle('opacity', 0);

		this.css(0);
		this.attach();
	},

	attach: function(){
		var _self = this, lBtn = this.leftBtn, rBtn = this.rightBtn, hd = this.handle;

		this.pageContainer.addEvents({
            'mouseenter': function(){
                _self.pause();
            },
            'mouseleave': function(){
                _self.restart();
            }
        });

        this.pageBtns.each(function(item, index) {
			item.addEvent('mouseenter', function() {
				_self.show(index);
			});
		});

		lBtn.addEvent('click', function(){
			if(_self.lock) return;
			var index = _self.index == 0 ? 4 : _self.index;
			_self.show(--index);
		});
		rBtn.addEvent('click', function() {
			if(_self.lock) return;
			_self.show(++_self.index % (items.length));
		});
		[lBtn, rBtn].each(function(item) {
			item.addEvent('mouseenter', this.bound.clear);
		}.bind(this));

		hd.addEvent('mouseenter', this.bound.enter);
		hd.addEvent('mouseleave', this.bound.leave);	
	},

	focus: function(){
		var index = ++this.index % (this.items.length);
		this.show(index);
	},

	show: function(index){
		this.lock = true;
		this.index = index;
		this.hide(this.lastIndex);
		this.items[index].get('tween').start('opacity', 1);
		this.lastIndex = this.index;
		this.css(index);
	},

	hide: function(index){
		this.items[index].get('tween').start('opacity', 0);
	},

	clear: function() {
		//$clear(this.timer);
		window.clearInterval(this.timer);
	},

	pause: function(){
        if(!this.periodical) return;
        //$clear(this.periodical);
		window.clearInterval(this.periodical);
        this.periodical = null;    
    },

    restart: function(){
        !this.periodical && this.init();   
    },

	enter: function() {
		var lBtn = this.leftBtn, op = lBtn.getStyle("opacity");
		if (op == 1) return;
		this.leftBtn.get('tween').start('opacity', op, 1);
		this.rightBtn.get('tween').start('opacity', op, 1);
		
		this.pause();
	},

	leave: function() {
		this.timer = setTimeout(function() {
			this.leftBtn.get('tween').start('opacity', 1, 0);
			this.rightBtn.get('tween').start('opacity', 1, 0);
		}.bind(this), this.options.button.delay);
		
		this.restart();
	},

	css: function(index) {
		var selectClass = this.options['pagebtns']['selected-class'];
		var pageBtns = this.pageBtns;
		pageBtns[index].addClass(selectClass);
		var lastBtn = this.lastPageBtn;
		if (lastBtn) lastBtn.removeClass(selectClass);
		this.lastPageBtn = pageBtns[index];
	}

});