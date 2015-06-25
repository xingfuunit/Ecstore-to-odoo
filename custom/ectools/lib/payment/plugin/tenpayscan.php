<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 财付通微信扫码支付(WEB扫码)
 * @auther nick
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_tenpayscan extends ectools_payment_app implements ectools_interface_payment_app{

	/**
	 * @var string 支付方式名称
	 */
    public $name = '财付通微信扫码支付[即时到账]';//快钱网上支付
    /**
     * @var string 支付方式接口名称
     */
	public $app_name = '财付通微信扫码支付接口';
	/**
     * @var string 支付方式key
     */
	public $app_key = 'tenpayscan';
	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'tenpayscan';
	/**
	 * @var string 当前支付方式的版本号
	 */
    public $ver = '1.0';
    /**
	 * @var string 统一显示的名称
	 */
    public $display_name = '财付通微信扫码支付接口';
    /**
	 * @var string 货币名称
	 */
    public $curname = 'CNY';

	/**
	 * @var array 扩展参数
	 */
	public $supportCurrency = array("CNY"=>"1");
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

	/**
     * 构造方法
     * @param object 传递应用的app
     * @return null
     */
    public function __construct($app){
		parent::__construct($app);

        //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_tenpayscan', 'callback');
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
		{
			$this->callback_url = str_replace('http://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "http://" . $this->callback_url;
		}
		else
		{
			$this->callback_url = str_replace('https://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "https://" . $this->callback_url;
		}
        $this->submit_url = 'https://gw.tenpay.com/gateway/pay.htm';
        $this->submit_method = 'POST';
        $this->submit_charset = 'gb2312';
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function intro(){
        return app::get('ectools')->_('财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。').'<a target="_blank" href="http://help.tenpay.com/helpcenter/guidelines.shtml">'.app::get('ectools')->_("
如何使用财务通付款？").'</a>';
    }

    /**
	 * 显示支付接口表单基本信息
	 * @params null
	 * @return string - description include account.
	 */
    function admin_intro(){
        return '<div class="division" id="payInfoPad"><img border="0" src="' . $this->app->res_url . '/payments/images/TENPAYTRAD.gif"><br>'.app::get('ectools')->_('财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。').'<br>'.app::get('ectools')->_('财付通构建全新的综合支付平台，业务覆盖B2B、B2C和C2C各领域，提供卓越的网上支付及清算服务。<br>财付通先后荣膺2006年电子支付平台十佳奖、2006年最佳便捷支付奖、2006年中国电子支付最具增长潜力平台奖和2007年最具竞争力电子支付企业奖等奖项，并于2007年首创获得“国家电子商务专项基金”资金支持。').'<br><br><font color="red">'.app::get('ectools')->_('本接口需点击【立即申请财付通担保账户】链接进行在线签约和付费后方可使用。').'</font><br><br><a onclick="document.applyFormAgain.submit()" href="javascript:void(0)">'.app::get('ectools')->_('立即申请财付通担保账户').'</a><br><form target="_blank" action="http://top.shopex.cn/recordpayagent.php" method="get" name="applyFormAgain"><input type="hidden" value="get" name="postmethod"><input type="hidden" value="2289480" name="sp_suggestuser"><input type="hidden" value="https://www.tenpay.com/mchhelper/mch_register_c2c.shtml" name="agenturl"><input type="hidden" value="'.app::get('ectools')->_('腾讯财付通[担保交易]').'" name="payagentname"><input type="hidden" value="TENPAYTRADDB" name="payagentkey"><input type="hidden" value="127.0.0.1" name="regIp"><input type="hidden" value="http://localhost/shopex/" name="domain"></form></div>';
    }

    /**
     * 提交支付信息的接口
     * 支付接口表单提交方式
     * @param array 提交信息的数组
     * @return mixed false or null
     */
	public function dopay($payment){
		bcscale(2);
        $merId = $this->getConf('mer_id', __CLASS__);
        $ikey = $this->getConf('PrivateKey', __CLASS__);
        $payment['currency'] = "1";    //$order->M_Currency = "1";

        $orderdate = date("YmdHis",$payment['t_begin']);    //$order->M_Time
        $payment['M_Amount'] = bcadd($payment['cur_money'],0) * 100;    //$order->M_Amount
        //$v_orderid = $merId.$orderdate."0000" . $payment['M_OrderId'];  //$order->M_OrderId
        $v_orderid = $merId.$orderdate.substr($payment['payment_id'],-10);

        $subject = $payment['orders'][0]['rel_id'];

        
        //必要支付参数
        $return['partner'] = $merId;
        $return['out_trade_no'] = $payment['payment_id'];
     	$return['total_fee'] = $payment['M_Amount'];
        $return['return_url'] = kernel::base_url(true).$payment['return_url'];
        $return['notify_url'] = $this->callback_url;
//         $return['body'] = '商户订单号：'.$payment['order_id'];
        $return['body'] = $payment['body'].'...';				//支付商品， 只显示一个
        $return['bank_type'] = 'WX';							//银行类型
        
        $return['time_start'] = $orderdate;
        $return['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];	//客户端IP
        $return['fee_type'] = '1';								//币种
        
        //系统可选参数
        $return['sign_type'] = 'MD5';							//签名方式，默认为MD5，可选RSA
        $return['service_version'] = '1.0';						//接口版本号
        $return['input_charset'] = 'UTF-8';						//字符集UTF-8,GBK
        $return['sign_key_index'] = '1';						//密钥序号
        
		foreach($return as $key=>$val) {
            $this->add_field($key,$val);
        }

        //生成校验码
        $this->add_field("sign", $this->_get_mac($ikey));
        
        if($this->is_fields_valiad()){
            header('Content-type: text/html;charset=gb2312',false);
            echo $this->get_html();exit;
        }else{
            return false;
        }
    }

    /**
     * 支付回调的方法
     * @param array 回调参数数组
     * @return array 处理后的结果
     */
    public function callback(&$in){
    	//file_put_contents('wx_log.log',serialize($in)."\r\n",FILE_APPEND);
		$objMath = kernel::single('ectools_math');
        $pay_result=$in["trade_state"];
        $transaction_id=$in["transaction_id"];
        $total_fee=$in["total_fee"];
        $fee_type=$in["fee_type"];
        $sign=$in["sign"];
        $date = $in['time_end'];
        $v_orderid = substr($v_order_no,-6);
        $ikey = $this->getConf('PrivateKey', __CLASS__);
        $merId = $this->getConf('mer_id', __CLASS__);

        foreach($in as $key => $val){
            if ($key<>'pay_time'&&$key<>'bankname'&&$key<>'sign'&&$val<>''){
                $str.=$key."=".urldecode(trim($val))."&";
            }
        }
        $str.="key=".$ikey;
        $md5mac=strtoupper(md5($str));
        $money=$objMath->number_multiple(array($total_fee, 0.01));
        $tradeno = $in['transaction_id'];

		$ret['payment_id'] = $in['out_trade_no'];
		$ret['account'] = $merId;
		$ret['bank'] = app::get('ectools')->_('财付通扫码支付');
		$ret['pay_account'] = app::get('ectools')->_('付款帐号');
		$ret['currency'] = 'CNY';
		$ret['money'] = $money;
		$ret['paycost'] = '0.000';
		$ret['cur_money'] = $money;
		$ret['tradeno'] = $in['out_trade_no'];
		$ret['t_payed'] = strtotime($date);
		$ret['pay_app_id'] = "tenpayscan";
		$ret['pay_type'] = 'online';
		$ret['memo'] = $in['out_trade_no'];
        if($md5mac!=$sign)
		{
           $message = app::get('ectools')->_('腾讯财付通签名认证失败,请立即与商店管理员联系');
           logger::error($message);
		   $ret['status'] =  'invalid';

		   return $ret;
           //return PAY_ERROR;
        }else{
            if($pay_result==0){
				$ret['status'] = 'succ';

                return $ret;
            }else{
                $message = app::get('ectools')->_('腾讯财付通支付失败,请立即与商店管理员联系').$pay_info;
                logger::error($message);
				$ret['status'] =  'failed';

                return $ret;
            }
        }
    }

    /**
	 * 显示支付接口表单选项设置
	 * @params null
	 * @return array - 字段参数
	 */
    function setting(){
        return array(
                'pay_name'=>array(
                    'title'=>app::get('ectools')->_('支付方式名称'),
                    'type'=>'string',
					'validate_type' => 'required',
                ),
				'mer_id'=>array(
                        'title'=>app::get('ectools')->_('客户号'),
                        'type'=>'string',
						'validate_type' => 'required',
                ),
                'PrivateKey'=>array(
                        'title'=>app::get('ectools')->_('私钥'),
                        'type'=>'string',
						'validate_type' => 'required',
                ),
                'order_by' =>array(
                    'title'=>app::get('ectools')->_('排序'),
                    'type'=>'string',
                    'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
                ),
//                 'authtype'=>array(
//                     'title'=>app::get('ectools')->_('商家支付模式'),
//                     'type'=>'select',
//                     'options'=>array('0'=>app::get('ectools')->_('套餐包量商家'),'1'=>app::get('ectools')->_('单笔支付商家'))
//                 ),
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
// 				'pay_type'=>array(
// 					 'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
// 					 'type'=>'hidden',
// 					 'name' => 'pay_type',
// 				),
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

    /**
     * 验证签名的算法
     * @param 加密的依据的key
     * @return 加密后的字符串
     */
	private function _get_mac($key)
	{
		ksort($this->fields);
        reset($this->fields);
		$mac= "";
        foreach($this->fields as $k=>$v){
			if ($v == "")
				continue;
            $mac .= "&{$k}={$v}";
        }

		$mac = substr($mac,1);
		$mac .= "&key=" . $key;
		$mac = strtoupper(md5($mac));

		return $mac;
	}

	/**
     * 生成form的方法
     * @param null
     * @return string html
     */
    public function gen_form(){
      //$tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即注册即时到帐帐户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">立即注册担保帐户</a>';
      //$tmp_form= '<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即申请财付通<font color="red"><b>套餐</b></font>即时账户(适合大商家)</a><br>';
      $tmp_form.='<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">'.app::get('ectools')->_('立即申请财付通').'<font color="red"><b>'.app::get('ectools')->_('单笔').'</b></font>'.app::get('ectools')->_('即时账户(适合小商家)').'</a>';
      $tmp_tc_form="<form name='applyForm' method='".$this->fields['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      $tmp_db_form="<form name='applyFormAgain' method='".$this->fields['postmethod']."'  action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($this->fields as $key => $val){
          if ($key == "payagentkey"){
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
          }
          else {
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."'>";
              if ($key=="sp_suggestuser")
                  $val="1202822001";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."'>";
          }
      }
      $tmp_tc_form.="</form>";
      $tmp_db_form.="</form>";
      $tmp_form.=$tmp_tc_form.$tmp_db_form;
      return $tmp_form;
   }
   /**
	 * 校验方法
	 * @param null
	 * @return boolean
	 */
   function is_fields_valiad(){
        return true;
    }
    
}
?>
