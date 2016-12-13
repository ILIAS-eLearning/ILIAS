<#1>
<?php
if($ilDB->tableExists('svy_qst_oblig'))
{
	$ilDB->manipulate("UPDATE svy_question".
		" INNER JOIN svy_qst_oblig".
		" ON svy_question.question_id = svy_qst_oblig.question_fi".
		" SET svy_question.obligatory = svy_qst_oblig.obligatory");
}
?>
<#2>
<?php
if($ilDB->tableExists('svy_qst_oblig'))
	$ilDB->dropTable('svy_qst_oblig');
if($ilDB->tableExists('svy_qst_oblig_seq'))
	$ilDB->dropTable('svy_qst_oblig_seq');
?>

