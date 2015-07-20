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
    
    
    public function add() {
    	$card_content = file_get_contents(ROOT_DIR."/card/jin_number1.txt");
    	$card_number = explode("\r\n", $card_content);
    	
    	$passwd_content = file_get_contents(ROOT_DIR."/card/jin_password1.txt");
    	$card_passwd = explode("\r\n", $passwd_content);

    	foreach($card_number as $k => $v){
    		$card_data=array(
    				'card_number'=>$v,
    				'card_password'=>$card_passwd[$k],
    				'card_lv_id'=>3,
    				'card_advance'=>'0',
    				'card_point'=>'0',
    				'create_time'=>time(),
    				'expired_time'=>'0',
    				'active_time'=>'0',
    				'card_etc'=>date('Ymd').'1110',
    				'card_state'=>'0',
    		);
    		$card_info = $this->app->model('member_card')->getList('*',array('card_number'=>$v));
    		$card_number = $card_info[0]['card_number'];
    		if($card_number){
    			exit('card exist '.$card_number);
    		}
    		if($this->app->model('member_card')->insert($card_data)){
    			echo "card insert succ " .$v. "<br />";
    		}else{
    			echo "card insert failed " .$v. "<br />";
    		}
    	}
    }
    

}
