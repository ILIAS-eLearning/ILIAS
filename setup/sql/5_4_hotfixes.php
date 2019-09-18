<#1>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CodeInline", "code_inline", "code",
	array());
?>
<#2>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("Code", "code_block", "pre",
	array());
?>
<#3>
<?php
$ilDB->update("style_data", array(
	"uptodate" => array("integer", 0)
), array(
	"1" => array("integer", 1)
));
?>
<#4>
<?php
$template = 'il_lso_admin';
$perms = [
	'create_htlm',
	'create_iass',
	'create_copa',
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

<#5>
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
<#6>
<?php
if ($ilDB->tableExists('license_data')) {
	$ilDB->dropTable('license_data');
}
?>
<#7>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE module = %s',
	['text'],
	['license']
);
?>
<#8>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#9>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#10>
<?php
require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::applyInitialPermissionGuideline('iass', true, false);
?>
<#11>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#14>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#15>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#16>
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
<#17>
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
<#18>
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
<#19>
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
<#20>
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
<#21>
<?php
$set = $ilDB->query("SELECT * FROM object_data as obj inner join object_reference as ref on ref.obj_id = obj.obj_id and ref.deleted is not null where type = 'orgu'");
while ($rec = $ilDB->fetchAssoc($set))
{
	$ilDB->manipulate("DELETE FROM object_data where obj_id = ".$ilDB->quote($rec['obj_id'],'integer'));
	$ilDB->manipulate("DELETE FROM object_reference where obj_id = ".$ilDB->quote($rec['obj_id'],'integer'));
}
?>

<#22>
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

<#23>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#24>
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
<#25>
<?php
// Create migration table
if (!$ilDB->tableExists('frm_thread_tree_mig')) {
	$fields = [
		'thread_id' => [
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		]
	];

	$ilDB->createTable('frm_thread_tree_mig', $fields);
	$ilDB->addPrimaryKey('frm_thread_tree_mig', ['thread_id']);
	$GLOBALS['ilLog']->info(sprintf(
		'Created thread migration table: frm_thread_tree_mig'
	));
}
?>
<#26>
<?php
$query = "
	SELECT frmpt.thr_fk
	FROM frm_posts_tree frmpt
	INNER JOIN frm_posts fp ON fp.pos_pk = frmpt.pos_fk
	WHERE frmpt.parent_pos = 0
	GROUP BY frmpt.thr_fk
	HAVING COUNT(frmpt.fpt_pk) > 1
";
$ignoredThreadIds = [];
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
	$ignoredThreadIds[$row['thr_fk']] = $row['thr_fk'];
}

$step = 26;

$query = "
	SELECT fp.*, fpt.fpt_pk, fpt.thr_fk, fpt.lft, fpt.rgt, fpt.fpt_date
	FROM frm_posts_tree fpt
	INNER JOIN frm_posts fp ON fp.pos_pk = fpt.pos_fk
	LEFT JOIN frm_thread_tree_mig ON frm_thread_tree_mig.thread_id = fpt.thr_fk
	WHERE fpt.parent_pos = 0 AND fpt.depth = 1 AND frm_thread_tree_mig.thread_id IS NULL
