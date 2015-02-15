//单品热销 图片滚动 效果
    var Speed_0 = 0; //速度(毫秒)
    var Space_0 = 5; //每次移动(px)
    var PageWidth_0 = 106 * 1; //翻页宽度
    var interval_0 = 5000; //翻页间隔
    var fill_0 = 0; //整体移位
    var MoveLock_0 = false;
    var MoveTimeObj_0;
    var MoveWay_0="right";
    var Comp_0 = 0;
    var AutoPlayObj_0=null;
    function GetObj(objName){if(document.getElementById){return eval('document.getElementById("'+objName+'")')}else{return eval('document.all.'+objName)}}
    function AutoPlay_0(){
		clearInterval(AutoPlayObj_0);
		AutoPlayObj_0=setInterval('ISL_GoDown_0();ISL_StopDown_0();',interval_0)
	}
    function ISL_GoUp_0(){if(MoveLock_0)return;clearInterval(AutoPlayObj_0);MoveLock_0=true;MoveWay_0="left";MoveTimeObj_0=setInterval('ISL_ScrUp_0();',Speed_0);}
    function ISL_StopUp_0(){if(MoveWay_0 == "right"){return};clearInterval(MoveTimeObj_0);if((GetObj('ISL_Cont_0').scrollLeft-fill_0)%PageWidth_0!=0){Comp_0=fill_0-(GetObj('ISL_Cont_0').scrollLeft%PageWidth_0);CompScr_0()}else{MoveLock_0=false}
    	//AutoPlay_0()
	}
    function ISL_ScrUp_0(){if(GetObj('ISL_Cont_0').scrollLeft<=0){GetObj('ISL_Cont_0').scrollLeft=GetObj('ISL_Cont_0').scrollLeft+GetObj('List1_0').offsetWidth}
    GetObj('ISL_Cont_0').scrollLeft-=Space_0}
    function ISL_GoDown_0(){clearInterval(MoveTimeObj_0);if(MoveLock_0)return;clearInterval(AutoPlayObj_0);MoveLock_0=true;MoveWay_0="right";ISL_ScrDown_0();MoveTimeObj_0=setInterval('ISL_ScrDown_0()',Speed_0)}
    function ISL_StopDown_0(){if(MoveWay_0 == "left"){return};clearInterval(MoveTimeObj_0);if(GetObj('ISL_Cont_0').scrollLeft%PageWidth_0-(fill_0>=0?fill_0:fill_0+1)!=0){Comp_0=PageWidth_0-GetObj('ISL_Cont_0').scrollLeft%PageWidth_0+fill_0;CompScr_0()}else{MoveLock_0=false}
    	//AutoPlay_0()
	}
    function ISL_ScrDown_0(){if(GetObj('ISL_Cont_0').scrollLeft>=GetObj('List1_0').scrollWidth){GetObj('ISL_Cont_0').scrollLeft=GetObj('ISL_Cont_0').scrollLeft-GetObj('List1_0').scrollWidth}
    GetObj('ISL_Cont_0').scrollLeft+=Space_0}
    function CompScr_0(){if(Comp_0==0){MoveLock_0=false;return}
    var num,TempSpeed=Speed_0,TempSpace=Space_0;if(Math.abs(Comp_0)<PageWidth_0/2){TempSpace=Math.round(Math.abs(Comp_0/Space_0));if(TempSpace<1){TempSpace=1}}
    if(Comp_0<0){if(Comp_0<-TempSpace){Comp_0+=TempSpace;num=TempSpace}else{num=-Comp_0;Comp_0=0}
    GetObj('ISL_Cont_0').scrollLeft-=num;setTimeout('CompScr_0()',TempSpeed)}else{if(Comp_0>TempSpace){Comp_0-=TempSpace;num=TempSpace}else{num=Comp_0;Comp_0=0}
    GetObj('ISL_Cont_0').scrollLeft+=num;setTimeout('CompScr_0()',TempSpeed)}}
    
	function picrun_ini(width, n){
	PageWidth_0 = width * n;
    GetObj("List2_0").innerHTML=GetObj("List1_0").innerHTML;
    GetObj('ISL_Cont_0').scrollLeft=fill_0>=0?fill_0:GetObj('List1_0').scrollWidth-Math.abs(fill_0);
    GetObj("ISL_Cont_0").onmouseover=function(){clearInterval(
		//AutoPlayObj_0
	)}
    GetObj("ISL_Cont_0").onmouseout=function(){
		//AutoPlay_0()
	}
    //AutoPlay_0();
}

