<?php
class b2c_rpc_xmlupdate {

	public function sync($request, $rpcService) 
	{
		if (!$data = unserialize($request['data'])) return '没有数据';	
		$goodsModel = app::get('b2c')->model('goods');
		$productModel = app::get('b2c')->model('products');
		$data = unserialize($request['data']);
 		if ($data['goods_id']) {
			unset($data['goods_id']);
			if ($goods = $goodsModel->dump(array('bn' => $data['bn']), 'goods_id')) {
				$data['goods_id'] = $goods['goods_id'];
				if ($goodsModel->update($data, array('goods_id' => $goods['goods_id']))) {
					$params = $data;
					$params['bn'] = $data['p_bn'];
					$product = $productModel->dump(array('goods_id' => $params['goods_id'], 'bn' => $params['bn']), 'product_id');
					if ($product['product_id']) {
						unset($params['goods_id'], $params['last_modify'], $params['intro'], $params['modify_status'], $params['goods_status']);
						$params['product_id'] = $product['product_id'];
						if($productModel->update($params, array('product_id' => $product['product_id'])))
							return '更新数据成功';
					} else {
						$rpcService->send_user_error('error', '更新ecstore product数据失败！');
					}				
				} else {
					$rpcService->send_user_error('error', '更新ecstore goods数据失败！');
				}
			}
		} else {
			$goodsModel->save($data);
			return '更新数据成功';
		}
	}
}