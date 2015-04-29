<?php
/**
 * 
 * @author Sam
 *
 * 查询用户的优惠券
 */
class b2c_mdl_analysis_coupon extends dbeav_model {
	public function get_schema() {
		$schema = array (
				'columns' => array (
						'memc_code' => array (
								'order' => 1,
								'type' => 'table:orders@b2c',
								'required' => true,
								'label' => app::get ( 'couponlog' )->_ ( '优惠券码' ),
								'searchtype' => 'has',
								'filtertype' => 'yes',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'cpns_id' => array (
								'order' => 2,
								'type' => 'number',
								'required' => true,
								'label' => app::get ( 'couponlog' )->_ ( '优惠券方案ID' ),
								'width' => 70,
								'editable' => false,
								'in_list' => true,
								'searchtype' => 'has',
								'filtertype' => 'yes' 
						),
						'cpns_name' => array (
								'order' => 3,
								'type' => 'varchar(255)',
								'label' => app::get ( 'couponlog' )->_ ( '优惠券方案名称' ),
								'searchtype' => 'has',
								'filtertype' => 'yes',
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'member_id' => array (
								'order' => 4,
								'type' => 'table:account@pam',
								'label' => app::get ( 'couponlog' )->_ ( '用户ID' ),
								'width' => 60,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'login_account_local' => array (
								'order' => 5,
								'type' => 'table:account@pam',
								'label' => app::get ( 'couponlog' )->_ ( '用户名' ),
								'width' => 150,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'login_account_mobile' => array (
								'order' => 6,
								'type' => 'table:account@pam',
								'label' => app::get ( 'couponlog' )->_ ( '用户手机' ),
								'width' => 150,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'login_account_email' => array (
								'order' => 7,
								'type' => 'table:account@pam',
								'label' => app::get ( 'couponlog' )->_ ( '用户邮箱' ),
								'width' => 150,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						
						'memc_isvalid' => array (
								'order' => 8,
								'type' => array (
										'true' => '可用',
										'false' => '不可用' 
								),
								'label' => app::get ( 'couponlog' )->_ ( '当前是否可用' ),
								'width' => 80,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'disabled' => array (
								'order' => 9,
								'type' => array (
										'true' => '失效',
										'false' => '' 
								),
								'label' => app::get ( 'couponlog' )->_ ( '是否失效' ),
								'width' => 60,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'memc_gen_time' => array (
								'order' => 10,
								'type' => 'time',
								'label' => app::get ( 'couponlog' )->_ ( '优惠券产生时间' ),
								'width' => 120,
								'searchtype' => 'has',
								'filtertype' => false,
								'filterdefault' => 'true',
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						),
						'memc_used_times' => array (
								'order' => 11,
								'type' => 'text',
								'label' => app::get ( 'couponlog' )->_ ( '已使用次数' ),
								'width' => 80,
								
								'editable' => false,
								'in_list' => true,
								'default_in_list' => true 
						) 
				),
				'idColumn' => 'member_id',
				'in_list' => array (
						0 => 'member_id',
						1 => 'login_account_local',
						2 => 'login_account_mobile',
						3 => 'login_account_email',
						4 => 'cpns_name',
						5 => 'cpns_id',
						6 => 'memc_code',
						7 => 'memc_gen_time',
						8 => 'memc_isvalid',
						9 => 'disabled',
						10 => 'memc_used_times' 
				),
				'default_in_list' => array (
						0 => 'member_id',
						1 => 'login_account_local',
						2 => 'login_account_mobile',
						3 => 'login_account_email',
						4 => 'cpns_name',
						5 => 'cpns_id',
						6 => 'memc_code',
						7 => 'memc_gen_time',
						8 => 'memc_isvalid',
						9 => 'disabled',
						10 => 'memc_used_times' 
				) 
		);
		return $schema;
	}
	
	public function count($filter = null) {
		
		$sql = 'SELECT count(*) as _count  FROM sdb_b2c_member_coupon ' . ' left join sdb_b2c_members ' . ' on sdb_b2c_member_coupon.member_id=sdb_b2c_members.member_id left join sdb_b2c_coupons on sdb_b2c_member_coupon.cpns_id=sdb_b2c_coupons.cpns_id ';
		if ($filter) {
			$sql .= ' WHERE ' . $this->_filter ( $filter );
		}
		$row = $this->db->select ( $sql );
		return intval ( $row [0] ['_count'] );
	}
	
	/**
	 * 重写搜索的下拉选项方法
	 * 
	 * @param
	 *        	null
	 * @return null
	 */
	
	public function getlist($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
		
		$sql = 'SELECT   sdb_b2c_member_coupon.memc_code,sdb_b2c_member_coupon.disabled,sdb_b2c_member_coupon.memc_used_times,sdb_b2c_member_coupon.memc_isvalid,sdb_b2c_member_coupon.cpns_id,sdb_b2c_coupons.cpns_name,sdb_b2c_member_coupon.member_id,sdb_b2c_member_coupon.memc_gen_time,sdb_b2c_member_coupon.memc_gen_orderid FROM sdb_b2c_member_coupon ' . ' left join sdb_b2c_members ' . ' on sdb_b2c_member_coupon.member_id=sdb_b2c_members.member_id left join sdb_b2c_coupons on sdb_b2c_member_coupon.cpns_id=sdb_b2c_coupons.cpns_id ';
		if ($filter) {
			$sql .= ' WHERE ' . $this->_filter ( $filter );
		}
		$sql .= ' order by sdb_b2c_member_coupon.memc_gen_time DESC';
		
		// echo $sql;
		
		$rows = $this->db->selectLimit ( $sql, $limit, $offset );
		foreach ( $rows as $k => $v ) {
			// $v['member_id']=376;
			$sql = 'SELECT login_account,login_type FROM sdb_pam_members WHERE disabled=\'false\' AND member_id=' . $v ['member_id'];
			$row1 = $this->db->select ( $sql );
			foreach ( $row1 as $v1 ) {
				if ($v1 ['login_type'] == 'local')
					$rows [$k] ['login_account_local'] = $v1 ['login_account'];
				if ($v1 ['login_type'] == 'email')
					$rows [$k] ['login_account_email'] = $v1 ['login_account'];
				if ($v1 ['login_type'] == 'mobile')
					$rows [$k] ['login_account_mobile'] = $v1 ['login_account'];
			}
		
		}
		// var_dump($rows);
		return $rows;
	}
	public function _filter($filter, $tableAlias = null, $baseWhere = null) {
		$where = array (
				1 
		);
		
		if (isset ( $filter ['member_id'] ) && $filter ['member_id']) {
			$where [] = ' sdb_b2c_member_coupon.member_id = ' . $filter ['member_id'];
		}
		if (isset ( $filter ['login_account_local'] ) && $filter ['login_account_local']) {
			$sql = 'SELECT member_id FROM sdb_pam_members WHERE login_account LIKE \'' . $filter ['login_account_local'] . '%\'';
			$row = $this->db->select ( $sql );
			$r = array ();
			foreach ( $row as $v ) {
				$r [] = $v ['member_id'];
			}
			$rr = implode ( ',', $r );
			
			$where [] = ' sdb_b2c_members.member_id IN (' . $rr . ') ';
		}
		if (isset ( $filter ['login_account_mobile'] ) && $filter ['login_account_mobile']) {
			$sql = 'SELECT member_id FROM sdb_pam_members WHERE login_account LIKE \'' . $filter ['login_account_mobile'] . '%\'';
			$row = $this->db->select ( $sql );
			$r = array ();
			foreach ( $row as $v ) {
				$r [] = $v ['member_id'];
			}
			$rr = implode ( ',', $r );
			
			$where [] = ' sdb_b2c_members.member_id IN (' . $rr . ') ';
		}
		if (isset ( $filter ['login_account_email'] ) && $filter ['login_account_email']) {
			$sql = 'SELECT member_id FROM sdb_pam_members WHERE login_account LIKE \'' . $filter ['login_account_email'] . '%\'';
			$row = $this->db->select ( $sql );
			$r = array ();
			foreach ( $row as $v ) {
				$r [] = $v ['member_id'];
			}
			$rr = implode ( ',', $r );
			
			$where [] = ' sdb_b2c_members.member_id IN (' . $rr . ') ';
		}
		
		if(isset($filter['time_from']) && $filter['time_from']){
			$filter['time_from'] = strtotime($filter['time_from']);
			$filter['time_to'] = (strtotime($filter['time_to'])+86400);
		
			$where [] = ' sdb_b2c_member_coupon.memc_gen_time <' . (strtotime ( $filter ['memc_gen_time'] ) + 86400);
		}
		if (isset ( $filter ['cpns_name'] ) && $filter ['cpns_name']) {
			$where [] = ' sdb_b2c_coupons.cpns_name = ' . '\'' . $filter ['cpns_name'] . '\'';
		}
		if (isset ( $filter ['cpns_id'] ) && $filter ['cpns_id']) {
			$where [] = ' sdb_b2c_member_coupon.cpns_id = ' . $filter ['cpns_id'];
		}
		if (isset ( $filter ['memc_code'] ) && $filter ['memc_code']) {
			$where [] = ' sdb_b2c_member_coupon.memc_code = ' . '\'' . $filter ['memc_code'] . '\'';
		}
		if (isset ( $filter ['disabled'] ) && $filter ['disabled']) {
			if ($filter ['disabled'] == '失效') {
				$filter ['disabled'] = 'true';
			}
			
			$where [] = ' sdb_b2c_member_coupon.disabled = ' . '\'' . $filter ['disabled'] . '\'';
		
		}
		if (isset ( $filter ['memc_isvalid'] ) && $filter ['memc_isvalid']) {
			if ($filter ['memc_isvalid'] == '可用') {
				$filter ['memc_isvalid'] = 'true';
			}
			if ($filter ['memc_isvalid'] == '不可用') {
				$filter ['memc_isvalid'] = 'false';
			}
			$where [] = ' sdb_b2c_member_coupon.memc_isvalid = ' . '\'' . $filter ['memc_isvalid'] . '\'';
			// var_dump($where);
		}
		
		return implode ( $where, ' AND ' );
	}

}