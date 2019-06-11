<#5432>
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
<#5452>
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
<#5453>
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

$step = 5453;

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
			"Cannot migrate forum tree for thread id %s in database update step %s", $row['thr_fk'], $step
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
		"Created gaps in tree for thread with id %s in database update step %s", $row['thr_fk'], $step
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
		"Created new root posting with id %s in thread with id %s in database update step %s",
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
		"Created new tree root with id %s in thread with id %s in database update step %s",
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
		"Set parent to %s for posting with id %s in thread with id %s in database update step %s",
		$nextId, $row['fpt_pk'], $row['thr_fk'], $step
	));

	// Mark as migrated
	$ilDB->insert('frm_thread_tree_mig', array(
		'thread_id' => array('integer', $row['thr_fk'])
	));
}
?>
<#5454>
<?php
// Drop migration table
if ($ilDB->tableExists('frm_thread_tree_mig')) {
	$ilDB->dropTable('frm_thread_tree_mig');
	$GLOBALS['ilLog']->info(sprintf(
		'Dropped thread migration table: frm_thread_tree_mig'
	));
}
?>
<#5455>
<?php
// Add new index
if (!$ilDB->indexExistsByFields('frm_posts_tree', ['parent_pos'])) {
	$ilDB->addIndex('frm_posts_tree', ['parent_pos'], 'i3');
}
?>
<#5456>
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
<#5457>
<?php
if ($ilDB->tableColumnExists('lso_settings', 'online'))
{
	$ilDB->dropTableColumn('lso_settings', 'online');
}
?>
<#5458>
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
<#5459>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::updateOperationOrder('participate', 1010);
ilDBUpdateNewObjectType::updateOperationOrder('unparticipate', 1020);
?>
<#5460>
<?php
/**
 * @var $ilDB ilDBInterface
 */
$ilDB->modifyTableColumn('il_gs_identifications', 'identification', ['length' => 255]);
$ilDB->modifyTableColumn('il_mm_items', 'identification', ['length' => 255]);
?>
<#5461>
<?php
if( !$ilDB->tableColumnExists('qpl_questions', 'lifecycle') )
{
	$ilDB->addTableColumn('qpl_questions', 'lifecycle', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => 'draft'
	));
	
	$ilDB->queryF('UPDATE qpl_questions SET lifecycle = %s', array('text'), array('draft'));
}
?>
<#5462>
<?php
if( !$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'lifecycle_filter'))
{
	$ilDB->addTableColumn('tst_rnd_quest_set_qpls', 'lifecycle_filter',
		array('type' => 'text', 'length' => 250, 'notnull'	=> false, 'default'	=> null)
	);
}
?>
<#5463>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['context_id'])) {
	$ilDB->addIndex('il_orgu_permissions', array( 'context_id' ), 'co');
}
?>
<#5464>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['position_id'])) {
$ilDB->addIndex('il_orgu_permissions', array('position_id'), 'po');
}
?>
<#5465>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
$ilDB->modifyTableColumn('il_orgu_permissions', 'operations', array("length" => 256));
}
?>
<#5466>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id'])) {
$ilDB->addIndex('il_orgu_ua', array('position_id'), 'pi');
}
?>
<#5467>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['user_id'])) {
$ilDB->addIndex('il_orgu_ua', array('user_id'), 'ui');
}
?>
<#5468>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['orgu_id'])) {
$ilDB->addIndex('il_orgu_ua', array('orgu_id'), 'oi');
}
?>
<#5469>
<?php
/*if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations'), 'oi');
}*/
?>
<#5470>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id','orgu_id'])) {
$ilDB->addIndex('il_orgu_ua', array('position_id','orgu_id'), 'po');
}
?>
<#5471>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id','user_id'])) {
$ilDB->addIndex('il_orgu_ua', array('position_id','user_id'), 'pu');
}
?>
<#5472>
<?php
/*if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations','parent_id'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations','parent_id'), 'op');
}*/
?>
<#5473>
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
<#5474>
<?php
if(!$ilDB->tableColumnExists('lso_activation', 'activation_start_ts')) {
	$ilDB->addTableColumn('lso_activation', 'activation_start_ts',
		array(
			"type"    => "integer",
			"notnull" => false,
			"length"  => 4
		)
	);
}
?>
<#5475>
<?php
if(!$ilDB->tableColumnExists('lso_activation', 'activation_end_ts')) {
	$ilDB->addTableColumn('lso_activation', 'activation_end_ts',
		array(
			"type"    => "integer",
			"notnull" => false,
			"length"  => 4
		)
	);
}
?>
<#5476>
<?php
if($ilDB->tableColumnExists('lso_activation', 'activation_start')) {
	$ilDB->manipulate(
		'UPDATE lso_activation'
		.'	SET activation_start_ts = UNIX_TIMESTAMP(activation_start)'
		.'	WHERE activation_start IS NOT NULL'
	);
}
?>
<#5477>
<?php
if($ilDB->tableColumnExists('lso_activation', 'activation_end')) {
	$ilDB->manipulate(
		'UPDATE lso_activation'
		.'	SET activation_end_ts = UNIX_TIMESTAMP(activation_end)'
		.'	WHERE activation_end IS NOT NULL'
	);
}
?>
<#5478>
<?php
if($ilDB->tableColumnExists('lso_activation', 'activation_start')) {
	$ilDB->dropTableColumn("lso_activation", "activation_start");
}
?>
<#5479>
<?php
if($ilDB->tableColumnExists('lso_activation', 'activation_end')) {
	$ilDB->dropTableColumn("lso_activation", "activation_end");
}
?>
<#5480>
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
<#5481>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5482>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5483>
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

