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

<#6>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_usr_progress','completion_date')) {
	$db->addTableColumn(
			'prg_usr_progress',
			'completion_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
?>

<#7>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_settings','vq_period')) {
	$db->addTableColumn(
			'prg_settings',
			'vq_period',
			[
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => -1
			]
		);
}
if(!$db->tableColumnExists('prg_settings','vq_date')) {
	$db->addTableColumn(
			'prg_settings',
			'vq_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
if(!$db->tableColumnExists('prg_settings','vq_restart_period')) {
	$db->addTableColumn(
			'prg_settings',
			'vq_restart_period',
			[
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => -1
			]
		);
}
?>

<#8>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_usr_progress','vq_date')) {
	$db->addTableColumn(
			'prg_usr_progress',
			'vq_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
?>

<#9>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_usr_assignments','restart_date')) {
	$db->addTableColumn(
			'prg_usr_assignments',
			'restart_date',
			[
				'type' => 'timestamp',
				'notnull' => false
			]
		);
}
?>

<#10>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_usr_assignments','restarted_assignment_id')) {
	$db->addTableColumn(
			'prg_usr_assignments',
			'restarted_assignment_id',
			[
				'type' => 'integer',
				'notnull' => true,
				'default' => -1
			]
		);
}
?>

<#11>
<?php
$ilCtrlStructureReader->getStructure();
?>