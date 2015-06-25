<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class importdata_ctl_default extends base_controller{

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->data_dir = str_replace('/app/', '/custom/', $this->app->app_dir.'/xmlData');
    }
    
    function index(){
    	kernel::single('importdata_func')->sync();
    }

    
}
