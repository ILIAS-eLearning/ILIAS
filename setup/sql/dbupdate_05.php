<#5432>
<?php
$template = 'il_lso_admin';
$perms = [
	'create_htlm',
	'create_iass',
	'create_copa',
	'create_svy',
	'create_svy',
	'create_lm',
	'create_exc',
	'create_tst',
	'create_sahs',
	'create_file',
	'participate',
	'unparticipate',
	'edit_learning_progress',
	'manage_members',
	'copy'
];

$query = "SELECT obj_id FROM object_data"
	." WHERE object_data.type = " .$ilDB->quote('rolt', 'text')
	." AND title = " .$ilDB->quote($template,'text');
$result = $ilDB->query($query);
$rol_id = array_shift($ilDB->fetchAssoc($result));

$op_ids = [];
$query = "SELECT ops_id FROM rbac_operations"
	." WHERE operation IN ('"
	.implode("', '", $perms)
	."')";
$result = $ilDB->query($query);
while($row = $ilDB->fetchAssoc($result)) {
	$op_ids[] = $row['ops_id'];
}

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', $op_ids,	ROLE_FOLDER_ID);
?>

<#5433>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$template = 'il_lso_member';
$op_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('unparticipate');

$query = "SELECT obj_id FROM object_data"
	." WHERE object_data.type = " .$ilDB->quote('rolt', 'text')
	." AND title = " .$ilDB->quote($template,'text');
$result = $ilDB->query($query);
$rol_id = array_shift($ilDB->fetchAssoc($result));

ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', [$op_id], ROLE_FOLDER_ID);
?>
<#5434>
<?php
if ($ilDB->tableExists('license_data')) {
	$ilDB->dropTable('license_data');
}
?>
<#5435>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE module = %s',
	['text'],
	['license']
);
?>
<#5436>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5437>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5438>
<?php
require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::applyInitialPermissionGuideline('iass', true, false);
?>
<#5439>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5440>
<?php
$ilCtrlStructureReader->getStructure();
?>
