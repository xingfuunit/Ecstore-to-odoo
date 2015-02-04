<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * ��������
 * @package default
 * @author kxgsy163@163.com
 */
class timedbuy extends PHPUnit_Framework_TestCase
{
    
    function setUp() {
        // ����model
        
    }
    
    
    /*
     * test print
     */
    public function test_add_giftpackage()
    {
        #$this->markTestSkipped("���빺�ﳵ��Ʒ����");
        // ����һЩ����
        // ��Ʒ���빺�ﳵ
        $aTest = array (
                      'order_id' => '20110104166860',
                      'member_id' => '10',
                      'confirm' => 'N',
                      'status' => 'active',
                      'pay_status' => '0',
                      'ship_status' => '0',
                      'is_delivery' => 'Y',
                      'createtime' => 1294129593,
                      'last_modified' => 1294129593,
                      'memo' => '',
                      'ip' => '127.0.0.1',
                      'title' => '������ϸ����',
                      'shipping' => 
                      array (
                        'shipping_id' => '1',
                        'is_protect' => 'false',
                        'shipping_name' => '���',
                        'cost_shipping' => '10.00',
                        'cost_protect' => '0.00',
                      ),
                      'payinfo' => 
                      array (
                        'pay_app_id' => 'offline',
                        'cost_payment' => '0.00',
                      ),
                      'currency' => 'CNY',
                      'cur_rate' => '1.0000',
                      'is_tax' => 'false',
                      'tax_title' => NULL,
                      'weight' => 0,
                      'itemnum' => 1,
                      'cost_item' => '123.00',
                      'cost_tax' => '0.00',
                      'total_amount' => '133.00',
                      'cur_amount' => '133.00',
                      'pmt_goods' => '0.00',
                      'pmt_order' => '0.00',
                      'discount' => '0.00',
                      'payed' => '0.00',
                      'score_u' => 0,
                      'score_g' => 41,
                      'consignee' => 
                      array (
                        'name' => '123',
                        'addr' => '�Ϻ���¬����123',
                        'zip' => '',
                        'telephone' => '',
                        'mobile' => '123',
                        'email' => NULL,
                        'area' => 'mainland:�Ϻ�/�Ϻ���/¬����:24',
                        'r_time' => '������������ʱ���',
                        'meta' => 
                        array (
                        ),
                      ),
                      'order_objects' => 
                      array (
                        0 => 
                        array (
                          'order_id' => '20110104166860',
                          'obj_type' => 'goods',
                          'obj_alias' => '��Ʒ����',
                          'goods_id' => '96',
                          'bn' => 'P4CB2BB85A40AD',
                          'name' => '���ݼ� �ʲ��Ŷ���ʪ��Ĥ120G',
                          'price' => '59.000',
                          'quantity' => '1',
                          'amount' => '0.00',
                          'weight' => 0,
                          'score' => 41.3,
                          'order_items' => 
                          array (
                            0 => 
                            array (
                              'products' => 
                              array (
                                'product_id' => '548',
                              ),
                              'goods_id' => '96',
                              'order_id' => '20110104166860',
                              'item_type' => 'product',
                              'bn' => 'P4CB2BB85A40AD',
                              'name' => '���ݼ� �ʲ��Ŷ���ʪ��Ĥ120G',
                              'type_id' => '6',
                              'cost' => '0.000',
                              'quantity' => '1.00',
                              'sendnum' => 0,
                              'amount' => '123.00',
                              'score' => '41.30',
                              'price' => '123',
                              'weight' => '0.000',
                              'addon' => '',
                              'obj_id' => '82',
                              'item_id' => '84',
                            ),
                          ),
                          'obj_id' => '82',
                        ),
                      ),
                      'order_pmt' => 
                      array (
                        0 => 
                        array (
                          'pmt_id' => '1',
                          'order_id' => '20110104166860',
                          'pmt_type' => 'goods',
                          'pmt_amount' => -81.7,
                          'pmt_memo' => '��Ա�������ݻ�������Ʒ��С��һ�ݣ�',
                          'pmt_describe' => '��Ա�������ݻ�������Ʒ��С��һ��',
                        ),
                      ),
                    );
        $o = kernel::single('timedbuy_order_create');
        $o->generate($aTest);
        #$this->assertTrue(($aTest['goods_id'] == 1),"��Ʒ����2���빺�ﳵʧ��");
    }
    #End Func
    
}