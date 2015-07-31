<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
/**
 * b2c aftersales interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_order_aftersale
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = app::get('aftersales');
        $this->app_b2c = $app;
    }

    /**
     * 售后服务单创建
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj)
    {
        if (!$sdf['order_bn'] || !$sdf['return_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('售后服务单数据异常！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
        }
        else
        {
            $is_save = true;
            $obj_return_product = $this->app->model('return_product');

            $return_id = $obj_return_product->gen_id();
            $arr_product_data = json_decode($sdf['return_product_items'], true);
            $str_product_data = serialize($arr_product_data);
            $tmp = $obj_return_product->getList('*',array('return_bn'=>$sdf['return_bn'],'order_id'=>$sdf['order_bn']));
            if ($tmp)
            {
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单已经存在！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }
            if($sdf['order_bn']){
                $order_list = app::get('b2c')->model('orders')->getList('member_id',array('order_id'=>$sdf['order_bn']));
                $member_id = $order_list[0]['member_id'];
             }

            // 开始事务
            $db = kernel::database();
            $transaction_status = $db->beginTransaction();

            $arr_data = array(
                'order_id' => $sdf['order_bn'],
                'return_bn' => $sdf['return_bn'],
                'return_id' => $return_id,
                'title' => $sdf['title'],
                'content' => $sdf['content'],
                'comment' => $sdf['comment'],
                'status' => $sdf['status'],
                'product_data' => $str_product_data,
                'member_id' => $member_id,
                'add_time' => $sdf['add_time'],
            );
            if ($sdf['url'] && strpos($sdf['url'], '/') !== false)
            {
                $mdl_img = app::get('image')->model('image');
                $image_name = substr($sdf['url'], strrpos($sdf['url'],'/')+1);
                $image_id = $mdl_img->store($sdf['url'],null,null,$image_name);
                $arr_data['image_file'] = $image_id;
            }

            $is_save = $obj_return_product->save($arr_data);

            if ($is_save)
            {
                $db->commit($transaction_status);
                return array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']);
            }
            else
            {
                $db->rollback();
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单添加失败！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }
        }
    }

    /**
     * 售后服务单修改
     * @param array sdf
     * @param string message
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj)
    {
        if (!$sdf['order_bn'] || !$sdf['return_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('售后服务单数据异常！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
        }
        else
        {
            $obj_return_product = $this->app->model('return_product');

            $arr_data = $obj_return_product->dump(array('order_bn'=>$sdf['order_bn'],'return_bn'=>$sdf['return_bn']));
            if ($arr_data)
            {
                if ($sdf['return_product_items'])
                {
                    $arr_product_data = json_decode($sdf['return_product_items']);
                    $str_product_data = serialize($arr_product_data);
                }
                else
                {
                    $str_product_data = "";
                }

                $arr_data['order_id'] = $sdf['order_bn'];
                $arr_data['return_bn'] = $sdf['return_bn'];
                if ($sdf['title'])
                    $arr_data['title'] = $sdf['title'];
                if ($sdf['content'])
                    $arr_data['content'] = $sdf['content'];
                if ($sdf['comment'])
                    $arr_data['comment'] = $sdf['comment'];
                if ($sdf['status'])
                    $arr_data['status'] = $sdf['status'];
                if ($str_product_data)
                    $arr_data['product_data'] = $str_product_data;
                if ($sdf['member_id'])
                    $arr_data['member_id'] = $sdf['member_id'];
                if ($sdf['add_time'])
                    $arr_data['add_time'] = $sdf['add_time'];

                $obj_return_product->save($arr_data);

                return array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单不存在！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }
        }
    }
    
    /**
     * 获取售后申请单列表
     * @param unknown_type $sdf
     * @param unknown_type $thisObj
     */
	public function get_list( $params, &$service )
    {
        //校验参数
        if( !( $start_time = $params['start_time'] ) )
            $service->send_user_error('7001', '开始时间不能为空！');
        if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
            $service->send_user_error('7002', '开始时间不合法！');

        if( !( $end_time = $params['end_time'] ) )
            $service->send_user_error('7003', '结束时间不能为空！');
        if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
            $service->send_user_error('7004', '结束时间不合法！');

        $page_no = 1;
        if( $params['page_no'] != '' ){
            if( !is_numeric($params['page_no']) || $params['page_no'] < 1 )
                $service->send_user_error('7005', 'page_no不合法！');
            else
                $page_no = intval($params['page_no']);
        }

        $page_size = 40;
        if( $params['page_size'] != '' ){
            if( !is_numeric($params['page_size']) || $params['page_size'] < 1 || $params['page_size'] > 100 )
                $service->send_user_error('7006', 'page_size不合法！');
            else
                $page_size = intval($params['page_size']);
        }

        $obj_return_product = $this->app->model('return_product');

        $where = '';
        if( $start_time != '' )
            $where .= "AND last_modify > '" . $start_time . "' ";
        
        if( $end_time != '' )
            $where .= "AND last_modify < '" . $end_time . "' ";
        
        if( $params['status']){
        	$where .= "AND status IN (".$params['status'].")";
        }
        
        if( $where != '' )
           $where = 'WHERE ' . substr($where, 4);

        $sql	=	"SELECT ### FROM " .
            $obj_return_product->table_name(1) . ' ' .
            $where .
            "ORDER BY last_modify ASC";
        
        //获取总数
        $total_results = $obj_return_product->db->select( str_replace('###', 'count(*) cc', $sql) );
        if( $total_results )
            $total_results = $total_results[0]['cc'];
        else
            $total_results = 0;
        if($total_results == 0) {
            return $this->search_response(array());
        }

        //计算分页
        $offset = ($page_no-1) * $page_size;
        $limit = $page_size;

        $page_data =  $this->get_pager($page_no, $page_size, $obj_return_product->table_name(1),$where);
        
        $has_next = $total_results > ($offset+$limit) ? 'true' : 'false';
        
        $sdf = $obj_return_product->db->selectLimit( str_replace('###', ' * ', $sql), $limit, $offset );

        if(!$sdf){		
            return $this->search_response(array());
        }

        foreach( $sdf as $k=>&$row )
        {
        	$sdf[$k]['product_data']=unserialize($row['product_data']);
        }

        return $this->search_response($sdf, $total_results, $has_next,$page_data);
    }

    private function search_response($trades, $total_results=0, $has_next='false',$page_data){

        return array(
            'result' => $trades,
            'total_results' => $total_results,
            'has_next' => $has_next,
        	'pageno'	=> $page_data['page_no'],
        	'pageszie'	=> $page_data['page_size'],
        	'pagecount'	=> $page_data['page_count'],
        );

    }
    
    
    /**
     * 根据表名，查询条件，页码，返回总记录数，总分页数
     *
     * @param page_no 		当前页码
     * @param page_size		每页的记录数
     * @param sTableName 	表名
     * @param sWhere 		查询条件
     * @return array
     */
    private function get_pager($page_no, $page_size, $sTableName, $sWhere='')
    {
    	$page_no 	= intval($page_no);
    	$page_size 	= intval($page_size);
    	$limit		= '';
    
    	$rs_count = 0;
    
    	$sql = 'select count(*) as c from `'.$sTableName.'` '.$sWhere;
    
    	//-------------------------------------------------
    	$db = kernel::database();
    	$rows = $db->select($sql);
    	if ($rows && is_array($rows)){
    		$rs_count = intval($rows[0]['c']);
    	}
    	$str_limit 	= '';
    	$offset 	= 0;
    	$limit 		= -1;
    	$page_count = 0;
    
    	if($rs_count>0){
    		$page_count	= ceil($rs_count / $page_size);
    		if( $page_no < 1 ){
    			$page_no = 1;
    		}else if( $page_no > $page_count ){
    			$page_no = $page_count;
    		}
    		$offset		= (($page_no-1)* $page_size);
    		$limit		= $page_size;
    		$str_limit = ' LIMIT '.$offset.','.$limit;
    	}else{
    		$page_no = 1;
    	}
    
    	return array(
    			'rs_count' 		=> $rs_count,
    			'page_count' 	=> $page_count,
    			'page_no' 		=> $page_no,
    			'page_size' 	=> $page_size,
    			'offset' 		=> $offset,
    			'limit' 		=> $limit,
    			'str_limit' 	=> $str_limit
    	);
    }
}
