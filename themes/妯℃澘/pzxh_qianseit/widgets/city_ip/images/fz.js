window.stokeAddress=function(config){
	var _this=this;
	config=config||{};
	this.msg=config.msg;
	this.renderTo="#store-selector .content .clr";
	this.selectTab="#JD-stock .tab li";
	this.selectContent="#JD-stock div[data-widget='tab-content']";
	this.currentPageLoad =config.currentPageLoad||{"isLoad":true,"areaCookie":[1,72,0,0]}
	this.areaInfo={
		"currentLevel": 1,
	    "currentProvinceId": 1,"currentProvinceName":"",
		"currentCityId": 0,"currentCityName":"",
		"currentAreaId": 0,"currentAreaName":"",
		"currentTownId":0,"currentTownName":""
	};
	this.requestLevel = 2; //最少三级菜单
	this.init(config);	  //html
 }
stokeAddress.prototype={
	init:function(config){
	  var _this=this;
	  this.el=jq(document.createElement('div'));
	  var html='<div data-widget="tabs" class="m JD-stock" id="JD-stock">'
		   +'<div class="mt">'
		   +' <ul class="tab">'
		   +' <li data-index="0" data-widget="tab-item" class="curr"><a href="javascript:;" class="hover"><em>请选择</em><i></i></a></li>'
		   +' <li data-index="1" id="tab_city_item" data-widget="tab-item" style="display:none;"><a href="javascript:;" class=""><em>请选择</em><i></i></a></li>'
		   +'<li data-index="2" id="tab_area_item" data-widget="tab-item" style="display:none;"><a href="javascript:;" class=""><em>请选择</em><i></i></a></li>'
		   +'<li data-index="3" data-widget="tab-item" style="display:none;"><a href="javascript:;" class=""><em>请选择</em><i></i></a></li>'
		   +' </ul>'
		   +'<div class="stock-line"></div>'
		   +'</div>'
		   +'<div class="mc" data-area="0" data-widget="tab-content" id="stock_province_item"></div>'
		   +'<div class="mc" data-area="1" data-widget="tab-content" id="stock_city_item"></div>'
		   +'<div class="mc" data-area="2" data-widget="tab-content" id="stock_area_item"></div>'
		   +'<div class="mc" data-area="3" data-widget="tab-content" id="stock_town_item"></div>'
		   +'</div>';
	  this.el.html(html);
	  this.el.find("#stock_province_item").html(this.msg)
	  this.el.insertBefore(this.renderTo);
	  //判断已选cookie
	  if(config.currentPageLoad.areaCookie!=undefined)
	  {
		 var cookieJsonObj=config.currentPageLoad.areaCookie;
		 var tempDom = jq("#stock_province_item a[data-value='"+cookieJsonObj.ProvinceId+"']");
		 if(cookieJsonObj.ProvinceId&&tempDom.length>0){
			//本地cookie有该级地区ID
			// tempDom.click();
		 }
	 }
	 jq(_this.selectTab).bind('click',function(){
		var level = jq(this).attr("data-index");
		level = new Number(level);
		jq(_this.selectTab).removeClass("curr").eq(level).addClass("curr");
		jq(_this.selectTab).find("a").removeClass("hover").eq(level).addClass("hover");
		jq(_this.selectContent).hide().eq(level).show();
	  });
	 jq("#stock_province_item a").unbind("click").click(function(config) {	
		_this.currentPageLoad.isLoad = false;
		try{
			_this.areaInfo.currentProvinceId = jq(this).attr("data-value");
			_this.areaInfo.currentProvinceName = jq(this).html();
			jq(_this.selectTab).eq(0).find("em").html(_this.areaInfo.currentProvinceName);
			stokeAddress.prototype.GetNextAreas(_this,_this.areaInfo.currentProvinceId,_this.areaInfo.currentCityId,_this.areaInfo.currentAreaId,_this.areaInfo.currentTownId,1);  //获取下一级
		 }catch (err){
		   alert(err)
		  }
	}).end();
	jq("#store-selector .close").unbind("click").bind("click",function(){
		 
	});
  },
GetNextAreas:function(_this,ProvinceId,AreaInfo,CityId,AreaId,level){	 //获取下一级
  if (level == _this.requestLevel){
	  jq(_this.selectTab).removeClass("curr").eq(level-1).addClass("curr");
	 jq(_this.selectTab).find("a").removeClass("hover").eq(level-1).addClass("hover");
	 var fid=arguments[level];
	if(level == 3){	      //第3级并且有子级点
	  //stokeAddress.prototype.getStockCallback(_this,fid,level);
	  stokeAddress.prototype.getProvinceStockCallback(_this,ProvinceId,AreaInfo,CityId,AreaId,level) //级数结束 赋值文本框

	}else{
	  stokeAddress.prototype.getProvinceStockCallback(_this,ProvinceId,AreaInfo,CityId,AreaId,level) //级数结束 赋值文本框
	}
   }
  if(level < _this.requestLevel){ //还需要获取下级地址
	_this.currentLevel = level +1;
	jq(_this.selectTab).removeClass("curr").eq(level).addClass("curr");
	jq(_this.selectTab).find("a").removeClass("hover").eq(level).addClass("hover");
	 stokeAddress.prototype.getChildAreaHtml(_this,arguments[level],level +1);
  }
},
getStockCallback:function(_this,fid,level){
	_this.requestLevel = 4;
	//Ajax处理
	stokeAddress.prototype.getAreaList_callback(_this,"",level+1);
},
getProvinceStockCallback:function(_this,ProvinceId,AreaInfo,CityId,AreaId,level){
	if(_this.currentLevel==4){
	  for (var i=level,j=jq(_this.selectTab).length;i<j;i++){
		jq(_this.selectTab).eq(i).hide();
		jq(_this.selectContent).eq(i).hide();
	  }
	}
	//赋值到cookie中
	stokeAddress.prototype.setCookie("ProvinceId",_this.areaInfo.currentProvinceId);
	stokeAddress.prototype.setCookie("ProvinceName",_this.areaInfo.currentProvinceName);
	stokeAddress.prototype.setCookie("CityId",_this.areaInfo.currentCityId);
	stokeAddress.prototype.setCookie("CityName",_this.areaInfo.currentCityName);
	stokeAddress.prototype.setCookie("AreaId",_this.areaInfo.currentAreaId);
	stokeAddress.prototype.setCookie("AreaName",_this.areaInfo.currentAreaName);
    var address = _this.areaInfo.currentProvinceName+_this.areaInfo.currentCityName/*+_this.areaInfo.currentAreaName+_this.areaInfo.currentTownName*/;
	jq("#store-selector .text div").html(address).attr("title",address);
	 var adds = _this.areaInfo.currentProvinceId+"-"+_this.areaInfo.currentCityId+"-"+_this.areaInfo.currentAreaId+"-"+_this.areaInfo.currentTownId;
	//stokeAddress.prototype.setCookie("ipLoc-djd", adds, 30, "/", null, false);
	stokeAddress.prototype.reBindStockEvent(_this);  //文本框去掉hover样式

},
getChildAreaHtml:function(config,fid,level){	 
  var idName = stokeAddress.prototype.getIdNameByLevel(level);
  new Request.JSON({
	    url:getAreaByIDURL,
        onComplete: function(person, text){
          if(person.status==1){
        	  //alert(person.realregion.length);
        	  var provinceTownJsonList=new Array();
        	  for(var i=0; i<person.realregion.length; i++)
        	  {
        		  var provinceTownJson =new Object();
        		  provinceTownJson.id=person.realregion[i].region_id;
        		  provinceTownJson.name=person.realregion[i].local_name;
        		  provinceTownJsonList[i]=provinceTownJson;
        	  };
        	  if (idName){
        			jq("#stock_province_item,#stock_city_item,#stock_area_item,#stock_town_item").hide();
        			jq("#"+idName).show().html("<div class='iloading'>正在加载中，请稍候...</div>");
        			jq(config.selectTab).show().removeClass("curr").eq(level-1).addClass("curr").find("em").html("请选择");
        			for (var i=level,j=jq(config.selectTab).length;i<j ;i++ ){
        			  jq(config.selectTab).eq(i).hide();
        			}
        			if(level == 2){	
        			   stokeAddress.prototype.getAreaList_callback(config,provinceTownJsonList,level);
        			}
        			else{
        			  config.areaInfo.currentFid = fid;
        			  if(level == 3){	
        				stokeAddress.prototype.getAreaList_callback(config,provinceTownJsonList,level);
        			  }else{
        				stokeAddress.prototype.getAreaList_callback(config,provinceTownJsonList,level);      
        		 }
        	  }
            }
          }else{
              alert("提交失败");
          }
      }
  }).post('regionId='+fid);
},
getAreaList_callback:function(config,result,level){  //下级地址回调	
	config.currentLevel= level;
	stokeAddress.prototype.getAreaList(config,result,stokeAddress.prototype.getIdNameByLevel(level),level);
  },
getAreaList:function(config,result,idName,level){
 // debugger;
  if (idName && level){
  jq("#"+idName).html("");
  var html = "<ul class='area-list'>";
  var longhtml = "";
  var longerhtml = "";
  if (result&&result.length > 0){
	for (var i=0,j=result.length;i<j ;i++ ){
	  //result[i].name = result[i].name.replace(" ","");
	  if(result[i].name.length > 12){
		longerhtml += "<li class='longer-area'><a href='javascript:;' data-value='"+result[i].id+"'>"+result[i].name+"</a></li>";
	  }else if(result[i].name.length > 5){
		longhtml += "<li class='long-area'><a href='javascript:;' data-value='"+result[i].id+"'>"+result[i].name+"</a></li>";
	  }else{
		html += "<li><a href='javascript:;' data-value='"+result[i].id+"'>"+result[i].name+"</a></li>";
	  }
	}
  }else{
	html += "<li><a href='javascript:;' data-value='"+config.areaInfo.currentFid+"'> </a></li>";
  }
  html +=longhtml+longerhtml+"</ul>";
  jq("#"+idName).html(html);
  jq("#"+idName).find("a").click(function(){
	var areaId = jq(this).attr("data-value");
	var areaName = jq(this).html();
	var level = jq(this).parent().parent().parent().attr("data-area");
	jq(config.selectTab).eq(level).find("a").attr("title",areaName).find("em").html(areaName.length>6?areaName.substring(0,6):areaName);
	level = new Number(level)+1;
	if (level=="2"){
	  config.areaInfo.currentCityId = areaId;
	  config.areaInfo.currentCityName = areaName;
	  config.areaInfo.currentAreaId = 0;
	  config.areaInfo.currentAreaName = "";
	  config.areaInfo.currentTownId = 0;
	  config.areaInfo.currentTownName = "";
	}
	else if (level=="3"){
	  if (config.requestLevel == 4 && config.areaInfo.currentAreaId != areaId){
		config.requestLevel = 3;
	  }
		config.areaInfo.currentAreaId = areaId;
		config.areaInfo.currentAreaName = areaName;
		config.areaInfo.currentTownId = 0;
		config.areaInfo.currentTownName = "";
	}
	else if (level=="4"){
		config.areaInfo.currentTownId = areaId;
		config.areaInfo.currentTownName = areaName;
	}
	currentLocation = config.areaInfo.currentProvinceName;
	stokeAddress.prototype.GetNextAreas(config,config.areaInfo.currentProvinceId,config.areaInfo.currentCityId,config.areaInfo.currentAreaId,config.areaInfo.currentTownId,level);  //获取下一级
   });
    
   //页面初次加载
   if (config.currentPageLoad.isLoad){
     if(config.currentPageLoad.areaCookie!=undefined)
     {
	   var cookieJsonObj=config.currentPageLoad.areaCookie;
	   var tempDom = jq("#"+idName+" a[data-value='"+cookieJsonObj.CityId+"']");
	  // if(cookieJsonObj.CityId&&tempDom.length>0){
		 //本地cookie有该级地区ID
	//	 tempDom.click();
	 //  }
	 //  else{
	//	 jq("#"+idName+" a:first").click();
	 //  }
     }  
   }		
  }
},
getIdNameByLevel:function(level){
	var idName = "";
	if (level == 1){
		idName = "stock_province_item";
	}
	else if (level == 2){
		idName = "stock_city_item";
	}
	else if (level == 3){
		idName = "stock_area_item";
	}
	else if (level == 4){
		idName = "stock_town_item";
	}
	return idName;
   },
reBindStockEvent:function(_this){
	jq("#store-selector").removeClass("hover");
	if(_this.requestLevel == 3) {
		_this.requestLevel = 2;
		jq("#store-selector").find(".content, .close").removeAttr("style");
		// jq('#stock_area_item, #tab_area_item').css('display','none');
		// jq('#tab_city_item').addClass('curr');
		// jq('#tab_city_item a').addClass('hover');
		// jq('#stock_city_item').css('display','block');
	}
	if(typeof update_selectortext_goods == 'function'){
		update_selectortext_goods();	
	}
},
getCookie: function(name) { 
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
    if ( arr != null) {
        return unescape(arr[2]);
    } else {
        return "";
    }
  }, 
 setCookie: function(name, value, expires, path, domain, secure) {
    var today = new Date(); 
	today.setTime(today.getTime());
	if (expires) expires = expires * 1000 * 60 * 60 * 24; 
	var expires_date = new Date(today.getTime() + (expires)); 
	document.cookie = name + '=' + escape(value) + ((expires) ? ';expires=' + expires_date.toGMTString() : '') + ((path) ? ';path=' + path : '') + ((domain) ? ';domain=' + domain : '') + ((secure) ? ';secure' : ''); 
	},	
getNameById:function(provinceId){
		
	for(var o in iplocation){
		if (iplocation[o]&&iplocation[o].id==provinceId){
			return o;
		}
	}
	return "北京";
  }
}


