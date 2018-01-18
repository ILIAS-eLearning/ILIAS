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