<?php 
function widget_theme_sm_kf(&$setting,&$smarty){
  foreach($setting['floor'] as $floor){
     $data['floor'] = $floor;
  }
  return $data;
}
?>
