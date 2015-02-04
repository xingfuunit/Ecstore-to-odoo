<?php

class b2c_finder_local_staff{    
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_local_staff&act=edit&_finder[finder_id]=d7cb2a&finder_id=d7cb2a&p[0]='.$row['staff_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑员工信息').'\', width:680, height:250}">'.app::get('b2c')->_('编辑').'</a>';
    }
    
    var $column_local='仓库';
    
    function column_local($row){
        $local_store=app::get('ome')->model('branch')->getList('*',array('branch_id'=>$row['branch_id']));
       
       
        return $local_store[0]['name'];
    }
    
}
