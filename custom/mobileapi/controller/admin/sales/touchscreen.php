<?php
/**
 * july by 2015-06-15
 */
class mobileapi_ctl_admin_sales_touchscreen extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';
	
	//视频上传后的保存目录
    var $vod_saveDir = '/public/vod/';

    function index(){
        $this->finder('mobileapi_mdl_sales_touchscreen',array(
            'title'=>app::get('b2c')->_('门店触屏'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加门店触屏广告'),'icon'=>'add.gif','href'=>'index.php?app=mobileapi&ctl=admin_sales_touchscreen&act=create','target'=>'_blank'),

				)
            ));

		$html = file_get_contents(ROOT_DIR. '/custom/mobileapi/view/admin/sales/touchscreen_help.html');
		$this->pagedata['_PAGE_CONTENT'] = $html;	

        $this->page();
    }
	

    function create(){

		//记录当前登陆用户所在门店，这块后期处理
        $this->pagedata['users_branch_bn'] = $this->app->model('sales_touchscreen')->get_branch_bn();
		
		$this->pagedata['upload_key'] = $this->vod_uploadkey();
        $this->pagedata['adInfo'] = $adInfo;
        $this->pagedata['touchscreen_branch'] 	= kernel::database()->select("select branch_bn as id, name from sdb_ome_branch where is_show ='true' and  nostore_sell='false'  order by branch_bn asc");
        $this->pagedata['touchscreen_position'] = $this->app->model('sales_touchscreen')->get_sales_touchscreen_position_list();
    	$this->singlepage('admin/sales/touchscreen_detail.html');
    }
	
    function edit($ad_id){
    	header("Cache-Control:no-store");

		
		//记录当前登陆用户所在门店，这块后期处理
        $this->pagedata['users_branch_bn'] = $this->app->model('sales_touchscreen')->get_branch_bn();

        $this->path[] = array('text'=>app::get('b2c')->_('电视触屏图片编辑'));
        $objAd = $this->app->model('sales_touchscreen');
		
		$this->pagedata['upload_key'] = $this->vod_uploadkey();
        $this->pagedata['adInfo'] = $objAd->dump($ad_id);
		
		//------------------------------------------------------
		//检查权限
		$bn = $this->app->model('sales_touchscreen')->get_branch_bn();
		if(strlen($bn)>0){
			if($bn != $this->pagedata['adInfo']['branch_bn']){
				die('No authority!');
			}
		}
		//------------------------------------------------------
        $this->pagedata['touchscreen_branch'] 	= kernel::database()->select("select branch_bn as id, name from sdb_ome_branch where is_show ='true' and  nostore_sell='false'  order by branch_bn asc");
        
        $this->pagedata['touchscreen_position'] = $this->app->model('sales_touchscreen')->get_sales_touchscreen_position_list();
        $this->singlepage('admin/sales/touchscreen_detail.html');
    }
	
    function save(){
    	$this->begin('');
    	$objAd = $this->app->model('sales_touchscreen');
    	$_POST['last_modify'] = time();
		
		//-----------------------------------------------
        $arr_pos	= $this->app->model('sales_touchscreen')->get_sales_touchscreen_position_list();
		$pos_rs		= $arr_pos[$_POST['pos_id']];
		
		$_POST['url_type'] = $pos_rs['type'];
		$_POST['pos_name'] = $pos_rs['name'];
		$_POST['ad_img_w'] = intval($pos_rs['width']);
		$_POST['ad_img_h'] = intval($pos_rs['height']);
		$_POST['ordernum'] = intval($_POST['ordernum']);
		
		if(strlen($_POST['branch_bn'])>1){
			//------------------------------------------------
			//检查权限
			$bn = $this->app->model('sales_touchscreen')->get_branch_bn();
			if(strlen($bn)>0){
				if($_POST['branch_bn'] != $bn){
					die('No authority!');
				}
			}
			//------------------------------------------------
			
			$row = kernel::database()->selectrow("select name from sdb_ome_branch where branch_bn='". $_POST['branch_bn'] ."'");
			if(isset($row) && is_array($row)){
				$_POST['branch_name'] = $row['name'];	
			}else{
				$_POST['branch_bn'] = '';
				$_POST['branch_name'] = '';	
			}
		}else{
			$_POST['branch_bn'] = '';
			$_POST['branch_name'] = '';
		}

		
    	if ($objAd->save($_POST)) {

			//-----------------------------------------------
			//存在视频内容
			$vodfile = isset($_POST['vodfile'])?$_POST['vodfile']:'';
			if(strlen($vodfile)>5){
				//如果重新上传，移动到视频目录
				$this->vod_movefile($vodfile);
				
				//如果用户重新上传，即删除旧的视频文件
				$this->vod_delfile($_POST['vodfile_old'],$vodfile);
			}
			//-----------------------------------------------

			
    		$this->end(true,app::get('b2c')->_('保存成功'));
    	} else {
    		$this->end(true,app::get('b2c')->_('保存失败'));
    	}
    	
    }


	
	/*
	 * 上传专用key
	 * */
	function vod_uploadkey(){
		$rnd = strtolower(weixin_util::create_noncestr(16));
		$key = md5( $rnd.'视频上传').$rnd;
		return $key;
	}
	
	/*
	* 如果上传的新视频文件和旧的不相同，即删除旧文件
	* */
	function vod_delfile($oldFile,$newFile){
		
		//文件相同直接跳出
		if(strlen(''.$oldFile)<5 || $oldFile == $newFile){
			return true;
		}
		
		$oldPath = ROOT_DIR .$oldFile;
		@unlink($oldPath);
		return true;
	}
	
	/*
	* 视频上传后，不立即移动到网站目录下，只有保存时才移动
	* */
	function vod_movefile($newFile){
			
		if(strlen(''.$newFile)<5){
			return true;
		}
		
		$saveDir  = ROOT_DIR . $this->vod_saveDir;
		
		// Create saveDir
		if (!file_exists($saveDir)) {
			if(@mkdir($saveDir,0755)){
				@chmod($saveDir,0755);
			}
		}
		
		//-----------------------------------------------
		//如果文件存在，即不用再移动 
		$newPath = ROOT_DIR .$newFile;
		if(is_file($newPath)){
			return true;
		}
		

		//-----------------------------------------------
		$tmpPath = $this->get_temp_dir() . DIRECTORY_SEPARATOR . $this->get_filename($newFile);
		
		//如果暂时文件不存在，即直接跳出
		if(!is_file($tmpPath)){
			return false;
		}
		//-----------------------------------------------
		
		if (move_uploaded_file($tmpPath, $newPath)){
			return true;
			
		}else{
			if (copy($tmpPath, $newPath)) {
				@unlink($tmpPath);
				return true;
			}
		}
		return false;
	}
	
	/*
	 * 从路径中抽取文件名(是否包含扩展名)
	 * @author 	july
	 * @param string	$filePath	文件路径
	 * @return string
	 * */
	function get_filename($filePath){
		if(strlen($filePath)==0){return '';}

		if(strrpos( $filePath, '/' )!==false){
			$filePath = substr( $filePath, strrpos( $filePath, '/' )+1 );
			
		}else if(strrpos( $filePath, '\\' )!==false){
			$filePath = substr( $filePath, strrpos( $filePath, '\\' )+1 );
		}
		
		return $filePath;
	}
	
	/*
	* 取得上传的暂时文件夹
	* */
	function get_temp_dir(){
		$tmpDir = ''.ini_get("upload_tmp_dir");
		if($tmpDir==''){
			$tmpDir = sys_get_temp_dir();	//	sys_get_temp_dir() 必须php 5.2.1 后才有的函数				
		}
		return $tmpDir . DIRECTORY_SEPARATOR . 'ecstore_plupload';
	}
}