";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
	$GLOBALS['ilLog']->info(sprintf(
		"Started migration of thread with id %s", $row['thr_fk']
	));
	if (isset($ignoredThreadIds[$row['thr_fk']])) {
		$GLOBALS['ilLog']->warning(sprintf(
			"Cannot migrate forum tree for thread id %s in database hotfix step %s", $row['thr_fk'], $step
		));
		continue;
	}

	// Create space for a new root node, increment depth of all nodes, increment lft and rgt values
	$ilDB->manipulateF("
			UPDATE frm_posts_tree
			SET
				lft = lft + 1,
				rgt = rgt + 1,
				depth = depth + 1
			WHERE thr_fk = %s
		",
		['integer'],
		[$row['thr_fk']]
	);
	$GLOBALS['ilLog']->info(sprintf(
		"Created gaps in tree for thread with id %s in database hotfix step %s", $row['thr_fk'], $step
	));

	// Create a posting as new root
	$postId = $ilDB->nextId('frm_posts');
	$ilDB->insert('frm_posts', array(
		'pos_pk'		=> array('integer', $postId),
		'pos_top_fk'	=> array('integer', $row['pos_top_fk']),
		'pos_thr_fk'	=> array('integer', $row['pos_thr_fk']),
		'pos_display_user_id'	=> array('integer', $row['pos_display_user_id']),
		'pos_usr_alias'	=> array('text', $row['pos_usr_alias']),
		'pos_subject'	=> array('text', $row['pos_subject']),
		'pos_message'	=> array('clob', $row['pos_message']),
		'pos_date'		=> array('timestamp', $row['pos_date']),
		'pos_update'	=> array('timestamp', null),
		'update_user'	=> array('integer', 0),
		'pos_cens'		=> array('integer', 0),
		'notify'		=> array('integer', 0),
		'import_name'	=> array('text', (string)$row['import_name']),
		'pos_status'	=> array('integer', 1),
		'pos_author_id' => array('integer', (int)$row['pos_author_id']),
		'is_author_moderator' => array('integer', $row['is_author_moderator']),
		'pos_activation_date' => array('timestamp', $row['pos_activation_date'])
	));
	$GLOBALS['ilLog']->info(sprintf(
		"Created new root posting with id %s in thread with id %s in database hotfix step %s",
		$postId, $row['thr_fk'], $step
	));

	// Insert the new root and, set dept = 1, lft = 1, rgt = <OLR_ROOT_RGT> + 2
	$nextId = $ilDB->nextId('frm_posts_tree');
	$ilDB->manipulateF('
		INSERT INTO frm_posts_tree
		(
			fpt_pk,
			thr_fk,
			pos_fk,
			parent_pos,
			lft,
			rgt,
			depth,
			fpt_date
		) VALUES (%s, %s, %s, %s,  %s,  %s, %s, %s)',
		['integer','integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'timestamp'],
		[$nextId, $row['thr_fk'], $postId, 0, 1, $row['rgt'] + 2, 1, $row['fpt_date']]
	);
	$GLOBALS['ilLog']->info(sprintf(
		"Created new tree root with id %s in thread with id %s in database hotfix step %s",
		$nextId, $row['thr_fk'], $step
	));

	// Set parent_pos for old root
	$ilDB->manipulateF("
			UPDATE frm_posts_tree
			SET
				parent_pos = %s
			WHERE thr_fk = %s AND fpt_pk = %s
		",
		['integer', 'integer', 'integer'],
		[$nextId, $row['thr_fk'], $row['fpt_pk']]
	);
	$GLOBALS['ilLog']->info(sprintf(
		"Set parent to %s for posting with id %s in thread with id %s in database hotfix step %s",
		$nextId, $row['fpt_pk'], $row['thr_fk'], $step
	));

	// Mark as migrated
	$ilDB->insert('frm_thread_tree_mig', array(
		'thread_id' => array('integer', $row['thr_fk'])
	));
}
?>
<#27>
<?php
// Drop migration table
if ($ilDB->tableExists('frm_thread_tree_mig')) {
	$ilDB->dropTable('frm_thread_tree_mig');
	$GLOBALS['ilLog']->info(sprintf(
		'Dropped thread migration table: frm_thread_tree_mig'
	));
}
?>
<#28>
<?php
// Add new index
if (!$ilDB->indexExistsByFields('frm_posts_tree', ['parent_pos'])) {
	$ilDB->addIndex('frm_posts_tree', ['parent_pos'], 'i3');
}
?>
<#29>
<?php
if(!$ilDB->tableExists('lso_activation'))
{
	$ilDB->createTable('lso_activation', array(
		'ref_id' => array(
			"type"    => "integer",
			"length"  => 4,
			'notnull' => true
		),
		'online' => array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		),
		'activation_start' => array(
			'type' => 'timestamp',
			"notnull" => false
		),
		'activation_end' => array(
			'type' => 'timestamp',
			"notnull" => false
		)
	));
	$ilDB->addPrimaryKey("lso_activation", array("ref_id"));
}
?>
<#30>
<?php
if ($ilDB->tableColumnExists('lso_settings', 'online'))
{
	$ilDB->dropTableColumn('lso_settings', 'online');
}
?>
<#31>
<?php
if(!$ilDB->tableColumnExists('lso_activation', 'effective_online')) {
	$ilDB->addTableColumn('lso_activation', 'effective_online',
		array(
			"type"    => "integer",
			"notnull" => true,
			"length"  => 1,
			"default" => 0
		)
	);
}
?>
<#32>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::updateOperationOrder('participate', 1010);
ilDBUpdateNewObjectType::updateOperationOrder('unparticipate', 1020);
?>
<#33>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#34>
<?php
/**
 * @var $ilDB ilDBInterface
 */
