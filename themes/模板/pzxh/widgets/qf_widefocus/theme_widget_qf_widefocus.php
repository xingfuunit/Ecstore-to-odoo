<?php
function theme_widget_qf_widefocus(&$setting,&$smarty){
    foreach($setting['focus'] as $focus){
        $data['focus'] = $focus;
    }
	//print_r($setting['small']);exit;
    return $data;
}
?>

