<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
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
if(!$ilDB->tableColumnExists('notification_osd', 'visible_for'))
{
	$ilDB->addTableColumn('notification_osd', 'visible_for', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0)
	);
}
?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if($ilDB->tableColumnExists('svy_times', 'first_question'))
{
	$ilDB->modifyTableColumn('svy_times', 'first_question', array(
											'type'	=> 'integer',
											'length'=> 4)
	);
}
?>
<#4>
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
<#5>
<?php
if(!$ilDB->indexExistsByFields('il_qpl_qst_fq_unit',array('question_fi')))
{
	$ilDB->addIndex('il_qpl_qst_fq_unit',array('question_fi'), 'i2');
}
?>
<#6>
<?php
$setting = new ilSetting();
$setting->set('mail_send_html', 1);
?>
<#7>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');	
if($tgt_ops_id)
{				
	$book_type_id = ilDBUpdateNewObjectType::getObjectTypeId('book');
	if($book_type_id)
	{			
		// add "copy" to booking tool
		ilDBUpdateNewObjectType::addRBACOperation($book_type_id, $tgt_ops_id);				
									
		// clone settings from "write" to "copy"
		$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');	
		ilDBUpdateNewObjectType::cloneOperation('book', $src_ops_id, $tgt_ops_id);		
	}	
}

?>
<#8>
<?php
if(!$ilDB->indexExistsByFields('usr_data_multi',array('usr_id')))
{
	$ilDB->addIndex('usr_data_multi',array('usr_id'), 'i1');
}
?>
<#9>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#10>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');
if($tgt_ops_id)
{
	$mep_type_id = ilDBUpdateNewObjectType::getObjectTypeId('mep');
	if($mep_type_id)
	{
		if (!ilDBUpdateNewObjectType::isRBACOperation($mep_type_id, $tgt_ops_id))
		{
			// add "copy" to (external) feed
			ilDBUpdateNewObjectType::addRBACOperation($mep_type_id, $tgt_ops_id);

			// clone settings from "write" to "copy"
			$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
			ilDBUpdateNewObjectType::cloneOperation('mep', $src_ops_id, $tgt_ops_id);
		}
	}
}
?>
<#11>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#12>
<?php
require_once 'Services/Password/classes/class.ilPasswordUtils.php';
$salt_location = CLIENT_DATA_DIR . '/pwsalt.txt';
if(!is_file($salt_location) || !is_readable($salt_location))
{
	$result = @file_put_contents(
		$salt_location,
		substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(16))), 0, 22)
	);
	if(!$result)
	{
		die("Could not create the client salt for bcrypt password hashing.");
	}
}
if(!is_file($salt_location) || !is_readable($salt_location))
{
	die("Could not determine the client salt for bcrypt password hashing.");
}
?>
<#13>
<?php
if(!$ilDB->tableColumnExists('qpl_qst_lome', 'min_auto_complete'))
{
	$ilDB->addTableColumn('qpl_qst_lome', 'min_auto_complete', array(
			'type'	=> 'integer',
			'length'=> 1,
			'default' => 1)
	);
}
if($ilDB->tableColumnExists('qpl_qst_lome', 'min_auto_complete'))
{
	$ilDB->modifyTableColumn('qpl_qst_lome', 'min_auto_complete', array(
			'default' => 3)
	);
}
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
if($ilDB->sequenceExists('mail_obj_data'))
{
	$ilDB->dropSequence('mail_obj_data');
}

if($ilDB->sequenceExists('mail_obj_data'))
{
	die("Sequence could not be dropped!");
}
else
{
	$res1 = $ilDB->query("SELECT MAX(child) max_id FROM mail_tree");
	$row1 = $ilDB->fetchAssoc($res1);

	$res2 = $ilDB->query("SELECT MAX(obj_id) max_id FROM mail_obj_data");
	$row2 = $ilDB->fetchAssoc($res2);

	$start = max($row1['max_id'], $row2['max_id']) + 2; // add + 2 to be save

	$ilDB->createSequence('mail_obj_data', $start);
}
?>
<#17>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#18>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#19>
<?php

	if( !$ilDB->tableColumnExists('qpl_a_mterm', 'ident') )
	{
		$ilDB->addTableColumn('qpl_a_mterm', 'ident', array(
			'type'    => 'integer', 'length'  => 4,
			'notnull' => false, 'default' => null
		));
		
		$ilDB->manipulate("UPDATE qpl_a_mterm SET ident = term_id WHERE ident IS NULL");
	}
	
	if( !$ilDB->tableColumnExists('qpl_a_mdef', 'ident') )
	{
		require_once 'Services/Database/classes/class.ilDBAnalyzer.php';
		$ilDB->renameTableColumn('qpl_a_mdef', 'morder', 'ident');
	}
	
?>
<#20>
<?php
$ilDB->modifyTableColumn('exc_returned', 'mimetype', array(
										'type'	=> 'text',
										'length'=> 150,
										'notnull' => false)
);
?>
<#21>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#22>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#23>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#24>
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
<#25>
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
<#26>
<?php
$query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
$res = $ilDB->queryF($query, array('text', 'text'), array("mobs", "black_list_file_types"));
if (!$ilDB->fetchAssoc($res))
{
	$mset = new ilSetting("mobs");
	$mset->set("black_list_file_types", "html");
}
?>
<#27>
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
<#28>
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
<#29>
<?php
    // fix 20409 and 20638
    $old = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    $new = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML';

    $ilDB->manipulateF("UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'), array($new, $old)
    );
?>
<#30>
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
<#31>
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
<#32>
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
<#33>
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
<#34>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addRBACTemplate('orgu', 'il_orgu_employee', "OrgUnit Employee Role Template", null);
?>