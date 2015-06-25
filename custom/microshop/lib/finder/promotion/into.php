<?php

class microshop_finder_promotion_into {

	var $column_edit = '操作';
    function column_edit($row){
        $return = '<a href="index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=show_logs&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['pri_id'].'" target="dialog::{title:\'处理分成\', width:680, height:450}">处理分成</a>';
        return $return;
        
    } 
    
}
