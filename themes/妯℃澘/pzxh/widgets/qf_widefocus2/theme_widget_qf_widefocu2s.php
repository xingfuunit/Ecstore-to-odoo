<?php
function theme_widget_qf_widefocus2(&$setting,&$smarty){
    foreach($setting['focus'] as $focus){
        $data['focus'] = $focus;
    }
	//print_r($setting['small']);exit;
    return $data;
}
?>

