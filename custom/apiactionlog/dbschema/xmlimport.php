<?php
/**
*
 */
$db ['xmlimport'] = array (
		'columns' => array (
				'log_id' => array (
						'type' => 'number',
						'pkey' => true,
						'required' => true,
						'editable' => false,
						'extra' => 'auto_increment',
						'in_list' => false,
						'label' => app::get ( 'wmsync' )->_ ( '日志ID' ),
						'comment' => app::get ( 'wmsync' )->_ ( '日志ID' ) 
				),
				'file_name' => array (
					'type' => 'varchar(50)',
				     'label' => app::get('wmsync')->_('文件名称'),
				     'width' => 260,
				     'is_title' => true,
				     'required' => true,
				     'comment' => app::get('wmsync')->_('文件名称'),
				     'editable' => true,
				     'searchtype' => 'has',
				     'in_list' => true,
				     'default_in_list' => true,
    			),
    			// 'result_type' => array (
    			// 	'type' => array (
    			// 		'fail' => app::get('wmsync')->_('失败'),
				   //      'succ' => app::get('wmsync')->_('成功'),
      	// 			),
				   //   'default' => 'fail',
				   //   'is_title' => true,
				   //   'required' => true,
				   //   'label' => app::get('wmsync')->_('操作结果'),
				   //   'width' => 110,
				   //   'editable' => false,
				   //   'in_list' => true,
				   //   'default_in_list' => true,
				   //   'is_title' => true,
    			// ),
    			'log_data' => array (
				     'type' => 'serialize',
				     'comment' => app::get('wmsync')->_('说明'),
				     'editable' => false,
				     'width' => 300,
				     'label' => app::get('wmsync')->_('说明'),
				     'in_list' => false,
				),
    			'last_modify' => array (
				    'type' => 'last_modify',
				    'label' => app::get('wmsync')->_('更新时间'),
				    'width' => 140,
				    'editable' => false,
				    'in_list' => true,
				    'orderby' => true,
				    'default_in_list' => true,
				),
		),
		
		'index' => array (
				'ind_log_id' => array (
						'columns' => array (
								0 => 'log_id' 
						) 
				),
		),
		
		'engine' => 'innodb',
		'version' => '$Rev: 301 $',
		'comment' => app::get ( 'wmsync' )->_ ( '商品数据导入日志表' ) 
);
