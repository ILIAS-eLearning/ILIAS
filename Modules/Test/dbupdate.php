<?php

// ---------------------------------------------------------------------------------------------------------------------

chdir('../..');
include_once './include/inc.header.php';

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableExists('tst_rnd_quest_set_cfg') )
{
	$ilDB->createTable('tst_rnd_quest_set_cfg', array(            
			'test_fi' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'req_pools_homo_scored' => array(
				'type'     => 'integer',
				'length'   => 1,
				'notnull' => true,
				'default' => 0
			),
			'quest_amount_cfg_mode' => array(
				'type'     => 'text',
				'length'   => 16,
				'notnull' => false,
				'default' => null
			),            
			'quest_amount_per_test' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'quest_sync_timestamp' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			)
	));

	$ilDB->addPrimaryKey('tst_rnd_quest_set_cfg', array('test_fi'));
	
	$query = "SELECT test_id, random_question_count FROM tst_tests WHERE question_set_type = %s";
	$res = $ilDB->queryF($query, array('text'), array('RANDOM_QUEST_SET'));
	
	while( $row = $ilDB->fetchAssoc($res) )
	{
		if( $row['random_question_count'] > 0 )
		{
			$quest_amount_cfg_mode = 'TEST';
		}
		else
		{
			$quest_amount_cfg_mode = 'POOL';
		}
		
		$ilDB->insert('tst_rnd_quest_set_cfg', array(
				'test_fi' => array('integer', $row['test_id']),
				'req_pools_homo_scored' => array('integer', 0),
				'quest_amount_cfg_mode' => array('text', $quest_amount_cfg_mode),
				'quest_amount_per_test' => array('integer', $row['random_question_count'])
		));
	}
}

// ---------------------------------------------------------------------------------------------------------------------
