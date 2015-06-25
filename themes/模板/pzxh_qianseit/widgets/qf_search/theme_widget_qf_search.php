<?php
function theme_widget_qf_search(&$setting,&$smarty){
    $data['search_key'] = $GLOBALS['runtime']['search_key'];
    $res['search_key'] = $data['search_key'];
    //  print_r($res);exit;
    return $res;
}
?>
