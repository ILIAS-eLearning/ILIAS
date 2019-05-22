<?php
// This is the hotfix file for ILIAS 5.0.x DB fixes
// This file should be used, if bugfixes need DB changes, but the
// main db update script cannot be used anymore, since it is
// impossible to merge the changes with the trunk.
//
// IMPORTANT: The fixes done here must ALSO BE reflected in the trunk.
// The trunk needs to work in both cases !!!
// 1. If the hotfixes have been applied.
// 2. If the hotfixes have not been applied.
?>
<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'num_ratings',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#3>
<?php
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'avg_rating',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#4>
<?php
$query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
$res = $ilDB->queryF($query, array('text', 'text'), array("mobs", "black_list_file_types"));
if (!$ilDB->fetchAssoc($res))
{
	$mset = new ilSetting("mobs");
	$mset->set("black_list_file_types", "html");
}
?>
<#5>
<?php
// #0020342
$query = $ilDB->query('SELECT 
    stloc.*
FROM
    il_dcl_stloc2_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
WHERE
    f.datatype_id = 3
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
	$query2 = $ilDB->query('SELECT * FROM il_dcl_stloc1_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
	if ($ilDB->numRows($query2)) {
		$rec = $ilDB->fetchAssoc($query2);
		if ($rec['value'] != null) {
			continue;
		}
	}

	$id = $ilDB->nextId('il_dcl_stloc1_value');
	$ilDB->insert('il_dcl_stloc1_value', array(
		'id' => array('integer', $id),
		'record_field_id' => array('integer', $row['record_field_id']),
		'value' => array('text', $row['value']),
	));
	$ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE id = ' . $ilDB->quote($row['id'], 'integer'));
}
?>
<#6>
<?php

$ilDB->manipulate('update grp_settings set registration_start = '. $ilDB->quote(null, 'integer').', '.
	'registration_end = '.$ilDB->quote(null, 'integer') .' '.
	'where registration_unlimited = '.$ilDB->quote(1,'integer')
);
?>

<#7>
<?php
$ilDB->manipulate('update crs_settings set '
	.'sub_start = ' . $ilDB->quote(null,'integer').', '
	.'sub_end = '.$ilDB->quote(null,'integer').' '
	.'WHERE sub_limitation_type != '.$ilDB->quote(2,'integer')
);
	
?>
<#8>
<?php
if(!$ilDB->tableColumnExists('frm_posts', 'pos_activation_date'))
{
	$ilDB->addTableColumn('frm_posts', 'pos_activation_date',
		array('type' => 'timestamp', 'notnull' => false));
}

if($ilDB->tableColumnExists('frm_posts', 'pos_activation_date'))
{
	$ilDB->manipulate('
	UPDATE frm_posts SET pos_activation_date = pos_date 
	WHERE pos_status = '. $ilDB->quote(1, 'integer')
	.' AND pos_activation_date is NULL'
	);
}
?>
<#9>
<?php
// #0020342
$query = $ilDB->query('SELECT 
    stloc.*,
	fp.value as fp_value,
	fp.name as fp_name
FROM
    il_dcl_stloc1_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
		INNER JOIN
	il_dcl_field_prop fp ON rf.field_id = fp.field_id
WHERE
    f.datatype_id = 3
	AND fp.name = "multiple_selection"
	AND fp.value = 1
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
	if (!is_numeric($row['value'])) {
		continue;
	}

	$value_array = array($row['value']);

	$query2 = $ilDB->query('SELECT * FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
	while ($row2 = $ilDB->fetchAssoc($query2)) {
		$value_array[] = $row2['value'];
	}

	$ilDB->update('il_dcl_stloc1_value', array(
		'id' => array('integer', $row['id']),
		'record_field_id' => array('integer', $row['record_field_id']),
		'value' => array('text', json_encode($value_array)),
	), array('id' => array('integer', $row['id'])));
	$ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
}
?>
<#10>
<?php
$set = $ilDB->query("SELECT * FROM mep_item JOIN mep_tree ON (mep_item.obj_id = mep_tree.child) ".
	" WHERE mep_item.type = ".$ilDB->quote("pg", "text")
);
while ($rec = $ilDB->fetchAssoc($set))
{
	$q = "UPDATE page_object SET ".
		" parent_id = ".$ilDB->quote($rec["mep_id"], "integer").
		" WHERE parent_type = ".$ilDB->quote("mep", "text").
		" AND page_id = ".$ilDB->quote($rec["obj_id"], "integer");
	//echo "<br>".$q;
	$ilDB->manipulate($q);
}
?>
<#11>
<?php
	// fix 20706 (and 20743)
	require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
	$analyzer = new ilDBAnalyzer();
	$cons = $analyzer->getPrimaryKeyInformation('page_question');
	if (is_array($cons["fields"]) && count($cons["fields"]) > 0)
	{
		$ilDB->dropPrimaryKey('page_question');
	}

	$set = $ilDB->query("select page_parent_type, page_id, question_id, page_lang, count(*) from page_question group by page_parent_type, page_id, question_id, page_lang HAVING count(*) > 1");
	while ($rec = $this->db->fetchAssoc($set))
	{
		// remove all datasets with duplicates
		$del = "DELETE FROM page_question ".
			" WHERE page_parent_type = ".$ilDB->quote($rec['page_parent_type'], 'text').
			" AND page_id = ".$ilDB->quote($rec['page_id'], 'integer').
			" AND question_id = ".$ilDB->quote($rec['question_id'], 'integer').
			" AND page_lang = ".$ilDB->quote($rec['page_lang'], 'text');
		$ilDB->manipulate($del);
		$ilDB->insert('page_question', array(
			'page_parent_type' => array('text', $rec['page_parent_type']),
			'page_id' => array('integer', $rec['page_id']),
			'question_id' => array('integer', $rec['question_id']),
			'page_lang' => array('text', $rec['page_lang'])
		));
	}

	$ilDB->addPrimaryKey('page_question', array('page_parent_type', 'page_id', 'question_id', 'page_lang'));
?>
<#12>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
    // fix 20409 and 20638
    $old = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    $new = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML';

    $ilDB->manipulateF("UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'), array($new, $old)
    );
?>
<#14>
<?php
require_once('./Services/Component/classes/class.ilPluginAdmin.php');
require_once('./Services/Component/classes/class.ilPlugin.php');
require_once('./Services/UICore/classes/class.ilCtrl.php');

// Mantis #17842
/** @var $ilCtrl ilCtrl */
global $ilCtrl, $ilPluginAdmin;
if (is_null($ilPluginAdmin)) {
	$GLOBALS['ilPluginAdmin'] = new ilPluginAdmin();
}
if (is_null($ilCtrl)) {
	$GLOBALS['ilCtrl'] = new ilCtrl();
}
global $ilCtrl;

function writeCtrlClassEntry(ilPluginSlot $slot, array $plugin_data) {
	global $ilCtrl;
	$prefix = $slot->getPrefix() . '_' . $plugin_data['id'];
	$ilCtrl->insertCtrlCalls("ilobjcomponentsettingsgui", ilPlugin::getConfigureClassName($plugin_data['name']), $prefix);
}

include_once("./Services/Component/classes/class.ilModule.php");
$modules = ilModule::getAvailableCoreModules();
foreach ($modules as $m) {
	$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE, $m["subdir"]);
	foreach ($plugin_slots as $ps) {
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
		foreach ($slot->getPluginsInformation() as $p) {
			if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p["name"]) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p["name"]))) {
				writeCtrlClassEntry($slot, $p);
			}
		}
	}
}
include_once("./Services/Component/classes/class.ilService.php");
$services = ilService::getAvailableCoreServices();
foreach ($services as $s) {
	$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $s["subdir"]);
	foreach ($plugin_slots as $ps) {
		$slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
		foreach ($slot->getPluginsInformation() as $p) {
			if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p["name"]) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p["name"]))) {
				writeCtrlClassEntry($slot, $p);
			}
		}
	}
}
?>
<#15>
<?php

