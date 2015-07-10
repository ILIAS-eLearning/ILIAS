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
