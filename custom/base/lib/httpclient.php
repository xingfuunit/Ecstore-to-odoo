<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 本类使用推荐new class，不应使用kernel:single方法
 */
 
class base_httpclient{
	function __construct(){
			if(ECAE_MODE==true){
				$this->netcore = kernel::single('base_curl');
			}else{
				$this->netcore = kernel::single('base_http');
			}	
	}
	
    function get($url,$headers=null,$callback=null,$ping_only=false){
    	if(ODOO_ERP == '1' && strstr(strtolower($url),'shopex.cn')){  
    		$url_array = unserialize(PZ_PASS_URL);
    		foreach($url_array as $ua){
    			if(strstr(strtolower($url),$ua)){
    				return $this->netcore->action(__FUNCTION__,$url,$headers,$callback,null,$ping_only);
    			}
    		}		    		

    		//去掉原有的sign
    		unset($data['sign']);
    		$data['sign'] = base_certificate::gen_sign($data);
    		
    		//ripcrocd 发送到 odoo
    		$ripc = kernel::single('ripcord_builder');
    		return $ripc->calling_methods($data);
    	}else{
    		return $this->netcore->action(__FUNCTION__,$url,$headers,$callback,null,$ping_only);
    	}
        
    }

    function post($url,$data,$headers=null,$callback=null,$ping_only=false){

    	if(ODOO_ERP == '1' && strstr(strtolower($url),'shopex.cn')){    		   	    			
    		$url_array = unserialize(PZ_PASS_URL);
    		foreach($url_array as $ua){
    			if(strstr(strtolower($url),$ua)){
    				return $this->netcore->action(__FUNCTION__,$url,$headers,$callback,$data,$ping_only);
    			}
    		}	    			
    		
    		//去掉原有的sign
    		unset($data['sign']);
    		$data['sign'] = base_certificate::gen_sign($data);
    		
    		//ripcrocd 发送到 odoo
    		
    		$ripc = kernel::single('base_ripcordbuilder');
    		$re = $ripc->calling_methods($data);
    		
    		return $re;
    	}else{
        	return $this->netcore->action(__FUNCTION__,$url,$headers,$callback,$data,$ping_only);
    	}
    }

	function set_timeout($timeout){
        $this->netcore->set_timeout(30);
        $this->timeout = $timeout;
		return $this;
    }
	function action($action,$url,$headers=null,$callback=null,$data=null,$ping_only=false){
		return $this->netcore->action($action,$url,$headers,$callback,$data,$ping_only);
	}
	function is_addr($ip){
		return $this->netcore->is_addr($ip);
	}
    function upload($url,$files,$data,$headers=null,$callback=null,$ping_only=false){
        $boundary = '----ShopExFormBoundaryEsor2rdD1hne8INi';
        $headers['Content-Type']='multipart/form-data; boundary='.$boundary;
        $formData = array();
        $this->_http_query($formData,$data);

        $output ='';
        foreach($formData as $k=>$v){
            $output .= '--'.$boundary."\r\n";
            $output .= 'Content-Disposition: form-data; name="'
                .str_replace('"','\\\"',$k)."\"\r\n\r\n";
            $output .= $v."\r\n";
        }
        foreach($files as $k=>$v){
            $output .= '--'.$boundary."\r\n";
            $output .= 'Content-Disposition: form-data; name="'
                .str_replace('"','\\\"',$k).'"; filename="'.basename($v)."\"\r\n";
            $mime = function_exists('mime_content_type')?mime_content_type($v):'application/octet-stream';
            $output .= "Content-Type: $mime\r\n\r\n";
            $output .= file_get_contents($v)."\r\n";
        }
        $output .= '--'.$boundary."--\r\n";

        return $this->netcore->action('post',$url,$headers,$callback,$output,$ping_only);
    }

    function _http_query(&$return,$data,$prefix=null,$key='')
    {
        $ret = array();
        foreach((array)$data as $k => $v){
            if(is_int($k) && $prefix != null){
                $k = $prefix.$k;
            }
            if(!empty($key)){
                $k = $key."[".$k."]";
            }

            if(is_array($v) || is_object($v)){
                $this->_http_query($return,$v,"",$k);
            }else{
                $return[$k]=$v;
            }
        }
    }
    
    
    

}
?>
