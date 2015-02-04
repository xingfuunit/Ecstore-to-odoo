<?php

class b2c_finder_local_dailycash{    
//    var $column_edit = '操作';
//    function column_edit($row){
//        return '<a href="index.php?app=b2c&ctl=admin_local_charge&act=edit&_finder[finder_id]=d7cb2a&finder_id=d7cb2a&p[0]='.$row['staff_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑员工信息').'\', width:680, height:250}">'.app::get('b2c')->_('查看').'</a>';
//        
//    }
    var $detail_basic='详细信息';
    
    function detail_basic($cash_id){
       $app = app::get('b2c');
       $render = $app->render();
       $cash=app::get('ome')->model('dailycash')->dump($cash_id);
       $staff=app::get('b2c')->model('local_staff')->dump($cash['staff_id']);
      
      $branch=app::get('ome')->model('branch')->dump($cash['branch_id']);
      $cash['branch_name']=$branch['name'];
       $cash['staff_name']=$staff['staff_name'];
       $render->pagedata['cash']=$cash;
       return $render->fetch('admin/local/cash.html');
    }
    
    var $column_print='打印';
    
    function column_print($row){
        return '<a href="index.php?app=b2c&ctl=admin_local_dailycash&act=printing&p[0]=' . $row['cash_id'] . '" title='.app::get('b2c')->_("门店现金日结记录").' target="_blank">'.app::get('b2c')->_('日结记录单').'</a>';
    }
    

    
}