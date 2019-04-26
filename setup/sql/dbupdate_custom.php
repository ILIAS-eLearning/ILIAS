<#1>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];
$db->addTableColumn(
		'prg_settings',
		'deadline_period',
		[
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		]
	);
$db->addTableColumn(
		'prg_settings',
		'deadline_date',
		[
			'type' => 'timestamp',
			'notnull' => false
		]
	);
?>