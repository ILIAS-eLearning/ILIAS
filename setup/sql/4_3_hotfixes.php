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
<#13>
<?php
	$setting = new ilSetting();
	$ilfrmthri2 = $setting->get('ilfrmthri2');
	if(!$ilfrmthri2)
	{
		$ilDB->addIndex('frm_threads', array('thr_top_fk'), 'i2');
		$setting->set('ilfrmthri2', 1);
	}
?>
<#14>
<?php

// #10745
if(!$ilDB->tableColumnExists('tst_tests','starting_time'))
{
        $ilDB->addTableColumn(
                        'tst_tests',
                        'starting_time',
                        array(
                                'type' => 'text',
                                'length' => 14,
                                'notnull' => false
                        )
        );
}
if(!$ilDB->tableColumnExists('tst_tests','ending_time'))
{
        $ilDB->addTableColumn(
                        'tst_tests',
                        'ending_time',
                        array(
                                'type' => 'text',
                                'length' => 14,
                                'notnull' => false
                        )
        );
}

?>
<#15>
<?php
	$setting = new ilSetting();
	$ilfrmnoti1 = $setting->get('ilfrmnoti1');
	if(!$ilfrmnoti1)
	{
		$ilDB->addIndex('frm_notification', array('user_id', 'thread_id'), 'i1');
		$setting->set('ilfrmnoti1', 1);
	}
?>
<#16>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#17>
<?php

	if(!$ilDB->tableColumnExists('ecs_import', 'content_id'))
	{
		$ilDB->addTableColumn('ecs_import', 'content_id',
				array(
					"type" => "text",
					"notnull" => false,
					"length" => 255,
					'default' => ''
				)
		);
	}
?>
<#18>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#19>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#20>
<?php
if (!$ilDB->tableColumnExists("usr_data", "is_self_registered"))
{
	$ilDB->addTableColumn("usr_data", "is_self_registered", array(
			"type" => "integer",
			"notnull" => true,
			"default" => 0,
			"length" => 1)
	);
}
?>
<#21>
<?php
	// Manual feedback
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('tst_manual_fb', 'feedback_tmp', array(
											 'type' => 'clob',
											 'notnull' => false,
											 'default' => null)
	);
	
	$ilDB->manipulate('UPDATE tst_manual_fb SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('tst_manual_fb', 'feedback');
	$ilDB->renameTableColumn('tst_manual_fb', 'feedback_tmp', 'feedback');
?>
<#22>
<?php
	// Suggested Solution
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_sol_sug', 'value_tmp', array(
										   'type' => 'clob',
										   'notnull' => false,
										   'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_sol_sug SET value_tmp = value');
	$ilDB->dropTableColumn('qpl_sol_sug', 'value');
	$ilDB->renameTableColumn('qpl_sol_sug', 'value_tmp', 'value');
?>
<#23>
<?php
	// Feedback Cloze
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_cloze', 'feedback_tmp', array(
										   'type' => 'clob',
										   'notnull' => false,
										   'default' => null)
	);

	$ilDB->manipulate('UPDATE qpl_fb_cloze SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_cloze', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_cloze', 'feedback_tmp', 'feedback');
?>
<#24>
<?php
	// Feedback Errortext
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_errortext', 'feedback_tmp', array(
										   'type' => 'clob',
										   'notnull' => false,
										   'default' => null)
	);

	$ilDB->manipulate('UPDATE qpl_fb_errortext SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_errortext', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_errortext', 'feedback_tmp', 'feedback');
?>
<#25>
<?php
	// Feedback Essay
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_essay', 'feedback_tmp', array(
												'type' => 'clob',
												'notnull' => false,
												'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_essay SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_essay', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_essay', 'feedback_tmp', 'feedback');
?>
<#26>
<?php
	// Generic feedback
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_generic', 'feedback_tmp', array(
											  'type' => 'clob',
											  'notnull' => false,
											  'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_generic SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_generic', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_generic', 'feedback_tmp', 'feedback');
?>
<#27>
<?php
	// Feedback Imagemap
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_imap', 'feedback_tmp', array(
										   'type' => 'clob',
										   'notnull' => false,
										   'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_imap SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_imap', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_imap', 'feedback_tmp', 'feedback');
?>
<#28>
<?php
	// Feedback Matching
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_matching', 'feedback_tmp', array(
												'type' => 'clob',
												'notnull' => false,
												'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_matching SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_matching', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_matching', 'feedback_tmp', 'feedback');
?>
<#29>
<?php
	// Feedback Multiple Choice
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_mc', 'feedback_tmp', array(
										 'type' => 'clob',
										 'notnull' => false,
										 'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_mc SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_mc', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_mc', 'feedback_tmp', 'feedback');
?>
<#30>
<?php
	// Feedback Single Choice
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_fb_sc', 'feedback_tmp', array(
										 'type' => 'clob',
										 'notnull' => false,
										 'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_fb_sc SET feedback_tmp = feedback');
	$ilDB->dropTableColumn('qpl_fb_sc', 'feedback');
	$ilDB->renameTableColumn('qpl_fb_sc', 'feedback_tmp', 'feedback');
