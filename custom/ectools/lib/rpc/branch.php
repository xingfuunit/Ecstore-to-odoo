<?php
class ectools_rpc_branch {

	public function sync($request, $rpcService) 
	{
		if ($request) {		
			unset($request['direct'], $request['date'], $request['sync_type'], $request['method'], $request['sign']);
			if(app::get('ome')->model('branch')->save($request)) {
				return '同步成功'; 
			}
		}
	}
}