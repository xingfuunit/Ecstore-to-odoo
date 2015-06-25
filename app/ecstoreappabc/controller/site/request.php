<?php
/**
http://www.shopexdream.com
 */
 

class ecstoreappabc_ctl_site_request extends site_controller 
{
    /*unionpay conf*/
    const VERIFY_HTTPS_CERT = false;

    var $timezone        = "Asia/Shanghai"; //时区
    var $sign_method     = "md5"; //摘要算法，目前仅支持md5 (2011-08-22)
    const FRONT_PAY = 1;
    const BACK_PAY  = 2;
    const RESPONSE  = 3;
    const QUERY     = 4;

    const CONSUME                = "01";
    const CONSUME_VOID           = "31";
    const PRE_AUTH               = "02";
    const PRE_AUTH_VOID          = "32";
    const PRE_AUTH_COMPLETE      = "03";
    const PRE_AUTH_VOID_COMPLETE = "33";
    const REFUND                 = "04";
    const REGISTRATION           = "71";

    const CURRENCY_CNY      = "156";
    
    /**/
    const RESP_SUCCESS  = "00"; //返回成功

    const QUERY_SUCCESS = "0";  //查询成功
    const QUERY_FAIL    = "1";
    const QUERY_WAIT    = "2";
    const QUERY_INVALID = "3";

    var $args;
    var $api_url;

    var $signature;
    /**/
    //支付请求可为空字段（但必须填写）
    var $pay_params_empty = array(
        "origQid"           => "",
        "acqCode"           => "",
        "merCode"           => "",
        "commodityUrl"      => "",
        "commodityName"     => "",
        "commodityUnitPrice"=> "",
        "commodityQuantity" => "",
        "commodityDiscount" => "",
        "transferFee"       => "",
        "customerName"      => "",
        "defaultPayType"    => "",
        "defaultBankNumber" => "",
        "transTimeout"      => "",
        "merReserved"       => "",
    );

    //支付请求必填字段检查
    var $pay_params_check = array(
        "version",
        "charset",
        "transType",
        "origQid",
        "merId",
        "merAbbr",
        "acqCode",
        "merCode",
        "commodityUrl",
        "commodityName",
        "commodityUnitPrice",
        "commodityQuantity",
        "commodityDiscount",
        "transferFee",
        "orderNumber",
        "orderAmount",
        "orderCurrency",
        "orderTime",
        "customerIp",
        "customerName",
        "defaultPayType",
        "defaultBankNumber",
        "transTimeout",
        "frontEndUrl",
        "backEndUrl",
        "merReserved",
    );

    //查询请求必填字段检查
    var $query_params_check = array(
        "version",
        "charset",
        "transType",
        "merId",
        "orderNumber",
        "orderTime",
        "merReserved",
    );

    //商户保留域可能包含的字段
    var $mer_params_reserved = array(
    //  NEW NAME            OLD NAME
        "cardNumber",       "pan",
        "cardPasswd",       "password",
        "credentialType",   "idType",
        "cardCvn2",         "cvn",
        "cardExpire",       "expire",
        "credentialNumber", "idNo",
        "credentialName",   "name",
        "phoneNumber",      "mobile",
        "merAbstract",

        //tdb only
        "orderTimeoutDate",
        "origOrderNumber",
        "origOrderTime",
    );

    var $notify_param_check = array(
        "version",
        "charset",
        "transType",
        "respCode",
        "respMsg",
        "respTime",
        "merId",
        "merAbbr",
        "orderNumber",
        "traceNumber",
        "traceTime",
        "qid",
        "orderAmount",
        "orderCurrency",
        "settleAmount",
        "settleCurrency",
        "settleDate",
        "exchangeRate",
        "exchangeDate",
        "cupReserved",
        "signMethod",
        "signature",
    );

    var $sign_ignore_params = array(
        "bank",
    );
    
	function __construct($app)
	{
		$this->app = $app;
	}
    public function index() 
    {
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ecstoreappabc_payment_plugin_ecstoreappabc', 'callback');
		$this->callback_url = $this->callback_url."?".http_build_query($_POST); 
		header('Location:' .$this->callback_url);
		exit;
    }
        
    public function serverCallback() {
        $servercallback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ecstoreappabc_payment_plugin_ecstoreappabc', 'callback');
        
        $httpclient = kernel::single('base_httpclient');
        $httpclient->timeout = 30;
        $result = $httpclient->post($servercallback_url,$_POST);
        exit;
    }

}//End Class
