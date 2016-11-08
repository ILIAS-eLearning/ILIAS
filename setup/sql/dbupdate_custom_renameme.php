<#1>
<?php
// 1. Select all the questions in svy_question with original_id > 0

$q = "SELECT svy_question.question_id, svy_svy_qst.survey_fi FROM svy_question, svy_svy_qst WHERE svy_question.original_id > 0 AND svy_question.question_id = svy_svy_qst.question_fi";
$res = $this->db->query($q);

$qst_data = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
{
	$qst_data[] = $row;
}

if(!empty($qst_data))
{
	// for every question in surveys we query the object_id and then update the svy_question with this survey object id
	foreach ($qst_data as $svy_data)
	{
		$question_id = $svy_data['question_id'];
		$svy_id = $svy_data['survey_fi'];

		$q = "SELECT obj_fi FROM svy_svy WHERE survey_id = $svy_id";
		$res = $this->db->query($q);
		$row = $res->fetchRow();
		$obj_id  = $row['obj_fi'];

		$u = "UPDATE svy_question SET obj_fi = $obj_id WHERE question_id = $question_id";
		$this->db->query($u);
	}
}
unset($qst_data);
?>
</#1>