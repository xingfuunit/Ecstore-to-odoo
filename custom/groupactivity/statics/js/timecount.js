//团购倒计时js
var timeCount = new Class({
    init:function(timeStart,timeEnd,dom,isReload){
        this.isReload = isReload || true;
        var diff = Math.abs((timeStart.getTime() - timeEnd.getTime())/1000);
        var secondDiff = diff % 60;
        var minuteDiff = ((diff - secondDiff)/60) % 60;
        var hourDiff = (diff - secondDiff  - minuteDiff*60) / 3600;
        if(hourDiff > 24){
            var dayDiff = parseInt(hourDiff/24);
            hourDiff = hourDiff - dayDiff * 24;
            var timeDiff = [hourDiff,minuteDiff,secondDiff,dayDiff];
        }else{
            var timeDiff = [hourDiff,minuteDiff,secondDiff];
        }
        this.s = this.calcTime.periodical(1000,this,{
            time:timeDiff,
            dom:dom
        });
        if(document.getElement('.desc')){
        this.desc = 10;
        this.d = this.calcDesc.periodical(100,this);
        (function(){$('timer').setStyle('display','block')}).delay(1100);
        }
    },
    addZero:function(timeDiff){
        for(var i=0;i<timeDiff.length;i++){
            if(timeDiff[i].toString().length<2){
                timeDiff[i] = "0" + timeDiff[i].toString();
                return timeDiff;
            }
        }
    },
    formatToInt : function(timeDiff){
        for(var i=0;i<timeDiff.length;i++){
            parseInt(timeDiff[i]);
        }
        return timeDiff;
    },
    judgeTime : function(timeDiff){
        if(timeDiff[2]< 0  && timeDiff[1]>0){
            timeDiff[2] = 59;
            timeDiff[1]--;
            return timeDiff;
        }else if(timeDiff[2] <0 && timeDiff[1]==0 && timeDiff[0]>0){
            timeDiff[2] = 59;
            timeDiff[1] = 59;
            timeDiff[0]--;
            return timeDiff;
        }else if(timeDiff[2]==0 && timeDiff[1]==0 && timeDiff[0]==0){
            $clear(this.s);
            if(document.getElement('.desc')){ $clear(this.d); document.getElement('.desc').innerHTML = 0; }
            if(this.isReload){
                if(typeOf(this.isReload) == 'function'){
                    this.isReload();
                }else{
                    location.reload();
                }
            }
            return;
        }
    },
    calcTime : function (obj){
        if(!obj.dom) return;
        var _timeDiff = obj.time;
        this.addZero(_timeDiff);
        this.formatToInt(_timeDiff);
        _timeDiff[2]--;
        this.judgeTime(_timeDiff);
        this.addZero(_timeDiff);
        var dom = obj.dom;
        if(_timeDiff[3]){
            if(dom.day) dom.day.innerHTML = _timeDiff[3];
            if(dom.second){
                var domBox = dom.second.getParent('span');
                if(domBox) domBox.hide();
            }
            if(dom.minute) dom.minute.innerHTML = _timeDiff[1];
            if(dom.hour) dom.hour.innerHTML = _timeDiff[0];
        }else{
            if(dom.day) {
                var domBox = dom.day.getParent('span');
                if(domBox) domBox.hide();
            }
            if(dom.second){
                dom.second.innerHTML = _timeDiff[2];
            };
            if(dom.minute) dom.minute.innerHTML = _timeDiff[1];
            if(dom.hour) dom.hour.innerHTML = _timeDiff[0];
        }
    },
    calcDesc:function(){
        this.desc--;
        document.getElement('.desc').innerHTML = this.desc;
        if(this.desc == 0)
        this.desc = 10;
    }
});