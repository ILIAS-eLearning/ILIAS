<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
// This is the hotfix file for ILIAS 4.1.x DB fixes
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

	if(!$ilDB->tableColumnExists('ecs_import', 'sub_id'))
	{
		$ilDB->addTableColumn('ecs_import', 'sub_id',
				array(
					"type" => "text",
					"notnull" => false,
					"length" => 64
				)
		);
	}
?>
<#2>
<?php

	if(!$ilDB->tableColumnExists('ecs_course_assignments', 'cms_sub_id'))
	{
		$ilDB->addTableColumn('ecs_course_assignments', 'cms_sub_id',
				array(
					"type" => "integer",
					"notnull" => false,
					"length" => 4,
					'default' => 0
				)
		);
	}
?>
<#3>
<?php

	if(!$ilDB->tableColumnExists('ecs_import', 'ecs_id'))
	{
		$ilDB->addTableColumn('ecs_import', 'ecs_id',
				array(
					"type" => "integer",
					"notnull" => false,
					"length" => 4,
					'default' => 0
				)
		);
	}
?>
<#4>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php

// find all essay questions without keywords stored in qpl_a_essay
// and migrate them to scoring mode "NON" (keyword relation)

$res = $ilDB->query("
	SELECT qstq.question_fi
	
	FROM qpl_qst_essay qstq
	
	LEFT JOIN qpl_a_essay qsta
	ON qstq.question_fi = qsta.question_fi
	
	WHERE qsta.answer_id IS NULL
");

$questionIds = array();

while( $row = $ilDB->fetchAssoc($res) )
{
	$questionIds[] = $row['question_fi'];
}

$questionId__IN__questionIds = $ilDB->in('question_fi', $questionIds, false, 'integer');

$query = "
	UPDATE qpl_qst_essay
	SET keyword_relation = %s
	WHERE $questionId__IN__questionIds
";

$ilDB->manipulateF($query, array('text'), array('non'));

?>
<#7>
<?php

// find all essay questions with exactly one keyword stored in qpl_a_essay
// and migrate them to scoring mode "ONE" (keyword relation)

$query = "
	SELECT	qstq.question_fi,
			COUNT(qsta.answer_id) keywordscount,
			SUM(qsta.points) qst_points
	
	FROM qpl_qst_essay qstq
	
	INNER JOIN qpl_a_essay qsta
	ON qstq.question_fi = qsta.question_fi
	
	WHERE qstq.keywords IS NOT NULL
	
	GROUP BY qstq.question_fi
";

$res = $ilDB->query($query);

$questionPoints = array();

while( $row = $ilDB->fetchAssoc($res) )
{
	if( $row['keywordscount'] != 1 )
	{
		continue;
	}
	
	$questionPoints[$row['question_fi']] = $row['qst_points'];
}

$questionId__IN__questionIds = $ilDB->in(
		'question_fi', array_keys($questionPoints), false, 'integer'
);

$query = "
	UPDATE qpl_qst_essay
	SET keyword_relation = %s
	WHERE $questionId__IN__questionIds
";

$ilDB->manipulateF($query, array('text'), array('one'));

$updateQuestionPoints = $ilDB->prepareManip(
	"UPDATE qpl_questions SET points = ? WHERE question_id = ?", array('integer', 'integer')
);

foreach($questionPoints as $questionId => $points)
{
	$ilDB->execute($updateQuestionPoints, array($points, $questionId));
}

?>
<#8>
<?php

// find all essay questions with more than one keywords stored in qpl_a_essay
// where only one of them has store points > 0
// and migrate them to scoring mode "ONE" (keyword relation)

$query = "
	SELECT	qstq.question_fi,
			SUM(qsta.points) points_sum,
			MIN(qsta.points) points_min,
			MAX(qsta.points) points_max,
			COUNT(qsta.answer_id) keywordscount
	
	FROM qpl_qst_essay qstq
	
	LEFT JOIN qpl_a_essay qsta
	ON qstq.question_fi = qsta.question_fi
	
	WHERE qstq.keywords IS NOT NULL
	AND qsta.answer_id IS NOT NULL
	
	GROUP BY qstq.question_fi
";

$res = $ilDB->queryF($query, array('integer'), array(0));

$questionPoints = array();

while( $row = $ilDB->fetchAssoc($res) )
{
	if( $row['keywordscount'] <= 1 )
	{
		continue;
	}
	
	if( $row['points_sum'] != $row['points_max'] )
	{
		continue;
	}
	
	if( $row['points_min'] > 0 )
	{
		continue;
	}
	
	$questionPoints[$row['question_fi']] = $row['points_sum'];
}

$questionId__IN__questionIds = $ilDB->in(
		'question_fi', array_keys($questionPoints), false, 'integer'
);

$query = "
	UPDATE qpl_qst_essay
	SET keyword_relation = %s
	WHERE $questionId__IN__questionIds
";

$ilDB->manipulateF($query, array('text'), array('one'));

$updateQuestionPoints = $ilDB->prepareManip(
	"UPDATE qpl_questions SET points = ? WHERE question_id = ?", array('integer', 'integer')
);

foreach($questionPoints as $questionId => $points)
{
	$ilDB->execute($updateQuestionPoints, array($points, $questionId));
}

?>
<#9>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#10>
<?php
	// ensure that ID 1 is not used
	$ilDB->nextId("tax_node");
?>
<#11>
<?php

$base_path = ilUtil::getDataDir();

$set = $ilDB->query("SELECT at.*,ea.exc_id".
	" FROM exc_assignment ea".
	" JOIN il_exc_team at ON (at.ass_id = ea.id)".
	" WHERE ea.type = ".$ilDB->quote(4, "integer"));
while($row = $ilDB->fetchAssoc($set))
{	
	// see ilFileSystemStorage::_createPathFromId()
	$tpath = array();
	$tfound = false;
	$tnum = $row["exc_id"];
	for($i = 3; $i > 0;$i--)
	{
		$factor = pow(100, $i);
		if(($tmp = (int) ($tnum / $factor)) or $tfound)
		{
			$tpath[] = $tmp;
			$tnum = $tnum % $factor;
			$tfound = true;
		}	
	}
	
	$ass_path = $base_path."/ilExercise/";
	if(count($tpath))
	{
		$ass_path .= (implode('/',$tpath).'/');
	}
	$ass_path .= "exc_".$row["exc_id"]."/feedb_".$row["ass_id"]."/";
	
	$team_path = $ass_path."t".$row["id"]."/";
	$user_path = $ass_path.$row["user_id"]."/";
	
	foreach(glob($user_path."*") as $ufile)
	{
		if(!is_dir($team_path))
		{
			mkdir($team_path);
		}
		$tfile = $team_path.basename($ufile);		
		if(!file_exists($tfile))
		{
			copy($ufile, $tfile);
		}
	}
}

?>
<#12>
<?php
	$ilCtrlStructureReader->getStructure();
?>