$ilDB->modifyTableColumn('il_gs_identifications', 'identification', ['length' => 255]);
$ilDB->modifyTableColumn('il_mm_items', 'identification', ['length' => 255]);
?>
<#35>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['context_id'])) {
	$ilDB->addIndex('il_orgu_permissions', array( 'context_id' ), 'co');
}
?>
<#36>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['position_id'])) {
	$ilDB->addIndex('il_orgu_permissions', array( 'position_id' ), 'po');
}
?>
<#37>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
	$ilDB->modifyTableColumn('il_orgu_permissions', 'operations', array( "length" => 256 ));
}
?>
<#38>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id'])) {
	$ilDB->addIndex('il_orgu_ua', array( 'position_id' ), 'pi');
}
?>
<#39>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['user_id'])) {
	$ilDB->addIndex('il_orgu_ua', array( 'user_id' ), 'ui');
}
?>
<#40>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['orgu_id'])) {
	$ilDB->addIndex('il_orgu_ua', array( 'orgu_id' ), 'oi');
}
?>
<#41>
<?php
//$ilDB->addIndex('il_orgu_permissions', array('operations'), 'oi');
?>
<#42>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', [ 'position_id', 'orgu_id'])) {
	$ilDB->addIndex('il_orgu_ua', array( 'position_id', 'orgu_id' ), 'po');
}
?>
<#43>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', [ 'position_id','user_id'])) {
	$ilDB->addIndex('il_orgu_ua', array( 'position_id', 'user_id' ), 'pu');
}
?>
<#44>
<?php
//$ilDB->addIndex('il_orgu_permissions', array('operations','parent_id'), 'op');
?>
<#45>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$query = "SELECT obj_id FROM object_data"
	." WHERE object_data.type = " .$ilDB->quote('rolt', 'text')
	." AND title = " .$ilDB->quote('il_lso_member','text');
$result = $ilDB->query($query);
$rol_id_member = array_shift($ilDB->fetchAssoc($result));

$query = "SELECT obj_id FROM object_data"
	." WHERE object_data.type = " .$ilDB->quote('rolt', 'text')
	." AND title = " .$ilDB->quote('il_lso_admin','text');
$result = $ilDB->query($query);
$rol_id_admin = array_shift($ilDB->fetchAssoc($result));

$op_ids = [];
$query = "SELECT operation, ops_id FROM rbac_operations";
$result = $ilDB->query($query);
while($row = $ilDB->fetchAssoc($result)) {
	$op_ids[$row['operation']] = $row['ops_id'];
}

$types = [
	'copa',
	'exc',
	'file',
	'htlm',
	'sahs',
	'lm',
	'svy',
	'tst'
];

$member_ops = [
	$op_ids['visible'],
	$op_ids['read'],
];
$admin_ops = [
	$op_ids['visible'],
	$op_ids['read'],
	$op_ids['edit_learning_progress'],
	$op_ids['read_learning_progress']
];

foreach ($types as $type) {
	ilDBUpdateNewObjectType::setRolePermission($rol_id_member, $type, $member_ops, ROLE_FOLDER_ID);
	ilDBUpdateNewObjectType::setRolePermission($rol_id_admin, $type, $admin_ops, ROLE_FOLDER_ID);
}

$type_perms = [
	'iass' => [
		$op_ids['visible'],
		$op_ids['read'],
		$op_ids['manage_members'],
		$op_ids['edit_members'],
		$op_ids['edit_learning_progress'],
		$op_ids['read_learning_progress']
	],
	'exc' => [
		$op_ids['edit_submissions_grades']
	],
	'svy' => [
		$op_ids['invite'],
		$op_ids['read_results']
	],
	'tst' => [
		$op_ids['tst_results'],
		$op_ids['tst_statistics']
	]
];

