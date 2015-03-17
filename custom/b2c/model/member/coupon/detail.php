<?php
/**
 * 
 * @author Sam
 *
 * 查询用户的优惠券
 */
class b2c_mdl_member_coupon_detail extends dbeav_model{
public function get_schema(){
        $schema = array (
            'columns' => array (
                'memc_code' =>
    array (
      'type' => 'table:orders@b2c',
      'required' => true,
      'label' => app::get('couponlog')->_('优惠券码'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cpns_id' =>
    array (
      'type' => 'number',
      'required' => true,     
      'label' => app::get('couponlog')->_('优惠券方案ID'),
	  'width' => 70,
      'editable' => false,
      'in_list' => true,
      'searchtype' => 'has',
      'filtertype' => 'yes',
    ),
    'cpns_name' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('couponlog')->_('优惠券方案名称'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
	  'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'login_account'=>
	array(		
      'type' => 'table:account@pam',
      'label' => app::get('couponlog')->_('使用者名称'),
      'width' => 150,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
	),
    'member_id' => 
    array (
      'type' => 'table:account@pam',
      'label' => app::get('couponlog')->_('使用者ID'),
      'width' => 60,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
	'memc_gen_time'=>array(
	   'type' => 'time',
      'label' => app::get('couponlog')->_('优惠券产生时间'),
      'width' => 120,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
	),
			),
			'idColumn' => 'member_id',
            'in_list' => array (
                0 => 'member_id',
                1 => 'login_account',
                2 => 'cpns_name',
                3 => 'cpns_id',
                4 => 'memc_code',
				5=>'memc_gen_time',
            ),
            'default_in_list' => array (
                0 => 'member_id',
                1 => 'login_account',
                2 => 'cpns_name',
                3 => 'cpns_id',
                4 => 'memc_code',
				5=>'memc_gen_time',
            ),
        );
        return $schema;
    }

	public function count($filter=null){
    	
        $sql = 'SELECT count(*) as _count  FROM sdb_b2c_member_coupon '.
                ' left join sdb_pam_members '.
                ' on sdb_b2c_member_coupon.member_id=sdb_pam_members.member_id left join sdb_b2c_coupons on sdb_b2c_member_coupon.cpns_id=sdb_b2c_coupons.cpns_id ';
		if($filter){
			$sql .= ' WHERE ' . $this->_filter($filter);
		}
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
    }
	
	/**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    
	public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){

        //$payment_where = " left join sdb_ome_orders on sdb_ome_orders.order_id = sdb_ome_payments.order_id ";
        //$refund_where = " left join sdb_ome_orders on sdb_ome_orders.order_id = sdb_ome_refunds.order_id ";              
		$sql = 'SELECT sdb_pam_members.login_account,sdb_b2c_member_coupon.memc_code,sdb_b2c_member_coupon.cpns_id,sdb_b2c_coupons.cpns_name,sdb_b2c_member_coupon.member_id,sdb_b2c_member_coupon.memc_gen_time,sdb_b2c_member_coupon.memc_gen_orderid FROM sdb_b2c_member_coupon '.
                ' left join sdb_pam_members '.
                ' on sdb_b2c_member_coupon.member_id=sdb_pam_members.member_id left join sdb_b2c_coupons on sdb_b2c_member_coupon.cpns_id=sdb_b2c_coupons.cpns_id ';
        if($filter){
			$sql .= ' WHERE ' . $this->_filter($filter);
		}
		$sql .= ' order by sdb_b2c_member_coupon.memc_gen_time DESC';

		//echo $sql;

        $rows = $this->db->selectLimit($sql,$limit,$offset);
        
        return $rows;
    }
	public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where = array(1);
		
        if(isset($filter['member_id']) && $filter['member_id']){
            $where[] = ' sdb_b2c_member_coupon.member_id = '.$filter['member_id'];
        }
		if(isset($filter['login_account']) && $filter['login_account']){
            $where[] = ' sdb_pam_members.login_account LIKE \''.$filter['login_account'].'%\'';
        }
		if(isset($filter['memc_gen_time']) && $filter['memc_gen_time']){
            $where[] = ' sdb_b2c_member_coupon.memc_gen_time <'.(strtotime($filter['memc_gen_time'])+86400);
        }		
		if(isset($filter['cpns_name']) && $filter['cpns_name']){
            $where[] = ' sdb_b2c_coupons.cpns_name = '.'\''.$filter['cpns_name'].'\'';
        }
		if(isset($filter['cpns_id']) && $filter['cpns_id']){
            $where[] = ' sdb_b2c_member_coupon.cpns_id = '.$filter['cpns_id'];
        }
		if(isset($filter['memc_code']) && $filter['memc_code']){
            $where[] = ' sdb_b2c_member_coupon.memc_code = '.'\''.$filter['memc_code'].'\'';
        }
		
		
		
        return implode($where,' AND ');
    }
	
}