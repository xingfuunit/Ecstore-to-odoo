<?php

class weixin_api{

    public function __construct($app){
        $this->wechat = kernel::single('weixin_wechat');
        $this->weixinObject = kernel::single('weixin_object');
    }

    public function api(){
        //签名验证，消息有效性验证
        if( !empty($_GET) && $this->doget() ){
            echo $_GET["echostr"];
        }

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if( !empty($postStr) ){
            $this->dopost($postStr);
        }else{
            echo "";
        }
    }

    /**
     * 处理微信消息有效验证
     */
    public function doget(){
        //获取到token
        $token = $this->weixinObject->get_token($_GET['eid']);
        //验证
        if( $this->wechat->checkSignature($_GET["signature"], $_GET["timestamp"], $_GET["nonce"], $token) ){
            return true;
        }else{
            return false;
        }
    }

    public function dopost($postXml){
        $postArray = kernel::single('site_utility_xml')->xml2array($postXml);
        $postData  = $postArray['xml'];

        //公众账号ID获取
        $weixin_id = $postData['ToUserName'];
        $bind = app::get('weixin')->model('bind')->getList('id,eid',array('weixin_id'=>$weixin_id,'status'=>'active'));
        if( !empty($bind) ){
            $postData['bind_id'] = $bind[0]['id'];
            $postData['eid'] = $bind[0]['eid'];
        }else{
            $this->weixinObject->send('');
        }

        switch($postData['MsgType']){
        case 'event':
            /**
             * subscribe(订阅)、unsubscribe(取消订阅)
             * scan 带参数二维码事件
             * location 上报地理位置事件
             * click 自定义菜单事件
             * view  点击菜单跳转链接时的事件推送
             * */
            $method = strtolower($postData['Event']);
            if( method_exists($this->wechat,$method) ){
                $this->wechat->$method($postData);
            }else{
                $this->weixinObject->send('');
            }
            break;
        default:
            $this->wechat->commonMsg($postData);
        }
    }

    // 微信支付回调地址
    function wxpay(){
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];

        $nodify_data = array_merge($_GET,$postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        echo 'success';exit;
    }

    // 新微信支付回调地址
    function wxpayjsapi(){
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpayjsapi', 'callback');

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];

        $nodify_data = array_merge($_GET,$postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        // if($notify->checkSign() == FALSE){
        //     $arr = array('return_code'=>'FAIL','return_msg'=>'签名失败')；
        // }else{
        //     $arr = array('return_code'=>'SUCCESS');
        // }
        // $returnXml = $notify->returnXml();
        // echo $returnXml;exit;
    }


    // 维权通知接口
    public function safeguard(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData  = $postArray['xml'];
        #$postData = array (
        #    'OpenId' => 'ow1l7t6coRbI3aBBNztBc6qT8F4w',
        #    'AppId' => 'wxfdd2db839d9e8984',
        #    'TimeStamp' => '1403080919',
        #    'MsgType' => 'request',
        #    'FeedBackId' => '13221259825330037179',
        #    'TransId' => '1219419901201406183166617972',
        #    'Reason' => '商品质量有问题',
        #    'Solution' => '退款，并不退货',
        #    'ExtInfo' => '我是备注 1391000000',
        #    'AppSignature' => '5f0dba6a6ba427cf523f22c815f6600cfbe1c365',
        #    'SignMethod' => 'sha1',
        #);
        $signData = array(
            'OpenId' => $postData['OpenId'],
            'TimeStamp' => $postData['TimeStamp'],
        );
        if(!weixin_util::verifySignatureShal($signData, $postData['AppSignature'])){
            return false;
        }
        
        $saveData['openid'] = $postData['OpenId'];
        $saveData['appid'] = $postData['AppId'];
        $saveData['msgtype'] = $postData['MsgType'];
        $saveData['feedbackid'] = $postData['FeedBackId'];
        $saveData['transid'] = $postData['TransId'];
        $saveData['reason'] = $postData['Reason'];
        $saveData['solution'] = $postData['Solution'];
        $saveData['extinfo'] = $postData['ExtInfo'];
        $saveData['picurl'] = $postData['PicUrl'];
        $saveData['timestamp'] = $postData['TimeStamp'];
        $safeguardModel = app::get('weixin')->model('safeguard');
        $row = $safeguardModel->getRow('id',array('feedbackid'=>$saveData['feedbackid']));
        if( $row ){
            if( $saveData['msgtype'] == 'confirm'){
                $status = '3';
                $safeguardModel->update(array('msgtype'=>$saveData['msgtype'],'status'=>$status),array('id'=>$row['id']));
            }else{
                $saveData['status'] = '1';
                $safeguardModel->update($saveData,array('id'=>$row['id']));
            }
        }else{
            $bindData = app::get('weixin')->model('bind')->getRow('id',array('appid'=>$saveData['appid'])); 
            $res = kernel::single('weixin_wechat')->get_basic_userinfo($bindData['id'],$saveData['openid']);
            $saveData['weixin_nickname'] = $res['nickname'];
            if( !$safeguardModel->save($saveData) ){
                logger::info(var_export($saveData,1),'维权信息记录失败');
            }
        }
    }

