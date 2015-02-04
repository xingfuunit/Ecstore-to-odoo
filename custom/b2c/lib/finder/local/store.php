<?php

class b2c_finder_local_store{    
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_local_store&act=modify_address&_finder[finder_id]=cf98a1&finder_id=cf98a1&p[0]='.$row['local_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑会员等级').'\', width:680, height:250}">'.app::get('b2c')->_('编辑').'</a>';
    }  
}