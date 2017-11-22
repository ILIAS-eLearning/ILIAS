<#1>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'identifier' => array(
		'notnull' => '1',
		'type' => 'text',
		'length' => '50',

	),
	'data_type' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'position' => array(
		'type' => 'integer',
		'length' => '3',

	),
	'is_standard_field' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'object_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
);
global $ilDB;
if (! $ilDB->tableExists('il_bibl_field')) {
	$ilDB->createTable('il_bibl_field', $fields);
	$ilDB->addPrimaryKey('il_bibl_field', array( 'id' ));

	if (! $ilDB->sequenceExists('il_bibl_field')) {
		$ilDB->createSequence('il_bibl_field');
	}

}
?>
<#9>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'field_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'object_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'filter_type' => array(
		'type' => 'integer',
		'length' => '1',

	),

);
if (! $ilDB->tableExists('il_bibl_filter')) {
	$ilDB->createTable('il_bibl_filter', $fields);
	$ilDB->addPrimaryKey('il_bibl_filter', array( 'id' ));

	if (! $ilDB->sequenceExists('il_bibl_filter')) {
		$ilDB->createSequence('il_bibl_filter');
	}

}
?>