    // 微信告警通知接口
    public function alert(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData  = $postArray['xml'];

        $insertData = array(
            'appid' =>$postData['AppId'],
            'errortype' => $postData['ErrorType'],
            'description' => $postData['Description'],
            'alarmcontent' => $postData['AlarmContent'],
            'timestamp' => $postData['TimeStamp'],
        );
        app::get('weixin')->model('alert')->save($insertData);

        // 微信需要返回页面编码也未gbk的success
        echo "<meta charset='GBK'>";
        $rs = 'success';
        echo iconv('UTF-8', 'GBK//TRANSLIT', $rs);exit;
        /*告警通知数据格式
         $postData=array(
            'AppId'=>'wxf8b4f85f3a794e77',
            'ErrorType'=>'1001',
            'Description'=>'错误描述',
            'AlarmContent'=>'错误详情',
            'TimeStamp'=>'1393860740',
            'AppSignature'=>'f8164781a303f4d5a944a2dfc68411a8c7e4fbea',
            'SignMethod'=>'sha1'
        );*/
    }
    
    public  function feedback(){
        //kernel::log('sdadasd1321331');exit;
        
       // header('Content-Type:text/xml');
        
        $postdata = file_get_contents("php://input");
//        $postObj = simplexml_load_string ( $postdata, 'SimpleXMLElement', LIBXML_NOCDATA );
//        $openId = $postObj->OpenId;
//        $AppId = $postObj->AppId;
//        $IsSubscribe = $postObj->IsSubscribe;
//        $ProductId = $postObj->ProductId;//订单ID
//        $TimeStamp = $postObj->TimeStamp;
//        $NonceStr = $postObj->NonceStr;
//        $AppSignature = $postObj->AppSignature;
//        $SignMethod = $postObj->SignMethod;
        
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//     
//        
//        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
//        $postData['weixin_postdata']  = $postArray['xml'];
//        $AppId=$postData['appid'];
//        $openId=$postData['openid'];
//        $IsSubscribe=$postData['is_subscribe'];
//        $TimeStamp=$postData['timestamp'];
//        $AppSignature=$postData['appsignature'];
//        $NonceStr=$postData['nonce_str'];
//        $ProductId=$postData['product_id'];
         $fh=  fopen('api.txt', 'a');
//        foreach ($postData as $k=>$v){
//            $str.=$k.':'.$v.'||';
//        }
         fwrite($fh, $postdata.'weixinzhifu');
        fclose($fh);exit;
        $orderMdl=app::get('b2c')->model('orders');
        $orderItemMdl=app::get('b2c')->model('order_items');
        $orders=$orderMdl->getList('total_amount',array('order_id'=>$ProductId));
        
        $total_fee=$orders[0]['total_amount'];
        
        $order_items=$orderItemMdl->getList('name',array('order_id'=>$ProductId));
        $body='';
        
        foreach ($order_items as $item){
            $body.=$item['name'].',';
        }
        
        $data=array(
		"AppId"=>$AppId,
		"Package"=>$this->getPackage('商品测试',100,'201311291504302501231'),
		"TimeStamp"=>strtotime(),
		"NonceStr"=>$NonceStr,
		"RetCode"=>0,   //RetCode 为0 表明正确，可以定义其他错误；当定义其他错误时，可以在RetErrMsg 中填上UTF8 编码的错误提示信息，比如“该商品已经下架”，客户端会直接提示出来。
		"RetErrMsg"=>"正确返回",
		"AppSignature"=>$AppSignature,
		"SignMethod"=>"sha1"
        );
        
        echo weixin_util::arrayToXml($data);
    }
    