foreach ($type_perms as $type => $ops) {
	ilDBUpdateNewObjectType::setRolePermission($rol_id_admin, $type, $ops, ROLE_FOLDER_ID);
}
?>

<#46>
<?php
if(!$ilDB->tableColumnExists('lso_activation', 'activation_start_ts')) {
    $ilDB->addTableColumn(
                          'lso_activation',
                          'activation_start_ts',
                          array(
                                "type"    => "integer",
                                "notnull" => false,
                                "length"  => 4
                          )
    );
}
?>
<#47>
<?php
if(!$ilDB->tableColumnExists('lso_activation', 'activation_end_ts')) {
    $ilDB->addTableColumn(
                          'lso_activation',
                          'activation_end_ts',
                          array(
                                "type"    => "integer",
                                "notnull" => false,
                                "length"  => 4
                          )
    );
}
?>
<#48>
<?php
if(
	$ilDB->tableColumnExists('lso_activation','activation_start_ts') &&
	$ilDB->tableColumnExists('lso_activation','activation_start')
) {
	$ilDB->manipulate(
	                  'UPDATE lso_activation'
	                  .'	SET activation_start_ts = UNIX_TIMESTAMP(activation_start)'
	                  .'	WHERE activation_start IS NOT NULL'
	);
}
?>
<#49>
<?php
if(
	$ilDB->tableColumnExists('lso_activation','activation_end_ts') &&
	$ilDB->tableColumnExists('lso_activation','activation_end')
) {
	$ilDB->manipulate(
	                  'UPDATE lso_activation'
	                  .'	SET activation_end_ts = UNIX_TIMESTAMP(activation_end)'
	                  .'	WHERE activation_end IS NOT NULL'
	);
}
?>
<#50>
<?php
if($ilDB->tableColumnExists('lso_activation','activation_start')) {
	$ilDB->dropTableColumn("lso_activation", "activation_start");
}
?>
<#51>
<?php
if($ilDB->tableColumnExists('lso_activation','activation_end')) {
	$ilDB->dropTableColumn("lso_activation", "activation_end");
}
?>
<#52>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if($lp_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('lp_other_users', 'See learning progress overview of other users', 'object', 3595);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $new_ops_id);
	}
}

?>
<#53>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#54>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#55>
<?php
if (!$ilDB->tableColumnExists("post_conditions", "condition_operator")) {
	$ilDB->addTableColumn("post_conditions", "condition_operator", [
			"type" => "text",
			"notnull" => false,
		 	"length" => 32,
		 	"fixed" => false
	]);
}

if ($ilDB->tableColumnExists("post_conditions", "condition_type")) {
	$ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'always' WHERE condition_type = 0");
	$ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'finished' WHERE condition_type = 1");
	$ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'passed' WHERE condition_type = 2");
	$ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'failed' WHERE condition_type = 3");

	$ilDB->dropPrimaryKey('post_conditions');
	$ilDB->addPrimaryKey('post_conditions', ['ref_id', 'condition_operator', 'value']);
	$ilDB->dropTableColumn('post_conditions', 'condition_type');
}
?>
<#56>
<?php

$ilDB->manipulate("TRUNCATE TABLE il_mm_items");