?>
<#31>
<?php
	// Hints
	/** @var ilDB $ilDB */
	$ilDB->addTableColumn('qpl_hints', 'hint_text_tmp', array(
										 'type' => 'clob',
										 'notnull' => false,
										 'default' => null)
	);
	
	$ilDB->manipulate('UPDATE qpl_hints SET hint_text_tmp = qht_hint_text');
	$ilDB->dropTableColumn('qpl_hints', 'qht_hint_text');
	$ilDB->renameTableColumn('qpl_hints', 'hint_text_tmp', 'qht_hint_text');
?>
<#32>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#33>
<?php

// #12845
$set = $ilDB->query("SELECT od.owner, prtf.id prtf_id, pref.value public".
	", MIN(acl.object_id) acl_type".
	" FROM usr_portfolio prtf".
	" JOIN object_data od ON (od.obj_id = prtf.id)".
	" LEFT JOIN usr_portf_acl acl ON (acl.node_id = prtf.id)".
	" LEFT JOIN usr_pref pref ON (pref.usr_id = od.owner".
	" AND pref.keyword = ".$ilDB->quote("public_profile", "text").")".
	" WHERE prtf.is_default = ".$ilDB->quote(1, "integer").
	" GROUP BY od.owner, prtf.id, pref.value");
while($row = $ilDB->fetchAssoc($set))
{	
	$acl_type = (int)$row["acl_type"];
	$pref = trim($row["public"]);
	
	// portfolio is not published, remove as profile
	if($acl_type >= 0)
	{
		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(0, "integer").
			" WHERE id = ".$ilDB->quote($row["prtf_id"], "integer"));		
		$new_pref = "n";
	}
	// check if portfolio sharing matches user preference
	else 
	{		
		// registered vs. published
		$new_pref = ($acl_type < -1)
			? "g"
			: "y";		
	}	
	
	if($pref)
	{
		if($pref != $new_pref)
		{
			$ilDB->manipulate("UPDATE usr_pref".
				" SET value = ".$ilDB->quote($new_pref, "text").
				" WHERE usr_id = ".$ilDB->quote($row["owner"], "integer").
				" AND keyword = ".$ilDB->quote("public_profile", "text"));
		}
	}	
	else
	{
		$ilDB->manipulate("INSERT INTO usr_pref (usr_id, keyword, value) VALUES".
			" (".$ilDB->quote($row["owner"], "integer").
			", ".$ilDB->quote("public_profile", "text").
			", ".$ilDB->quote($new_pref, "text").")");
	}	
}

?>
<#34>
<?php

$ilDB->modifyTableColumn(
		'usr_pwassist', 
		'pwassist_id',
		array(
			"type" => "text", 
			"length" => 180, 
			"notnull" => true,
			'fixed' => true
		)
	);
?>
<#35>
<?php

	$ilDB->addIndex('cal_shared',array('obj_id','obj_type'),'i1');
	
?>
<#36>
<?php

	$ilDB->addIndex('booking_reservation',array('user_id'),'i1');
	
?>
<#37>
<?php

	$ilDB->addIndex('booking_reservation',array('object_id'),'i2');
	
?>