<?php
function theme_widget_qf_ad(&$setting,&$smarty){	
	if(is_array($setting['ad'])){
		foreach($setting['ad'] as $ad){
			$data[] = $ad;
		}
	}
    return $data;
}
?>

