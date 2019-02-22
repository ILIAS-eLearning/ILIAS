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
