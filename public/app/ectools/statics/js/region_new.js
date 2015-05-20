function genOptions(level,key){
    var data='';
    var html = '<option value="0">请选择</option>';

    if(level!=0){
        data = region_Data[level][key];
    }
    else{
        data = region_Data[level];
    }

    if(region_Data){
        $.each(data,function(k,v){
            var attrs = v.split(':');
            var level = '';
            if(attrs[2]) {
                level = ' data-key="' + attrs[2] + '"';
            }
            html = html+'<option value="' + attrs[1] + '"' + level + '>' + attrs[0] + '</option>';
        })
    }

    return html;
}

function showNextSelect(select){
    selectedOption = $(select).find('option:selected');
    var nextkey = $(selectedOption).attr('data-key');
    if(!nextkey)
        return;
    var nextlevel = parseInt($(select).attr('data-level')) + 1;
    $(select).next().html( genOptions(nextlevel,nextkey) ).css('display','inline-block');
}



function hideNextSelect(select){
    $(select).nextAll().html('').hide();
}

function setRegionData($container){
    var ipt = $container.find('input');
    var pack = $(ipt).attr('package');
    var sels = $container.find('select');
    var regionArr = [];
    var text = '';
    var value = 0;
    $.each(sels,function(k,v){
        var curVal = $(v).find('option:selected').val();
        if(curVal == '0' || curVal == undefined)
            return false;

        regionArr.push( $(v).find('option:selected').html() );
        value=curVal;
    });

    $(ipt).val( pack+':'+regionArr.join('/')+':'+value );
    
}

function initRegionSelect($container){

    var firstSelect = $container.find('select')[0];
    firstSelect.innerHTML=genOptions(0);
    $(firstSelect).css('display','inline-block');

    var regionData = $container.find('input').val();
    var regionDataArr = regionData.split(':');
    var regionTextArr = regionDataArr[1].split('/');

    $.each(regionTextArr,function(k,v){
        var thisSelect = $container.find('select[data-level='+k+']')[0];

        $(thisSelect).find('option').each(function(key,value){
            if($(this).html()==v){
                $(this).attr("selected", true);
                return false;
            }
        });
        
        showNextSelect(thisSelect);
    })

}

function checkRegionSelect($container){
    var sels = container.find('select');
    var status = true;

    $.each(sels,function(k,v){
        if(!$(v).is(':hidden') && $(v).find('option:selected').val() == '0'){
            status = false;
            return false;
        }
    });
    return status;
}