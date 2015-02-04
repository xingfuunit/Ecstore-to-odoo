<?php
/**
 * ********************************************
 * Description   : 地区接口
 * Filename      : regions.php
 * Create time   : 2014-07-12 10:39:27
 * Last modified : 2014-07-12 10:40:06
 * License       : MIT, GPL
 * ********************************************
 */

class ectools_rpc_regions {

    /**
     * 需要同步的数据
     */
    public function sync($request, $rpcService) {
        // $cnt    = kernel::single('b2c_shop')->getShopBindStatus();
        // if ( $cnt > 0 ) {
            if ( $request['region_id']) {
                $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
                if ( $request['sync_type'] == 'del' ) {
                    if ( $request['region_id'] ) {
                        $obj_regions_op->toRemoveArea($request['region_id']);
                    }
                } else {
                    $ordernum   = isset($request['ordernum']) ? $request['ordernum'] : 50;
                    $package    = $request['package'] ? $request['package'] : 'mainland';
                    $data   = array (
                            'region_id' => $request['region_id'],
                            'local_name'=> $request['local_name'],
                            'ordernum'  => $ordernum,
                            'package'   => $package,
                            );
                    if ( $request['p_region_id'] ) {
                        $data['p_region_id']= $request['p_region_id'];
                    }

                    $obj_regions_op->replaceDlArea($data, $msg);
                }
                $ret    = array (
                        'code'  => 1,
                        'msg'   => app::get('ectools')->_('同步成功'),
                        );
            } else {
                $ret    = array (
                        'code'  => -1,
                        'msg'   => app::get('ectools')->_('请确保地区ID'),
                        );
            }
        // } else {
        //     $ret    = array (
        //             'code'  => -2,
        //             'msg'   => app::get('ectools')->_('店铺未绑定无需同步'),
        //             );
        // }
        if ( $ret['code'] < 0 ) {
            $rpcService->send_user_error($ret['code'], $ret['msg']);
        } 
        return $ret;
    }

}
