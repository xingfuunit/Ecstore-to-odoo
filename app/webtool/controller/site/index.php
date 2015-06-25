<?php
/**
 * 凌云
 *
 * @package default
 * @author 758238751@qq.com
 */
class webtool_ctl_site_index extends site_controller
{
	public function __construct(&$app){
		parent::__construct($app);
		$this->password = md5('999admin');
		$this->app = $app;
        $this->dir = $this->app->app_dir.'/data/';
	}

    function index($api_id){
        if ($api_id) {
            $file = $this->dir.$api_id.'.xml';
            if (file_exists($file)) {
            	$cate_detail = kernel::single('site_utility_xml')->xml2arrayValues(file_get_contents($file), 0);

	            $api_para = unserialize($cate_detail['root']['api_para']);
	            $cate_detail['root']['api_para'] = $api_para;
	            $this->pagedata['cate_detail'] = $cate_detail['root'];
	            $this->pagedata['api_id'] = $api_id;
	            $this->pagedata['api_url'] = preg_replace('/\/index.html(\/?)$/', '/api', $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));
            }
        }
        $this->pagedata['cate_list'] = $this->get_xml_arr();
       	$this->page('site/index.html', 1);
    }

    function edit($api_id){
        $file = $this->dir.$api_id.'.xml';
        if (!$file) return;
        $cate_detail = kernel::single('site_utility_xml')->xml2arrayValues(file_get_contents($file), 0);
        $api_para = unserialize($cate_detail['root']['api_para']);
        $cate_detail['root']['api_para'] = $api_para;
        $this->pagedata['cate_detail'] = $cate_detail['root'];
        $this->pagedata['api_id'] = $api_id;
        $this->pagedata['cate_list'] = $this->get_xml_arr();
        $this->page('site/add.html', 1);
    }

    function add() {
        $this->pagedata['cate_list'] = $this->get_xml_arr();
        $this->page('site/add.html', 1);
    }

    function save(){
        # 过滤数据
        $_POST = $this->check_input($_POST);

        # 更新分类
        $this->update_cate($_POST);
    }

    function delete($api_id, $pw) {
        if(md5($pw) != $this->password){
           echo '删除失败！';exit();
        }
        if (!$api_id) $this->redirect($this->gen_url(array('app'=>'webtool','ctl'=>'site_index','act'=>'index')));
        $xml = kernel::single('site_utility_xml');
        $cate_list = $this->get_xml_arr();
        foreach ($cate_list as $key => $value) {
            foreach ($value['child']['items'] as $k => $v) {
                if ($api_id == $v['api_id']) {
                    unset($cate_list[$key]['child']['items'][$k]);
                    sort($cate_list[$key]['child']['items']);
                    $nodes['items'] = $cate_list;
                    $content = '<?xml version="1.0" encoding="UTF-8"?>'.$xml->array_xml('root', $nodes);
                    if (file_put_contents($this->dir.'api_cate.xml', $content)){
                        break;
                    }
                }
            }
        }

        $file = $this->dir.$api_id.'.xml';
        if(file_exists($file)){
            unlink($file);
        }
        $this->redirect($this->gen_url(array('app'=>'webtool','ctl'=>'site_index','act'=>'index')));
    }

    function send_request($params) {
    	echo $this->sign_data($_POST);exit();
    }

    /*
        更新分类
    */
    private function update_cate($params){
        $xml = kernel::single('site_utility_xml');

        $data = $this->get_xml_arr();
        $is_exist = false;

        foreach ($data as $key => $value) {
            #如果父分类存在，更新到该分类下面
            if ($value['cate_id'] == $params['cate_id']) {
                $is_exist = true;
                $i = rand(10, 1000);
                foreach ($value['child']['items'] as $k => $v) {
                    # 如果存在api，更新
                    if ($v['api_id'] == $params['api_id']) {
                        $i = $k;
                        break;
                    }
                }
                # 保存接口信息
                $data[$key]['child']['items'][$i]['api_id'] = $params['api_id'];
                $data[$key]['child']['items'][$i]['api_title'] = $params['api_title'];
                sort($data[$key]['child']['items']);
            }
        }

        #如果父级不存在，新增分类，插入
        if (!$is_exist) {
            $arr = array(
                'cate_id'   =>  $params['cate_id'],
                'cate_name' =>  $params['cate_name'],
                'child'     =>  array(
                    'items' => array(
                        'api_id'   => $params['api_id'],
                        'api_title' => $params['api_title']
                    )
                )
            );
            $data[] = $arr;
        }
        $nodes['items'] = $data;
        $content = '<?xml version="1.0" encoding="UTF-8"?>'.$xml->array_xml('root', $nodes);
        if (file_put_contents($this->dir.'api_cate.xml', $content)){
            # 生成分类文件
            $content = '<?xml version="1.0" encoding="UTF-8"?>'.$xml->array_xml('root', $params);
            if (file_put_contents($this->dir.$params['api_id'].'.xml', $content)) {
                echo json_encode(array('msg'=> '保存成功'));exit();
            }
        }

        echo json_encode(array('error'=> '保存出错'));exit();
    }

    private function get_xml_arr() {
        $xml = kernel::single('site_utility_xml');
        $cate_arr = $xml->xml2arrayValues(file_get_contents($this->dir.'api_cate.xml'), 0);

        $data = array();
        $database = $cate_arr['root']['items'];

        if ($database['cate_id']) {
            $data[0] = $database;
        } else {
            $data = $database;
        }

        foreach ($data as $key => $value) {
            if (!$value['child']['items'][0]) {
                $child = $value['child']['items'];
                unset($data[$key]['child']['items']);
                $data[$key]['child']['items'][0] = $child;
            } else {
                $data[$key]['child'] = $value['child'];
            }
        }

        return $data;
    }

    private function check_input($_POST) {
        if(md5($_POST['api_password']) != $this->password){
            echo json_encode(array('error'=> '密码错误，无权限添加！'));
            exit();
        }
        $_POST['cate_name'] = trim($_POST['cate_name']);
        $_POST['api_title'] = trim($_POST['api_title']);        
        unset($_POST['api_password']);
        if (!$_POST['cate_name'] &&  (!$_POST['cate_id'] || $_POST['cate_id'] == '请选择')) {
            $result['error'] .= "请输入选择或添加接口分类\n";
        }
        if (!$_POST['api_title']) {
             $result['error'] .= "请输入接口标题\n";
        }
        if (!$_POST['api_explain']) {
            $result['error'] .= "请输入接口描述\n";
        }
        if (!$_POST['api_request_url']) {
            $result['error'] .= "请输入请求地址\n";
        }
        if ($result['error']) {
            echo json_encode($result);
            exit();
        } 

        if (!empty($_POST['api_para'])) {
            foreach ($_POST['api_para'] as $key => $value) {
                if (!$value['name']){
                    unset($_POST['api_para'][$key]);
                    continue;
                }
                $_POST['api_para'][$key]['directions'] = strip_tags($value['directions']);
            }
            $_POST['api_para'] = serialize($_POST['api_para']);
        }
        $_POST['api_return'] = htmlspecialchars($_POST['api_return']);
        $_POST['is_required'] = $_POST['is_required'] == 1 ? $_POST['is_required'] : 0;
        $_POST['cate_id'] = $_POST['cate_id'] ? $_POST['cate_id'] : $this->create_id();
        $_POST['cate_name'] = $_POST['cate_name'];
        $_POST['api_id'] =  $_POST['api_id'] ? $_POST['api_id'] : $this->create_id();

        return $_POST;
    }

    private function create_id() {
        return uniqid();
    }

    private function sign_data($params) {
    	$params['date'] = date('Y-m-d H:m:s',time());
        $params['direct'] = 'true';
        $sign = $this->get_sign($params);
        $sign_string = "";
    	foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $sign_string .= $key.'['.$k.']='.$v.'&';
                }
            }else{
               $sign_string .= $key.'='.$value.'&'; 
            }
    	}
    	return $sign_string.'sign='.$sign;
    }

    private function get_sign($params){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).base_certificate::token()));
    }
    
    
    private function assemble($params)
    {
        if(!is_array($params)) return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }

}
