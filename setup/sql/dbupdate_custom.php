<#1>
<?php
$fields = array(
	'id'             => array(
		'type'   => 'integer',
		'length' => '8',

	),
	'identification' => array(
		'type'   => 'text',
		'length' => '64',

	),
	'language_key'   => array(
		'type'   => 'text',
		'length' => '8',

	),
	'translation'    => array(
		'type'   => 'text',
		'length' => '4000',

	),

);
if (!$ilDB->tableExists('il_mm_translation')) {
	$ilDB->createTable('il_mm_translation', $fields);
	$ilDB->addPrimaryKey('il_mm_translation', array('id'));

	if (!$ilDB->sequenceExists('il_mm_translation')) {
		$ilDB->createSequence('il_mm_translation');
	}
}
?>
<#2>
<?php
$fields = array(
	'provider_class' => array(
		'type'   => 'text',
		'length' => '256',

	),
	'purpose'        => array(
		'type'   => 'text',
		'length' => '256',

	),
	'dynamic'        => array(
		'type'   => 'integer',
		'length' => '1',

	),

);
if (!$ilDB->tableExists('il_gs_providers')) {
	$ilDB->createTable('il_gs_providers', $fields);
	$ilDB->addPrimaryKey('il_gs_providers', array('provider_class'));
}
?>
<#3>
<?php
$fields = array(
	'identification' => array(
		'type'   => 'text',
		'length' => '64',

	),
	'provider_class' => array(
		'type'   => 'text',
		'length' => '256',

	),
	'active'         => array(
		'type'   => 'integer',
		'length' => '1',

	),

);
if (!$ilDB->tableExists('il_gs_identifications')) {
	$ilDB->createTable('il_gs_identifications', $fields);
	$ilDB->addPrimaryKey('il_gs_identifications', array('identification'));
}
?>
<#4>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addAdminNode('mme', 'Main Menu');

$ilCtrlStructureReader->getStructure();
?>
