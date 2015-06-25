<?php

class mobileapi_ctl_admin_sales_goods extends desktop_controller{

    var $workground = 'mobileapi.wrokground.mobileapi';


    function index(){
        $this->finder('mobileapi_mdl_sales_goods',array(
            'title'=>app::get('b2c')->_('移动商品促销'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加促销'),'href'=>'index.php?app=mobileapi&ctl=admin_sales_goods&act=add','target'=>'_blank'),
            )
            ));
    }

    public function add(){
    	$this->pagedata['rule']['sort_order'] = 50;
        $this->singlepage('admin/sales/detail.html');
    }

    public function edit($rule_id){
        $oGoodsPromotion = $this->app->model('sales_goods');
        $aRule = $oGoodsPromotion->dump($rule_id,'*','default');

        $this->pagedata['rule'] = $aRule;
        $this->singlepage('admin/sales/detail.html');
    }

    public function toAdd(){
        $this->begin();
        $aData = $this->_prepareRuleData($_POST);
        $oGoodsPromotion = $this->app->model('sales_goods');
        $bResult = $oGoodsPromotion->save($aData);
        $this->end($bResult,app::get('b2c')->_('操作成功,'));
    }

    private function _prepareRuleData($aData){
        $aResult = $aData['rule'];

        if( !$aResult['name'] ) $this->end( false,'促销名称不能为空！' );

        // 开始时间&结束时间
        foreach ($aData['_DTIME_'] as $val) {
            $temp['from_time'][] = $val['from_time'];
            $temp['to_time'][] = $val['to_time'];
        }
        $aResult['from_time'] = strtotime($aData['from_time'].' '. implode(':', $temp['from_time']));
        $aResult['to_time'] = strtotime($aData['to_time'].' '. implode(':', $temp['to_time']));
        if( $aResult['to_time']<=$aResult['from_time'] ) $this->end( false,'结束时间不能小于或等于开始时间！' );

        // 创建时间 (修改时不处理)
        //if(empty($aResult['rule_id']))
        $aResult['create_time'] = time();

        ////////////////////////////// 过滤规则 //////////////////////////////////
        if (!$aResult['conditions']) {
            $this->end( false,'请选择促销商品！' );
        }
        $aResult['conditions'] = $aResult['conditions'];

        if( empty($aResult['sort_order']) && $aResult['sort_order']!==0 )
            $aResult['sort_order'] = 50;
        if( $aResult['sort_order'] ) $aResult['sort_order'] = (int)$aResult['sort_order'];

        return $aResult;
    }

}