$ilDB->insert("il_mm_items", array('identification' => array('', 'ilAdmGlobalScreenProvider|adm'), 'active' => array('', '1'), 'position' => array('', '3'), 'parent_identification' => array('', '')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilAdmGlobalScreenProvider|adm_content'), 'active' => array('', '1'), 'position' => array('', '0'), 'parent_identification' => array('', 'ilAdmGlobalScreenProvider|adm')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilBookmarkGlobalScreenProvider|mm_pd_bookm'), 'active' => array('', '1'), 'position' => array('', '3'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilCalendarGlobalScreenProvider|mm_pd_cal'), 'active' => array('', '1'), 'position' => array('', '4'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilContactGlobalScreenProvider|mm_pd_contacts'), 'active' => array('', '1'), 'position' => array('', '9'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilMailGlobalScreenProvider|mm_pd_mail'), 'active' => array('', '1'), 'position' => array('', '8'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilNewsGlobalScreenProvider|mm_pd_news'), 'active' => array('', '1'), 'position' => array('', '10'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilNotesGlobalScreenProvider|mm_pd_notes'), 'active' => array('', '1'), 'position' => array('', '11'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilPDGlobalScreenProvider|desktop'), 'active' => array('', '1'), 'position' => array('', '1'), 'parent_identification' => array('', '')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilPDGlobalScreenProvider|mm_pd_achiev'), 'active' => array('', '1'), 'position' => array('', '7'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilPDGlobalScreenProvider|mm_pd_crs_grp'), 'active' => array('', '1'), 'position' => array('', '2'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilPDGlobalScreenProvider|mm_pd_sel_items'), 'active' => array('', '1'), 'position' => array('', '1'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilPrtfGlobalScreenProvider|mm_pd_port'), 'active' => array('', '1'), 'position' => array('', '6'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilRepositoryGlobalScreenProvider|last_visited'), 'active' => array('', '1'), 'position' => array('', '0'), 'parent_identification' => array('', 'ilRepositoryGlobalScreenProvider|rep')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilRepositoryGlobalScreenProvider|rep'), 'active' => array('', '1'), 'position' => array('', '2'), 'parent_identification' => array('', '')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilRepositoryGlobalScreenProvider|rep_main_page'), 'active' => array('', '1'), 'position' => array('', '0'), 'parent_identification' => array('', 'ilRepositoryGlobalScreenProvider|rep')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilStaffGlobalScreenProvider|mm_pd_mst'), 'active' => array('', '1'), 'position' => array('', '12'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
$ilDB->insert("il_mm_items", array('identification' => array('', 'ilWorkspaceGlobalScreenProvider|mm_pd_wsp'), 'active' => array('', '1'), 'position' => array('', '5'), 'parent_identification' => array('', 'ilPDGlobalScreenProvider|desktop')));
?>
<#57>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
	$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
	ilDBUpdateNewObjectType::deleteRBACOperation($lp_type_id, $ops_id);
}

?>
<#58>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
	$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
	ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ops_id);
}

?>
<#59>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#60>
<?php
	$ilDB->dropPrimaryKey('post_conditions');
	$ilDB->addPrimaryKey('post_conditions', ['ref_id', 'condition_operator', 'value']);
?>

<#61>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
ilDBUpdateNewObjectType::deleteRBACOperation("lso", $ops_id);

?>
<#62>
<?php
if($ilDB->tableColumnExists("map_area", "href")) {
	$field = array(
		'type' 		=> 'text',
		'length' 	=> 800,
		'notnull' 	=> false
	);

	$ilDB->modifyTableColumn("map_area", "href", $field);
}
?>
<#63>
<?php

$tempTableName = 'tmp_tst_qst_fixparent';

$tempTableFields = array(
	'qst_id' => array(
		'type' => 'integer',
		'notnull' => true,
		'length' => 4,
		'default' => 0
	),
	'tst_obj_id' => array(
		'type' => 'integer',
		'notnull' => true,
		'length' => 4,
		'default' => 0
	),
	'qpl_obj_id' => array(
		'type' => 'integer',
		'notnull' => true,
		'length' => 4,
		'default' => 0
	)
);

$brokenFixedTestQuestionsQuery = "
    SELECT qq.question_id qst_id, t.obj_fi tst_obj_id, qq.obj_fi qpl_obj_id
    FROM tst_tests t
    INNER JOIN tst_test_question tq
    ON t.test_id = tq.test_fi
    INNER JOIN qpl_questions qq
    ON qq.question_id = tq.question_fi
    WHERE t.question_set_type = 'FIXED_QUEST_SET'
    AND t.obj_fi != qq.obj_fi
";

$brokenRandomTestQuestionsQuery = "
    SELECT qq.question_id qst_id, t.obj_fi tst_obj_id, qq.obj_fi qpl_obj_id
    FROM tst_tests t
    INNER JOIN tst_rnd_cpy tq
    ON t.test_id = tq.tst_fi
    INNER JOIN qpl_questions qq
    ON qq.question_id = tq.qst_fi
    WHERE t.question_set_type = 'RANDOM_QUEST_SET'
    AND t.obj_fi != qq.obj_fi
";