<#5484>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
	$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
	ilDBUpdateNewObjectType::deleteRBACOperation($lp_type_id, $ops_id);
}

?>

<#5485>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
	$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
	ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ops_id);
}

?>
<#5486>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#5487>
<?php
	$ilDB->dropPrimaryKey('post_conditions');
	$ilDB->addPrimaryKey('post_conditions', ['ref_id', 'condition_operator', 'value']);
?>

<#5488>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
ilDBUpdateNewObjectType::deleteRBACOperation("lso", $ops_id);

?>
<#5489>
<?php
if( !$ilDB->tableColumnExists('qpl_qst_essay', 'word_cnt_enabled') )
{
	$ilDB->addTableColumn('qpl_qst_essay', 'word_cnt_enabled', array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>
<#5490>
<?php
if (!$ilDB->tableColumnExists('exc_assignment_peer', 'is_valid'))
{
	$ilDB->addTableColumn('exc_assignment_peer', 'is_valid', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
}
?>
<#5491>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5492>
<?php
if(!$ilDB->tableColumnExists('exc_returned', 'web_dir_access_time'))
{
	$ilDB->addTableColumn('exc_returned', 'web_dir_access_time', array(
		'type' => 'timestamp',
		'notnull' => false,
		'default' => null
	));
}
$ilCtrlStructureReader->getStructure();
?>
<#5493>
<?php
$settings = new \ilSetting('chatroom');
$settings->set('conversation_idle_state_in_minutes', 1);

$res = $ilDB->query("SELECT * FROM chatroom_admconfig");
while ($row = $ilDB->fetchAssoc($res)) {
	$settings = json_decode($row['client_settings'], true);

	if (!is_numeric($settings['conversation_idle_state_in_minutes'])) {
		$settings['conversation_idle_state_in_minutes'] = 1;
	}

	$ilDB->update('chatroom_admconfig', [
		'client_settings' => ['text', json_encode($settings)]
	], [
		'instance_id' => ['integer', $row['instance_id']]
	]);
}
?>
<#5494>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5495>
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
<#5496>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'passwd_policy_reset')) {
	$ilDB->addTableColumn('usr_data', 'passwd_policy_reset', array(
		'type' => 'integer',
		'notnull' => true,
		'length' => 1,
		'default' => 0
	));
}
?>
<#5497>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE keyword = %s',
	['text'],
	['block_activated_chatviewer']
);

$ilDB->manipulateF(
	'DELETE FROM usr_pref WHERE keyword = %s',
	['text'],
	['chatviewer_last_selected_room']
);
?>
<#5498>
<?php
if ($ilDB->tableColumnExists('mail_saved', 'm_type')) {
	$ilDB->dropTableColumn('mail_saved', 'm_type');
}

if ($ilDB->tableColumnExists('mail', 'm_type')) {
	$ilDB->dropTableColumn('mail', 'm_type');
}

$ilDB->manipulateF(
	'DELETE FROM settings WHERE keyword = %s',
	['text'],
	['pd_sys_msg_mode']
);
?>
<#5499>
<?php
$res = $ilDB->queryF('SELECT * FROM rbac_operations WHERE operation = %s', ['text'], ['system_message']);
$row = $ilDB->fetchAssoc($res);

if ($row['ops_id']) {
	$opsId = $row['ops_id'];

	$ilDB->manipulateF('DELETE FROM rbac_templates WHERE ops_id = %s', ['integer'], [$opsId]);
	$ilDB->manipulateF('DELETE FROM rbac_ta WHERE ops_id = %s', ['integer'], [$opsId]);
	$ilDB->manipulateF('DELETE FROM rbac_operations WHERE ops_id = %s', ['integer'], [$opsId]);
}
?>
<#5500>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE keyword = %s',
	['text'],
	['block_activated_pdfrmpostdraft']
);
?>
<#5501>
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
<#5502>
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
<#5503>
<?php
if( !$ilDB->tableExists('cont_filter_field') )
{
	$ilDB->createTable('cont_filter_field', array(
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'record_set_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>
<#5504>
<?php
if(!$ilDB->tableExists('il_cert_bgtask_migr')) {
	$ilDB->dropTable('il_cert_bgtask_migr');
}
?>
<#5505>
<?php
if ($ilDB->tableExists('il_bt_task')) {
    if ($ilDB->tableExists('il_bt_value_to_task')) {
        if ($ilDB->tableExists('il_bt_value')) {
            $deleteBucketValuesSql = '
DELETE FROM il_bt_value WHERE id IN (
    SELECT value_id FROM il_bt_value_to_task WHERE task_id IN (
        SELECT id FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%') . '
    )
)';
            $ilDB->manipulate($deleteBucketValuesSql);
        }

        $deleteValueToTask = '
DELETE FROM il_bt_value_to_task
WHERE task_id IN (
    SELECT id FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%') . '
)';

        $ilDB->manipulate($deleteValueToTask);
    }
    $deleteBackgroundTasksSql = 'DELETE FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%');
    $ilDB->manipulate($deleteBackgroundTasksSql);
}

if ($ilDB->tableExists('il_bt_bucket')) {
    $deleteBucketsSql = 'DELETE FROM il_bt_bucket WHERE title = ' . $ilDB->quote('Certificate Migration', 'text') ;
    $ilDB->manipulate($deleteBucketsSql);
}

?>
