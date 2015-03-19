<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
// This is the hotfix file for ILIAS 4.4.x DB fixes
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

$ilDB->modifyTableColumn(
		'object_data', 
		'title',
		array(
			"type" => "text", 
			"length" => 255, 
			"notnull" => false,
			'fixed' => true
		)
	);
?>
<#2>
<?php

// #12845
$set = $ilDB->query("SELECT od.owner, prtf.id prtf_id, pref.value public".
	", MIN(acl.object_id) acl_type".
	" FROM usr_portfolio prtf".
	" JOIN object_data od ON (od.obj_id = prtf.id".
	" AND od.type = ".$ilDB->quote("prtf", "text").")".
	" LEFT JOIN usr_portf_acl acl ON (acl.node_id = prtf.id)".
	" LEFT JOIN usr_pref pref ON (pref.usr_id = od.owner".
	" AND pref.keyword = ".$ilDB->quote("public_profile", "text").")".
	" WHERE prtf.is_default = ".$ilDB->quote(1, "integer").
	" GROUP BY od.owner, prtf.id, pref.value");
while($row = $ilDB->fetchAssoc($set))
{	
	$acl_type = (int)$row["acl_type"];
	$pref = trim($row["public"]);
	$user_id = (int)$row["owner"];
	$prtf_id = (int)$row["prtf_id"];
	
	if(!$user_id || !$prtf_id) // #12862
	{
		continue;
	}
	
	// portfolio is not published, remove as profile
	if($acl_type >= 0)
	{		
		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(0, "integer").
			" WHERE id = ".$ilDB->quote($prtf_id, "integer"));		
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
				" WHERE usr_id = ".$ilDB->quote($user_id, "integer").
				" AND keyword = ".$ilDB->quote("public_profile", "text"));
		}
	}	
	else
	{
		$ilDB->manipulate("INSERT INTO usr_pref (usr_id, keyword, value) VALUES".
			" (".$ilDB->quote($user_id, "integer").
			", ".$ilDB->quote("public_profile", "text").
			", ".$ilDB->quote($new_pref, "text").")");
	}	
}

?>

<#3>
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
	
