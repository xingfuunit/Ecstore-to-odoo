<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * mdl_coupon
 *
 * @uses base_db_model
 * @package
 * @version $Id: mdl.coupon.php 2057 2010-04-02 08:38:32Z
 * @copyright
 * @author
 * @license Commercial
 */

class ome_mdl_dailycash extends dbeav_model{
	
	function get_total($date_line,$branch_id) {
		$total = array();
		$branch_where = '';
		if ($branch_id>0) {
			$branch_where = "and branch_id='{$branch_id}'";
		}
		
		$rs = $this->db->select("select count(*) as _count,sum(final_amount) as price from sdb_b2c_orders where shipping='门店收银' and pay_status != '4' and pay_status != '5' ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' ");
		$total['total_num'] = $rs[0]['_count']; //总共订单数
		$total['total_price'] = $rs[0]['price'];//订单总金额
		
		$rs = $this->db->select("select count(*) as _count,sum(final_amount) as price,payment from sdb_b2c_orders where shipping='门店收银' and pay_status != '4' and pay_status != '5' ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' GROUP BY payment");
		$total['total_xianjin_num'] = 0;//现金交易笔数
		$total['total_xianjin_price'] = 0;//现金交易钱数
		$total['total_shuaka_num'] = 0;//刷卡交易笔数
		$total['total_shuaka_price'] = 0;//刷卡交易钱数
		$total['total_deposit_num'] = 0;//帐号余额支付（包含余额+现金/刷卡）
		$total['total_deposit_price'] = 0;//帐号余额支付（包含余额+现金/刷卡）
		foreach ($rs as $key=>$value) {
			if ($value['payment'] == 'xianjin') {
				$total['total_xianjin_num'] = $value['_count'];
				$total['total_xianjin_price'] = $value['price'];
			} else if ($value['payment'] == 'shuaka') {
				$total['total_shuaka_num'] = $value['_count'];
				$total['total_shuaka_price'] = $value['price'];
			} else if ($value['payment'] == 'offline' || $value['payment'] == 'deposit') {
				$total['total_deposit_num'] += $value['_count'];
				$total['total_deposit_price'] += $value['price'];
			}
		}
		
		//订单优惠与没有优惠
		$rs = $this->db->select("select count(*) as _count,sum(final_amount) as price,payment from sdb_b2c_orders where shipping='门店收银' and pay_status != '4' and pay_status != '5' ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' and pmt_order>0");
		$total['pmt_num'] = $rs[0]['_count']; //优惠订单数
		$total['pmt_price'] = $rs[0]['price'];//优惠订单总金额
		
	//	echo "select count(*) as _count,sum(final_amount) as price,payment from sdb_b2c_orders where shipping='门店收银' and pay_status != '4' and pay_status != '5' ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' and pmt_order>0";
		
		$rs = $this->db->select("select count(*) as _count,sum(final_amount) as price,payment from sdb_b2c_orders where shipping='门店收银' and pay_status != '4' and pay_status != '5' ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' and pmt_order=0");
		$total['no_pmt_num'] = $rs[0]['_count']; //没优惠订单数
		$total['no_pmt_price'] = $rs[0]['price'];//没优惠订单总金额
		
		//刷卡充值
		$rs = $this->db->select("SELECT COUNT(*) AS _count,SUM(money) AS money,paymethod FROM  sdb_b2c_member_advance  WHERE FROM_UNIXTIME( mtime, '%Y-%m-%d' ) = '{$date_line}' ".$branch_where." and import_money > 0  GROUP BY paymethod");
		$total['advance_xianjin_num'] = 0;//现金充值笔数
		$total['advance_xianjin_money'] = 0;//现金充值金额
		$total['advance_shuaka_num'] = 0;//刷卡充值笔数
		$total['advance_shuaka_money'] = 0;//刷卡充值金额
		$total['advance_chongzhiquan_num'] = 0;//充值券充值笔数
		$total['advance_chongzhiquan_money'] = 0;//充值券充值金额
		
		foreach ($rs as $key=>$value) {
			if ($value['paymethod'] == 'xianjin') {
				$total['advance_xianjin_num'] = $value['_count'];
				$total['advance_xianjin_money'] = $value['money'];
			} else if ($value['paymethod'] == 'shuaka') {
				$total['advance_shuaka_num'] = $value['_count'];
				$total['advance_shuaka_money'] = $value['money'];
			} else if ($value['paymethod'] == 'chongzhiquan') {
				$total['advance_chongzhiquan_num'] = $value['_count'];
				$total['advance_chongzhiquan_money'] = $value['money'];
			}
		}
		
		$rs = $this->db->select("select count(*) as _count,sum(final_amount) as price,payment from sdb_b2c_orders where shipping='门店自提' and pay_status in ('1','2') and ship_status in ('1','2')  ".$branch_where." and FROM_UNIXTIME( createtime, '%Y-%m-%d' ) = '{$date_line}' ");
		
		$total['store_num'] = $rs[0]['_count']; //门店自提订单数
		$total['store_price'] = $rs[0]['price'];//门店自提订单总金额
		foreach ($total as $key=>$value) {
			if (empty($value)) {
				$total[$key] = 0;
			}
		}
		return $total;
	}
	
	
    
}