if($ilDB->tableColumnExists('reg_registration_codes','generated'))
{
	$ilDB->renameTableColumn('reg_registration_codes', "generated", 'generated_on');
}
?>
<#16>
<?php
if(!$ilDB->indexExistsByFields('style_parameter',array('style_id')))
{
	$ilDB->addIndex('style_parameter',array('style_id'),'i1');
}
?>
<#17>
<?php
if($ilDB->tableColumnExists('wiki_stat', 'del_pages'))
{
	$ilDB->modifyTableColumn('wiki_stat', 'del_pages', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#18>
<?php
if($ilDB->tableColumnExists('wiki_stat', 'avg_rating'))
{
	$ilDB->modifyTableColumn('wiki_stat', 'avg_rating', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#19>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#20>
<?php

$query = "
	SELECT	qpl.question_id qid,
			qpl.points qpl_points,
			answ.points answ_points
	
	FROM qpl_questions qpl
	
	INNER JOIN qpl_qst_essay qst
	ON qst.question_fi = qpl.question_id
	
	INNER JOIN qpl_a_essay answ
	ON answ.question_fi = qst.question_fi
	
	WHERE qpl.question_id IN(
	
		SELECT keywords.question_fi
	
		FROM qpl_a_essay keywords
	
		INNER JOIN qpl_qst_essay question
		ON question.question_fi = keywords.question_fi
		AND question.keyword_relation = {$ilDB->quote('', 'text')}
	
		WHERE keywords.answertext = {$ilDB->quote('', 'text')}
		GROUP BY keywords.question_fi
		HAVING COUNT(keywords.question_fi) = {$ilDB->quote(1, 'integer')}
		
	)
";

$res = $ilDB->query($query);

while( $row = $ilDB->fetchAssoc($res) )
{
	if( $row['answ_points'] > $row['qpl_points'] )
	{
		$ilDB->update('qpl_questions',
			array('points' => array('float', $row['answ_points'])),
			array('question_id' => array('integer', $row['qid']))
		);
	}
	
	$ilDB->manipulateF(
		"DELETE FROM qpl_a_essay WHERE question_fi = %s",
		array('integer'), array($row['qid'])
	);
	
	$ilDB->update('qpl_qst_essay',
		array('keyword_relation' => array('text', 'non')),
		array('question_fi' => array('integer', $row['qid']))
	);
}

?>
<#21>
<?php
if($ilDB->tableExists('svy_answer'))
{
	if($ilDB->tableColumnExists('svy_answer','textanswer'))
	{
		$ilDB->modifyTableColumn('svy_answer', 'textanswer', array(
			'type'	=> 'clob',
			'notnull' => false
		));
	}
}
?>
<#22>
<?php
if( $ilDB->indexExistsByFields('cmi_objective', array('id')) )
{
	$ilDB->dropIndexByFields('cmi_objective',array('id'));
}
?>
<#23>
<?php
if (!$ilDB->indexExistsByFields('page_style_usage', array('page_id', 'page_type', 'page_lang', 'page_nr')) )
{
	$ilDB->addIndex('page_style_usage',array('page_id', 'page_type', 'page_lang', 'page_nr'),'i1');
}
?>
<#24>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$rp_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
$ep_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$w_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if($rp_ops_id && $ep_ops_id && $w_ops_id)
{			
	// see ilObjectLP
	$lp_types = array('mcst');

	foreach($lp_types as $lp_type)
	{
		$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
		if($lp_type_id)
		{			
			ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $rp_ops_id);				
			ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ep_ops_id);				
			ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $rp_ops_id);
			ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $ep_ops_id);
		}
	}
}
?>
<#25>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addRBACTemplate('orgu', 'il_orgu_employee', "OrgUnit Employee Role Template", null);
?>
<#26>
<?php
	$ilDB->manipulate('UPDATE exc_mem_ass_status SET status='.$ilDB->quote('notgraded', 'text').' WHERE status = '.$ilDB->quote('', 'text'));
?>
<#27>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#28>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#29>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#30>
<?php
$ilDB->modifyTableColumn('il_dcl_tableview', 'roles',array('type' => 'clob'));
?>
<#31>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#32>
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
<#33>
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