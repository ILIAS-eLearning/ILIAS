<#1>
<?php

if( !$ilDB->tableExists('background_task') )
{
	$ilDB->createTable('background_task', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'handler' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false
		),
		'steps' => array(
			'type' => 'integer',
			'length' => 3,
			'notnull' => true,
			'default' => 0
		),
		'cstep' => array(
			'type' => 'integer',
			'length' => 3,
			'notnull' => false
		),
		'start_date' => array(
			'type' => 'timestamp'
		),
		'status' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => false
		),
		'params' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false
		)
	));
	
	$ilDB->addPrimaryKey('background_task', array('id'));
	$ilDB->createSequence('background_task');
}

?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>

