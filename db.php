<?php

// ---------------------------------------------------------------------------------------------------------------------

require_once 'include/inc.header.php';

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

/**
 * @var ilDB $ilDB
 */

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('qpl_questionpool', 'skill_service') )
{
	$ilDB->addTableColumn('qpl_questionpool', 'skill_service', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		'UPDATE qpl_questionpool SET skill_service = %s',
		array('integer'), array(0)
	);
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableExists('qpl_qst_skl_assigns') )
{
	$ilDB->createTable('qpl_qst_skl_assigns', array(
		'obj_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_skl_assigns', array('obj_fi', 'question_fi', 'skill_base_fi', 'skill_tref_fi'));

	if( $ilDB->tableExists('tst_skl_qst_assigns') )
	{
		$res = $ilDB->query("
			SELECT tst_skl_qst_assigns.*, tst_tests.obj_fi
			FROM tst_skl_qst_assigns
			INNER JOIN tst_tests ON test_id = test_fi
		");
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$ilDB->replace('qpl_qst_skl_assigns',
				array(
					'obj_fi' => array('integer', $row['obj_fi']),
					'question_fi' => array('integer', $row['question_fi']),
					'skill_base_fi' => array('integer', $row['skill_base_fi']),
					'skill_tref_fi' => array('integer', $row['skill_tref_fi'])
				),
				array(
					'skill_points' => array('integer', $row['skill_points'])
				)
			);
		}

		$ilDB->dropTable('tst_skl_qst_assigns');
	}
}

// ---------------------------------------------------------------------------------------------------------------------

$setting = new ilSetting();

if( !$setting->get('dbup_tst_skl_thres_mig_done', 0) )
{
	if( !$ilDB->tableExists('tst_threshold_tmp') )
	{
		$ilDB->createTable('tst_threshold_tmp', array(
			'test_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			),
			'obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			)
		));

		$ilDB->addPrimaryKey('tst_threshold_tmp', array('test_id'));
	}
	
	$res = $ilDB->query("
		SELECT DISTINCT tst_tests.test_id, obj_fi FROM tst_tests
		INNER JOIN tst_skl_thresholds ON test_fi = tst_tests.test_id
		LEFT JOIN tst_threshold_tmp ON tst_tests.test_id = tst_threshold_tmp.test_id
		WHERE tst_threshold_tmp.test_id IS NULL
	");
	
	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('tst_threshold_tmp',
			array('test_id' => array('integer', $row['test_id'])),
			array('obj_id' => array('integer', $row['obj_fi']))
		);
	}

	if( !$ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
	{
		$ilDB->addTableColumn('tst_skl_thresholds', 'tmp', array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => null
		));
	}

	$setting->set('dbup_tst_skl_thres_mig_done', 1);
}

// ---------------------------------------------------------------------------------------------------------------------

if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$stmtSelectSklPointSum = $ilDB->prepare(
		"SELECT skill_base_fi, skill_tref_fi, SUM(skill_points) points_sum FROM qpl_qst_skl_assigns
			WHERE obj_fi = ? GROUP BY skill_base_fi, skill_tref_fi", array('integer')
	);

	$stmtUpdatePercentThresholds = $ilDB->prepareManip(
		"UPDATE tst_skl_thresholds SET tmp = ROUND( ((threshold * 100) / ?), 0 )
			WHERE test_fi = ? AND skill_base_fi = ? AND skill_tref_fi = ?",
		array('integer', 'integer', 'integer', 'integer')
	);

	$res1 = $ilDB->query("
		SELECT DISTINCT test_id, obj_id FROM tst_threshold_tmp
		INNER JOIN tst_skl_thresholds ON test_fi = test_id
		WHERE tmp IS NULL
	");
	
	while( $row1 = $ilDB->fetchAssoc($res1) )
	{
		$res2 = $ilDB->execute($stmtSelectSklPointSum, array($row1['obj_id']));
		
		while( $row2 = $ilDB->fetchAssoc($res2) )
		{
			$ilDB->execute($stmtUpdatePercentThresholds, array(
				$row2['points_sum'], $row1['test_id'], $row2['skill_base_fi'], $row2['skill_tref_fi']
			));
		}
	}
}

// ---------------------------------------------------------------------------------------------------------------------

if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$ilDB->dropTable('tst_threshold_tmp');
}

// ---------------------------------------------------------------------------------------------------------------------

if( $ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
{
	$ilDB->manipulate("UPDATE tst_skl_thresholds SET threshold = tmp");
	$ilDB->dropTableColumn('tst_skl_thresholds', 'tmp');
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('qpl_qst_skl_assigns', 'eval_mode') )
{
	$ilDB->addTableColumn('qpl_qst_skl_assigns', 'eval_mode', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		"UPDATE qpl_qst_skl_assigns SET eval_mode = %s", array('text'), array('result')
	);
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableExists('qpl_qst_skl_sol_expr') )
{
	$ilDB->createTable('qpl_qst_skl_sol_expr', array(
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'order_index' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'expression' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => ''
		),
		'points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_skl_sol_expr', array(
		'question_fi', 'skill_base_fi', 'skill_tref_fi', 'order_index'
	));
}

// ---------------------------------------------------------------------------------------------------------------------
