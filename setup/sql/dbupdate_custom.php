<#1>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if($type_id && $tgt_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#2>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);

?>
<#3>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addCustomRBACOperation(
		'manage_materials',
		'Manage Materials',
		'object',
		6500
);
?>
<#4>
<?php
// Hallo
?>
<#5>
<?php
// Hallo 2
?>
<#6>
<?php
// Hallo 3
?>

<#7>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');

if($tgt_ops_id && $type_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}

?>
<#8>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
?>


<#9>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addCustomRBACOperation(
	'edit_metadata',
	'Edit Metadata',
	'object',
	5800
);
?>


<#10>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_metadata');

if($tgt_ops_id && $type_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}

?>
<#11>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_metadata');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
?>

<#13>
<?php
if(!$ilDB->tableColumnExists('adv_md_record','gpos'))
{
	$ilDB->addTableColumn('adv_md_record', 'gpos',
		array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		)
	);
}
?>
<#14>
<?php
if (!$ilDB->tableExists('adv_md_record_obj_ord'))
{
	$ilDB->createTable(
		'adv_md_record_obj_ord',
		[
			'record_id' => [
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			],
			'obj_id' => [
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			],
			'position' => [
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			]
		]
	);
	$ilDB->addPrimaryKey(
		'adv_md_record_obj_ord',
		[
			'record_id',
			'obj_id'
		]
	);
}
?>

<#15>
<?php
if(!$ilDB->tableColumnExists('event', 'show_members'))
{
	$ilDB->addTableColumn(
			'event',
			'show_members',
			[
				"notnull" => true,
				"length" => 1,
				"type" => "integer",
				'default' => 0
			]
	);
}
?>

<#16>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#17>
<?php
if(!$ilDB->tableColumnExists('event', 'mail_members'))
{
	$ilDB->addTableColumn(
		'event',
		'mail_members',
		[
			"notnull" => true,
			"length" => 1,
			"type" => "integer",
			'default' => 0
		]
	);
}
?>