<#4>
<?php
if( !$ilDB->tableColumnExists('tst_active', 'last_finished_pass') )
{
	$ilDB->addTableColumn('tst_active', 'last_finished_pass', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>
	
<#5>
<?php

if( !$ilDB->uniqueConstraintExists('tst_pass_result', array('active_fi', 'pass')) )
{
	$groupRes = $ilDB->query("
		SELECT COUNT(*), active_fi, pass FROM tst_pass_result GROUP BY active_fi, pass HAVING COUNT(*) > 1
	");

	$ilSetting = new ilSetting();

	$setting = $ilSetting->get('tst_passres_dupl_del_warning', 0);

	while( $groupRow = $ilDB->fetchAssoc($groupRes) )
	{
		if(!$setting)
		{
			echo "<pre>
				Dear Administrator,
				
				DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS
				
				The update process has been stopped due to data security reasons.
				A Bug has let to duplicate datasets in tst_pass_result table.
				Duplicates have been detected in your installation.
				
				Please have a look at: http://www.ilias.de/mantis/view.php?id=12904
				
				You have the opportunity to review the data in question and apply 
				manual fixes on your own risk.
				
				If you try to rerun the update process, this warning will be skipped.
				The duplicates will be removed automatically by the criteria documented at Mantis #12904
				
				Best regards,
				The Test Maintainers
			</pre>";

			$ilSetting->set('tst_passres_dupl_del_warning', 1);
			exit;
		}

		$dataRes = $ilDB->queryF(
			"SELECT * FROM tst_pass_result WHERE active_fi = %s AND pass = %s ORDER BY tstamp ASC",
			array('integer', 'integer'), array($groupRow['active_fi'], $groupRow['pass'])
		);

		$passResults = array();
		$latestTimstamp = 0;

		while( $dataRow = $ilDB->fetchAssoc($dataRes) )
		{
			if( $latestTimstamp < $dataRow['tstamp'] )
			{
				$latestTimstamp = $dataRow['tstamp'];
				$passResults = array();
			}

			$passResults[] = $dataRow;
		}

		$bestPointsRatio = 0;
		$bestPassResult = null;

		foreach($passResults as $passResult)
		{
			if( $passResult['maxpoints'] > 0 )
			{
				$pointsRatio = $passResult['points'] / $passResult['maxpoints'];
			}
			else
			{
				$pointsRatio = 0;
			}

			if( $bestPointsRatio <= $pointsRatio )
			{
				$bestPointsRatio = $pointsRatio;
				$bestPassResult = $passResult;
			}
		}

		$dataRes = $ilDB->manipulateF(
			"DELETE FROM tst_pass_result WHERE active_fi = %s AND pass = %s",
			array('integer', 'integer'), array($groupRow['active_fi'], $groupRow['pass'])
		);

		$ilDB->insert('tst_pass_result', array(
			'active_fi' => array('integer', $bestPassResult['active_fi']),
			'pass' => array('integer', $bestPassResult['pass']),
			'points' => array('float', $bestPassResult['points']),
			'maxpoints' => array('float', $bestPassResult['maxpoints']),
			'questioncount' => array('integer', $bestPassResult['questioncount']),
			'answeredquestions' => array('integer', $bestPassResult['answeredquestions']),
			'workingtime' => array('integer', $bestPassResult['workingtime']),
			'tstamp' => array('integer', $bestPassResult['tstamp']),
			'hint_count' => array('integer', $bestPassResult['hint_count']),
			'hint_points' => array('float', $bestPassResult['hint_points']),
			'obligations_answered' => array('integer', $bestPassResult['obligations_answered']),
			'exam_id' => array('text', $bestPassResult['exam_id'])
		));
	}

	$ilDB->addUniqueConstraint('tst_pass_result', array('active_fi', 'pass'));
}

?>

<#6>
<?php
if( !$ilDB->uniqueConstraintExists('tst_sequence', array('active_fi', 'pass')) )
{
	$groupRes = $ilDB->query("
		SELECT COUNT(*), active_fi, pass FROM tst_sequence GROUP BY active_fi, pass HAVING COUNT(*) > 1
	");

	$ilSetting = new ilSetting();

	$setting = $ilSetting->get('tst_seq_dupl_del_warning', 0);

	while( $groupRow = $ilDB->fetchAssoc($groupRes) )
	{
		if(!$setting)
		{
			echo "<pre>
				Dear Administrator,
				
				DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS
				
				The update process has been stopped due to data security reasons.
				A Bug has let to duplicate datasets in tst_sequence table.
				Duplicates have been detected in your installation.
				
				Please have a look at: http://www.ilias.de/mantis/view.php?id=12904
				
				You have the opportunity to review the data in question and apply 
				manual fixes on your own risk.
				
				If you try to rerun the update process, this warning will be skipped.
				The duplicates will be removed automatically by the criteria documented at Mantis #12904
				
				Best regards,
				The Test Maintainers
			</pre>";

			$ilSetting->set('tst_seq_dupl_del_warning', 1);
			exit;
		}

		$dataRes = $ilDB->queryF(
			"SELECT * FROM tst_sequence WHERE active_fi = %s AND pass = %s ORDER BY tstamp DESC",
			array('integer', 'integer'), array($groupRow['active_fi'], $groupRow['pass'])
		);

		while( $dataRow = $ilDB->fetchAssoc($dataRes) )
		{
			$ilDB->manipulateF(
				"DELETE FROM tst_sequence WHERE active_fi = %s AND pass = %s",
				array('integer', 'integer'), array($groupRow['active_fi'], $groupRow['pass'])
			);

			$ilDB->insert('tst_sequence', array(
				'active_fi' => array('integer', $dataRow['active_fi']),
				'pass' => array('integer', $dataRow['pass']),
				'sequence' => array('text', $dataRow['sequence']),
				'postponed' => array('text', $dataRow['postponed']),
				'hidden' => array('text', $dataRow['hidden']),
				'tstamp' => array('integer', $dataRow['tstamp'])
			));

			break;
		}
	}

	$ilDB->addUniqueConstraint('tst_sequence', array('active_fi', 'pass'));
}
?>
<#7>
<?php

	$ilDB->dropIndexByFields('cal_auth_token',array('user_id'));

?>
<#8>
<?php

	if(!$ilDB->indexExistsByFields('cal_shared',array('obj_id','obj_type')))
	{
		$ilDB->addIndex('cal_shared',array('obj_id','obj_type'),'i1');
	}
?>

<#9>
<?php
	if(!$ilDB->indexExistsByFields('cal_entries',array('last_update')))
	{
		$ilDB->addIndex('cal_entries',array('last_update'),'i1');
	}
?>
<#10>
<?php

	$query = 'SELECT value from settings where module = '.$ilDB->quote('common','text').
			'AND keyword = '.$ilDB->quote('main_tree_impl','text');
	$res = $ilDB->query($query);
	
	$tree_impl = 'ns';
	while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$tree_impl = $row->value;
	}
	
	if($tree_impl == 'mp')
	{
		if(!$ilDB->indexExistsByFields('tree',array('path')))
		{
			$ilDB->dropIndexByFields('tree',array('lft'));
			$ilDB->addIndex('tree',array('path'),'i4');
		}
	}
?>
<#11>
<?php
	if(!$ilDB->indexExistsByFields('booking_reservation',array('user_id')))
	{
		$ilDB->addIndex('booking_reservation',array('user_id'),'i1');
	}
?>
<#12>
<?php
	if(!$ilDB->indexExistsByFields('booking_reservation',array('object_id')))
	{
		$ilDB->addIndex('booking_reservation',array('object_id'),'i2');
	}
?>
<#13>
<?php
	if(!$ilDB->indexExistsByFields('cal_entries',array('context_id')))
	{
		$ilDB->addIndex('cal_entries',array('context_id'),'i2');
	}
?>
<#14>
<?php

	$ilDB->modifyTableColumn(
			'usr_data', 
			'ext_account',
			array(
				"type" => "text", 
				"length" => 250, 
				"notnull" => false,
				'fixed' => false
			)
		);
?>

<#15>
<?php

	$ilDB->modifyTableColumn(
			'usr_session', 
			'session_id',
			array(
				"type" => "text", 
				"length" => 250, 
				"notnull" => true,
				'fixed' => false
			)
		);
?>
<#16>
<?php
	// Get defective active-id sequences by finding active ids lower than zero. The abs of the low-pass is the count of the holes
	// in the sequence.
	$result = $ilDB->query('SELECT active_fi, min(pass) pass FROM tst_pass_result WHERE pass < 0 GROUP BY active_fi');
	$broken_sequences = array();

	while ( $row = $ilDB->fetchAssoc($result) )
	{
		$broken_sequences[] = array('active' => $row['active'], 'holes' => abs($row['pass']));
	}

	$stmt_inc_pass_res 	= $ilDB->prepareManip('UPDATE tst_pass_result 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
	$stmt_inc_man_fb 	= $ilDB->prepareManip('UPDATE tst_manual_fb 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
	$stmt_inc_seq 		= $ilDB->prepareManip('UPDATE tst_sequence 		SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
	$stmt_inc_sol 		= $ilDB->prepareManip('UPDATE tst_solutions 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
	$stmt_inc_times 	= $ilDB->prepareManip('UPDATE tst_times 		SET pass = pass + 1 WHERE active_fi = ?', array('integer'));

	$stmt_sel_passes 	= $ilDB->prepare('SELECT pass FROM tst_pass_result WHERE active_fi = ? ORDER BY pass', array('integer'));

	$stmt_dec_pass_res 	= $ilDB->prepareManip('UPDATE tst_pass_result 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
	$stmt_dec_man_fb 	= $ilDB->prepareManip('UPDATE tst_manual_fb 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
	$stmt_dec_seq 		= $ilDB->prepareManip('UPDATE tst_sequence 		SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
	$stmt_dec_sol 		= $ilDB->prepareManip('UPDATE tst_solutions 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
	$stmt_dec_times 	= $ilDB->prepareManip('UPDATE tst_times 		SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));

	// Iterate over affected passes
	foreach ( $broken_sequences as $broken_sequence )
	{
		// Recreate the unbroken, pre-renumbering state by incrementing all passes on all affected tables for the detected broken active_fi.
		for($i = 1; $i <= $broken_sequence['holes']; $i++)
		{
			$ilDB->execute($stmt_inc_pass_res,	array($broken_sequence['active']));
			$ilDB->execute($stmt_inc_man_fb, 	array($broken_sequence['active']));
			$ilDB->execute($stmt_inc_seq, 		array($broken_sequence['active']));
			$ilDB->execute($stmt_inc_sol, 		array($broken_sequence['active']));
			$ilDB->execute($stmt_inc_times, 	array($broken_sequence['active']));
		}

		// Detect the holes and renumber correctly on all affected tables.
		for($i = 1; $i <= $broken_sequence['holes']; $i++)
		{
			$result = $ilDB->execute($stmt_sel_passes, array($broken_sequence['active']));
			$index = 0;
			while($row = $ilDB->fetchAssoc($result))
			{
				if ($row['pass'] == $index)
				{
					$index++;
					continue;
				}

				// Reaching here, there is a missing index, now decrement all higher passes, preserving additional holes.
				$ilDB->execute($stmt_dec_pass_res, 	array($broken_sequence['active'], $index));
				$ilDB->execute($stmt_dec_man_fb, 	array($broken_sequence['active'], $index));
				$ilDB->execute($stmt_dec_seq, 		array($broken_sequence['active'], $index));
				$ilDB->execute($stmt_dec_sol, 		array($broken_sequence['active'], $index));
				$ilDB->execute($stmt_dec_times, 	array($broken_sequence['active'], $index));
				break;
				// Hole detection will start over.
			}
		}
	}
?>
<#17>
<?php

if( !$ilDB->tableExists('tmp_tst_to_recalc') )
{
	$ilDB->createTable('tmp_tst_to_recalc', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => -1
		)
	));

	$ilDB->addUniqueConstraint('tmp_tst_to_recalc', array('active_fi', 'pass'));
}

$groupQuery = "
			SELECT      tst_test_result.active_fi,
						tst_test_result.question_fi,
						tst_test_result.pass,
						MAX(test_result_id) keep_id
			
			FROM        tst_test_result

            INNER JOIN  tst_active
            ON          tst_active.active_id = tst_test_result.active_fi
			
            INNER JOIN  tst_tests
            ON          tst_tests.test_id = tst_active.test_fi
			
            INNER JOIN  object_data
            ON          object_data.obj_id = tst_tests.obj_fi

            WHERE       object_data.type = %s
			
			GROUP BY    tst_test_result.active_fi,
						tst_test_result.question_fi,
						tst_test_result.pass
			
			HAVING      COUNT(*) > 1
		";

$numQuery = "SELECT COUNT(*) num FROM ($groupQuery) tbl";
$numRes = $ilDB->queryF($numQuery, array('text'), array('tst'));
$numRow = $ilDB->fetchAssoc($numRes);

$ilSetting = new ilSetting();
$setting = $ilSetting->get('tst_test_results_dupl_del_warn', 0);

if( (int)$numRow['num'] && !(int)$setting )
{
	echo "<pre>

		Dear Administrator,
		
		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS
		
		The update process has been stopped due to data security reasons.
		A Bug has let to duplicate datasets in \"tst_test_result\" table.
		Duplicates have been detected in your installation.
		
		Please have a look at: http://www.ilias.de/mantis/view.php?id=8992#c27369
		
		You have the opportunity to review the data in question and apply 
		manual fixes on your own risk.
		If you change any data manually, make sure to also add an entry in the table \"tmp_tst_to_recalc\"
		for each active_fi/pass combination that is involved.
		The required re-calculation of related result aggregations won't be triggered otherwise.
		
		If you try to rerun the update process, this warning will be skipped.
		The remaining duplicates will be removed automatically by the criteria documented at Mantis #8992
		
		Best regards,
		The Test Maintainers
		
	</pre>";

	$ilSetting->set('tst_test_results_dupl_del_warn', 1);
	exit;
}

if( (int)$numRow['num'] )
{
	$groupRes = $ilDB->queryF($groupQuery, array('text'), array('tst'));

	$deleteStmt = $ilDB->prepareManip(
		"DELETE FROM tst_test_result WHERE active_fi = ? AND pass = ? AND question_fi = ? AND test_result_id != ?",
		array('integer', 'integer', 'integer', 'integer')
	);

	while( $groupRow = $ilDB->fetchAssoc($groupRes) )
	{
		$pkCols = array(
			'active_fi' => array('integer', $groupRow['active_fi']),
			'pass' => array('integer', $groupRow['pass'])
		);

		$ilDB->replace('tmp_tst_to_recalc', $pkCols, array());

		$ilDB->execute($deleteStmt, array(
			$groupRow['active_fi'], $groupRow['pass'], $groupRow['question_fi'], $groupRow['keep_id']
		));
	}
}

?>
<#18>
<?php

if( $ilDB->tableExists('tmp_tst_to_recalc') )
{
	$deleteStmt = $ilDB->prepareManip(
		"DELETE FROM tmp_tst_to_recalc WHERE active_fi = ? AND pass = ?", array('integer', 'integer')
	);

	$res = $ilDB->query("
			SELECT		tmp_tst_to_recalc.*,
						tst_tests.obligations_enabled,
						tst_tests.question_set_type,
						tst_tests.obj_fi,
						tst_tests.pass_scoring
	
			FROM		tmp_tst_to_recalc
	
			INNER JOIN  tst_active
			ON          tst_active.active_id = tmp_tst_to_recalc.active_fi
			
			INNER JOIN  tst_tests
			ON          tst_tests.test_id = tst_active.test_fi
	");

	require_once 'Services/Migration/Hotfix_18/classes/class.DBUpdateTestResultCalculator.php';

	while( $row = $ilDB->fetchAssoc($res) )
	{
		DBUpdateTestResultCalculator::_updateTestPassResults(
			$row['active_fi'], $row['pass'], $row['obligations_enabled'],
			$row['question_set_type'], $row['obj_fi']
		);

		DBUpdateTestResultCalculator::_updateTestResultCache(
			$row['active_fi'], $row['pass_scoring']
		);

		$ilDB->execute($deleteStmt, array($row['active_fi'], $row['pass']));
	}

	$ilDB->dropTable('tmp_tst_to_recalc');
}

// reset setting for shown clean up warning (see last hotfix),
// so trunk dbupdate won't clean up automatically without another warning
$ilSetting = new ilSetting();
$ilSetting->set('tst_test_results_dupl_del_warn', 0);

?>
<#19>
<?php
	// get all imagemap questions in ILIAS learning modules or scorm learning modules
	$set = $ilDB->query("SELECT pq.question_id FROM page_question pq JOIN qpl_qst_imagemap im ON (pq.question_id = im.question_fi) ".
		" WHERE pq.page_parent_type = ".$ilDB->quote("lm", "text").
		" OR pq.page_parent_type = ".$ilDB->quote("sahs", "text")
	);
	while ($rec = $ilDB->fetchAssoc($set))
	{
		// now cross-check against qpl_questions to ensure that this is neither a test nor a question pool question
		$set2 = $ilDB->query("SELECT obj_fi FROM qpl_questions ".
			" WHERE question_id = ".$ilDB->quote($rec["question_id"], "integer")
		);
		if ($rec2 = $ilDB->fetchAssoc($set2))
		{
			// this should not be the case for question pool or test questions
			if ($rec2["obj_fi"] == 0)
			{
				$q = "UPDATE qpl_qst_imagemap SET ".
					" is_multiple_choice = ".$ilDB->quote(1, "integer").
					" WHERE question_fi = ".$ilDB->quote($rec["question_id"], "integer");
				$ilDB->manipulate($q);
			}
		}
	}
	$ilSetting = new ilSetting();
	$setting = $ilSetting->set('lm_qst_imap_migr_run', 1);
?>
<#20>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#21>
<?php
// REMOVED: is done at #23 in an abstracted way
// Bibliographic Module: Increase the allowed text-size for attributes from 512 to 4000
//$ilDB->query('ALTER TABLE il_bibl_attribute MODIFY value VARCHAR(4000)');
?>
<#22>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#23>
<?php
// Bibliographic Module: Increase the allowed text-size for attributes from 512 to 4000
$ilDB->modifyTableColumn("il_bibl_attribute", "value", array("type" => "text", "length" => 4000));
?>
<#24>
<?php
if( !$ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'answer_filter_enabled') )
{
	$ilDB->addTableColumn('tst_dyn_quest_set_cfg', 'answer_filter_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
}
if( !$ilDB->tableColumnExists('tst_active', 'answerstatusfilter') )
{
	$ilDB->addTableColumn('tst_active', 'answerstatusfilter', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));
}
?>
<#25>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#26>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#27>
<?php
if( $ilDB->tableColumnExists('qpl_qst_essay', 'keyword_relation') )
{
	$ilDB->queryF(
		"UPDATE qpl_qst_essay SET keyword_relation = %s WHERE keyword_relation = %s",
		array('text', 'text'), array('non', 'none')
	);
}
?>
<#28>
<?php
	$ilDB->modifyTableColumn(
		'help_map',
		'screen_id',
		array(
			"type" => "text",
			"length" => 100,
			"notnull" => false,
			'fixed' => false
		)
	);
?>
<#29>
	<?php
	$ilDB->modifyTableColumn(
		'help_map',
		'screen_sub_id',
		array(
			"type" => "text",
			"length" => 100,
			"notnull" => false,
			'fixed' => false
		)
	);
?>
<#30>
<?php
if( !$ilDB->tableExists('tst_seq_qst_checked') )
{
	$ilDB->createTable('tst_seq_qst_checked', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_seq_qst_checked',array('active_fi','pass', 'question_fi'));
}

if( !$ilDB->tableColumnExists('tst_tests', 'inst_fb_answer_fixation') )
{
	$ilDB->addTableColumn('tst_tests', 'inst_fb_answer_fixation', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
}
?>
<#31>
<?php
	$ilDB->modifyTableColumn(
		'ecs_server',
		'auth_pass',
		array(
			"type" => "text",
			"length" => 128,
			"notnull" => false,
			'fixed' => false
		)
	);
?>
<#32>
<?php

// #13822 - oracle does not support ALTER TABLE varchar2 to CLOB
	
$ilDB->lockTables(array(
	array('name'=>'exc_assignment_peer', 'type'=>ilDB::LOCK_WRITE)
));

$def = array(
	'type'    => 'clob',
	'notnull' => false
);
$ilDB->addTableColumn('exc_assignment_peer', 'pcomment_long', $def);	

$ilDB->manipulate('UPDATE exc_assignment_peer SET pcomment_long = pcomment');

$ilDB->dropTableColumn('exc_assignment_peer', 'pcomment');

$ilDB->renameTableColumn('exc_assignment_peer', 'pcomment_long', 'pcomment');

$ilDB->unlockTables();

?>
<#33>
<?php

	$a_obj_id = array();
	$a_scope_id = array();
	$a_scope_id_one = array();
	//select targetobjectiveid = cmi_gobjective.objective_id
	$res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid 
		FROM cp_package, cp_mapinfo, cp_node 
		WHERE cp_package.global_to_system = 0 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id 
		GROUP BY cp_mapinfo.targetobjectiveid');
	while($data = $ilDB->fetchAssoc($res)) 
	{
		$a_obj_id[] = $data['targetobjectiveid'];
	}
	//make arrays
	for ($i=0;$i<count($a_obj_id);$i++) {
		$a_scope_id[$a_obj_id[$i]] = array();
		$a_scope_id_one[$a_obj_id[$i]] = array();
	}
	//only global_to_system=0 -> should be updated
	$res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id 
		FROM cp_package, cp_mapinfo, cp_node 
		WHERE cp_package.global_to_system = 0 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
	while($data = $ilDB->fetchAssoc($res)) 
	{
		$a_scope_id[$data['targetobjectiveid']][] = $data['obj_id'];
	}
	//only global_to_system=1 -> should maintain
	$res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id 
		FROM cp_package, cp_mapinfo, cp_node 
		WHERE cp_package.global_to_system = 1 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
	while($data = $ilDB->fetchAssoc($res)) 
	{
		$a_scope_id_one[$data['targetobjectiveid']][] = $data['obj_id'];
	}

	//for all targetobjectiveid
	for ($i=0;$i<count($a_obj_id);$i++) {
		$a_toupdate = array();
		//get old data without correct scope_id
		$res = $ilDB->queryF(
			"SELECT * FROM cmi_gobjective WHERE scope_id = %s AND objective_id = %s",
			array('integer', 'text'),
			array(0, $a_obj_id[$i])
		);
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$a_toupdate[] = $data;
		}
		//check specific possible scope_ids with global_to_system=0 -> a_o
		$a_o = $a_scope_id[$a_obj_id[$i]];
		for ($z=0; $z<count($a_o); $z++) {
			//for all existing entries
			for ($y=0; $y<count($a_toupdate); $y++) {
				$a_t=$a_toupdate[$y];
				//only users attempted
				$res = $ilDB->queryF('SELECT user_id FROM sahs_user WHERE obj_id=%s AND user_id=%s',
					array('integer', 'integer'),
					array($a_o[$z], $a_t['user_id'])
				);
				if($ilDB->numRows($res)) {
				//check existing entry
					$res = $ilDB->queryF('SELECT user_id FROM cmi_gobjective WHERE scope_id=%s AND user_id=%s AND objective_id=%s',
						array('integer', 'integer','text'),
						array($a_o[$z], $a_t['user_id'],$a_t['objective_id'])
					);
					if(!$ilDB->numRows($res)) {
						$ilDB->manipulate("INSERT INTO cmi_gobjective (user_id, satisfied, measure, scope_id, status, objective_id, score_raw, score_min, score_max, progress_measure, completion_status) VALUES"
						." (".$ilDB->quote($a_t['user_id'], "integer")
						.", ".$ilDB->quote($a_t['satisfied'], "text")
						.", ".$ilDB->quote($a_t['measure'], "text")
						.", ".$ilDB->quote($a_o[$z], "integer")
						.", ".$ilDB->quote($a_t['status'], "text")
						.", ".$ilDB->quote($a_t['objective_id'], "text")
						.", ".$ilDB->quote($a_t['score_raw'], "text")
						.", ".$ilDB->quote($a_t['score_min'], "text")
						.", ".$ilDB->quote($a_t['score_max'], "text")
						.", ".$ilDB->quote($a_t['progress_measure'], "text")
						.", ".$ilDB->quote($a_t['completion_status'], "text")
						.")");
					}
				}
			}
		}
		//delete entries if global_to_system=1 is not used by any learning module
		if (count($a_scope_id_one[$a_obj_id[$i]]) == 0) {
			$ilDB->queryF(
				'DELETE FROM cmi_gobjective WHERE scope_id = %s AND objective_id = %s',
				array('integer', 'text'),
				array(0, $a_obj_id[$i])
			);
		}
	}
	
	
?>
<#34>
<?php
	$ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
?>
<#35>
<?php
//#13883
	if($ilDB->getDBType() == 'innodb')
	{
		$ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
	}
?>
<#36>
<?php

// #13858 
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::varchar2text('rbac_log', 'data');

?>
<#37>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#38>
<?php
$ilDB->addTableColumn("tst_test_defaults", "marks_tmp", array(
	"type" => "clob",
	"notnull" => false,
	"default" => null)
);

$ilDB->manipulate('UPDATE tst_test_defaults SET marks_tmp = marks');
$ilDB->dropTableColumn('tst_test_defaults', 'marks');
$ilDB->renameTableColumn("tst_test_defaults", "marks_tmp", "marks");
?>
<#39>
<?php
$ilDB->addTableColumn("tst_test_defaults", "defaults_tmp", array(
	"type" => "clob",
	"notnull" => false,
	"default" => null)
);

$ilDB->manipulate('UPDATE tst_test_defaults SET defaults_tmp = defaults');
$ilDB->dropTableColumn('tst_test_defaults', 'defaults');
$ilDB->renameTableColumn("tst_test_defaults", "defaults_tmp", "defaults");
?>
<#40>
<?php

if( !$ilDB->tableExists('tst_seq_qst_tracking') )
{
	$ilDB->createTable('tst_seq_qst_tracking', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'status' => array(
			'type' => 'text',
			'length' => 16,
			'notnull' => false
		),
		'orderindex' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_seq_qst_tracking', array('active_fi', 'pass', 'question_fi'));
	$ilDB->addIndex('tst_seq_qst_tracking', array('active_fi', 'pass'), 'i1');
	$ilDB->addIndex('tst_seq_qst_tracking', array('active_fi', 'question_fi'), 'i2');
}

?>
<#41>
<?php

$query = "
	SELECT active_fi, pass, sequence
	FROM tst_tests
	INNER JOIN tst_active
	ON test_fi = test_id
	INNER JOIN tst_sequence
	ON active_fi = active_id
	AND sequence IS NOT NULL
	WHERE question_set_type = %s
";

$res = $ilDB->queryF($query, array('text'), array('DYNAMIC_QUEST_SET'));

while( $row = $ilDB->fetchAssoc($res) )
{
	$tracking = unserialize($row['sequence']);

	if( is_array($tracking) )
	{
		foreach($tracking as $index => $question)
		{
			$ilDB->replace('tst_seq_qst_tracking',
				array(
					'active_fi' => array('integer', $row['active_fi']),
					'pass' => array('integer', $row['pass']),
					'question_fi' => array('integer', $question['qid'])
				),
				array(
					'status' => array('text', $question['status']),
					'orderindex' => array('integer', $index + 1)
				)
			);
		}

		$ilDB->update('tst_sequence',
			array(
				'sequence' => array('text', null)
			),
			array(
				'active_fi' => array('integer', $row['active_fi']),
				'pass' => array('integer', $row['pass'])
			)
		);
	}
}

?>
<#42>
<?php

if( !$ilDB->tableExists('tst_seq_qst_postponed') )
{
	$ilDB->createTable('tst_seq_qst_postponed', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'cnt' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_seq_qst_postponed', array('active_fi', 'pass', 'question_fi'));
	$ilDB->addIndex('tst_seq_qst_postponed', array('active_fi', 'pass'), 'i1');
	$ilDB->addIndex('tst_seq_qst_postponed', array('active_fi', 'question_fi'), 'i2');
}

?>
<#43>
<?php

$query = "
	SELECT active_fi, pass, postponed
	FROM tst_tests
	INNER JOIN tst_active
	ON test_fi = test_id
	INNER JOIN tst_sequence
	ON active_fi = active_id
	AND postponed IS NOT NULL
	WHERE question_set_type = %s
";

$res = $ilDB->queryF($query, array('text'), array('DYNAMIC_QUEST_SET'));

while( $row = $ilDB->fetchAssoc($res) )
{
	$postponed = unserialize($row['postponed']);

	if( is_array($postponed) )
	{
		foreach($postponed as $questionId => $postponeCount)
		{
			$ilDB->replace('tst_seq_qst_postponed',
				array(
					'active_fi' => array('integer', $row['active_fi']),
					'pass' => array('integer', $row['pass']),
					'question_fi' => array('integer', $questionId)
				),
				array(
					'cnt' => array('integer', $postponeCount)
				)
			);
		}

		$ilDB->update('tst_sequence',
			array(
				'postponed' => array('text', null)
			),
			array(
				'active_fi' => array('integer', $row['active_fi']),
				'pass' => array('integer', $row['pass'])
			)
		);
	}
}

?>
<#44>
<?php

if( !$ilDB->tableExists('tst_seq_qst_answstatus') )
{
	$ilDB->createTable('tst_seq_qst_answstatus', array(
		'active_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'pass' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'correctness' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_seq_qst_answstatus', array('active_fi', 'pass', 'question_fi'));
	$ilDB->addIndex('tst_seq_qst_answstatus', array('active_fi', 'pass'), 'i1');
	$ilDB->addIndex('tst_seq_qst_answstatus', array('active_fi', 'question_fi'), 'i2');
}

?>
<#45>
<?php

$query = "
	SELECT active_fi, pass, hidden
	FROM tst_tests
	INNER JOIN tst_active
	ON test_fi = test_id
	INNER JOIN tst_sequence
	ON active_fi = active_id
	AND hidden IS NOT NULL
	WHERE question_set_type = %s
";

$res = $ilDB->queryF($query, array('text'), array('DYNAMIC_QUEST_SET'));

while( $row = $ilDB->fetchAssoc($res) )
{
	$answerStatus = unserialize($row['hidden']);

	if( is_array($answerStatus) )
	{
		foreach($answerStatus['correct'] as $questionId)
		{
			$ilDB->replace('tst_seq_qst_answstatus',
				array(
					'active_fi' => array('integer', $row['active_fi']),
					'pass' => array('integer', $row['pass']),
					'question_fi' => array('integer', $questionId)
				),
				array(
					'correctness' => array('integer', 1)
				)
			);
		}

		foreach($answerStatus['wrong'] as $questionId)
		{
			$ilDB->replace('tst_seq_qst_answstatus',
				array(
					'active_fi' => array('integer', $row['active_fi']),
					'pass' => array('integer', $row['pass']),
					'question_fi' => array('integer', $questionId)
				),
				array(
					'correctness' => array('integer', 0)
				)
			);
		}

		$ilDB->update('tst_sequence',
			array(
				'hidden' => array('text', null)
			),
			array(
				'active_fi' => array('integer', $row['active_fi']),
				'pass' => array('integer', $row['pass'])
			)
		);
	}
}

?>
<#46>
<?php

$indexName = $ilDB->constraintName('tst_dyn_quest_set_cfg', $ilDB->getPrimaryKeyIdentifier());

if( ($ilDB->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) && $ilDB->db->options['field_case'] == CASE_LOWER )
{
	$indexName = strtolower($indexName);
}
else
{
	$indexName = strtoupper($indexName);
}

$indexDefinition = $ilDB->db->loadModule('Reverse')->getTableConstraintDefinition('tst_dyn_quest_set_cfg', $indexName);

if( $indexDefinition instanceof MDB2_Error )
{
	$res = $ilDB->query("
		SELECT test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		FROM tst_dyn_quest_set_cfg
		GROUP BY test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		HAVING COUNT(*) > 1
	");
	
	$insertStmt = $ilDB->prepareManip("
		INSERT INTO tst_dyn_quest_set_cfg (
		test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		) VALUES (?, ?, ?, ?, ?, ?)
		", array('integer', 'integer', 'text', 'integer', 'integer', 'integer')
	);
	
	while($row = $ilDB->fetchAssoc($res) )
	{
		$expressions = array();
		
		foreach($row as $field => $value)
		{
			if($value === null)
			{
				$expressions[] = "$field IS NULL";
			}
			else
			{
				if( $field == 'source_qpl_title' )
				{
					$value = $ilDB->quote($value, 'text');
				}
				else
				{
					$value = $ilDB->quote($value, 'integer');
				}
				
				$expressions[] = "$field = $value";
			}
		}
		
		$expressions = implode(' AND ', $expressions);
		
		$ilDB->manipulate("DELETE FROM tst_dyn_quest_set_cfg WHERE $expressions");
		
		$ilDB->execute($insertStmt, array_values($row));
	}
	
	$ilDB->addPrimaryKey('tst_dyn_quest_set_cfg', array('test_fi'));
}

?>
<#47>
<?php
if(!$ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled'))
{
	$ilDB->addTableColumn(
		'tst_dyn_quest_set_cfg',
		'prev_quest_list_enabled',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => TRUE,
			'default' => 0
		));
}
?>
<#48>
<?php

$ilDB->manipulate("DELETE FROM settings".
	" WHERE module = ".$ilDB->quote("common", "text").
	" AND keyword = ".$ilDB->quote("obj_dis_creation_rcrs", "text"));

?>
<#49>
<?php
	$ilDB->manipulate("UPDATE frm_posts SET pos_update = pos_date WHERE pos_update IS NULL");
?>
<#50>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#51>
<?php
$setting = new ilSetting();
$fixState = $setting->get('dbupdate_randtest_pooldef_migration_fix', '0');

if( $fixState === '0' )
{
	$query = "
		SELECT		tst_tests.test_id, COUNT(tst_rnd_quest_set_qpls.def_id)
		 
		FROM		tst_tests

		LEFT JOIN	tst_rnd_quest_set_qpls
		ON			tst_tests.test_id = tst_rnd_quest_set_qpls.test_fi
		 
		WHERE		question_set_type = %s
		
		GROUP BY	tst_tests.test_id
		
		HAVING		COUNT(tst_rnd_quest_set_qpls.def_id) < 1
	";

	$res = $ilDB->queryF($query, array('text'), array('RANDOM_QUEST_SET'));

	$testsWithoutDefinitionsDetected = false;

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$testsWithoutDefinitionsDetected = true;
		break;
	}

	if( $testsWithoutDefinitionsDetected )
	{
		echo "<pre>

		Dear Administrator,
		
		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped, because your attention is required.
		
		If you did not migrate ILIAS from version 4.3 to 4.4, but installed a 4.4 version from scratch,
		please ignore this message and simply refresh the page.

		Otherwise please have a look to: http://www.ilias.de/mantis/view.php?id=12700

		A bug in the db migration for ILIAS 4.4.x has lead to missing source pool definitions within several random tests.
		Your installation could be affected, because random tests without any source pool definition were detected.
		Perhaps, these tests were just created, but the update process has to assume that these tests are broken.
		
		If you have a backup of your old ILIAS 4.3.x database the update process can repair these tests.
		Therefor please restore the table > tst_test_random < from your ILIAS 4.3.x backup database to your productive ILIAS 4.4.x database.
		
		If you try to rerun the update process by refreshing the page, this message will be skipped.
		
		Possibly broken random tests will be repaired, if the old database table mentioned above is available.
		After repairing the tests, the old database table will be dropped again.
		
		Best regards,
		The Test Maintainers
		
		</pre>";

		$setting->set('dbupdate_randtest_pooldef_migration_fix', '1');

		exit; // db update step MUST NOT finish in a normal way, so step will be processed again
	}
	else
	{
		$setting->set('dbupdate_randtest_pooldef_migration_fix', '2');
	}
}
elseif( $fixState === '1' )
{
	if( $ilDB->tableExists('tst_test_random') )
	{
		$query = "
			SELECT		tst_test_random.test_fi,
						tst_test_random.questionpool_fi,
						tst_test_random.num_of_q,
						tst_test_random.tstamp,
						tst_test_random.sequence,
						object_data.title pool_title
			 
			FROM		tst_tests
			 
			INNER JOIN	tst_test_random
			ON			tst_tests.test_id = tst_test_random.test_fi
			 
			LEFT JOIN	tst_rnd_quest_set_qpls
			ON			tst_tests.test_id = tst_rnd_quest_set_qpls.test_fi
			
			LEFT JOIN	object_data
			ON 			object_data.obj_id = tst_test_random.questionpool_fi
			 
			WHERE		question_set_type = %s
			AND			tst_rnd_quest_set_qpls.def_id IS NULL
		";

		$res = $ilDB->queryF($query, array('text'), array('RANDOM_QUEST_SET'));

		$syncTimes = array();

		while( $row = $ilDB->fetchAssoc($res) )
		{
			if( !(int)$row['num_of_q'] )
			{
				$row['num_of_q'] = null;
			}

			if( !strlen($row['pool_title']) )
			{
				$row['pool_title'] = '*** unknown/deleted ***';
			}

			$nextId = $ilDB->nextId('tst_rnd_quest_set_qpls');

			$ilDB->insert('tst_rnd_quest_set_qpls', array(
				'def_id' => array('integer', $nextId),
				'test_fi' => array('integer', $row['test_fi']),
				'pool_fi' => array('integer', $row['questionpool_fi']),
				'pool_title' => array('text', $row['pool_title']),
				'origin_tax_fi' => array('integer', null),
				'origin_node_fi' => array('integer', null),
				'mapped_tax_fi' => array('integer', null),
				'mapped_node_fi' => array('integer', null),
				'quest_amount' => array('integer', $row['num_of_q']),
				'sequence_pos' => array('integer', $row['sequence'])
			));

			if( !is_array($syncTimes[$row['test_fi']]) )
			{
				$syncTimes[$row['test_fi']] = array();
			}

			$syncTimes[$row['test_fi']][] = $row['tstamp'];
		}

		foreach($syncTimes as $testId => $times)
		{
			$assumedSyncTS = max($times);

			$ilDB->update('tst_rnd_quest_set_cfg',
				array(
					'quest_sync_timestamp' => array('integer', $assumedSyncTS)
				),
				array(
					'test_fi' => array('integer', $testId)
				)
			);
		}
	}

	$setting->set('dbupdate_randtest_pooldef_migration_fix', '2');
}
?>
<#52>
<?php
if( $ilDB->tableExists('tst_test_random') )
{
	$ilDB->dropTable('tst_test_random');
}
?>
<#53>
<?php
// code erased 
?>
<#54>
<?php

if( !$ilDB->uniqueConstraintExists('tst_active', array('user_fi', 'test_fi', 'anonymous_id')) )
{
	$ilDB->createTable('tmp_active_fix', array(
		'test_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'anonymous_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => '-'
		),
		'active_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => null
		)
	));

	$ilDB->addPrimaryKey('tmp_active_fix', array('test_fi', 'user_fi', 'anonymous_id'));
	
	$res = $ilDB->query("
		SELECT COUNT(*), test_fi, user_fi, anonymous_id
		FROM tst_active
		GROUP BY user_fi, test_fi, anonymous_id
		HAVING COUNT(*) > 1
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		if( is_null($row['anonymous_id']) || !strlen($row['anonymous_id']) )
		{
			$row['anonymous_id'] = '-';
		}
		
		$ilDB->replace('tmp_active_fix',
			array(
				'test_fi' => array('integer', $row['test_fi']),
				'user_fi' => array('integer', (int)$row['user_fi']),
				'anonymous_id' => array('text', $row['anonymous_id'])
			),
			array()
		);
	}
}



?>
<#55>
<?php

if( $ilDB->tableExists('tmp_active_fix') )
{
	$selectUser = $ilDB->prepare("
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id IS NULL
		", array('integer', 'integer')
	);

	$selectAnonym = $ilDB->prepare("
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi IS NULL AND anonymous_id = ?
		", array('integer', 'text')
	);

	$select = $ilDB->prepare("
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id = ?
		", array('integer', 'integer', 'text')
	);

	$update = $ilDB->prepareManip("
			UPDATE tmp_active_fix SET active_id = ?
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id = ?
		", array('integer', 'integer', 'integer', 'text')
	);

	$res1 = $ilDB->query("SELECT * FROM tmp_active_fix WHERE active_id IS NULL");
	
	while($row1 = $ilDB->fetchAssoc($res1))
	{
		if(!$row1['user_fi'])
		{
			$res2 = $ilDB->execute($selectAnonym, array(
				$row1['test_fi'], $row1['anonymous_id']
			));
		}
		elseif($row1['anonymous_id'] == '-')
		{
			$res2 = $ilDB->execute($selectUser, array(
				$row1['test_fi'], $row1['user_fi']
			));
		}
		else
		{
			$res2 = $ilDB->execute($select, array(
				$row1['test_fi'], $row1['user_fi'], $row1['anonymous_id']
			));
		}
		
		$activeId = null;
		$passed = null;
		$points = null;
		
		while($row2 = $ilDB->fetchAssoc($res2))
		{
			if($activeId === null)
			{
				$activeId = $row2['active_id'];
				$passed = $row2['passed'];
				$points = $row2['reached_points'];
				continue;
			}
			
			if( !$row2['max_points'] )
			{
				continue;
			}

			if(!$passed && $row2['passed'])
			{
				$activeId = $row2['active_id'];
				$passed = $row2['passed'];
				$points = $row2['reached_points'];
				continue;
			}
			
			if($passed && !$row2['passed'])
			{
				continue;
			}

			if($row2['reached_points'] > $points)
			{
				$activeId = $row2['active_id'];
				$passed = $row2['passed'];
				$points = $row2['reached_points'];
				continue;
			}
		}
		
		$ilDB->execute($update, array(
			$activeId, $row1['test_fi'], $row1['user_fi'], $row1['anonymous_id']
		));
	}
}

?>
<#56>
<?php

if( $ilDB->tableExists('tmp_active_fix') )
{
	$deleteUserActives = $ilDB->prepareManip(
		"DELETE FROM tst_active WHERE active_id != ? AND test_fi = ? AND user_fi = ? AND anonymous_id IS NULL",
		array('integer', 'integer', 'integer')
	);

	$deleteAnonymActives = $ilDB->prepareManip(
		"DELETE FROM tst_active WHERE active_id != ? AND test_fi = ? AND user_fi IS NULL AND anonymous_id = ?",
		array('integer', 'integer', 'text')
	);

	$deleteActives = $ilDB->prepareManip(
		"DELETE FROM tst_active WHERE active_id != ? AND test_fi = ? AND user_fi = ? AND anonymous_id = ?",
		array('integer', 'integer', 'integer', 'text')
	);

	$deleteLp = $ilDB->prepareManip(
		"DELETE FROM ut_lp_marks WHERE obj_id = ? AND usr_id = ?",
		array('integer', 'integer')
	);

	$deleteTmpRec = $ilDB->prepareManip(
		"DELETE FROM tmp_active_fix WHERE test_fi = ? AND user_fi = ? AND anonymous_id = ?",
		array('integer', 'integer', 'text')
	);
	
	$res = $ilDB->query("
		SELECT tmp_active_fix.*, obj_fi FROM tmp_active_fix INNER JOIN tst_tests ON test_id = test_fi
	");
	
	while($row = $ilDB->fetchAssoc($res))
	{
		if(!$row['user_fi'])
		{
			$ilDB->execute($deleteAnonymActives, array(
				$row['active_id'], $row['test_fi'], $row['anonymous_id']
			));
		}
		elseif( $row['anonymous_id'] == '-' )
		{
			$ilDB->execute($deleteUserActives, array(
				$row['active_id'], $row['test_fi'], $row['user_fi']
			));
		}
		else
		{
			$ilDB->execute($deleteActives, array(
				$row['active_id'], $row['test_fi'], $row['user_fi'], $row['anonymous_id']
			));
		}

		$ilDB->execute($deleteLp, array(
			$row['obj_fi'], $row['user_fi']
		));

		$ilDB->execute($deleteTmpRec, array(
			$row['test_fi'], $row['user_fi'], $row['anonymous_id']
		));
	}
	
	$ilDB->dropTable('tmp_active_fix');
}

?>
<#57>
<?php

if( !$ilDB->uniqueConstraintExists('tst_active', array('user_fi', 'test_fi', 'anonymous_id')) )
{
	$ilDB->addUniqueConstraint('tst_active', array('user_fi', 'test_fi', 'anonymous_id'), 'uc1');
}

?>
<#58>
<?php

$settings = new ilSetting('assessment');

if( !(int)$settings->get('quest_process_lock_mode_autoinit', 0) )
{
	if( $settings->get('quest_process_lock_mode', 'none') == 'none' )
	{
		$settings->set('quest_process_lock_mode', 'db');
	}

	$settings->set('quest_process_lock_mode_autoinit_done', 1);
}

?>
<#59>
<?php
if( $ilDB->tableColumnExists('tst_tests', 'examid_in_kiosk') )
{
	$ilDB->renameTableColumn('tst_tests', 'examid_in_kiosk', 'examid_in_test_pass');
}
?>
<#60>
<?php
if( $ilDB->tableColumnExists('tst_tests', 'show_exam_id') )
{
	$ilDB->renameTableColumn('tst_tests', 'show_exam_id', 'examid_in_test_res');
}
?>
<#61>
<?php
if( !$ilDB->tableColumnExists('tst_active', 'start_lock'))
{
	$ilDB->addTableColumn('tst_active', 'start_lock',
		array(
			'type' => 'text',
			'length' => 128,
			'notnull' => false,
			'default' => null
		)
	);
}
?>
<#62>
<?php
$row = $ilDB->fetchAssoc($ilDB->queryF(
	"SELECT count(*) cnt FROM settings WHERE module = %s AND keyword = %s",
	array('text', 'text'), array('assessment', 'ass_process_lock_mode')
));

if( $row['cnt'] )
{
	$ilDB->manipulateF(
		"DELETE FROM settings WHERE module = %s AND keyword = %s",
		array('text', 'text'), array('assessment', 'quest_process_lock_mode')
	);
}
else
{
	$ilDB->update('settings',
		array(
			'keyword' => array('text', 'ass_process_lock_mode')
		),
		array(
			'module' => array('text', 'assessment'),
			'keyword' => array('text', 'quest_process_lock_mode')
		)
	);
}	
?>
<#63>
<?php
	if(!$ilDB->sequenceExists('booking_reservation_group'))
	{
		$ilDB->createSequence('booking_reservation_group');
	}
?>
<#64>
<?php
$ilDB->manipulate('DELETE FROM addressbook WHERE login NOT IN(SELECT login FROM usr_data) AND email IS NULL');
$ilDB->manipulate(
	'DELETE FROM addressbook_mlist_ass WHERE addr_id NOT IN(
		SELECT addr_id FROM addressbook
	)'
);
?>
<#65>
<?php
	if(!$ilDB->indexExistsByFields('page_question',array('page_parent_type','page_id', 'page_lang')))
	{
		$ilDB->addIndex('page_question',array('page_parent_type','page_id', 'page_lang'),'i1');
	}
?>
<#66>
<?php

$query = "
	UPDATE tst_rnd_quest_set_qpls SET pool_title = (
		COALESCE(
			(SELECT title FROM object_data WHERE obj_id = pool_fi), %s 
		)
	) WHERE pool_title IS NULL OR pool_title = %s
";

$ilDB->manipulateF($query, array('text', 'text'), array('*** unknown/deleted ***', ''));

?>
<#67>
<?php

if( !$ilDB->tableColumnExists('tst_tests', 'broken'))
{
	$ilDB->addTableColumn('tst_tests', 'broken',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null
		)
	);
	
	$ilDB->queryF("UPDATE tst_tests SET broken = %s", array('integer'), array(0));
}

?>
