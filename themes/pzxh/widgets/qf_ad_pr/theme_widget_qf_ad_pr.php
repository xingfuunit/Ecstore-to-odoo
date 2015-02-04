<?php
function theme_widget_qf_ad_pr(&$setting,&$smarty){	
    foreach($setting['ad'] as $focus){
        $data['ad'] = $focus;
    }
    return $data;
}
?>

