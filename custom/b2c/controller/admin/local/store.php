<?php

class b2c_ctl_admin_local_store extends desktop_controller {
	public function __construct($app) {
		parent::__construct($app);
	}

	function index() {
		$local_address = $this->app->model('local_store');
		$this->finder('b2c_mdl_local_store',array(
            'title'=>app::get('b2c')->_('门店地址'),
            'allow_detail_popup'=>false,
            'use_buildin_filter'=>false,
            'base_filter' =>array('for_comment_id' => 0),
            'use_view_tab'=>true,
            'actions'=>array(
            	array('label'=>app::get('b2c')->_('添加门店地址'),'href'=>'index.php?app=b2c&ctl=admin_local_store&act=modify_address','target'=>'dialog::{width:680,height:350,title:\''.app::get('b2c')->_('添加门店地址').'\'}'),
            )
        ));
	}

	function modify_address($local_id = null) {
		$local_address = $this->app->model('local_store');
		$this->begin();
		if ($_POST) {
			if( !kernel::single('b2c_member_addrs')->check_addr_post($_POST, 1, $msg) ) {
				$this->end(false, $msg);
			}
			/*需要存储的数据*/
			$save_data = array(
	            'local_id' => $_POST['local_id'],
	            'local_name' => $_POST['local_name'],
	            'area' => $_POST['area'],
	            'addr' => $_POST['addr'],
	            'name' => $_POST['name'],
	            'zip'  => $_POST['zip'],
	            'tel'  => $_POST['tel'],
	            'mobile' => $_POST['mobile'],
        	);
        	if ( !$save_data['local_id'] ) {
        		$local_address->insert($save_data);
        	} else {
        		$local_address->update($save_data, array('local_id' => $save_data['local_id']));
        	}
            // 同步erp
            /*if ( $save_data['local_id'] ) {
                $store_obj  = kernel::single('b2c_erp_store');
                $save_data['shop_bn']   = $store_obj->getShopBN($save_data['local_id']);
                $ret = $store_obj->sync($save_data);
            }*/
			$this->end(true,app::get('b2c')->_('操作成功'));
		}

		if ($local_id) {
			$data = $local_address->getRow('*', array('local_id'=> $local_id));
			$this->pagedata['local_id'] = $local_id;
			$this->pagedata = $data;
		}
		$this->display('admin/local/address.html');
	}

    /**
     * curl post
     */
    private function curl_post( $param = array() ) {
        $opt    = array(
            CURLOPT_POST            => 1,
            CURLOPT_HEADER          => 0,
            CURLOPT_URL             => $param['url'],
            CURLOPT_FRESH_CONNECT   => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_FORBID_REUSE    => 1,
            CURLOPT_TIMEOUT         => 4,
            CURLOPT_POSTFIELDS      => http_build_query($param['post']),
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
        );
        $ch = curl_init();
        curl_setopt_array($ch, $opt);
        if( !$result = curl_exec($ch) ) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}
