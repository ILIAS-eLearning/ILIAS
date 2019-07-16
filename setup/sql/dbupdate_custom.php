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
;
?>

<#11>
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

<#12>
<?php
;
?>

<#13>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$type_id  = ilDBUpdateNewObjectType::addNewType('prgr', 'Study Programme Reference');

ilDBUpdateNewObjectType::addRBACOperations($type_id, [
	ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
	ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
	ilDBUpdateNewObjectType::RBAC_OP_READ,
	ilDBUpdateNewObjectType::RBAC_OP_WRITE,
	ilDBUpdateNewObjectType::RBAC_OP_DELETE,
	ilDBUpdateNewObjectType::RBAC_OP_COPY
]);

ilDBUpdateNewObjectType::addRBACCreate('create_prgr', 'Create Study Programme Reference', [
	'prg'
]);
?>

<#14>
<?php
if (!$ilDB->tableExists('prg_auto_content'))
{
	$ilDB->createTable('prg_auto_content', array(
		'prg_obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'cat_ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'last_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'last_edited' => array(
			'type' => 'timestamp',
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey('prg_auto_content', ['prg_obj_id', 'cat_ref_id']);
}
?>

<#15>
<?php
;
?>

<#16>
<?php
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_PRG, ilOrgUnitOperationContext::CONTEXT_OBJECT);
?>

<#17>
<?php
	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_VIEW_MEMBERS,
		'View Memberships of other users',
		ilOrgUnitOperationContext::CONTEXT_PRG
	);

	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
		'View learning progress of other users',
		ilOrgUnitOperationContext::CONTEXT_PRG
	);

	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN,
		'View Individual Plans of other users',
		ilOrgUnitOperationContext::CONTEXT_PRG
	);

	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN,
		'Edit Individual Plans of other users',
		ilOrgUnitOperationContext::CONTEXT_PRG
	);

	ilOrgUnitOperationQueries::registerNewOperation(
		ilOrgUnitOperation::OP_MANAGE_MEMBERS,
		'Manage Memberships of other users',
		ilOrgUnitOperationContext::CONTEXT_PRG
	);

?>

<#18>
<?php
if (!$ilDB->tableExists('prg_auto_membership'))
{
	$ilDB->createTable('prg_auto_membership', array(
		'prg_obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'source_type' => array(
			'type' => 'text',
			'length' => 8,
			'notnull' => true
		),
		'source_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'enabled' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'last_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'last_edited' => array(
			'type' => 'timestamp',
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey('prg_auto_membership', ['prg_obj_id', 'source_type', 'source_id']);
}
?>

<#19>
<?php
$ilCtrlStructureReader->getStructure();
?>


<#20>
<?php
global $DIC;
$db = $DIC['ilDB'];
if(!$db->tableColumnExists('prg_settings','access_ctrl_org_pos')) {
	$db->addTableColumn(
			'prg_settings',
			'access_ctrl_org_pos',
			[
				'type' => 'integer',
				'length' => 1,
				'notnull' => true,
				'default' => 0
			]
		);
}
?>

