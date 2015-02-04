<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class importdata_func{

    public $data_dir;

    function __construct() {
        if(defined('SAP_GOODS_XML'))$this->data_dir = SAP_GOODS_XML;
        //$this->data_dir = '/media/psf/htdocs/testXml/ecstore';
    }

    function sync() {
        set_time_limit(0);
        if(!$this->data_dir) return false;

        $xml = kernel::single('site_utility_xml');
        $goodsModel = app::get('b2c')->model('goods');
        $brandModel = app::get('b2c')->model('brand');
        $productModel = app::get('b2c')->model('products');

        # 获取文件列表
        $files = $this->get_files($this->data_dir);

        # 循环读取xml里的数据
        foreach ((array)$files as $dir) {
            $xmlData = $xml->xml2arrayValues(file_get_contents($dir), 0);
            sort($xmlData);
            if (!array_key_exists(0, $xmlData[0]['items'])) {
                $tmpArr = $xmlData[0]['items'];
                unset($xmlData);
                $xmlData[0]['items'][] = $tmpArr;
            }

            # 批量查询bn是否存在
            $bnsArr = array();
            foreach ($xmlData[0]['items'] as $key => $value) {
                $bnsArr[] = $value['bn'];
            }
            $productItems = $productModel->getList('goods_id, bn', array('bn|in' => $bnsArr));
            foreach ($xmlData[0]['items'] as $k => $v) {
                foreach ($productItems as $value) {
                    if ($value['bn'] == $v['bn']) {
                        $xmlData[0]['items'][$k]['is_exist'] = 'true';
                        $xmlData[0]['items'][$k]['goods_id'] = $value['goods_id'];
                        continue;
                    }
                    
                }
            }

            #循环xml里的每一项
            foreach ($xmlData[0]['items'] as $value) {
                $goods = $msg = array();
                # 如果存在，根据modify_status的值对应更新
                if ($value['is_exist'] == 'true') {
                    if ($value['modify_status'] == 1) {
                        # 全部更新但价格，上下架不更新
                        $goods['name'] = $value['name'];
                        $goods['barcode'] = $value['barcode'];
                        if ($value['brand_id']) {
                            $brandModel = app::get('ome')->model('brand');
                            $brand_id = $brandModel->dump(array('brand_name|head' => $value['brand_id']), 'brand_id');
                            $goods['brand_id'] = $brand_id;
                        }
                        if ($value['price']) $goods['price'] = $value['price'];
                        if ($value['cost']) $goods['cost'] = $value['cost'];
                        if ($value['mktprice']) $goods['mktprice'] = $value['mktprice'];
                        $goods['weight'] = $value['weight'];
                        $goods['g_weight'] = 0;
                        $goods['intro'] = $value['spec_info'];
                        $goods['good_form'] = $value['good_form'];
                        $goods['taxrate'] = $value['taxrate'];
                    } elseif ($value['modify_status'] == 2) {
                        #2 商品状态变更
                        $goods['goods_status'] = $value['goods_status'];
                        if (in_array($value['goods_status'], array(1, 2, 3))) {
                            $goods['store'] = 9999;
                        } else {
                            $goods['store'] = 0;
                        }
                    } elseif ($value['modify_status'] == 3) {
                        #3 售价变更
                        if ($value['price']) $goods['price'] = $value['price'];
                        if ($value['cost']) $goods['cost'] = $value['cost'];
                        if ($value['mktprice']) $goods['mktprice'] = $value['mktprice'];
                        $goods['price_modify'] = $value['price_modify'];
                    }

                    # 禁售(下架)
                    if ($value['attrid'] == '1') {
                        $goods['marketable'] = 'false';
                    }

                    # 更新数据
                    $goods['modify_status'] = $value['modify_status'];
                    $goods['last_modify'] = time();
                    $updateArr = $goods;
                    unset($updateArr['store']);
                    if ($goodsModel->update($updateArr, array('goods_id' => $value['goods_id']))) {
                        $params = $goods;
                        $params['bn'] = $value['p_bn'];
                        unset($params['cat_id'], $params['goods_id'], $params['last_modify'], $params['intro'], $params['modify_status'], $params['goods_status'], $params['taxrate']);
                        if (!$productModel->update($params, array('bn' => $params['bn']))) {
                            $msg['error'] .= '更新product数据失败！';
                            file_put_contents('ecstore_product_error_arr.txt', serialize($params)."\r\n", FILE_APPEND);
                        }
                    } else {
                        $msg['error'] .= '更新goods数据失败！';
                        file_put_contents('ecstore_goods_error_arr.txt', serialize($updateArr)."\r\n", FILE_APPEND);
                    }
                } elseif($value['modify_status'] == '1') {
                    /* 不存在插入 */
                    $goods['category']['cat_id'] = 1;
                    $goods['type']['type_id'] = 1;
                    if ($value['brand_id']) {
                        $brandModel = app::get('ome')->model('brand');
                        $brand_id = $brandModel->dump(array('brand_name|head' => $value['brand_id']), 'brand_id');
                        $goods['brand']['brand_id'] = $brand_id;
                    }
                    $goods['name'] = $value['name'];
                    $goods['bn'] = $value['bn'];
                    $goods['serial_number'] = 'true';
                    $goods['status'] = 'false';
                    $goods['product'][0]['status'] = 'false';
                    $goods['product'][0]['price']['price']['price'] = $value['price'];
                    $goods['product'][0]['price']['cost']['price'] = $value['cost'];
                    $goods['product'][0]['price']['mktprice']['price'] = $value['mktprice'];
                    $goods['product'][0]['bn'] = $value['p_bn'];
                    $goods['product'][0]['barcode'] = $value['barcode'];
                    $goods['product'][0]['weight'] = $value['weight'];
                    $goods['product'][0]['g_weight'] = 0;
                    $goods['product'][0]['default'] = 1;
                    $goods['product'][0]['visibility'] = 'true';
                    $goods['product'][0]['store'] = 9999;
                    $goods['barcode'] = $value['barcode'];
                    $goods['good_form'] = $value['good_form'];
                    $goods['goods_status'] = $value['goods_status'];
                    $goods['modify_status'] = $value['modify_status'];
                    $goods['price_modify'] = $value['price_modify'];
                    $goods['taxrate'] = $value['taxrate'];
                    $insertArr = $goods;
                    $rs = $goodsModel->saveGoods($insertArr, null);
                    if (!$rs) {
                        $msg['error'] .= '插入数据失败！';
                    }
                } else {
                    # 没有主数据跳出循环，插入日志
                    $log_data[] = array(
                        'bn' => $value['bn'],
                        'result_type' => 'fail',
                        'msg' => '商品bn不存在！',
                    );
                    continue;
                }

                # 同步ecstore
                /*
                $goods['bn'] = $value['bn'];
                $goods['p_bn'] = $value['p_bn'];
                $sdf['data'] = serialize($goods);
                $sdf['sync_type'] = 'sync';
                $sdf['method']    = 'b2c.xmlupdate.sync';
                $rec = json_decode(kernel::single('ome_ecstore_base')->sync($sdf));

                if ($rec->rsp != 'succ') {
                    $msg['error'] .= '同步到ecstore数据失败！';
                }
                */

                $result_type = $msg ? 'fail' : 'succ';

                # 更新日志
                $log_data[] = array(
                    'bn' => $value['bn'],
                    'result_type' => $result_type,
                    'msg' => $msg['error'],
                );
            }

            # 写入日志
            $this->write_log(substr(strrchr($dir, '/'), 1), $log_data);

            # 移动文件
            $this->move_file($dir, $result_type);
        }
    }

    /* 写入日志 */
    function write_log($name, $log_data = array()) {
        if (!$name || $desc) return false;
        $logModel = app::get('apiactionlog')->model('xmlimport');
        $log_data = array(
            'file_name' => $name,
            'log_data' => $log_data,
            'last_modify' => time()
        );
        $logModel->save($log_data);
    }

    /* 列出目录下文件 */
    function get_files($dir, $type = 'xml') {
        $handle = opendir($dir);
        $fileArr = array();
        if ($handle) {
            while(($file = readdir($handle)) !== false){
                if(in_array($file, array('.', '..'))) continue;
                if(preg_match('/\.'.$type.'$/', $file, $matches)){
                    $key = filemtime($dir . '/' . $file)*1000+rand(100,999);
                    $fileArr[$key] = $dir . '/' . $file;
                }
            }
        }
        closedir($handle);

        # 排序
        ksort($fileArr);
        $keys = range(1, count($fileArr));
        $arr = array_combine($keys, $fileArr);

        return $arr;
    }

    /* 移动文件 */
    function move_file($file, $type = 'fail') {
        //copy($file, $this->data_dir.'/ecstore/'.substr(strrchr($file, '/'), 1)); # 复制到ecstore目录
        $dir_path = $this->data_dir.'/'.$dir_name.date('Ymd');
        if (!is_dir($dir_path))  mkdir($dir_path);
        rename($file, $dir_path.strrchr($file, '/'));
    }

    
}
