<?php
/**
 * july
 */


class utils2{
	/**
	 * 检查字符串里（每个字）是否都在指定字符集内的字.（不共分大小写）
	 * 注意：如果存在特殊符号，使用IsExistValue2
	 * 例如:整数(0123456789)
	 *  	字母(abcdefghijklmnopqrstuvwxyz)
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @param string  	$chars 			字符集
	 * @param string  	$isIgnoreCase 	是否区分大小写,true=不区分,false=区分大小写
	 * @return  bool					true=在字符集内,false=存在非字符集的字
	 */
	static function IsExistValue($str, $chars ,$isIgnoreCase = true){
		$ret = false;
		if(strlen($str)>0){
			$pattern  =  '/^['.$chars.']+$/';
	 
			if($isIgnoreCase){ $pattern.='i';}
			if(preg_match($pattern,$str) ){$ret = true;}
		}
		return $ret;
	}

	/**
	 * 检查字符串里（每个字）是否都在指定字符集内的字.（不共分大小写）
	 * 注意：如果存在特殊符号，使用本函数
	 * 
	 * 例如:整数(0123456789)
	 *  	字母(abcdefghijklmnopqrstuvwxyz)
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @param string  	$chars 			字符集
	 * @param string  	$isIgnoreCase 	是否区分大小写,true=不区分,false=区分大小写
	 * @return  bool					true=在字符集内,false=存在非字符集的字
	 */
	static function IsExistValue2($str, $chars ,$isIgnoreCase = true){
		if(strlen($str)==0 ||strlen($chars)==0 ){return false;}
		
		if($isIgnoreCase){
			$str 	= strtolower($str);
			$chars 	= strtolower($chars);
		}
		
		$ti = strlen($str);
		for ($i = 0; $i < $ti; $i++){
			if (strpos($chars,$str[$i]) === false){
				return false;
			}
		}
		return true;
	}

	/**
	 * 检查输入是否为指字内容,检查id列(用于数据库查询),"34,5,1,8"
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @param string  	$flag 			字符串,分隔符默认为,
	 * @return  bool					true=是id列,false=非id列
	 */
	static function IsIdList($str,$flag = ','){     
		return IsExistValue($str,'0123456789'.$flag);
	}

	/**
	 * 是否为纯数字字符
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @return  bool					true=是,false=不是
	 */
	static function IsNumberChar($str){     
		return IsExistValue($str,'0123456789');
	}

	/**
	 * 是否为纯字母组成（不区分大小写）
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @return  bool					true=是,false=不是
	 */
	static function IsEnglish($str){     
		return IsExistValue($str,'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * 查输入,是否为随机生成的文件名称(即全由字母和数字组成)
	 * @author 	july
	 * @param string  	$str 			字符串
	 * @return  bool					true=是,false=不是
	 */
	static function IsRndKey($str){     
		return strlen($str) > 0 && IsExistValue($str,'abcdefghijklmnopqrstuvwxyz0123456789');
	}

	/**
	 * 过滤sql,防止注入
	 * @author			july
	 * @param string  	$str	
	 * @param bool  	$isTrim
	 * @return string 	
	 */
	static function CheckSql($str, $isTrim = true){
		if(strlen($str)==0){return '';}
		
		$ret = $isTrim?trim($str):$str;
		$ret = str_replace("\\'", '', $ret);
		$ret = str_replace(chr(0), '', $ret);
		$ret = str_replace("'", '', $ret);
		$ret = str_replace('<?', '', $ret);
		$ret = str_replace('?>', '', $ret);
		return $ret;
	}

}
