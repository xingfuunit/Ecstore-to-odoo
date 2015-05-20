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

function fillNextSelect(select){
    selectedOption = $(select).find('option:selected');
    var nextkey = $(selectedOption).attr('data-key');
    var nextlevel = parseInt($(select).attr('data-level')) + 1;
    $(select).next().innerHTML = genOptions(nextlevel,nextkey);
}