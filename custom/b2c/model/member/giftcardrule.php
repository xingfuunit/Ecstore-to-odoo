<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_mdl_member_giftcardrule extends dbeav_model{
	var $defaultOrder = array('rule_id','DESC'); //列表默认排序
    function save($aData){
    	parent::save($aData);
    }

    //验证是否可以删除
    function pre_recycle($data){
    	
       $giftcardMdl = $this->app->model('member_giftcard');
       foreach($data as $val){
			$aData = $giftcardMdl->getList('rule_id,status',array('rule_id'=>$val['rule_id']));
			$a = 0;
			foreach($aData as $k=>$v){
				if($v['status'] == 'active'){
					$a = 1;
				}
			}
			if($a){
			  	$this->recycle_msg = app::get('b2c')->_('操作失败！存在兑换卡未使用');
			   	return false;
			}
       }
       return true;
   	}	
   	
    public function validate(&$data,&$msg){
		$fag = 1;
		//规则名称验证
		if( '' == $data['rule_name'] ){
			$msg = app::get('b2c')->_('兑换卡规则名称不能为空！');
			$fag = 0;
		}else{
			$rule_name = $this->getList('rule_name',array('rule_name'=>$data['rule_name']));

			$rule_name = $rule_name[0]['rule_name'];

			if( !$data['rule_id'] && $rule_name){
				$msg = app::get('b2c')->_('操作失败！兑换卡规则名称重复');
				$fag = 0;
			}

		}

		//面值验证


		//兑换卡前缀验证
		if( '' == $data['gcard_prefix'] ){
			$msg = app::get('b2c')->_('兑换卡前缀不能为空！');
			$fag = 0;
		}else{			
				
			if(preg_match('/[^A-Za-z0-9]/i',$data['gcard_prefix']) || strlen(trim($data['gcard_prefix'])) <= 0 || strlen(trim($data['gcard_prefix'])) > 6 ){
				$msg = app::get('b2c')->_('兑换卡前缀请输入1-6位的数组和字母组合');
				$fag = 0;
			}

			$rule_prefix = $this->getList('gcard_prefix',array('gcard_prefix'=>$data['gcard_prefix']));

			$rule_prefix = $rule_prefix[0]['gcard_prefix'];
			if(!$data['rule_id'] && $rule_prefix){
				$msg = app::get('b2c')->_('操作失败！兑换卡前缀重复');
				$fag = 0;
			}

		}
		
		//有效期验证
		 $data['d_valid'] = intval($data['d_valid']) ? $data['d_valid'] : 60 ;			
		if( !is_numeric($data['d_valid']) ||  intval($data['d_valid']) <= 0) {
			$msg = app::get('b2c')->_('有效期必须是数字！');
			$fag = 0;
		}

		return $fag;

    }

    //生成兑换卡
    function auto_giftcard(&$aData){   
		$length = 13; //总长度
    	$rule_id = $aData['rule_id'];
    	$nums = $aData['nums']; //生成数量
    	$d_valid = strtotime($aData['end_time']); 
    	$aRule = $this->getList('*',array('rule_id'=>$rule_id));
    	$aRule = $aRule[0];    	
    	if($aRule['gcard_prefix']){
    		$c = strlen($aRule['gcard_prefix']);
    	}

    	for($i=1;$i<=$nums;$i++){    		
    		$code_list[] = 	$this->generate_code($length-$c);
    	}

    	//日志对应生成记录唯一标识
    	$c_token = md5(time());


    	$giftcardMdl = app::get('b2c')->model('member_giftcard');
    	if($code_list && is_array($code_list)){
	    	foreach($code_list as $k=>$v){
		    	$arr = array(
		    		'gcard_code' => strtoupper($aRule['gcard_prefix']).$v,
		    		'uname' =>'',
		    		'rule_id'=>$aRule['rule_id'],
		    		'rule_name' => $aRule['rule_name'],
		    		'gcard_money' => $aRule['gcard_money'],
		    		'status' => 'active',
		    		'start_time'=>time(),
		    		'end_time' =>$d_valid,
		    		'is_overdue' => ($d_valid-time()) > 0 ? 'false' : 'true',
		    		'used_status' =>'false',
		    		'c_token' => $c_token,

				);
				
				$giftcardMdl->save($arr);				       
	    	}

	    	//更新生成数量	    	
	    	$r = $this->update(array('count'=>$aRule['count']+$nums),array('rule_id'=>$aRule['rule_id']));
	    	//生成操作日志
	    	$gclogMdl = app::get('b2c')->model('member_giftcardlog');
	    	$log = array(
	    		'rule_id'=>$aRule['rule_id'],
	    		'op_id' =>$aData['op_id'],
	    		'op_name' =>$aData['op_name'],
	    		'alttime' =>time(),
	    		'd_valid' =>$d_valid,
	    		'nums' => $nums,
	    		'c_token' => $c_token,
	    		'log_text' =>$aData['remark']
    		);
    		$gclogMdl->save($log);
    		$aData['log_id'] = $log['log_id'];
    		
    		return true;
    	}

    	return false;
    	
    	
    }

    //生成兑换卡 算法
    function generate_code($length=8,$pre_code=""){

	    $code = md5(microtime());//md5微妙时间
	    $code = strtoupper($code);//转大写
	   	$pattern = '/(0|1|O|I|o|2|Z|i|z)/i';//替换字符“0 1 O I”
	    $replacement = '';
	    $code = preg_replace($pattern, $replacement, $code);
	    $code = str_shuffle($pre_code.$code);//拼接per_code字符串 ,随机打乱
	    if(strlen($code)<$length){
	        $code .= generate_code($length,$code);
	    }
	    $code = substr($code,1,$length);//截取长度
	    return $code;
	}

	//获取特定兑换卡生成日志
	function getGiftcardLogList($rule_id,$page=0,$limit=-1){
		$gclogMdl = app::get('b2c')->model('member_giftcardlog');
		$arrlogs= array();
		$arr_returns = array();

		if($limit < 0){
			$arrlogs = $gclogMdl->getList('*',array('rule_id'=>$rule_id));
		}

		$limitStart = $page * $limit;
		$arrlogs_all = $gclogMdl->getList('*', array('rule_id' => $rule_id));
		$arrlogs = $gclogMdl->getList('*', array('rule_id' => $rule_id), $limitStart, $limit);

		$arr_returns['page'] = count($arrlogs_all);
		$arr_returns['data'] = $arrlogs;
		return $arr_returns;
	}

	var $fget_task_name = 'giftcards';
	function fgetlist_csv(&$data,$filter,$offset,$exportType=1){
		//$dbschema = app::get('b2c')->model('member_giftcardlog')->get_schema();
		//$gclogMdl = app::get('b2c')->model('member_giftcardlog');
		$giftcardMdl = app::get('b2c')->model('member_giftcard');
		$limit = 100;       
		$giftcard = $giftcardMdl->getList('*',$filter,$limit*$offset,$limit);

		if(!$giftcard){
		    return false;
		};

		$cols = $this->_columns();
		if(!$data['title']){
		    $title = array();
		    $title[] = '兑换卡规则名称';
		    $title[] = '兑换卡';
		    $title[] = '面额';
		    $title[] = '有效截止日期';
		    $title[] = '兑换卡状态';
		    $title[] = '过期状态';
		    $title[] = '使用状态';
		    $data['title'] = '"'.implode('","',$title).'"';
		}
		$data['contents'] = array();
		foreach( $giftcard as $info ){
		    $rowVal = array();
		    $rowVal[] = $info['rule_name'];
		    $rowVal[] = $info['gcard_code'];
		    $rowVal[] = $info['gcard_money'];
		    $rowVal[] = date('Y-m-d H:i:s',$info['end_time']);
		    $rowVal[] = ($info['status'] == 'active') ? '活动' : '作废';
		    $rowVal[] = ((string)$info['is_overdue'] == 'false') ? '未过期' : '已过期';
		    $rowVal[] = ((string)$info['used_status'] == 'false') ? '未使用' : '已使用';
		    $data['contents'][] = '"'.implode('","',$rowVal).'"';
		}
		
		return true;
	}

}
?>
