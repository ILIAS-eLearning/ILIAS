<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'title' => array(
		'type' => 'text',
		'length' => '512',

	),
	'description' => array(
		'type' => 'text',
		'length' => '4000',

	),
	'core_position' => array(
		'type' => 'integer',
		'length' => '1',

	),

);
if (! $ilDB->tableExists('il_orgu_positions')) {
	$ilDB->createTable('il_orgu_positions', $fields);
	$ilDB->addPrimaryKey('il_orgu_positions', array( 'id' ));

	if (! $ilDB->sequenceExists('il_orgu_positions')) {
		$ilDB->createSequence('il_orgu_positions');
	}

}
?>
<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'over' => array(
		'type' => 'integer',
		'length' => '1',

	),
	'scope' => array(
		'type' => 'integer',
		'length' => '1',

	),
	'position_id' => array(
		'type' => 'integer',
		'length' => '1',

	),

);
if (! $ilDB->tableExists('il_orgu_authority')) {
	$ilDB->createTable('il_orgu_authority', $fields);
	$ilDB->addPrimaryKey('il_orgu_authority', array( 'id' ));

	if (! $ilDB->sequenceExists('il_orgu_authority')) {
		$ilDB->createSequence('il_orgu_authority');
	}

}
?>
<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'user_id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'position_id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'orgu_id' => array(
		'type' => 'integer',
		'length' => '8',

	),

);
if (! $ilDB->tableExists('il_orgu_ua')) {
	$ilDB->createTable('il_orgu_ua', $fields);
	$ilDB->addPrimaryKey('il_orgu_ua', array( 'id' ));

	if (! $ilDB->sequenceExists('il_orgu_ua')) {
		$ilDB->createSequence('il_orgu_ua');
	}

}
?>
<#1>
<?php
$fields = array(
	'operation_id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'operation_string' => array(
		'type' => 'text',
		'length' => '16',

	),
	'description' => array(
		'type' => 'text',
		'length' => '512',

	),
	'list_order' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'context_id' => array(
		'type' => 'integer',
		'length' => '8',

	),

);
if (! $ilDB->tableExists('il_orgu_operations')) {
	$ilDB->createTable('il_orgu_operations', $fields);
	$ilDB->addPrimaryKey('il_orgu_operations', array( 'operation_id' ));

	if (! $ilDB->sequenceExists('il_orgu_operations')) {
		$ilDB->createSequence('il_orgu_operations');
	}

}
?>
<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'context' => array(
		'type' => 'text',
		'length' => '16',

	),
	'parent_context_id' => array(
		'type' => 'integer',
		'length' => '8',

	),

);
if (! $ilDB->tableExists('il_orgu_op_contexts')) {
	$ilDB->createTable('il_orgu_op_contexts', $fields);
	$ilDB->addPrimaryKey('il_orgu_op_contexts', array( 'id' ));

	if (! $ilDB->sequenceExists('il_orgu_op_contexts')) {
		$ilDB->createSequence('il_orgu_op_contexts');
	}

}
?>
<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'context_id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'operations' => array(
		'type' => 'text',
		'length' => '2048',

	),
	'parent_id' => array(
		'type' => 'integer',
		'length' => '8',

	),
	'position_id' => array(
		'type' => 'integer',
		'length' => '8',

	),

);
if (! $ilDB->tableExists('il_orgu_permissions')) {
	$ilDB->createTable('il_orgu_permissions', $fields);
	$ilDB->addPrimaryKey('il_orgu_permissions', array( 'id' ));

	if (! $ilDB->sequenceExists('il_orgu_permissions')) {
		$ilDB->createSequence('il_orgu_permissions');
	}

}
?>

