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
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations'), 'oi');
}
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
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations','parent_id'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations','parent_id'), 'op');
}
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
<#5475>
<?php
$ilCtrlStructureReader->getStructure();
?>