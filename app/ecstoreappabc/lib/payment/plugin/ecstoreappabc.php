<?php
/**
http://www.shopexdream.com
 */
final class ecstoreappabc_payment_plugin_ecstoreappabc extends ectools_payment_app implements ectools_interface_payment_app {

    public $name = '中国农业银行B2C在线支付ecstoreappabc';
    public $app_name = '中国农业银行B2C在线支付ecstoreappabc';
    public $app_key = 'ecstoreappabc';
	/** 中心化统一的key **/
	public $app_rpc_key = 'ecstoreappabc';
    public $display_name = '中国农业银行B2C在线支付ecstoreappabc';
    public $curname = 'CNY';
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

	// 扩展参数
	public $supportCurrency = array("CNY"=>"156");

    var $sign_ignore_params = array(
        "bank",
    );
	function payment_plugin_ecstoreappabc_skv(){
	    if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}};
	}
    //构造支付接口基本信息
    public function __construct($app)
	{
		$this->payment_plugin_ecstoreappabc_skv();
		parent::__construct($app);
        $this->callback_url  = app::get('site')->router()->gen_url(array('app'=>'ecstoreappabc','ctl'=>'site_request','act'=>'index','full'=>1));
        $this->servercallback_url = app::get('site')->router()->gen_url(array('app'=>'ecstoreappabc','ctl'=>'site_request','act'=>'serverCallback','full'=>1));
        
        $this->submit_url = 'https://www.95599.cn/b2c/trustpay/ReceiveMerchantIERequestServlet';
        if(defined('ecstoreappabc')){
		    $this->submit_url = "https://www.95599.cn/b2c/trustpay/ReceiveMerchantIERequestServlet";//测试
        }
        $this->submit_method = 'POST';
        $this->submit_charset = 'gbk';
    }
	//支付接口表单提交方式
    public function dopay($payment)
	{
        $merId =$this->getConf('member_id', __CLASS__); 
        $keyPass =$this->getConf('keyPass', __CLASS__);

        $keyFile =$this->getConf('keyFile', __CLASS__); 
        $certFile =$this->getConf('certFile', __CLASS__);
        if (is_dir(DATA_DIR . "/cert/payment_plugin_ecstoreappabc/"))
            $realpath = DATA_DIR . "/cert/payment_plugin_ecstoreappabc/";


        $key = $realpath.$keyFile;//私钥文件
        $cert = $realpath.$certFile;//公钥文件


        if(!file_exists($key)){
            die("ABC key file not found!");
        }
         if(!file_exists($cert)){
            die("ABC Cert file not found!");
         }
         $aREQ["merchantNo"] = $merId;
        
        $ret_url =$this->callback_url;
        $server_url =$this->servercallback_url; 
        $amount = number_format($payment['cur_money'],2,".","");
//print_r($payment);exit;

         $toSign = '<Merchant><ECMerchantType>B2C</ECMerchantType><MerchantID>'.$merId.'</MerchantID></Merchant><TrxRequest><TrxType>PayReq</TrxType><Order><OrderNo>'.$payment["payment_id"].'</OrderNo><ExpiredDate>30</ExpiredDate><OrderAmount>'.$amount.'</OrderAmount><OrderDesc>goodsname</OrderDesc><OrderDate>'.date('Y/m/d').'</OrderDate><OrderTime>'.date('H:i:s').'</OrderTime><OrderURL>'. $ret_url.'</OrderURL><BuyIP>'.$_SERVER['REMOTE_ADDR'].'</BuyIP><OrderItems><OrderItem><ProductID>1</ProductID><ProductName>'.$payment['orders'][0]['rel_id'].'</ProductName><UnitPrice>'.$payment['M_Amount'].'</UnitPrice><Qty>1</Qty></OrderItem></OrderItems></Order><ProductType>1</ProductType><PaymentType>1</PaymentType><NotifyType>1</NotifyType><ResultNotifyURL>'.$ret_url.'</ResultNotifyURL><MerchantRemarks>'.$payment['orders'][0]['rel_id'].'</MerchantRemarks><PaymentLinkType>1</PaymentLinkType></TrxRequest>';
     //paymentlinktype改成2，是wap
        // print_r($toSign);exit;
         $sign=$this->tosignature($toSign,$key,$keyPass);

        
        $lastreturn['Signature']='<MSG><Message>'.$toSign.'</Message><Signature-Algorithm>SHA1withRSA</Signature-Algorithm><Signature>'.$sign.'</Signature></MSG>';




        foreach($lastreturn as $key=>$val) {
            $this->add_field($key,$val);
        }
        //print_r($this->fields);exit;

        if($this->is_fields_valiad())
		{
			// Generate html and send payment.
            echo $this->get_html();exit;
        }
		else
		{
            return false;
        }
    }

    //验证提交表单参数有效性
    public function is_fields_valiad()
	{
        return true;
    }

	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */

    function callback(&$recv){
     
        $msg = base64_decode($recv['MSG']);
    
        $paymentIdarr=$this->getDataForXML($msg,'/MSG/Message/TrxResponse/OrderNo');
        $paymentId=$paymentIdarr[0];
         
        $tradenoarr=$this->getDataForXML($msg,'/MSG/Message/TrxResponse/BatchNo');
        $tradeno=$tradenoarr[0];
            
            
        $moneyarr=$this->getDataForXML($msg,'/MSG/Message/TrxResponse/Amount');
        
        $money=$moneyarr[0];

      //file_put_contents('eachbianliang.txt',var_export($paymentId."|".$tradeno."|".$money,true));
      
        $merId =$this->getConf('member_id', __CLASS__); 
        $keyPass =$this->getConf('keyPass', __CLASS__);

        $keyFile =$this->getConf('keyFile', __CLASS__); 
        $certFile =$this->getConf('certFile', __CLASS__);
        
        if (is_dir(DATA_DIR . "/cert/payment_plugin_ecstoreappabc/"))
            $realpath = DATA_DIR . "/cert/payment_plugin_ecstoreappabc/";
   
        $key = $realpath.$keyFile;//私钥文件
        $cert = $realpath.$certFile;//公钥文件


        if(!file_exists($key)){
            die("ABC key file not found!");
        }
         if(!file_exists($cert)){
            die("ABC Cert file not found!");
         }


        preg_match("/\<Message\>(.*)\<\/Message\>.*\<Signature\>(.*)\<\/Signature\>/i",$msg,$match);
        $contents = $match[1];
        $signature = $match[2];
        
        
        $verifyresult=$this->verifyReturn($contents,$signature,$cert );  
                

        if ($verifyresult){
          $message="支付成功！";
             echo "success";
           $ret['status']='succ';
        }
        else{
            $message = "验证签名错误！";

                echo "fail";
                $ret['status']='failed';
        }

        $ret['payment_id']=$paymentId;//传递单据号
        //$recv['money']=$recv['orderAmount']/100;//传递金额前台result提示用。分计算得/100

        $ret['account']=$merId;
        $ret['bank']=app::get('ecstoreappabc')->_('中国农业银行B2C在线支付ecstoreappabc');
        $ret['pay_account']=app::get('ectools')->_('付款帐号');
        $ret['currency']='CNY';
        $ret['money']=$money;
        $ret['paycost']='0.000';
        $ret['cur_money']=$money;
        $ret['trade_no']=$tradeno;
        $ret['t_payed']=time();
        $ret['pay_app_id']="ecstoreappabc";
        $ret['pay_type']='online';
        $ret['memo']='';
        return $ret;
    }   
  
	/**
	 * 生成支付表单 - 自动提交
	 * @params null
	 * @return null
	 */
    public function gen_form()
	{
      $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">'.app::get('ectools')->_('立即申请中国银联').'</a>';
      $tmp_form.="<form name='applyForm' method='".$this->submit_method."' action='" . $this->submit_url . "' target='_blank'>";
	  // 生成提交的hidden属性
      foreach($this->fields as $key => $val)
	  {
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }

      $tmp_form.="</form>";

      return $tmp_form;
    }

    function tosignature($toSign,$cert_file,$password){
 
          $signature = null;

           
          // $toSign签名后的数据应该是nfJAveUtLG1YHqsjUdopB8Jl9QX4ZtlQrUn+HoiCy0yS9An19z5IxTIVYOuQXjNnbMGgmZlCwK3dSSnRTLHxZMC3zJUiE58qEwxatOgHNFUhAHTBxkUMO5ikC7C5qm/9L67/Xp7kYvHK9Fo/8CyXckROb+w+eLYcPaYo6+Of2Dg=
           
          $fp = fopen($cert_file, "rb");
          $priv_key = fread($fp, 8192);
          fclose($fp);
          //获取pem文件中的私钥
          $pkeyid = openssl_get_privatekey($priv_key,$password);
          //使用用户私钥对消息进行签名
          openssl_sign($toSign, $signature, $pkeyid);
          // Free the key.
          openssl_free_key($pkeyid);
          $b64 = base64_encode( $signature );
          return $b64;

    }
   function verifyReturn($data,$signature,$cert_file){
    $fp = fopen($cert_file, "r");
    $cert = fread($fp, 8192);
    fclose($fp);
    $pubkeyid = openssl_get_publickey($cert);
    $signature = base64_decode($signature);
    // state whether signature is okay or not
    $ok = openssl_verify($data, $signature, $pubkeyid);
    openssl_free_key($pubkeyid);
    return $ok;
} 
//xml某Node转换为数组
function getDataForXML($res_data,$node)
{
	//file_put_contents('retrun.txt',var_export($res_data,true));
    $xml = simplexml_load_string($res_data);

    $result = $xml->xpath($node);

    while(list( , $node) = each($result)) 
    {
        return (array)$node;
    }
}

    //显示支付接口表单基本信息
    public function admin_intro()
    {
        return app::get('ecstoreappabc')->_('中国农业银行B2C在线支付（ecstoreappabc）是中国农业银行旗下的在线支付产品。');
    }

    //显示支付接口表单选项设置
    public function setting()
    {
        return array(
                'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string',
                        'validate_type' => 'required',
                ),
                'member_id'=>array(
                        'title'=>app::get('ectools')->_('商户号'),
                        'type'=>'string',
                        'validate_type' => 'required',
                ),

                'certFile'=>array(
                        'title'=>app::get('ectools')->_('支付平台证书(TrustPay.pem-trustpay.cer转))'),
                        'type'=>'file',
                        'validate_type' => 'required',
                ),
                'keyFile'=>array(
                        'title'=>app::get('ectools')->_('个人私钥文件(user.pem-pfx转)'),
                        'type'=>'file',
                        'validate_type' => 'required',
                ),

                'keyPass'=>array(
                        'title'=>app::get('ectools')->_('私钥密码'),
                        'type'=>'string',
                        'validate_type' => 'required',
                ),

                
                'order_by' =>array(
                    'title'=>app::get('ectools')->_('排序'),
                    'type'=>'string',
                    'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
                ),
               
                'support_cur'=>array(
                    'title'=>app::get('ectools')->_('支持币种'),
                    'type'=>'text hidden cur',
                    'options'=>$this->arrayCurrencyOptions,
                ),
                'pay_fee'=>array(
                    'title'=>app::get('ectools')->_('交易费率'),
                    'type'=>'pecentage',
                    'validate_type' => 'number',
                ),
                'pay_brief'=>array(
                    'title'=>app::get('ectools')->_('支付方式简介'),
                     'type'=>'textarea',
                ),
                'pay_desc'=>array(
                    'title'=>app::get('ectools')->_('描述'),
                    'type'=>'html',
                    'includeBase' => true,
                ),

                'pay_type'=>array(
                     'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                    'type'=>'radio',
                    'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                     'name' => 'pay_type',
                ),
                'status'=>array(
                    'title'=>app::get('ectools')->_('是否开启此支付方式'),
                    'type'=>'radio',
                    'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                    'name' => 'status',
                ),
        );
    }

    public function intro()
    {
        return app::get('ecstoreappabc')->_('中国农业银行B2C在线支付（ecstoreappabc）是中国农业银行旗下的在线支付产品。');
    }

}
