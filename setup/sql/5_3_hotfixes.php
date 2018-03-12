<?php
// This is the hotfix file for ILIAS 5.3.x DB fixes
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
$ilDB->query("
UPDATE il_dcl_stloc1_value 
SET value = NULL 
WHERE value = '[]' 
       AND record_field_id IN (
               SELECT rf.id 
               FROM il_dcl_record_field rf 
               INNER JOIN il_dcl_field f ON f.id = rf.field_id 
               WHERE f.datatype_id = 14
       )
");
?>
<#3>
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
<#4>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
if (!$ilDB->tableColumnExists(ilOrgUnitPermission::TABLE_NAME, 'protected')) {
	$ilDB->addTableColumn(ilOrgUnitPermission::TABLE_NAME, 'protected', [
		"type"    => "integer",
		"length"  => 1,
		"default" => 0,
	]);
}
$ilDB->manipulate("UPDATE il_orgu_permissions SET protected = 1 WHERE parent_id = -1");
?>
<#6>
<?php
if( $ilDB->indexExistsByFields('cmi_objective', array('id')) )
{
	$ilDB->dropIndexByFields('cmi_objective',array('id'));
}
?>
<#7>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#8>
<?php
if (!$ilDB->indexExistsByFields('page_style_usage', array('page_id', 'page_type', 'page_lang', 'page_nr')) )
{
	$ilDB->addIndex('page_style_usage',array('page_id', 'page_type', 'page_lang', 'page_nr'),'i1');
}
?>
<#9>
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
<#10>
<?php
$set = $ilDB->query("
  SELECT obj_id, title, description, role_id, usr_id FROM object_data
  INNER JOIN role_data role ON role.role_id = object_data.obj_id
  INNER JOIN rbac_ua on role.role_id = rol_id
  WHERE title LIKE '%il_orgu_superior%' OR title LIKE '%il_orgu_employee%'
");
$assigns = [];
$superior_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
$employee_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

while ($res = $ilDB->fetchAssoc($set)) {
	$user_id = $res['usr_id'];

	$tmp = explode("_", $res['title']);
	$orgu_ref_id = (int) $tmp[3];
	if ($orgu_ref_id == 0) {
		//$ilLog->write("User $user_id could not be assigned to position. Role description does not contain object id of orgu. Skipping.");
		continue;
	}

	$tmp = explode("_", $res['title']); //il_orgu_[superior|employee]_[$ref_id]
	$role_type = $tmp[2]; // [superior|employee]

	if ($role_type == 'superior')
		$position_id = $superior_position_id;
	elseif ($role_type == 'employee')
		$position_id = $employee_position_id;
	else {
		//$ilLog->write("User $user_id could not be assigned to position. Role type seems to be neither superior nor employee. Skipping.");
		continue;
	}
	if(!ilOrgUnitUserAssignment::findOrCreateAssignment(
		$user_id,
		$position_id,
		$orgu_ref_id)) {
		//$ilLog->write("User $user_id could not be assigned to position $position_id, in orgunit $orgu_ref_id . One of the ids might not actually exist in the db. Skipping.");
	}
}
?>
<#11>
<?php
	$ilDB->manipulate('UPDATE exc_mem_ass_status SET status='.$ilDB->quote('notgraded', 'text').' WHERE status = '.$ilDB->quote('', 'text'));
?>
<#12>
<?php

$query = 'SELECT MAX(meta_description_id) desc_id from il_meta_description ';
$res = $ilDB->query($query);
while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	$query = 'UPDATE il_meta_description_seq SET sequence = '. $ilDB->quote($row->desc_id + 100);
	$ilDB->manipulate($query);
}
?>
<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>