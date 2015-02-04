<?php
class microshop_ctl_admin_promotioninto extends desktop_controller {
	var $workground = 'b2c.workground.promotion_into';
	
	/**
	 * 商品毛利 = 订单商品总价 - 订单商品总成本价
	 *
	 * 推广总费 = 商品毛利 * 60%
	 * 发货费 = 商品毛利 * 40% （经销商会员发货获得）
	 *
	 *
	 * 微店会员 = 推广总费 * 60% （微店会员获得推广费）
	 * 经销商会员 = 推广总费 * 40% （微店会员所属经销商会员获得推广费）
	 */
	var $prom_total_scale = 0.6; // 推广总费比
	var $ship_total_scale = 0.4; // 发货费比
	var $prom_microshop_scale = 0.6; // 微店会员比
	var $prom_agency_scale = 0.4; // 经销商会员比
	
	/**
	 * 列表
	 */
	function index() {
		$this->finder ( 'microshop_mdl_promotion_into', array (
				'title' => '分成产品列表',
				'use_buildin_filter' => true,
				'use_buildin_export' => true, 
				'use_buildin_recycle'=>false,
		) );
	}
	
	/**
	 * logs列表
	 */
	function logs() {
		$this->finder ( 'microshop_mdl_promotion_into_logs', array (
				'title' => '分成处理记录列表',
				'use_buildin_filter' => true,
				'use_buildin_export' => true, 
				'use_buildin_recycle'=>false,
		) );
	}
	
	
	function show_logs($pri_id = 0) {
		header ( "Cache-Control:no-store" );
		
		$pri_id = isset($_POST['pri_id'])?intval($_POST['pri_id']):$pri_id;
		
		$proi_mdl = kernel::single( 'microshop_mdl_promotion_into' );
		$proi = $proi_mdl->dump ( $pri_id );
		
		$members_mdl = kernel::single( 'b2c_mdl_members' );
		
		if ($proi) {
			
			$prom_data ['goods_gross_profit'] = ($proi ['price'] - $proi ['cost']) * $proi ['nums']; // 商品毛利
			$prom_data ['goods_gross_profit_str'] = ' (' . $proi ['price'] . ' - ' . $proi ['cost'] . ') * ' . $proi ['nums'].' = '.$prom_data ['goods_gross_profit'] .' 元';
			
			$prom_data ['prom_total_price'] = $prom_data ['goods_gross_profit'] * $this->prom_total_scale; // 推广总费
			$prom_data ['prom_total_price_str'] = $prom_data ['goods_gross_profit'].' * '.$this->prom_total_scale.' = '.$prom_data ['prom_total_price'] .' 元';
			
			$prom_data ['ship_total_price'] = $prom_data ['goods_gross_profit'] * $this->ship_total_scale; // 发货总费
			$prom_data ['ship_total_price_str'] = $prom_data ['goods_gross_profit'].' * '.$this->ship_total_scale.' = '.$prom_data ['ship_total_price'] .' 元';
		
			$proi['microshop_member'] = $members_mdl->dump($proi['pro_member_id']);//微店用户
			$prom_data ['microshop_member_price'] = $prom_data ['prom_total_price'] * $this->prom_microshop_scale;
			$prom_data ['microshop_member_price_str'] = $prom_data ['prom_total_price'].' * '.$this->prom_microshop_scale.' = '.$prom_data ['microshop_member_price'] .' 元';
			
			$proi['agency_member'] = $members_mdl->dump($proi['agency_id']);//经销商用户
			$prom_data ['agency_member_price'] = $prom_data ['prom_total_price'] * $this->prom_agency_scale;
			$prom_data ['agency_member_price_str'] = $prom_data ['prom_total_price'].' * '.$this->prom_agency_scale.' = '.$prom_data ['agency_member_price'] .' 元';
			
			//print_r($proi['pro_members']);
			//发货商用户
			if(is_array($proi['ship_members'])){
				foreach ($proi['ship_members'] as $k=>$v){
					$_filter    = array (
							'agency_no' => trim($v['agency_no']), //经销商编号
							'member_type'   => 3,
					);
					$ship_members = $members_mdl->dump($_filter);
					$proi['ship_members'][$k]['member_id'] = isset($ship_members['member_id'])?$ship_members['member_id']:0;
					$proi['ship_members'][$k]['ship_price'] = $prom_data ['ship_total_price'] * ( $v['number'] / $proi['nums'] ) ;
					$proi['ship_members'][$k]['ship_price_str'] = $prom_data ['ship_total_price'].' * ( '.$v['number'].' / '.$proi['nums'].' ) = '.$proi['ship_members'][$k]['ship_price'] .' 元';
				}
			}
			
			if(isset($_POST['save']) && $_POST['save']=='save_logs'){
				
				$this->begin();
				
				if($proi['pri_status'] == 1){
					$this->end(false,'分成已经处理过不能再处理！');
				}
				
				$proi_logs_mdl = kernel::single( 'microshop_mdl_promotion_into_logs' );
				$objAdvance = kernel::single("b2c_mdl_member_advance");
				$time_now = time();
				
				if($proi['microshop_member']){
					$logs = array(
							'pri_id' => $pri_id,
							'member_id' => $proi['microshop_member']['member_id'],
							'money' => $prom_data ['microshop_member_price'],
							'pri_type' => 0,
							'money_status' => 0,
							'mtime' => $time_now,	
					);
					if($proi_logs_mdl->save($logs)){
						$message = '分成编号:'.$pri_id.' 微店分成:'.$prom_data ['microshop_member_price_str'];
						$status = $objAdvance->add($logs['member_id'], $logs['money'], $message, $msg, '', '', '', '',0, false);
						$error_Msg = $msg;
						if (!$status){
							$this->end(false,$error_Msg);
						}
					}else{
						$this->end(false,'分成操作失败');
					}
				}
				
				if($proi['agency_member']){
					$logs = array(
							'pri_id' => $pri_id,
							'member_id' => $proi['agency_member']['member_id'],
							'money' => $prom_data ['agency_member_price'],
							'pri_type' => 1,
							'money_status' => 0,
							'mtime' => $time_now,
					);
					if($proi_logs_mdl->save($logs)){
						$message = '分成编号:'.$pri_id.' 经销商分成:'.$prom_data ['agency_member_price_str'];
						$status = $objAdvance->add($logs['member_id'], $logs['money'], $message, $msg, '', '', '', '',0, false);						$error_Msg = $msg;
						if (!$status){
							$this->end(false,$error_Msg);
						}
					}else{
						$this->end(false,'agency分成操作失败');
					}
				}
				
				if(is_array($proi['ship_members'])){
					foreach ($proi['ship_members'] as $k=>$v){
						$logs = array(
								'pri_id' => $pri_id,
								'member_id' => $v['member_id'],
								'money' => $v['ship_price'],
								'pri_type' => 2,
								'money_status' => 0,
								'mtime' => $time_now,
						);
						if($proi_logs_mdl->save($logs)){
							$message = '分成编号:'.$pri_id.' 发货分成:'.$v['ship_price_str'];
							$status = $objAdvance->add($logs['member_id'], $logs['money'], $message, $msg, '', '', '', '',0, false);
							$error_Msg = $msg;
							if (!$status){
								$this->end(false,$error_Msg);
							}
						}else{
							$this->end(false,'发货分成操作失败');
						}
					}
				}
				
				$proi_update['pri_status'] = '1';
				if(!$proi_mdl->update($proi_update, array('pri_id'=>$pri_id))){
					$this->end(false,'分成编号更新失败');
				}
				
				$this->end(true,app::get('b2c')->_('保存成功'));
			}

		}
		
		$this->pagedata['prom_data'] = $prom_data;
		$this->pagedata['proi'] = $proi;
		
		$this->display ('admin/promotion/showlogs.html');
	}
	
}
