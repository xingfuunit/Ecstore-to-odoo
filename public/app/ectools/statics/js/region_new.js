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

function fillSelect(select,key){
    level = $(select).attr('data-level');
    select.innerHTML = genOptions(level,key);
}