$brokenQuestionCountQuery = "
    SELECT COUNT(broken.qst_id) cnt FROM (
        SELECT q1.qst_id FROM ( {$brokenFixedTestQuestionsQuery} ) q1
        UNION
        SELECT q2.qst_id FROM ( {$brokenRandomTestQuestionsQuery} ) q2
    ) broken
";

$brokenQuestionSelectQuery = "
    SELECT q1.qst_id, q1.tst_obj_id, q1.qpl_obj_id FROM ( {$brokenFixedTestQuestionsQuery} ) q1
    UNION
    SELECT q2.qst_id, q2.tst_obj_id, q2.qpl_obj_id FROM ( {$brokenRandomTestQuestionsQuery} ) q2
";

$res = $ilDB->query($brokenQuestionCountQuery);
$row = $ilDB->fetchAssoc($res);

if( $ilDB->tableExists($tempTableName) )
{
	$ilDB->dropTable($tempTableName);
}

if( $row['cnt'] > 0 )
{
	$ilDB->createTable($tempTableName, $tempTableFields);
	$ilDB->addPrimaryKey($tempTableName, array('qst_id'));
	$ilDB->addIndex($tempTableName, array('tst_obj_id', 'qpl_obj_id'), 'i1');
	
	$ilDB->manipulate("
        INSERT INTO {$tempTableName} (qst_id, tst_obj_id, qpl_obj_id) {$brokenQuestionSelectQuery}
    ");
}

?>
<#64>
<?php

$tempTableName = 'tmp_tst_qst_fixparent';

if( $ilDB->tableExists($tempTableName) )
{
	$updateStatement = $ilDB->prepareManip("
        UPDATE qpl_questions SET obj_fi = ? WHERE obj_fi = ? AND question_id IN(
            SELECT qst_id FROM {$tempTableName} WHERE tst_obj_id = ? AND qpl_obj_id = ?
        )
    ", array('integer', 'integer', 'integer', 'integer')
	);
	
	$deleteStatement = $ilDB->prepareManip("
        DELETE FROM {$tempTableName} WHERE tst_obj_id = ? AND qpl_obj_id = ?
    ", array('integer', 'integer')
	);
	
	$res = $ilDB->query("SELECT DISTINCT tst_obj_id, qpl_obj_id FROM {$tempTableName}");
	
	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->execute($updateStatement, array(
			$row['tst_obj_id'], $row['qpl_obj_id'], $row['tst_obj_id'], $row['qpl_obj_id']
		));
		
		$ilDB->execute($deleteStatement, array(
			$row['tst_obj_id'], $row['qpl_obj_id']
		));
	}
	
	$ilDB->dropTable($tempTableName);
}

?>
<#65>
<?php

if($ilDB->indexExistsByFields('read_event',array('usr_id')))
{
	$ilDB->dropIndexByFields('read_event',array('usr_id'));
}
$ilDB->addIndex('read_event', array('usr_id'), 'i1');

?>
<#66>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#67>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#68>
<?php
if(!$ilDB->tableExists('crs_timings_exceeded'))
{
	$ilDB->createTable('crs_timings_exceeded', array(
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	,
		'sent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('crs_timings_exceeded', array('user_id', 'ref_id'));
}
?>
<#69>
<?php
if(!$ilDB->tableExists('crs_timings_started'))
{
	$ilDB->createTable('crs_timings_started', array(
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	,
		'sent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('crs_timings_started', array('user_id', 'ref_id'));
}
?>
<#70>
<?php
$setting = new ilSetting();
$idx = $setting->get('ilfrmposidx5', 0);
if (!$idx) {
	$ilDB->addIndex('frm_posts', ['pos_thr_fk', 'pos_date'], 'i5');
	$setting->set('ilfrmposidx5', 1);
}
?>
<#71>
<?php
$ilDB->modifyTableColumn('frm_notification', 'frm_id', array(
	'type'    => 'integer',
	'length'  => 8,
	'notnull' => true,
	'default' => 0
));
?>
<#72>
<?php
$ilDB->modifyTableColumn('frm_notification', 'thread_id', array(
	'type'    => 'integer',
	'length'  => 8,
	'notnull' => true,
	'default' => 0
));
?>
<#73>
<?php
$ilDB->modifyTableColumn('il_cert_template', 'version', array(
    'type'    => 'integer',
    'length'  => 8,
    'notnull' => true,
    'default' => 0
));
?>
