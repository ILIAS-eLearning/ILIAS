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
				'notnull' => false,
				'default' => null
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

if( !$ilDB->tableExists('tst_rnd_quest_set_qpls') )
{
	$ilDB->createTable('tst_rnd_quest_set_qpls', array(            
			'test_fi' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'pool_fi' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'pool_title' => array(
				'type'     => 'text',
				'length'   => 128,
				'notnull' => false,
				'default' => null
			),            
			'pool_path' => array(
				'type'     => 'text',
				'length'   => 512,
				'notnull' => false,
				'default' => null
			),
			'pool_quest_count' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => false,
				'default' => null
			),
			'filter_tax_fi' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => false,
				'default' => null
			),
			'filter_node_fi' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => false,
				'default' => null
			),
			'quest_amount' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => false,
				'default' => null
			),
			'sequence_pos' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => false,
				'default' => null
			)
	));

	$ilDB->addPrimaryKey('tst_rnd_quest_set_qpls', array('test_fi', 'pool_fi'));
	
	$query = "
		SELECT		tst_test_random.test_fi,
					tst_test_random.questionpool_fi,
					tst_rnd_qpl_title.qpl_title,
					tst_test_random.num_of_q,
					tst_test_random.tstamp,
					tst_test_random.sequence
					
		FROM		tst_tests
		
		INNER JOIN	tst_test_random
		ON			tst_tests.test_id = tst_test_random.test_fi
		
		INNER JOIN	tst_rnd_qpl_title
		ON			tst_test_random.test_fi = tst_rnd_qpl_title.tst_fi
		AND			tst_test_random.questionpool_fi = tst_rnd_qpl_title.qpl_fi
		
		WHERE		question_set_type = %s
	";
	
	$res = $ilDB->queryF($query, array('text'), array('RANDOM_QUEST_SET'));
	
	$syncTimes = array();
	
	while( $row = $ilDB->fetchAssoc($res) )
	{
		if( !(int)$row['num_of_q'] )
		{
			$row['num_of_q'] = null;
		}
		
		$ilDB->insert('tst_rnd_quest_set_qpls', array(
				'test_fi' => array('integer', $row['test_fi']),
				'pool_fi' => array('integer', $row['questionpool_fi']),
				'pool_title' => array('text', $row['qpl_title']),
				'filter_tax_fi' => array('integer', null),
				'filter_node_fi' => array('integer', null),
				'quest_amount' => array('integer', $row['num_of_q']),
				'sequence_pos' => array('integer', $row['sequence'])
		));
		
		if( !is_array($syncTimes[$row['test_fi']]) )
		{
			$syncTimes[$row['test_fi']] = array();
		}
		
		$syncTimes[$row['test_fi']][] = $row['tstamp'];
	}
	
	foreach($syncTimes as $testId => $times)
	{
		$assumedSyncTS = max($times);
		
		$ilDB->update('tst_rnd_quest_set_cfg',
			array(
				'quest_sync_timestamp' => array('integer', $assumedSyncTS)
			),
			array(
				'test_fi' => array('integer', $testId)
			)	
		);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
