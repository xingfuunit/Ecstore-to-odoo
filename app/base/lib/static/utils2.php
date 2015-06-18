<?php
/**
 * july
 */


class utils2{
	/**
	 * ����ַ����ÿ���֣��Ƿ���ָ���ַ����ڵ���.�������ִ�Сд��
	 * ע�⣺�������������ţ�ʹ��IsExistValue2
	 * ����:����(0123456789)
	 *  	��ĸ(abcdefghijklmnopqrstuvwxyz)
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @param string  	$chars 			�ַ���
	 * @param string  	$isIgnoreCase 	�Ƿ����ִ�Сд,true=������,false=���ִ�Сд
	 * @return  bool					true=���ַ�����,false=���ڷ��ַ�������
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
	 * ����ַ����ÿ���֣��Ƿ���ָ���ַ����ڵ���.�������ִ�Сд��
	 * ע�⣺�������������ţ�ʹ�ñ�����
	 * 
	 * ����:����(0123456789)
	 *  	��ĸ(abcdefghijklmnopqrstuvwxyz)
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @param string  	$chars 			�ַ���
	 * @param string  	$isIgnoreCase 	�Ƿ����ִ�Сд,true=������,false=���ִ�Сд
	 * @return  bool					true=���ַ�����,false=���ڷ��ַ�������
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
	 * ��������Ƿ�Ϊָ������,���id��(�������ݿ��ѯ),"34,5,1,8"
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @param string  	$flag 			�ַ���,�ָ���Ĭ��Ϊ,
	 * @return  bool					true=��id��,false=��id��
	 */
	static function IsIdList($str,$flag = ','){     
		return IsExistValue($str,'0123456789'.$flag);
	}

	/**
	 * �Ƿ�Ϊ�������ַ�
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @return  bool					true=��,false=����
	 */
	static function IsNumberChar($str){     
		return IsExistValue($str,'0123456789');
	}

	/**
	 * �Ƿ�Ϊ����ĸ��ɣ������ִ�Сд��
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @return  bool					true=��,false=����
	 */
	static function IsEnglish($str){     
		return IsExistValue($str,'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * ������,�Ƿ�Ϊ������ɵ��ļ�����(��ȫ����ĸ���������)
	 * @author 	july
	 * @param string  	$str 			�ַ���
	 * @return  bool					true=��,false=����
	 */
	static function IsRndKey($str){     
		return strlen($str) > 0 && IsExistValue($str,'abcdefghijklmnopqrstuvwxyz0123456789');
	}

	/**
	 * ����sql,��ֹע��
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
