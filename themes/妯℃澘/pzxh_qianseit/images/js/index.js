var index_fade = function() {  
    var fastHoverFun;  
    var hovetime=200;//无意识滑过时间  
    $$('.pt2-r img').addEvents({  
        'mouseenter': function(){       
		    var gallerys = $$(this).getParent(".pt2-r").getElements('img');       
            var _self = this;  
            fastHoverFun = setTimeout(function(){  
                $$(gallerys).fade(0.8); //鼠标移入，除当前图片，其余全部变暗(透明度50%)  
                _self.set("tween", {  
                    duration: 400,//淡出时间  
                    transition: Fx.Transitions.Sine.easeIn  
                }).fade(1); //鼠标移入，当前图片高亮(透明度100%)  
            },hovetime);  
        },   
        'mouseleave': function(){  
            clearTimeout(fastHoverFun);  
        }  
    });  
    $$('.pt2-r img').addEvent('mouseleave', function(){   
        $$('.pt2-r img').fade(1); //鼠标移出图片区域，恢复100%  
        }  
    );  
}; 

window.addEvent('domready', function() {  
    index_fade();  
});

var index_fade2 = function() {  
    var fastHoverFun;  
    var hovetime=200;//无意识滑过时间  
    $$('.pt1-ad img').addEvents({  
        'mouseenter': function(){       
		    var gallerys = $$(this).getParent(".pt1-ad").getElements('img');       
            var _self = this;  
            fastHoverFun = setTimeout(function(){  
                $$(gallerys).fade(0.8); //鼠标移入，除当前图片，其余全部变暗(透明度50%)  
                _self.set("tween", {  
                    duration: 400,//淡出时间  
                    transition: Fx.Transitions.Sine.easeIn  
                }).fade(1); //鼠标移入，当前图片高亮(透明度100%)  
            },hovetime);  
        },   
        'mouseleave': function(){  
            clearTimeout(fastHoverFun);  
        }  
    });  
    $$('.pt1-ad img').addEvent('mouseleave', function(){   
        $$('.pt1-ad img').fade(1); //鼠标移出图片区域，恢复100%  
        }  
    );  
}; 

window.addEvent('domready', function() {  
    index_fade2();  
});
var index_fade3 = function() {  
    var fastHoverFun;  
    var hovetime=200;//无意识滑过时间  
    $$('.pt3_c img').addEvents({  
        'mouseenter': function(){       
		    var gallerys = $$(this).getParent(".pt3_c").getElements('img');       
            var _self = this;  
            fastHoverFun = setTimeout(function(){  
                $$(gallerys).fade(0.8); //鼠标移入，除当前图片，其余全部变暗(透明度50%)  
                _self.set("tween", {  
                    duration: 400,//淡出时间  
                    transition: Fx.Transitions.Sine.easeIn  
                }).fade(1); //鼠标移入，当前图片高亮(透明度100%)  
            },hovetime);  
        },   
        'mouseleave': function(){  
            clearTimeout(fastHoverFun);  
        }  
    });  
    $$('.pt3_c img').addEvent('mouseleave', function(){   
        $$('.pt3_c img').fade(1); //鼠标移出图片区域，恢复100%  
        }  
    );  
}; 

window.addEvent('domready', function() {  
    index_fade3();  
});



