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