<?php

class b2c_finder_local_charge{    

    var $detail_basic='详细信息';
    
    function detail_basic($charge_id){
       $app = app::get('b2c');
       $render = $app->render();
       $charge=app::get('ome')->model('charge')->dump($charge_id);
      
       $render->pagedata['charge']=$charge;
       return $render->fetch('admin/local/charge.html');
    }
    
    var $column_print='打印';
    
    function column_print($row){
        return '<a href="index.php?app=b2c&ctl=admin_local_charge&act=printing&p[0]=' . $row['charge_id'] . '" title='.app::get('b2c')->_("交班结账单").' target="_blank">'.app::get('b2c')->_('结账单').'</a>';
    }
    
}