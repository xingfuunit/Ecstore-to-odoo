<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_updatecard extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        /** end **/
    }
    

    /*
     *会员中心首页
    * */
    public function index() {
    	$file = fopen(ROOT_DIR."/xiaolin_huangka_checked_500.txt", "r") or exit("Unable to open file!");
    	//Output a line of the file until the end is reached
    	//feof() check if file read end EOF
    	while(!feof($file))
    	{
    		$card = intval(fgets($file));
    		$card_info = $this->app->model('member_card')->getList('*',array('card_number'=>$card));
    		$card_number = $card_info[0]['card_number'];
    		if(!$card_number){
    			echo "No exist card " .$card. "<br />";
    		}
    		else{
    			$card_data=array(
					'card_lv_id'=>1
				);
    			if(!$this->app->model('member_card')->update($card_data,array('card_number'=>$card_number))){
    				echo "failed card  " .$card_number. "<br />";
    			}
    		}
    	}
    	fclose($file);
    }
    
    

}