    private function getPackage($body,$total_fee,$out_trade_no){  
        $notify_url = kernel::base_url(1).'/index.php/openapi/weixin/notify';
        #test
        // $this->notify_url = kernel::base_url(1).'/index.php/wap/paycenter-wxpay.html';
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $notify_url, $matches))
        {
            $notify_url = str_replace('http://','',$notify_url);
            $notify_url = preg_replace("|/+|","/", $notify_url);
            $notify_url = "http://" .notify_url;
        }
        else
        {
            $notify_url = str_replace('https://','',$notify_url);
            $notify_url = preg_replace("|/+|","/", $notify_url);
            $notify_url = "https://" . $notify_url;
        }
             
	$ip=$_SERVER["REMOTE_ADDR"];
	if($ip=="::1"||empty($ip)){
		$ip="127.0.0.1";
	}
	$banktype = "WX";
	$fee_type = "1";//费用类型，这里1为默认的人民币
	$input_charset = "GBK";//字符集，这里将统一使用GBK
	$notify_url = "http://pinzhen.qsit.com.cn/index.php/openapi/weixin/notify";//支付成功后将通知该地址
	$out_trade_no =kernel::single('ectools_pay')->get_payment_id($out_trade_no);//订单号，商户需要保证该字段对于本商户的唯一性 支付单号
        
	$partner = "1224152201"; //商户号
	$spbill_create_ip =$ip;//订单生成的机器IP
	$partnerKey = "6dc7e4736b6422518daf65efc30ede8c";//这个值和以上其他值不一样是：签名需要它，而最后组成的传输字符串不能含有它。这个key是需要商户好好保存的。
	//首先第一步：对原串进行签名，注意这里不要对任何字段进行编码。这里是将参数按照key=value进行字典排序后组成下面的字符串,在这个字符串最后拼接上key=XXXX。由于这里的字段固定，因此只需要按照这个顺序进行排序即可。
	$signString = "bank_type=".$banktype."&body=".$body."&fee_type=".$fee_type."&input_charset=".$input_charset."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&partner=".$partner."&spbill_create_ip=".$spbill_create_ip."&total_fee=".$total_fee."&key=".$partnerKey;
	$md5SignValue =  ("" .strtoupper(md5(($signString))));
	//echo $md5SignValue;
	//然后第二步，对每个参数进行url转码。
	$banktype = $this->encodeURIComponent($banktype);
	$body=$this->encodeURIComponent($body);
	$fee_type=$this->encodeURIComponent($fee_type);
	$input_charset = $this->encodeURIComponent($input_charset);
	$notify_url = $this->encodeURIComponent($notify_url);
	$out_trade_no = $this->encodeURIComponent($out_trade_no);
	$partner = $this->encodeURIComponent($partner);
	$spbill_create_ip = $this->encodeURIComponent($spbill_create_ip);
	$total_fee = $this->encodeURIComponent($total_fee);
	 
	//然后进行最后一步，这里按照key＝value除了sign外进行字典序排序后组成下列的字符串,最后再串接sign=value
	$completeString = "bank_type=".$banktype."&body=".$body."&fee_type=".$fee_type."&input_charset=".$input_charset."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&partner=".$partner."&spbill_create_ip=".$spbill_create_ip."&total_fee=".$total_fee;
	$completeString = $completeString."&sign=".$md5SignValue;
	$oldPackageString = $completeString; //记住package，方便最后进行整体签名时取用
	return $completeString;
    }
    
    private function encodeURIComponent($str) {
	$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	return strtr(rawurlencode($str), $revert);
    }
    
    public function notify(){
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];

        $nodify_data = array_merge($_GET,$postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        echo 'success';exit;
    }

}
