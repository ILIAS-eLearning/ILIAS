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
if(!$db->tableColumnExists('prg_settings','deadline_period')) {
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
}
if(!$db->tableColumnExists('prg_settings','deadline_date')) {
	$db->addTableColumn(
			'prg_settings',
			'deadline_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
?>

<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_usr_progress','assignment_date')) {
	$db->addTableColumn(
			'prg_usr_progress',
			'assignment_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
?>

<#5>
<?php
global $DIC;
$db = $DIC['ilDB'];
if($db->tableColumnExists('prg_usr_progress','assignment_date') && $db->tableColumnExists('prg_usr_assignments','last_change')) {
	$db->manipulate(
		'UPDATE prg_usr_progress'
		.'	JOIN prg_usr_assignments'
		.'		ON prg_usr_assignments.id = prg_usr_progress.assignment_id'
		.'	SET prg_usr_progress.assignment_date = prg_usr_assignments.last_change'
	);
}
?>
