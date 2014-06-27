<?php

// ---------------------------------------------------------------------------------------------------------------------

include_once './include/inc.header.php';

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('tst_tests', 'skill_service') )
{
	$ilDB->addTableColumn('tst_tests', 'skill_service', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		'UPDATE tst_tests SET skill_service = %s',
		array('integer'), array(0)
	);
}

if( !$ilDB->tableExists('tst_skl_qst_assigns') )
{
	$ilDB->createTable('tst_skl_qst_assigns', array(
		'test_fi' => array(
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

	$ilDB->addPrimaryKey('tst_skl_qst_assigns', array('test_fi', 'question_fi', 'skill_base_fi', 'skill_tref_fi'));
}

if( !$ilDB->tableExists('tst_skl_thresholds') )
{
	$ilDB->createTable('tst_skl_thresholds', array(
		'test_fi' => array(
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
		'skill_level_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'threshold' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_skl_thresholds', array('test_fi', 'skill_base_fi', 'skill_tref_fi', 'skill_level_fi'));
}

if( !$ilDB->tableColumnExists('tst_active', 'last_finished_pass') )
{
	$ilDB->addTableColumn('tst_active', 'last_finished_pass', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}

// ---------------------------------------------------------------------------------------------------------------------

echo '[ OK ]';
exit;

// ---------------------------------------------------------------------------------------------------------------------
