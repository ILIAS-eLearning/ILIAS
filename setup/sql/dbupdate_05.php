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
<#5441>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5442>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_personal_skill s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) ".
	" WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set))
{
	$user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0)
{
	$ilDB->manipulate("DELETE FROM skl_personal_skill WHERE "
		.$ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5443>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_assigned_material s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) ".
	" WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set))
{
	$user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0)
{
	$ilDB->manipulate("DELETE FROM skl_assigned_material WHERE "
		.$ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5444>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_profile_user s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) ".
	" WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set))
{
	$user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0)
{
	$ilDB->manipulate("DELETE FROM skl_profile_user WHERE "
		.$ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5445>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_user_skill_level s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) ".
	" WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set))
{
	$user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0)
{
	$ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE "
		.$ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5446>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_user_has_level s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) ".
	" WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set))
{
	$user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0)
{
	$ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
		.$ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5447>
<?php
//FIX 0020168: Delete orgus in Trash - Organisational units could not be restored from trash / imports lead to ambiguous import_ids
$set = $ilDB->query("SELECT * FROM object_data as obj inner join object_reference as ref on ref.obj_id = obj.obj_id and ref.deleted is not null where type = 'orgu'");
while ($rec = $ilDB->fetchAssoc($set))
{
	$ilDB->manipulate("DELETE FROM object_data where obj_id = ".$ilDB->quote($rec['obj_id'],'integer'));
	$ilDB->manipulate("DELETE FROM object_reference where obj_id = ".$ilDB->quote($rec['obj_id'],'integer'));
}
?>
<#5448>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$tpl_perms = [
	'il_grp_member' => [
		'participate'
	],
	'il_crs_member' => [
		'participate'
	],
	'il_grp_admin' => [
		'participate',
		'unparticipate',
		'manage_members',
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
		'edit_learning_progress'
	],
	'il_crs_admin' => [
		'participate',
		'unparticipate',
		'manage_members',
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
		'edit_learning_progress'
	],
	'il_crs_tutor' => [
		'participate',
		'unparticipate',
		'manage_members',
		'edit_learning_progress',
		'create_htlm',
		'create_iass',
		'create_copa',
		'create_svy',
		'create_svy',
		'create_lm',
		'create_exc',
		'create_tst',
		'create_sahs',
		'create_file'
	]
];

foreach($tpl_perms as $template=>$perms){
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
	ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', $op_ids,	ROLE_FOLDER_ID);
}
?>
<#5449>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5450>
<?php
if( !$ilDB->tableColumnExists('tst_tests', 'block_after_passed') )
{
	$ilDB->addTableColumn('tst_tests', 'block_after_passed', array(
		'type' => 'integer',
		'notnull' => false,
		'length' => 1,
		'default' => 0
	));
}
?>

<#5451>
<?php
if(!$ilDB->tableColumnExists('cal_entries','context_info'))
{
	$ilDB->addTableColumn(
		'cal_entries',
		'context_info',
		[
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		]
	);
}
?>
