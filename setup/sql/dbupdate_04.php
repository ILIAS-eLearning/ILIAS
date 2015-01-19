<#4183>
<?php
	if (!$ilDB->tableColumnExists('il_poll', 'result_sort'))
	{
		$ilDB->addTableColumn('il_poll', 'result_sort', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4184>
<?php
	if (!$ilDB->tableColumnExists('il_poll', 'non_anon'))
	{
		$ilDB->addTableColumn('il_poll', 'non_anon', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4185>
<?php

if(!$ilDB->tableColumnExists('il_blog','abs_shorten')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_shorten_len')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten_len',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_image')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_image',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_width')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_width',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_height')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_height',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

?>
<#4186>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4187>
<?php

if( !$ilDB->tableExists('usr_data_multi') )
{
	$ilDB->createTable('usr_data_multi', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'value' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
		)
	));
}

?>
<#4188>
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

<#4189>
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

<#4190>
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

<#4191>
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

<#4192>
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

<#4193>
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

<#4194>
<?php

	$ilDB->dropIndexByFields('cal_auth_token',array('user_id'));

?>

<#4195>
<?php

	if(!$ilDB->indexExistsByFields('cal_shared',array('obj_id','obj_type')))
	{
		$ilDB->addIndex('cal_shared',array('obj_id','obj_type'),'i1');
	}
?>
<#4196>
<?php

	$ilDB->dropIndexByFields('cal_entry_responsible',array('cal_id','user_id'));
	$ilDB->addPrimaryKey('cal_entry_responsible',array('cal_id','user_id'));
?>
<#4197>
<?php

	$ilDB->dropIndexByFields('cal_entry_responsible',array('cal_id'));
	$ilDB->dropIndexByFields('cal_entry_responsible',array('user_id'));
	
?>
<#4198>
<?php

	$ilDB->dropIndexByFields('cal_cat_assignments',array('cal_id','cat_id'));
	$ilDB->addPrimaryKey('cal_cat_assignments',array('cal_id','cat_id'));
	
?>

<#4199>
<?php
	if(!$ilDB->indexExistsByFields('cal_entries',array('last_update')))
	{
		$ilDB->addIndex('cal_entries',array('last_update'),'i1');
	}
?>
<#4200>
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
<#4201>
<?php
	if(!$ilDB->indexExistsByFields('booking_reservation',array('user_id')))
	{
		$ilDB->addIndex('booking_reservation',array('user_id'),'i1');
	}
?>
<#4202>
<?php
	if(!$ilDB->indexExistsByFields('booking_reservation',array('object_id')))
	{
		$ilDB->addIndex('booking_reservation',array('object_id'),'i2');
	}
?>
<#4203>
<?php
	if(!$ilDB->indexExistsByFields('cal_entries',array('context_id')))
	{
		$ilDB->addIndex('cal_entries',array('context_id'),'i2');
	}
?>
<#4204>
<?php
if( !$ilDB->tableColumnExists('il_poll', 'show_results_as') )
{
    $ilDB->addTableColumn('il_poll', 'show_results_as', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 1
    ));
}
if( !$ilDB->tableColumnExists('il_poll', 'show_comments') )
{
    $ilDB->addTableColumn('il_poll', 'show_comments', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#4205>
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
<#4206>

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
<#4207>
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
<#4208>
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
<#4209>
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

	require_once 'Services/Migration/DBUpdate_4209/classes/class.DBUpdateTestResultCalculator.php';

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

?>
<#4210>
<?php
$ilSetting = new ilSetting();
if ((int) $ilSetting->get('lm_qst_imap_migr_run') == 0)
{
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
}
?>
<#4211>
<?php
if( !$ilDB->tableColumnExists('qpl_a_cloze', 'gap_size') )
{
	$ilDB->addTableColumn('qpl_a_cloze', 'gap_size', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#4212>
<?php
if( !$ilDB->tableColumnExists('qpl_qst_cloze', 'qpl_qst_cloze') )
{
	$ilDB->addTableColumn( 'qpl_qst_cloze', 'cloze_text', array('type' => 'clob') );

	$clean_qst_txt = $ilDB->prepareManip('UPDATE qpl_questions SET question_text = "&nbsp;" WHERE question_id = ?', array('integer'));

	$result = $ilDB->query('SELECT question_id, question_text FROM qpl_questions WHERE question_type_fi = 3');

	/** @noinspection PhpAssignmentInConditionInspection */
	while( $row = $ilDB->fetchAssoc($result) )
	{
		$ilDB->update(
			'qpl_qst_cloze',
			array(
				'cloze_text'	=> array('clob', $row['question_text'] )
			),
			array(
				'question_fi'	=> array('integer', $row['question_id'] )
			)
		);
		$ilDB->execute($clean_qst_txt, $row['question_id'] );
	}
}
?>
<#4213>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4214>
<?php
if( !$ilDB->tableColumnExists('qpl_qst_matching', 'matching_mode') )
{
	$ilDB->addTableColumn('qpl_qst_matching', 'matching_mode', array(
		'type' => 'text',
		'length' => 3,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		'UPDATE qpl_qst_matching SET matching_mode = %s',
		array('text'), array('1:1')
	);
}

if( $ilDB->tableColumnExists('qpl_qst_matching', 'element_height') )
{
	$ilDB->dropTableColumn('qpl_qst_matching', 'element_height');
}
?>
<#4215>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4216>
<?php
// REMOVED: is done at #4220 in an abstracted way
// Bibliographic Module: Increase the allowed text-size for attributes from 512 to 4000
// $ilDB->query('ALTER TABLE il_bibl_attribute MODIFY value VARCHAR(4000)');
?>
<#4217>
<?php
    /* Introduce new DataCollection features
        - Comments on records
        - Default sort-field & sort-order
    */
    if(!$ilDB->tableColumnExists('il_dcl_table','default_sort_field_id')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'default_sort_field_id',
            array(
                'type' => 'text',
                'length' => 16,
                'notnull' => true,
                'default' => '0',
            ));
    }
    if(!$ilDB->tableColumnExists('il_dcl_table','default_sort_field_order')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'default_sort_field_order',
            array(
                'type' => 'text',
                'length' => 4,
                'notnull' => true,
                'default' => 'asc',
            ));
    }
    if(!$ilDB->tableColumnExists('il_dcl_table','public_comments')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'public_comments',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0,
            ));
    }
?>
<#4218>
<?php
if(!$ilDB->tableColumnExists('il_dcl_table','view_own_records_perm')) {
    $ilDB->addTableColumn(
        'il_dcl_table',
        'view_own_records_perm',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ));
}
?>
<#4219>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4220>
<?php
// Bibliographic Module: Increase the allowed text-size for attributes from 512 to 4000
$ilDB->modifyTableColumn("il_bibl_attribute", "value", array("type" => "text", "length" => 4000));
?>
<#4221>
<?php

if( !$ilDB->tableExists('adv_md_values_text') )
{
	$ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#4222>
<?php

if( !$ilDB->tableExists('adv_md_values_int') )
{
	$ilDB->createTable('adv_md_values_int', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}

?>
<#4223>
<?php

if( !$ilDB->tableExists('adv_md_values_float') )
{
	$ilDB->createTable('adv_md_values_float', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'float',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}

?>
<#4224>
<?php

if( !$ilDB->tableExists('adv_md_values_date') )
{
	$ilDB->createTable('adv_md_values_date', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'date',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}

?>
<#4225>
<?php

if( !$ilDB->tableExists('adv_md_values_datetime') )
{
	$ilDB->createTable('adv_md_values_datetime', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'timestamp',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}

?>
<#4226>
<?php

if( !$ilDB->tableExists('adv_md_values_location') )
{
	$ilDB->createTable('adv_md_values_location', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'loc_lat' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_long' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_zoom' => array(
			'type' => 'integer',			
			'length' => 1,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}

?>
<#4227>
<?php

	if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_int', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	
?>
<#4228>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4229>
<?php

// moving date/datetime to proper adv_md-tables
$field_map = array();

$set = $ilDB->query("SELECT field_id,field_type FROM adv_mdf_definition".
	" WHERE ".$ilDB->in("field_type", array(3,4), "", "integer"));
while($row = $ilDB->fetchAssoc($set))
{
	$field_map[$row["field_id"]] = $row["field_type"];
}

if(sizeof($field_map))
{
	$set = $ilDB->query("SELECT * FROM adv_md_values_text".
		" WHERE ".$ilDB->in("field_id", array_keys($field_map), "", "integer"));
	while($row = $ilDB->fetchAssoc($set))
	{
		if($row["value"])
		{
			// date
			if($field_map[$row["field_id"]] == 3)
			{
				$table = "adv_md_values_date";
				$value = date("Y-m-d", $row["value"]);
				$type = "date";
			}
			// datetime
			else
			{
				$table = "adv_md_values_datetime";
				$value = date("Y-m-d H:i:s", $row["value"]);
				$type = "timestamp";
			}
			
			$fields = array(
				"obj_id" => array("integer", $row["obj_id"])
				,"sub_type" => array("text", $row["sub_type"])
				,"sub_id" => array("integer", $row["sub_id"])
				,"field_id" => array("integer", $row["field_id"])				
				,"disabled" => array("integer", $row["disabled"])
				,"value" => array($type, $value)
			);
			
			$ilDB->insert($table, $fields);
		}		
	}	
	
	$ilDB->manipulate("DELETE FROM adv_md_values_text".
		" WHERE ".$ilDB->in("field_id", array_keys($field_map), "", "integer"));
}

?>
<#4230>
<?php

if (!$ilDB->tableColumnExists('il_blog', 'keywords'))
{		
	$ilDB->addTableColumn('il_blog', 'keywords', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => true,
		"default" => 1
	));
	$ilDB->addTableColumn('il_blog', 'authors', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => true,
		"default" => 1
	));
	$ilDB->addTableColumn('il_blog', 'nav_mode', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => true,
		"default" => 1
	));
	$ilDB->addTableColumn('il_blog', 'nav_list_post', array(
		"type" => "integer",
		"length" => 2,
		"notnull" => true,
		"default" => 10
	));
	$ilDB->addTableColumn('il_blog', 'nav_list_mon', array(
		"type" => "integer",
		"length" => 2,
		"notnull" => false,
		"default" => 0
	));
	$ilDB->addTableColumn('il_blog', 'ov_post', array(
		"type" => "integer",
		"length" => 2,
		"notnull" => false,
		"default" => 0
	));
}

?>
<#4231>
<?php

if (!$ilDB->tableColumnExists('il_blog', 'nav_order'))
{	
	$ilDB->addTableColumn('il_blog', 'nav_order', array(
		"type" => "text",
		"length" => 255,
		"notnull" => false
	));	
}

?>
<#4232>
<?php

if (!$ilDB->tableColumnExists('svy_svy', 'own_results_view'))
{	
	$ilDB->addTableColumn('svy_svy', 'own_results_view', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => false,
		"default" => 0
	));	
}
if (!$ilDB->tableColumnExists('svy_svy', 'own_results_mail'))
{	
	$ilDB->addTableColumn('svy_svy', 'own_results_mail', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => false,
		"default" => 0
	));	
}

?>
<#4233>
<?php

if (!$ilDB->tableColumnExists('exc_data', 'add_desktop'))
{	
	$ilDB->addTableColumn('exc_data', 'add_desktop', array(
		"type" => "integer",
		"length" => 1,
		"notnull" => true,
		"default" => 1
	));	
}

?>
<#4234>
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
<#4235>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4236>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4237>
<?php

if( !$ilDB->tableExists('pg_amd_page_list') )
{
	$ilDB->createTable('pg_amd_page_list', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'data' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false
		),		
	));
		
	$ilDB->addPrimaryKey('pg_amd_page_list', array('id', 'field_id'));
	$ilDB->createSequence('pg_amd_page_list');
}

?>
<#4238>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4239>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4240>
<?php
if( !$ilDB->tableColumnExists('tst_tests', 'skill_service') )
{
	$ilDB->addTableColumn('tst_tests', 'skill_service', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
	
	$ilDB->manipulateF(
		'UPDATE tst_tests SET skill_service = %s',
		array('integer'), array(0)
	);
}

if( !$ilDB->tableExists('tst_skl_qst_assigns') )
{
	$ilDB->createTable('tst_skl_qst_assigns', array(
		'test_fi' => array(
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
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	
	$ilDB->addPrimaryKey('tst_skl_qst_assigns', array('test_fi', 'question_fi', 'skill_base_fi', 'skill_tref_fi'));
}

if( !$ilDB->tableExists('tst_skl_thresholds') )
{
	$ilDB->createTable('tst_skl_thresholds', array(
		'test_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_base_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_tref_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'skill_level_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'threshold' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	
	$ilDB->addPrimaryKey('tst_skl_thresholds', array('test_fi', 'skill_base_fi', 'skill_tref_fi', 'skill_level_fi'));
}

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
<#4241>
<?php
if( !$ilDB->tableColumnExists('tst_tests', 'result_tax_filters') )
{
	$ilDB->addTableColumn('tst_tests', 'result_tax_filters', array(
		'type' => 'text',
		'length' => 255,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4242>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4243>
<?php
if( !$ilDB->tableColumnExists('tst_test_rnd_qst', 'src_pool_def_fi') )
{
	$ilDB->addTableColumn('tst_test_rnd_qst', 'src_pool_def_fi', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4244>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4245>
<?php

if(!$ilDB->tableExists('ecs_remote_user') )
{
	$ilDB->createTable('ecs_remote_user', array(
		'eru_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sid' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'mid' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'remote_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('ecs_remote_user', array('eru_id'));
	$ilDB->createSequence('ecs_remote_user');
}
?>
<#4246>
<?php

if($ilDB->tableExists('ecs_remote_user'))
{
	$ilDB->dropTable('ecs_remote_user');
}

?>
<#4247>
<?php
if(!$ilDB->tableExists('ecs_remote_user') )
{
	$ilDB->createTable('ecs_remote_user', array(
		'eru_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sid' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'mid' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'remote_usr_id' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => false,
			'fixed' => TRUE
		)
	));
	$ilDB->addPrimaryKey('ecs_remote_user', array('eru_id'));
	$ilDB->createSequence('ecs_remote_user');
}
?>
<#4248>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('excs', 'Exercise Settings');

?>
<#4249>
<?php

if ($ilDB->tableColumnExists('exc_data', 'add_desktop'))
{
	$ilDB->dropTableColumn('exc_data', 'add_desktop');
}

?>
<#4250>
<?php
if( !$ilDB->tableColumnExists('tst_tests', 'show_grading_status') )
{
	$ilDB->addTableColumn('tst_tests', 'show_grading_status', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));

	$ilDB->queryF("UPDATE tst_tests SET show_grading_status = %s", array('integer'), array(1));
}

if( !$ilDB->tableColumnExists('tst_tests', 'show_grading_mark') )
{
	$ilDB->addTableColumn('tst_tests', 'show_grading_mark', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));

	$ilDB->queryF("UPDATE tst_tests SET show_grading_mark = %s", array('integer'), array(1));
}
?>
<#4251>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('taxs', 'Taxonomy Settings');

?>
<#4252>
<?php
// Datacollection: Add formula fieldtype
$ilDB->insert('il_dcl_datatype', array(
        'id' => array('integer', 11),
        'title' => array('text', 'formula'),
        'ildb_type' => array('text', 'text'),
        'storage_location' => array('integer', 0),
        'sort' => array('integer', 90),
    ));
?>
<#4253>
<?php

if( !$ilDB->tableColumnExists('booking_settings', 'ovlimit') )
{
	$ilDB->addTableColumn('booking_settings', 'ovlimit', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	));
}

?>
<#4254>
<?php
if( $ilDB->tableColumnExists('qpl_qst_essay', 'keyword_relation') )
{
	$ilDB->queryF(
		"UPDATE qpl_qst_essay SET keyword_relation = %s WHERE keyword_relation = %s",
		array('text', 'text'), array('non', 'none')
	);
}
?>
<#4255>
    <?php
    // Datacollection: Add formula fieldtype
    $ilDB->insert('il_dcl_datatype_prop', array(
        'id' => array('integer', 12),
        'datatype_id' => array('integer', 11),
        'title' => array('text', 'expression'),
        'inputformat' => array('integer', 2),
    ));
?>
<#4256>
<?php
if( !$ilDB->tableExists('wiki_stat') )
{
	$ilDB->createTable('wiki_stat', array(
		'wiki_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ts' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'num_pages' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'del_pages' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'avg_rating' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	));

	$ilDB->addPrimaryKey('wiki_stat', array('wiki_id', 'ts'));
}
?>
<#4257>
<?php
if( !$ilDB->tableExists('wiki_stat_page_user') )
{
	$ilDB->createTable('wiki_stat_page_user', array(
		'wiki_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'page_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ts' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'changes' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'read_events' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('wiki_stat_page_user', array('wiki_id', 'page_id', 'ts', 'user_id'));
}
?>
<#4258>
<?php
if( !$ilDB->tableExists('wiki_stat_user') )
{
	$ilDB->createTable('wiki_stat_user', array(
		'wiki_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ts' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'new_pages' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	));

	$ilDB->addPrimaryKey('wiki_stat_user', array('wiki_id', 'user_id', 'ts'));
}
?>
<#4259>
<?php
if( !$ilDB->tableExists('wiki_stat_page') )
{
	$ilDB->createTable('wiki_stat_page', array(
		'wiki_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'page_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ts' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'int_links' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ext_links' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'footnotes' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'num_ratings' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'num_words' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'num_chars' => array(
			'type' => 'integer',
			'length' => 8,
			'notnull' => true
		),

	));

	$ilDB->addPrimaryKey('wiki_stat_page', array('wiki_id', 'page_id', 'ts'));
}
?>
<#4260>
<?php
if( !$ilDB->tableColumnExists('wiki_stat_page', 'avg_rating') )
{
	$ilDB->addTableColumn('wiki_stat_page', 'avg_rating',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		));			
}
?>
<#4261>
<?php

if( !$ilDB->tableColumnExists('wiki_stat', 'ts_day') )
{
	$ilDB->addTableColumn('wiki_stat', 'ts_day',
		array(
			'type' => 'text',
			'length' => 10,
			'fixed' => true,
			'notnull' => false
		));		
	$ilDB->addTableColumn('wiki_stat', 'ts_hour',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		));			
}

if( !$ilDB->tableColumnExists('wiki_stat_page', 'ts_day') )
{
	$ilDB->addTableColumn('wiki_stat_page', 'ts_day',
		array(
			'type' => 'text',
			'length' => 10,
			'fixed' => true,
			'notnull' => false
		));		
	$ilDB->addTableColumn('wiki_stat_page', 'ts_hour',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		));			
}

if( !$ilDB->tableColumnExists('wiki_stat_user', 'ts_day') )
{
	$ilDB->addTableColumn('wiki_stat_user', 'ts_day',
		array(
			'type' => 'text',
			'length' => 10,
			'fixed' => true,
			'notnull' => false
		));		
	$ilDB->addTableColumn('wiki_stat_user', 'ts_hour',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		));			
}

if( !$ilDB->tableColumnExists('wiki_stat_page_user', 'ts_day') )
{
	$ilDB->addTableColumn('wiki_stat_page_user', 'ts_day',
		array(
			'type' => 'text',
			'length' => 10,
			'fixed' => true,
			'notnull' => false
		));		
	$ilDB->addTableColumn('wiki_stat_page_user', 'ts_hour',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		));			
}

?>
<#4262>
<?php
	if( !$ilDB->tableExists('wiki_page_template') )
	{
		$ilDB->createTable('wiki_page_template', array(
			'wiki_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'wpage_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			)
		));

		$ilDB->addPrimaryKey('wiki_page_template', array('wiki_id', 'wpage_id'));
	}
?>
<#4263>
<?php
if(!$ilDB->tableColumnExists('wiki_page_template', 'new_pages') )
{
	$ilDB->addTableColumn('wiki_page_template', 'new_pages',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
	$ilDB->addTableColumn('wiki_page_template', 'add_to_page',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
}
?>
<#4264>
<?php
if(!$ilDB->tableColumnExists('il_wiki_data', 'empty_page_templ') )
{
	$ilDB->addTableColumn('il_wiki_data', 'empty_page_templ',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 1
		));
}
?>
<#4265>
<?php

if( !$ilDB->tableColumnExists('wiki_stat_page', 'deleted') )
{	
	$ilDB->addTableColumn('wiki_stat_page', 'deleted',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));			
}

?>
<#4266>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{	
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('statistics_read', 'Read Statistics', 'object', 2200);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
		
		$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
		if($src_ops_id)
		{
			ilDBUpdateNewObjectType::cloneOperation('wiki', $src_ops_id, $new_ops_id);
		}
	}
}

?>
<#4267>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4268>
<?php
    $ilDB->insert('il_dcl_datatype_prop', array(
    'id' => array('integer', 13),
    'datatype_id' => array('integer', 8),
    'title' => array('text', 'display_action_menu'),
    'inputformat' => array('integer', 4),
    ));
?>
<#4269>
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
<#4270>
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
<#4271>
<?php

$client_id = basename(CLIENT_DATA_DIR);
$web_path = ilUtil::getWebspaceDir().$client_id;
$sec_path = $web_path."/sec";

if(!file_exists($sec_path))
{
	ilUtil::makeDir($sec_path);
}

$mods = array("ilBlog", "ilPoll", "ilPortfolio");
foreach($mods as $mod)
{
	$mod_path = $web_path."/".$mod;
	if(file_exists($mod_path))
	{
		$mod_sec_path = $sec_path."/".$mod;
		rename($mod_path, $mod_sec_path);
	}
}

?>
<#4272>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4273>
<?php
//$ilDB->insert('il_dcl_datatype_prop', array(
//    'id' => array('integer', 14),
//    'datatype_id' => array('integer', 2),
//    'title' => array('text', 'link_detail_page'),
//    'inputformat' => array('integer', 4),
//));
//$ilDB->insert('il_dcl_datatype_prop', array(
//    'id' => array('integer', 15),
//    'datatype_id' => array('integer', 9),
//    'title' => array('text', 'link_detail_page'),
//    'inputformat' => array('integer', 4),
//));
?>
<#4274>
<?php

$ilDB->dropTable("ut_access"); // #13663

?>
<#4275>
<?php

if(!$ilDB->tableExists('obj_user_data_hist') )
{
	$ilDB->createTable('obj_user_data_hist', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'update_user' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'editing_time' => array(
			'type' => 'timestamp',
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey('obj_user_data_hist',array('obj_id','usr_id'));
}

?>
<#4276>
<?php
if(!$ilDB->tableColumnExists('frm_threads', 'avg_rating'))
{
	$ilDB->addTableColumn('frm_threads', 'avg_rating',
		array(
			'type' => 'float',
			'notnull' => true,
			'default' => 0
		));
}
?>
<#4277>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4278>
<?php
if(!$ilDB->tableColumnExists('frm_settings', 'thread_rating'))
{
	$ilDB->addTableColumn('frm_settings', 'thread_rating',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
}
?>
<#4279>
<?php
if(!$ilDB->tableColumnExists('exc_assignment', 'peer_file'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_file',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#4280>
<?php
if(!$ilDB->tableColumnExists('exc_assignment_peer', 'upload'))
{
	$ilDB->addTableColumn('exc_assignment_peer', 'upload',
		array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'fixed' => false
		));
}
?>
<#4281>
<?php
if(!$ilDB->tableColumnExists('exc_assignment', 'peer_prsl'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_prsl',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#4282>
<?php
if(!$ilDB->tableColumnExists('exc_assignment', 'fb_date'))
{
	$ilDB->addTableColumn('exc_assignment', 'fb_date',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 1
		));
}
?>
<#4283>
<?php
if(!$ilDB->tableColumnExists('container_sorting_set', 'sort_direction'))
{
	$ilDB->addTableColumn('container_sorting_set', 'sort_direction',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
}
?>
<#4284>
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
<#4285>
<?php
if( !$ilDB->tableExists('container_sorting_bl') )
{
	$ilDB->createTable('container_sorting_bl', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'block_ids' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false,
		)
	));

	$ilDB->addPrimaryKey('container_sorting_bl',array('obj_id'));
}
?>
<#4286>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4287>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
if(!$tgt_ops_id)
{	
	$tgt_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('read_learning_progress', 'Read Learning Progress', 'object', 2300);
}

$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');	
if($src_ops_id && 
	$tgt_ops_id)
{			
	// see ilObjectLP
	$lp_types = array("crs", "grp", "fold", "lm", "htlm", "sahs", "tst", "exc", "sess");

	foreach($lp_types as $lp_type)
	{
		$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
		if($lp_type_id)
		{			
			ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);				
			ilDBUpdateNewObjectType::cloneOperation($lp_type, $src_ops_id, $tgt_ops_id);
		}
	}
}

?>
<#4288>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4289>
<?php
$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
$ilDB->addTableColumn("content_object", "progr_icons", $def);
?>
<#4290>
<?php
$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
$ilDB->addTableColumn("content_object", "store_tries", $def);
?>
<#4291>
<?php
	$query = 'DELETE FROM rbac_fa WHERE parent = '.$ilDB->quote(0,'integer');
	$ilDB->manipulate($query);


	$query = 'UPDATE rbac_fa f '.
			'SET parent  = '.
				'(SELECT t.parent FROM tree t where t.child = f.parent) '.
			'WHERE f.parent != '.$ilDB->quote(8,'integer').' '.
			'AND EXISTS (SELECT t.parent FROM tree t where t.child = f.parent) ';
	$ilDB->manipulate($query);
?>

<#4292>
<?php
	$query = 'DELETE FROM rbac_templates WHERE parent = '.$ilDB->quote(0,'integer');
	$ilDB->manipulate($query);

	$query = 'UPDATE rbac_templates rt '.
			'SET parent = '.
			'(SELECT t.parent FROM tree t WHERE t.child = rt.parent) '.
			'WHERE rt.parent != '.$ilDB->quote(8,'integer').' '.
			'AND EXISTS (SELECT t.parent FROM tree t WHERE t.child = rt.parent) ';
	$ilDB->manipulate($query);
?>
<#4293>
<?php
$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
$ilDB->addTableColumn("content_object", "restrict_forw_nav", $def);
?>
<#4294>
<?php

// category taxonomy custom blocks are obsolete
$ilDB->manipulate("DELETE FROM il_custom_block".
	" WHERE context_obj_type = ".$ilDB->quote("cat", "text").
	" AND context_sub_obj_type = ".$ilDB->quote("tax", "text"));

?>
<#4295>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4296>
<?php
if( !$ilDB->tableColumnExists('container_sorting_set', 'new_items_position'))
{
	$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	);
	$ilDB->addTableColumn('container_sorting_set', 'new_items_position', $def);
}

if( !$ilDB->tableColumnExists('container_sorting_set', 'new_items_order'))
{
	$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
	$ilDB->addTableColumn('container_sorting_set', 'new_items_order', $def);
}
?>
<#4297>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4298>
<?php
if(!$ilDB->tableExists('usr_cron_mail_reminder'))
{
	$fields = array (
		'usr_id'    => array(
			'type'    => 'integer',
			'length'  => 4,
			'default' => 0,
			'notnull' => true
		),
		'ts'   => array(
			'type'    => 'integer',
			'length'  => 4,
			'default' => 0,
			'notnull' => true
		)
	);
	$ilDB->createTable('usr_cron_mail_reminder', $fields);
	$ilDB->addPrimaryKey('usr_cron_mail_reminder', array('usr_id'));
}
?>
<#4299>
    <?php
    if(!$ilDB->tableExists('orgu_types')) {
        $fields = array (
            'id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
            'default_lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
            'icon'    => array ('type' => 'text', 'length'  => 256, 'notnull' => false),
            'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
            'create_date'  => array ('type' => 'timestamp'),
            'last_update' => array('type' => 'timestamp'),
        );
        $ilDB->createTable('orgu_types', $fields);
        $ilDB->addPrimaryKey('orgu_types', array('id'));
        $ilDB->createSequence('orgu_types');
    }
    ?>
<#4300>
    <?php
    if(!$ilDB->tableExists('orgu_data')) {
        $fields = array (
            'orgu_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
            'orgu_type_id'   => array ('type' => 'integer', 'notnull' => false, 'length' => 4),
        );
        $ilDB->createTable('orgu_data', $fields);
        $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
    }
    ?>
<#4301>
    <?php
    if(!$ilDB->tableExists('orgu_types_trans')) {
        $fields = array (
            'orgu_type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
            'lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4),
            'member'    => array ('type' => 'text', 'length'  => 32, 'notnull' => true),
            'value' => array('type' => 'text', 'length' => 4000, 'notnull' => false),
        );
        $ilDB->createTable('orgu_types_trans', $fields);
        $ilDB->addPrimaryKey('orgu_types_trans', array('orgu_type_id', 'lang', 'member'));
    }
    ?>
<#4302>
    <?php
    $ilCtrlStructureReader->getStructure();
    ?>
<#4303>
    <?php
    if(!$ilDB->tableExists('orgu_types_adv_md_rec')) {
        $fields = array (
            'type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
            'rec_id'   => array ('type' => 'integer', 'notnull' => true, 'length' => 4),
        );
        $ilDB->createTable('orgu_types_adv_md_rec', $fields);
        $ilDB->addPrimaryKey('orgu_types_adv_md_rec', array('type_id', 'rec_id'));
    }
    ?>
<#4304>
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
<#4305>
<?php

// #13822 
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::varchar2text('exc_assignment_peer', 'pcomment');

?>
<#4306>
<?php
/**
 * @var $ilDB ilDB
 */
global $ilDB;

$ilDB->modifyTableColumn('usr_data', 'passwd', array(
	'type'    => 'text',
	'length'  => 80,
	'notnull' => false,
	'default' => null
));
?>
<#4307>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE keyword = %s',
	array('text'),
	array('usr_settings_export_password')
);
?>
<#4308>
<?php
if(!$ilDB->tableColumnExists('usr_data', 'passwd_enc_type'))
{
	$ilDB->addTableColumn('usr_data', 'passwd_enc_type', array(
		'type'    => 'text',
		'length'  => 10,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4309>
<?php
// We have to handle alle users with a password. We cannot rely on the auth_mode information.
$ilDB->manipulateF('
	UPDATE usr_data
	SET passwd_enc_type = %s
	WHERE (SUBSTR(passwd, 1, 4) = %s OR SUBSTR(passwd, 1, 4) = %s) AND passwd IS NOT NULL
	',
	array('text', 'text', 'text'),
	array('bcrypt', '$2a$', '$2y$')
);
$ilDB->manipulateF('
	UPDATE usr_data
	SET passwd_enc_type = %s
	WHERE SUBSTR(passwd, 1, 4) != %s AND SUBSTR(passwd, 1, 4) != %s AND LENGTH(passwd) = 32 AND passwd IS NOT NULL
	',
	array('text', 'text', 'text'),
	array('md5', '$2a$', '$2y$')
);
?>
<#4310>
<?php
if(!$ilDB->tableColumnExists('usr_data', 'passwd_salt'))
{
	$ilDB->addTableColumn('usr_data', 'passwd_salt', array(
		'type'    => 'text',
		'length'  => 32,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4311>
<?php
if($ilDB->tableColumnExists('usr_data', 'i2passwd'))
{
	$ilDB->dropTableColumn('usr_data', 'i2passwd');
}
?>
<#4312>
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
<#4313>
<?php
if($ilDB->tableColumnExists('exc_assignment_peer', 'upload'))
{
	$ilDB->dropTableColumn('exc_assignment_peer', 'upload');
}
?>

<#4314>
<?php

$res = $ilDB->queryF(
	"SELECT COUNT(*) cnt FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assKprimChoice')
);

$row = $ilDB->fetchAssoc($res);

if( !$row['cnt'] )
{
	$res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
	$data = $ilDB->fetchAssoc($res);
	$nextId = $data['maxid'] + 1;

	$ilDB->insert('qpl_qst_type', array(
		'question_type_id' => array('integer', $nextId),
		'type_tag' => array('text', 'assKprimChoice'),
		'plugin' => array('integer', 0)
	));
}

?>

<#4315>
<?php

if( !$ilDB->tableExists('qpl_qst_kprim') )
{
	$ilDB->createTable('qpl_qst_kprim', array(
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'shuffle_answers' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'answer_type' => array(
			'type' => 'text',
			'length' => 16,
			'notnull' => true,
			'default' => 'singleLine'
		),
		'thumb_size' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => null
		),
		'opt_label' => array(
			'type' => 'text',
			'length' => 32,
			'notnull' => true,
			'default' => 'right/wrong'
		),
		'custom_true' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false,
			'default' => null
		),
		'custom_false' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false,
			'default' => null
		),
		'score_partsol' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'feedback_setting' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_kprim', array('question_fi'));
}

?>

<#4316>
<?php

if( !$ilDB->tableExists('qpl_a_kprim') )
{
	$ilDB->createTable('qpl_a_kprim', array(
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'position' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'answertext' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'default' => null
		),
		'imagefile' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false,
			'default' => null
		),
		'correctness' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_a_kprim', array('question_fi', 'position'));
	$ilDB->addIndex('qpl_a_kprim', array('question_fi'), 'i1');
}
?>

<#4317>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4318>
<?php

// #13858 
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::varchar2text('rbac_log', 'data');

?>
<#4319>
<?php

$ilDB->addTableColumn('page_qst_answer', 'unlocked', array(
	"type" => "integer",
	"notnull" => true,
	"length" => 1,
	"default" => 0
));

?>
<#4320>
<?php
/** @var ilDB $ilDB */
if(!$ilDB->tableColumnExists('tst_solutions', 'step'))
{
    $ilDB->addTableColumn('tst_solutions', 'step', array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => null
        ));
}
?>
<#4321>
<?php
/** @var ilDB $ilDB */
if(!$ilDB->tableColumnExists('tst_test_result', 'step'))
{
	$ilDB->addTableColumn('	tst_test_result', 'step', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>

<#4322>
<?php

	$ilDB->addTableColumn('event', 'reg_type', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false,
		'default' => 0
	));
	
?>

<#4323>
<?php

	$query = 'UPDATE event set reg_type = registration';
	$ilDB->manipulate($query);
?>

<#4324>
<?php
	$ilDB->addTableColumn('event', 'reg_limit_users', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => 0
	));

?>
<#4325>
<?php
	$ilDB->addTableColumn('event', 'reg_waiting_list', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));

?>
<#4326>
<?php
	$ilDB->addTableColumn('event', 'reg_limited', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));

?>
<#4327>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('bibs', 'BibliographicAdmin');

$ilCtrlStructureReader->getStructure();
?>
<#4328>
<?php

if( !$ilDB->tableExists('il_bibl_settings') )
{
	$ilDB->createTable('il_bibl_settings', array(
		'id' => array(
			'type' => "integer",
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'name' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true,
			'default' => "-"
		),
		'url' => array(
			'type' => 'text',
			'length' => 128,
			'notnull' => true,
			'default' => "-"
		),
		'img' => array(
			'type' => 'text',
			'length' => 128,
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey('il_bibl_settings', array('id'));
}
?>
<#4329>
<?php
	if(!$ilDB->tableColumnExists('frm_threads', 'thr_author_id'))
	{
		$ilDB->addTableColumn('frm_threads', 'thr_author_id',
			array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			));
	}
?>
<#4330>
<?php
	if($ilDB->tableColumnExists('frm_threads', 'thr_author_id'))
	{
		$ilDB->manipulate('UPDATE frm_threads SET thr_author_id = thr_usr_id');
	}
?>
<#4331>
<?php
	if(!$ilDB->tableColumnExists('frm_posts', 'pos_author_id'))
	{
		$ilDB->addTableColumn('frm_posts', 'pos_author_id',
			array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			));
	}
?>
<#4332>
<?php
	if($ilDB->tableColumnExists('frm_posts', 'pos_author_id'))
	{
		$ilDB->manipulate('UPDATE frm_posts SET pos_author_id = pos_usr_id');
	}
?>
<#4333>
<?php
	if(!$ilDB->tableColumnExists('frm_threads', 'thr_display_user_id'))
	{
		$ilDB->addTableColumn('frm_threads', 'thr_display_user_id',
			array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			));
	}
?>
<#4334>
<?php
	if($ilDB->tableColumnExists('frm_threads', 'thr_display_user_id'))
	{
		$ilDB->manipulate('UPDATE frm_threads SET thr_display_user_id = thr_usr_id');
	}
?>
<#4335>
<?php
	if($ilDB->tableColumnExists('frm_threads', 'thr_usr_id'))
	{
		$ilDB->dropTableColumn('frm_threads', 'thr_usr_id');
	}
	
?>
<#4336>
<?php
	if(!$ilDB->tableColumnExists('frm_posts', 'pos_display_user_id'))
	{
		$ilDB->addTableColumn('frm_posts', 'pos_display_user_id',
			array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			));
	}
?>
<#4337>
<?php
	if($ilDB->tableColumnExists('frm_posts', 'pos_display_user_id'))
	{
		$ilDB->manipulate('UPDATE frm_posts SET pos_display_user_id = pos_usr_id');
	}
?>
<#4338>
<?php
	if($ilDB->tableColumnExists('frm_posts', 'pos_usr_id'))
	{
		$ilDB->dropTableColumn('frm_posts', 'pos_usr_id');
	}
?>
<#4339>
<?php

$ilDB->createTable('sty_media_query', array(
	'id' => array(
		'type' => "integer",
		'length' => 4,
		'notnull' => true,
		'default' => 0
	),
	'style_id' => array(
		'type' => "integer",
		'length' => 4,
		'notnull' => true,
		'default' => 0
	),
	'order_nr' => array(
		'type' => "integer",
		'length' => 4,
		'notnull' => true,
		'default' => 0
	),
	'mquery' => array(
		'type' => 'text',
		'length' => 2000,
		'notnull' => false,
	)));
?>
<#4340>
<?php
	$ilDB->addPrimaryKey('sty_media_query', array('id'));
	$ilDB->createSequence('sty_media_query');
?>
<#4341>
<?php
	$ilDB->addTableColumn('style_parameter', 'mq_id', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 4,
		"default" => 0
	));
?>
<#4342>
<?php
	$ilDB->addTableColumn('style_parameter', 'custom', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
?>

<#4343>
<?php
$ini = new ilIniFile(ILIAS_ABSOLUTE_PATH."/ilias.ini.php");

if($ini->read())
{
	$ilSetting = new ilSetting();
	
	$https_header_enable = (bool) $ilSetting->get('ps_auto_https_enabled',false);
	$https_header_name = (string) $ilSetting->get('ps_auto_https_headername',"ILIAS_HTTPS_ENABLED");
	$https_header_value = (string) $ilSetting->get('ps_auto_https_headervalue',"1");

	if(!$ini->groupExists('https'))
	{
		$ini->addGroup('https');
	}
	
	$ini->setVariable("https","auto_https_detect_enabled", (!$https_header_enable) ? 0 : 1);
	$ini->setVariable("https","auto_https_detect_header_name", $https_header_name);
	$ini->setVariable("https","auto_https_detect_header_value", $https_header_value);

	$ini->write();
}
?>
<#4344>
<?php
	$ilSetting = new ilSetting();

	$ilSetting->delete('ps_auto_https_enabled');
	$ilSetting->delete('ps_auto_https_headername');
	$ilSetting->delete('ps_auto_https_headervalue');
?>
<#4345>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4346>
<?php
if( !$ilDB->tableColumnExists('tst_active', 'objective_container') )
{
	$ilDB->addTableColumn('tst_active', 'objective_container', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4347>
<?php
if( !$ilDB->tableExists('qpl_a_cloze_combi_res') )
{
	$ilDB->createTable('qpl_a_cloze_combi_res', array(
		'combination_id' => array(
			'type' => "integer",
			'length' => 4,
			'notnull' => true
		),
		'question_fi' => array(
			'type' => "integer",
			'length' => 4,
			'notnull' => true
		),
		'gap_fi' => array(
			'type' => "integer",
			'length' => 4,
			'notnull' => true
		),
		'answer' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false
		),
		'points' => array(
			'type' => 'float'
		),
		'best_solution' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
	));
}
?>
<#4348>
<?php
if( !$ilDB->tableColumnExists('conditions', 'hidden_status') )
{
	$ilDB->addTableColumn('conditions', 'hidden_status', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>
<#4349>
<?php
	if($ilDB->tableColumnExists('frm_posts', 'pos_usr_id'))
	{
		$ilDB->dropTableColumn('frm_posts', 'pos_usr_id');
	}
?>
<#4350>
<?php
	if($ilDB->tableColumnExists('frm_threads', 'thr_usr_id'))
	{
		$ilDB->dropTableColumn('frm_threads', 'thr_usr_id');
	}
?>


<#4351>
<?php
	$res = $ilDB->query("SELECT value FROM settings WHERE module = 'google_maps' AND keyword = 'enable'");
	if ($rec = $ilDB->fetchAssoc($res)) {
		$ilDB->manipulate("INSERT INTO settings (module, keyword, value) VALUES ('maps', 'type', 'googlemaps')");
	}
	
	// adjust naming in settings
	$ilDB->manipulate("UPDATE settings SET module = 'maps' WHERE module = 'google_maps'");
	
	// adjust naming in language data
	$ilDB->manipulate("UPDATE lng_data SET module = 'maps' WHERE module = 'gmaps'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_enable_maps_info' WHERE identifier = 'gmaps_enable_gmaps_info'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_enable_maps' WHERE identifier = 'gmaps_enable_gmaps'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_extt_maps' WHERE identifier = 'gmaps_extt_gmaps'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_latitude' WHERE identifier = 'gmaps_latitude'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_longitude' WHERE identifier = 'gmaps_longitude'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_lookup_address' WHERE identifier = 'gmaps_lookup_address'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_public_profile_info' WHERE identifier = 'gmaps_public_profile_info'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_settings' WHERE identifier = 'gmaps_settings'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_std_location_desc' WHERE identifier = 'gmaps_std_location_desc'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_std_location' WHERE identifier = 'gmaps_std_location'");
	$ilDB->manipulate("UPDATE lng_data SET identifier = 'maps_zoom_level' WHERE identifier = 'gmaps_zoom_level'");

?>
<#4352>
<?php

if(!$ilDB->tableColumnExists('il_blog','abs_shorten')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_shorten_len')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten_len',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_image')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_image',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_width')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_width',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_height')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_height',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

?>

<#4353>
<?php

if( !$ilDB->tableExists('usr_data_multi') )
{
	$ilDB->createTable('usr_data_multi', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'value' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
		)
	));
}

?>

<#4354>
<?php
if(!$ilDB->tableColumnExists('crs_start', 'pos'))
{
	$ilDB->addTableColumn('crs_start', 'pos', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>

<#4355>
<?php
if(!$ilDB->tableExists('loc_settings'))
{
	$ilDB->createTable('loc_settings', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
		)
	);

	$ilDB->addPrimaryKey('loc_settings', array('obj_id'));
}
?>
<#4356>
<?php
if(!$ilDB->tableColumnExists('loc_settings', 'itest'))
{
	$ilDB->addTableColumn('loc_settings', 'itest', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}

if(!$ilDB->tableColumnExists('loc_settings', 'qtest'))
{
	$ilDB->addTableColumn('loc_settings', 'qtest', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
?>

<#4357>
<?php
if(!$ilDB->tableColumnExists('adm_settings_template', 'auto_generated'))
{
	$ilDB->addTableColumn('adm_settings_template', 'auto_generated', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>

<#4358>
<?php
if( !$ilDB->tableColumnExists('crs_objective_lm', 'position') )
{
	$ilDB->addTableColumn('crs_objective_lm', 'position', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => 0
	));
}
?>
<#4359>
<?php

if(!$ilDB->tableExists('loc_rnd_qpl') )
{
	$ilDB->createTable('loc_rnd_qpl', array(
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tst_type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'tst_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'qp_seq' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'percentage' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
	));
	$ilDB->addPrimaryKey('loc_rnd_qpl', array('container_id', 'objective_id', 'tst_type'));
}
?>
<#4360>
<?php

$query = 'INSERT INTO adm_settings_template '.
		'(id, type, title, description, auto_generated) '.
		'VALUES( '.
		$ilDB->quote($ilDB->nextId('adm_settings_template'),'integer').', '.
		$ilDB->quote('tst','text').', '.
		$ilDB->quote('il_astpl_loc_initial','text').', '.
		$ilDB->quote('il_astpl_loc_initial_desc','text').', '.
		$ilDB->quote(1,'integer').' '.
		')';
$ilDB->manipulate($query);
?>
<#4361>
<?php

$query = 'INSERT INTO adm_settings_template '.
		'(id, type, title, description, auto_generated) '.
		'VALUES( '.
		$ilDB->quote($ilDB->nextId('adm_settings_template'),'integer').', '.
		$ilDB->quote('tst','text').', '.
		$ilDB->quote('il_astpl_loc_qualified','text').', '.
		$ilDB->quote('il_astpl_loc_qualified_desc','text').', '.
		$ilDB->quote(1,'integer').' '.
		')';
$ilDB->manipulate($query);
?>

<#4362>
<?php

if( !$ilDB->tableExists('loc_user_results') )
{
	$ilDB->createTable('loc_user_results', array(
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'course_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'result_perc' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'limit_perc' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'tries' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'is_final' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('loc_user_results', array('user_id', 'course_id', 'objective_id', 'type'));
}
?>
<#4363>
<?php
if(!$ilDB->tableColumnExists('loc_settings', 'qt_vis_all'))
{
	$ilDB->addTableColumn('loc_settings', 'qt_vis_all', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 1
	));
}
?>

<#4364>
<?php
if(!$ilDB->tableColumnExists('loc_settings', 'qt_vis_obj'))
{
	$ilDB->addTableColumn('loc_settings', 'qt_vis_obj', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>

<#4365>
<?php
if(!$ilDB->tableColumnExists('crs_objectives', 'active'))
{
	$ilDB->addTableColumn('crs_objectives', 'active', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 1
	));
}
?>

<#4366>
<?php
if(!$ilDB->tableColumnExists('crs_objectives', 'passes'))
{
	$ilDB->addTableColumn('crs_objectives', 'passes', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false,
		'default' => 0
	));
}
?>

<#4367>
<?php
if(!$ilDB->tableExists('loc_tst_run'))
{
	$ilDB->createTable('loc_tst_run', array(
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'test_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'max_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		),
		'questions' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('loc_tst_run', array('container_id', 'user_id', 'test_id', 'objective_id'));
}
?>
<#4368>
<?php
if(!$ilDB->tableColumnExists('loc_settings','reset_results'))
{
    $ilDB->addTableColumn(
        'loc_settings',
        'reset_results',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}
?>
<#4369>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4370>
<?php
if(!$ilDB->tableColumnExists('il_bibl_settings', 'show_in_list'))
{
	$ilDB->addTableColumn('il_bibl_settings', 'show_in_list', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>
<#4371>
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
<#4372>
<?php
	if($ilDB->getDBType() == 'innodb')
	{
		$ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
	}
?>
<#4373>
<?php
	if($ilDB->getDBType() == 'innodb')
	{
		$ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
	}
?>
<#4374>
<?php
	if(!$ilDB->tableColumnExists('frm_posts', 'is_author_moderator'))
	{
		$ilDB->addTableColumn('frm_posts', 'is_author_moderator', array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null)
		);
	}
?>
<#4375>
<?php
if(!$ilDB->tableColumnExists('ecs_part_settings','token'))
{
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'token',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>
<#4376>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4377>
<?php
if(!$ilDB->tableColumnExists('ecs_part_settings','export_types'))
{
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'export_types',
        array(
            'type' => 'text',
			'length' => 4000,
            'notnull' => FALSE,
        ));
}
?>
<#4378>
<?php
if(!$ilDB->tableColumnExists('ecs_part_settings','import_types'))
{
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'import_types',
        array(
            'type' => 'text',
			'length' => 4000,
            'notnull' => FALSE,
        ));
}
?>
<#4379>
<?php

	$query = 'UPDATE ecs_part_settings SET export_types = '.$ilDB->quote(serialize(array('cat','crs','file','glo','grp','wiki','lm')),'text');
	$ilDB->manipulate($query);

?>

<#4380>
<?php

	$query = 'UPDATE ecs_part_settings SET import_types = '.$ilDB->quote(serialize(array('cat','crs','file','glo','grp','wiki','lm')),'text');
	$ilDB->manipulate($query);

?>
<#4381>
<?php
if(!$ilDB->tableColumnExists('reg_registration_codes','reg_enabled'))
{
    $ilDB->addTableColumn(
        'reg_registration_codes',
        'reg_enabled',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => TRUE,
			'default' => 1
        ));
}
?>

<#4382>
<?php
if(!$ilDB->tableColumnExists('reg_registration_codes','ext_enabled'))
{
    $ilDB->addTableColumn(
        'reg_registration_codes',
        'ext_enabled',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => TRUE,
			'default' => 0
        ));
}
?>
<#4383>
<?php

$query = 'SELECT * FROM usr_account_codes ';
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$until = $row->valid_until;
	if($until === '0')
	{
		$alimit = 'unlimited';
		$a_limitdt = null;
	}
	elseif(is_numeric($until))
	{
		$alimit = 'relative';
		$a_limitdt = array(
			'd' => (string) $until,
			'm' => '',
			'y' => ''
		);
		$a_limitdt = serialize($a_limitdt);
	}
	else
	{
		$alimit = 'absolute';
		$a_limitdt = $until;
	}
	
	$next_id = $ilDB->nextId('reg_registration_codes');
	$query = 'INSERT INTO reg_registration_codes '.
			'(code_id, code, role, generated, used, role_local, alimit, alimitdt, reg_enabled, ext_enabled ) '.
			'VALUES ( '.
			$ilDB->quote($next_id,'integer').', '.
			$ilDB->quote($row->code,'text').', '.
			$ilDB->quote(0,'integer').', '.
			$ilDB->quote($row->generated,'integer').', '.
			$ilDB->quote($row->used,'integer').', '.
			$ilDB->quote('','text').', '.
			$ilDB->quote($alimit,'text').', '.
			$ilDB->quote($a_limitdt,'text').', '.
			$ilDB->quote(0,'integer').', '.
			$ilDB->quote(1,'integer').' '.
			')';
	$ilDB->manipulate($query);
}
?>
<#4384>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4385>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4386>
<?php
	$ilSetting = new ilSetting("assessment");
	$ilSetting->set("use_javascript", "1");
?>
<#4387>
<?php
	$ilDB->update('tst_tests', 
		array('forcejs' => array('integer', 1)),
		array('forcejs' => array('integer', 0))
	);
?>
<#4388>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4389>
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
<#4390>
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

<#4391>
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

<#4392>
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

<#4393>
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

<#4394>
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

<#4395>
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

<#4396>
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

<#4397>
<?php

//$ilDB->addPrimaryKey('tst_dyn_quest_set_cfg', array('test_fi'));

?>

<#4398>
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

<#4399>
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
<#4400>
<?php
$set = $ilDB->query('SELECT * FROM il_dcl_datatype_prop WHERE id = 14');
if (!$ilDB->numRows($set)) {
    $ilDB->insert('il_dcl_datatype_prop', array(
        'id' => array('integer', 14),
        'datatype_id' => array('integer', 2),
        'title' => array('text', 'link_detail_page'),
        'inputformat' => array('integer', 4),
    ));
}
$set = $ilDB->query('SELECT * FROM il_dcl_datatype_prop WHERE id = 15');
if (!$ilDB->numRows($set)) {
    $ilDB->insert('il_dcl_datatype_prop', array(
        'id' => array('integer', 15),
        'datatype_id' => array('integer', 9),
        'title' => array('text', 'link_detail_page'),
        'inputformat' => array('integer', 4),
    ));
}
?>
<#4401>
<?php
$ilDB->dropIndex("page_object", $a_name = "i2");
?>
<#4402>
<?php

$ilDB->manipulate("DELETE FROM settings".
	" WHERE module = ".$ilDB->quote("common", "text").
	" AND keyword = ".$ilDB->quote("obj_dis_creation_rcrs", "text"));

?>
<#4403>
<?php

$settings = new ilSetting();
if( !$settings->get('ommit_legacy_ou_dbtable_deletion', 0) )
{
	$ilDB->dropSequence('org_unit_data');
	$ilDB->dropTable('org_unit_data');
	$ilDB->dropTable('org_unit_tree');
	$ilDB->dropTable('org_unit_assignments');
}

?>
<#4404>
<?php
	$ilDB->manipulate("UPDATE frm_posts SET pos_update = pos_date WHERE pos_update IS NULL");
?>
<#4405>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4406>
<?php
$ilDB->insert('il_dcl_datatype_prop', array(
    'id' => array('integer', 16),
    'datatype_id' => array('integer', 9),
    'title' => array('text', 'allowed_file_types'),
    'inputformat' => array('integer', 12),
));
?>
<#4407>
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
<#4408>
<?php
if( $ilDB->tableExists('tst_test_random') )
{
	$ilDB->dropTable('tst_test_random');
}
?>

<#4409>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4410>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4411>
<?php
if (!$ilDB->sequenceExists('il_bibl_settings')) {
	$ilDB->createSequence('il_bibl_settings');
	$set = $ilDB->query('SELECT MAX(id) new_seq FROM il_bibl_settings');
	$rec = $ilDB->fetchObject($set);
	$ilDB->insert('il_bibl_settings_seq', array('sequence' => array('integer', $rec->new_seq)));
}
?>
<#4412>
<?php
if(!$ilDB->tableColumnExists('ecs_part_settings', 'dtoken'))
{
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'dtoken',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => TRUE,
			'default' => 1
        ));
}
?>
<#4413>
<?php
if($ilDB->tableColumnExists('crs_objectives', 'description'))
{
	$ilDB->modifyTableColumn(
		'crs_objectives',
		'description',
		array(
			"type" => "text",
			"length" => 500,
			"notnull" => false,
			"default" => ""
		)
	);
}
?>
<#4414>
<?php

$ilDB->insert("payment_settings", array(
			"keyword" => array("text", 'enable_topics'),
			"value" => array("clob", 1),
			"scope" => array("text", 'gui')));

?>
<#4415>
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
<#4416>
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
<#4417>
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
<#4418>
<?php

if( !$ilDB->uniqueConstraintExists('tst_active', array('user_fi', 'test_fi', 'anonymous_id')) )
{
	$ilDB->addUniqueConstraint('tst_active', array('user_fi', 'test_fi', 'anonymous_id'), 'uc1');
}

?>
<#4419>
<?php

$ilDB->manipulate('delete from ecs_course_assignments');

?>
<#4420>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4421>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4422>
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
<#4423>
<?php

if($ilDB->tableColumnExists("usr_portfolio", "comments"))
{
	// #14661 - centralized public comments setting
	include_once "Services/Notes/classes/class.ilNote.php";

	$data = array();

	$set = $ilDB->query("SELECT prtf.id,prtf.comments,od.type".
		" FROM usr_portfolio prtf".
		" JOIN object_data od ON (prtf.id = od.obj_id)");
	while($row = $ilDB->fetchAssoc($set))
	{		
		$row["comments"] = (bool)$row["comments"];
		$data[] = $row;
	}

	$set = $ilDB->query("SELECT id,notes comments".
		" FROM il_blog");
	while($row = $ilDB->fetchAssoc($set))
	{		
		$row["type"] = "blog";
		$row["comments"] = (bool)$row["comments"];
		$data[] = $row;
	}

	$set = $ilDB->query("SELECT cobj.id,cobj.pub_notes comments,od.type".
		" FROM content_object cobj".
		" JOIN object_data od ON (cobj.id = od.obj_id)");
	while($row = $ilDB->fetchAssoc($set))
	{		
		$row["comments"] = ($row["comments"] == "y" ? true : false);
		$data[] = $row;
	}
	
	$set = $ilDB->query("SELECT id,show_comments comments".
		" FROM il_poll");
	while($row = $ilDB->fetchAssoc($set))
	{		
		$row["type"] = "poll";
		$row["comments"] = (bool)$row["comments"];
		$data[] = $row;
	}

	if(sizeof($data))
	{	
		foreach($data as $item)
		{
			if($item["id"] && $item["type"])
			{
				$ilDB->manipulate("DELETE FROM note_settings".
					" WHERE rep_obj_id = ".$ilDB->quote($item["id"], "integer").
					" AND obj_id = ".$ilDB->quote(0, "integer").
					" AND obj_type = ".$ilDB->quote($item["type"], "text"));

				if($item["comments"])
				{
					$ilDB->manipulate("INSERT INTO note_settings".
						" (rep_obj_id, obj_id, obj_type, activated)".
						" VALUES (".$ilDB->quote($item["id"], "integer").
						", ".$ilDB->quote(0, "integer").
						", ".$ilDB->quote($item["type"], "text").
						", ".$ilDB->quote(1, "integer").")");
				}
			}
		}		
	}
}

?>
<#4424>
<?php

if($ilDB->tableColumnExists("usr_portfolio", "comments"))
{
	$ilDB->dropTableColumn("usr_portfolio", "comments");
	$ilDB->dropTableColumn("il_blog", "notes");
	$ilDB->dropTableColumn("content_object", "pub_notes");
	$ilDB->dropTableColumn("il_poll", "show_comments");
}

?>

<#4425>
<?php

if($ilDB->tableColumnExists('ecs_cms_data','cms_id'))
{
	$ilDB->renameTableColumn('ecs_cms_data','cms_id','cms_bak');
	$ilDB->addTableColumn('ecs_cms_data', 'cms_id', array(
			"type" => "text",
			"notnull" => FALSE,
			"length" => 512
		)
	);
	
	$query = 'UPDATE ecs_cms_data SET cms_id = cms_bak ';
	$ilDB->manipulate($query);
	
	$ilDB->dropTableColumn('ecs_cms_data','cms_bak');
}
?>
<#4426>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4427>
<?php

if($ilDB->tableColumnExists('ecs_import','econtent_id'))
{
	$ilDB->renameTableColumn('ecs_import','econtent_id','econtent_id_bak');
	$ilDB->addTableColumn('ecs_import', 'econtent_id', array(
			"type" => "text",
			"notnull" => FALSE,
			"length" => 512
		)
	);
	
	$query = 'UPDATE ecs_import SET econtent_id = econtent_id_bak ';
	$ilDB->manipulate($query);
	
	$ilDB->dropTableColumn('ecs_import','econtent_id_bak');
}
?>
<#4428>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');	
if($tgt_ops_id)
{				
	$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
	if($lp_type_id)
	{			
		// add "edit_learning_progress" to session
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);				
									
		// clone settings from "write" to "edit_learning_progress"
		$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');	
		ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
		
		// clone settings from "write" to "read_learning_progress" (4287 did not work for sessions)
		$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');	
		if($tgt_ops_id)
		{
			ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
		}
	}	
}

?>

<#4429>
<?php

$query = 'DELETE from cal_recurrence_rules WHERE cal_id IN ( select cal_id from cal_entries where is_milestone =  '.$ilDB->quote(1,'integer').')';
$ilDB->manipulate($query);

?>

<#4430>
<?php
if(! $ilDB->tableColumnExists('qpl_a_cloze_combi_res', 'row_id'))
{
	$query = 'DELETE from qpl_a_cloze_combi_res';
	$ilDB->manipulate($query);
	$ilDB->addTableColumn(
		 'qpl_a_cloze_combi_res',
			 'row_id',
			 array(
				 'type' => 'integer',
				 'length' => 4,
				 'default' => 0
			 ));
}
?>
<#4431>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4432>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4433>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4434>
<?php
if( $ilDB->tableColumnExists('tst_tests', 'examid_in_kiosk') )
{
	$ilDB->renameTableColumn('tst_tests', 'examid_in_kiosk', 'examid_in_test_pass');
}
?>
<#4435>
<?php
if( $ilDB->tableColumnExists('tst_tests', 'show_exam_id') )
{
	$ilDB->renameTableColumn('tst_tests', 'show_exam_id', 'examid_in_test_res');
}
?>
<#4436>
<?php
if(! $ilDB->tableColumnExists('il_wiki_page', 'hide_adv_md'))
{	
	$ilDB->addTableColumn('il_wiki_page', 'hide_adv_md',
		array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0
		));
}
?>
<#4437>
<?php
if( !$ilDB->tableColumnExists('tst_active', 'start_lock'))
{	
	$ilDB->addTableColumn('tst_active', 'start_lock',
		array(
			'type' => 'text',
			'length' => 128,
			'notnull' => false,
			'default' => null
		));
}
?>
<#4438>
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
<#4439>
<?php
if( !$ilDB->tableColumnExists('file_based_lm', 'show_lic'))
{	
	$ilDB->addTableColumn('file_based_lm', 'show_lic',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null
		));
}
if( !$ilDB->tableColumnExists('file_based_lm', 'show_bib'))
{	
	$ilDB->addTableColumn('file_based_lm', 'show_bib',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null
		));
}
?>
<#4440>
<?php

$ilDB->manipulate("UPDATE settings ".
	"SET value = ".$ilDB->quote(1370, "text").
	" WHERE module = ".$ilDB->quote("blga", "text").
	" AND keyword = ".$ilDB->quote("banner_width", "text").
	" AND value = ".$ilDB->quote(880, "text"));

$ilDB->manipulate("UPDATE settings ".
	"SET value = ".$ilDB->quote(1370, "text").
	" WHERE module = ".$ilDB->quote("prfa", "text").
	" AND keyword = ".$ilDB->quote("banner_width", "text").
	" AND value = ".$ilDB->quote(880, "text"));

?>
<#4441>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');	
if($tgt_ops_id)
{				
	$feed_type_id = ilDBUpdateNewObjectType::getObjectTypeId('feed');
	if($feed_type_id)
	{			
		// add "copy" to (external) feed
		ilDBUpdateNewObjectType::addRBACOperation($feed_type_id, $tgt_ops_id);				
									
		// clone settings from "write" to "copy"
		$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');	
		ilDBUpdateNewObjectType::cloneOperation('feed', $src_ops_id, $tgt_ops_id);		
	}	
}

?>
<#4442>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4443>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4444>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4445>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4446>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4447>
<?php
	if (!$ilDB->tableColumnExists('skl_user_has_level', 'self_eval'))
	{
		$ilDB->addTableColumn("skl_user_has_level", "self_eval", array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
?>
<#4448>
<?php
	if (!$ilDB->tableColumnExists('skl_user_skill_level', 'self_eval'))
	{
		$ilDB->addTableColumn("skl_user_skill_level", "self_eval", array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
?>
<#4449>
<?php
		$ilDB->dropPrimaryKey("skl_user_has_level");
		$ilDB->addPrimaryKey("skl_user_has_level",
			array("level_id", "user_id", "trigger_obj_id", "tref_id", "self_eval"));
?>
<#4450>
<?php
		$ilDB->modifyTableColumn("skl_user_has_level", "trigger_obj_type",
			array(
				"type" => "text",
				"length" => 4,
				"notnull" => false
			));

		$ilDB->modifyTableColumn("skl_user_skill_level", "trigger_obj_type",
			array(
				"type" => "text",
				"length" => 4,
				"notnull" => false
			));
?>
<#4451>
<?php
	$ilSetting = new ilSetting();
	if ((int) $ilSetting->get("optes_360_db") <= 0)
	{
		/*$ilDB->manipulate("DELETE FROM skl_user_has_level WHERE ".
			" self_eval = ".$ilDB->quote(1, "integer")
		);
		$ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE ".
			" self_eval = ".$ilDB->quote(1, "integer")
		);*/

		$set = $ilDB->query("SELECT * FROM skl_self_eval_level ORDER BY last_update ASC");
		$writtenkeys = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (!in_array($rec["level_id"].":".$rec["user_id"].":".$rec["tref_id"], $writtenkeys))
			{
				$writtenkeys[] = $rec["level_id"].":".$rec["user_id"].":".$rec["tref_id"];
				$q = "INSERT INTO skl_user_has_level ".
					"(level_id, user_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_title, tref_id, trigger_obj_type, self_eval) VALUES (".
					$ilDB->quote($rec["level_id"], "integer").",".
					$ilDB->quote($rec["user_id"], "integer").",".
					$ilDB->quote($rec["last_update"], "timestamp").",".
					$ilDB->quote($rec["skill_id"], "integer").",".
					$ilDB->quote(0, "integer").",".
					$ilDB->quote(0, "integer").",".
					$ilDB->quote("", "text").",".
					$ilDB->quote($rec["tref_id"], "integer").",".
					$ilDB->quote("", "text").",".
					$ilDB->quote(1, "integer").
					")";
				$ilDB->manipulate($q);
			}
			else
			{
				$ilDB->manipulate("UPDATE skl_user_has_level SET ".
					" status_date = ".$ilDB->quote($rec["last_update"], "timestamp").",".
					" skill_id = ".$ilDB->quote($rec["skill_id"], "integer").
					" WHERE level_id = ".$ilDB->quote($rec["level_id"], "integer").
					" AND user_id = ".$ilDB->quote($rec["user_id"], "integer").
					" AND trigger_obj_id = ".$ilDB->quote(0, "integer").
					" AND tref_id = ".$ilDB->quote($rec["tref_id"], "integer").
					" AND self_eval = ".$ilDB->quote(1, "integer")
					);
			}
			$q = "INSERT INTO skl_user_skill_level ".
				"(level_id, user_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_title, tref_id, trigger_obj_type, self_eval, status, valid) VALUES (".
				$ilDB->quote($rec["level_id"], "integer").",".
				$ilDB->quote($rec["user_id"], "integer").",".
				$ilDB->quote($rec["last_update"], "timestamp").",".
				$ilDB->quote($rec["skill_id"], "integer").",".
				$ilDB->quote(0, "integer").",".
				$ilDB->quote(0, "integer").",".
				$ilDB->quote("", "text").",".
				$ilDB->quote($rec["tref_id"], "integer").",".
				$ilDB->quote("", "text").",".
				$ilDB->quote(1, "integer").",".
				$ilDB->quote(1, "integer").",".
				$ilDB->quote(1, "integer").
				")";
			$ilDB->manipulate($q);
		}
	}
?>
