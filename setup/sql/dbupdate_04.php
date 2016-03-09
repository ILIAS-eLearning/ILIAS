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
		$query = "show index from cmi_gobjective where Key_name = 'PRIMARY'";
		$res = $ilDB->query($query);
		if (!$ilDB->numRows($res)) {
			$ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
		}
	}
?>
<#4373>
<?php
	if($ilDB->getDBType() == 'innodb')
	{
		$query = "show index from cp_suspend where Key_name = 'PRIMARY'";
		$res = $ilDB->query($query);
		if (!$ilDB->numRows($res)) {
			$ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
		}
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
<#4452>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4453>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4454>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4455>
<?php
	if(!$ilDB->sequenceExists('booking_reservation_group'))
	{
		$ilDB->createSequence('booking_reservation_group');
	}
?>
<#4456>
<?php

	if(!$ilDB->tableColumnExists('crs_objective_tst','tst_limit_p'))
	{
		$ilDB->addTableColumn('crs_objective_tst', 'tst_limit_p', array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 0
		));
	}
?>
<#4457>
<?php

// update question assignment limits
$query = 'SELECT objective_id, ref_id, question_id FROM crs_objective_qst ';
$res = $ilDB->query($query);

$questions = array();
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$questions[$row->objective_id.'_'.$row->ref_id][] = $row->question_id;
}

$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($questions,TRUE));

foreach($questions as $objective_ref_id => $qst_ids)
{
	$parts = explode('_', $objective_ref_id);
	$objective_id = $parts[0];
	$tst_ref_id = $parts[1];
	
	$sum = 0;
	foreach((array) $qst_ids as $qst_id)
	{
		$query = 'SELECT points FROM qpl_questions WHERE question_id = ' . $ilDB->quote($qst_id,'integer');
		$res_qst = $ilDB->query($query);
		while($row = $res_qst->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$sum += $row->points;
		}
		if($sum > 0)
		{
			// read limit
			$query = 'SELECT tst_limit FROM crs_objective_tst '.
					'WHERE objective_id = '.$ilDB->quote($objective_id,'integer');
			$res_limit = $ilDB->query($query);
			
			$limit_points = 0;
			while($row = $res_limit->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$limit_points = $row->tst_limit;
			}
			// calculate percentage
			$limit_p = $limit_points / $sum * 100;
			$limit_p = intval($limit_p);
			$limit_p = ($limit_p >= 100 ? 100 : $limit_p);
			
			// update
			$query = 'UPDATE crs_objective_tst '.
					'SET tst_limit_p = '.$ilDB->quote($limit_p,'integer').' '.
					'WHERE objective_id = '.$ilDB->quote($objective_id,'integer').' '.
					'AND ref_id = '.$ilDB->quote($tst_ref_id,'integer');
			$ilDB->manipulate($query);
		}
	}
}
?>
<#4458>
<?php
if(!$ilDB->tableColumnExists('tst_tests','intro_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'intro_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4459>
<?php
if(!$ilDB->tableColumnExists('tst_tests','starting_time_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'starting_time_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4460>
<?php
if(!$ilDB->tableColumnExists('tst_tests','ending_time_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'ending_time_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4461>
<?php
if($ilDB->tableColumnExists('tst_tests','intro_enabled'))
{
	$ilDB->dropTableColumn('tst_tests', 'intro_enabled');
}
?>
<#4462>
<?php
if($ilDB->tableColumnExists('tst_tests','starting_time_enabled'))
{
	$ilDB->dropTableColumn('tst_tests', 'starting_time_enabled');
}
?>
<#4463>
<?php
if($ilDB->tableColumnExists('tst_tests','ending_time_enabled'))
{
	$ilDB->dropTableColumn('tst_tests', 'ending_time_enabled');
}
?>
<#4464>
<?php
if(!$ilDB->tableColumnExists('tst_tests','intro_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'intro_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->queryF(
		"UPDATE tst_tests SET intro_enabled = %s WHERE LENGTH(introduction) > %s",
		array('integer', 'integer'), array(1, 0)
	);

	$ilDB->queryF(
		"UPDATE tst_tests SET intro_enabled = %s WHERE LENGTH(introduction) = %s OR LENGTH(introduction) IS NULL",
		array('integer', 'integer'), array(0, 0)
	);
}
?>
<#4465>
<?php
if(!$ilDB->tableColumnExists('tst_tests','starting_time_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'starting_time_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->queryF(
		"UPDATE tst_tests SET starting_time_enabled = %s WHERE LENGTH(starting_time) > %s",
		array('integer', 'integer'), array(1, 0)
	);

	$ilDB->queryF(
		"UPDATE tst_tests SET starting_time_enabled = %s WHERE LENGTH(starting_time) = %s OR LENGTH(starting_time) IS NULL",
		array('integer', 'integer'), array(0, 0)
	);
}
?>
<#4466>
<?php
if(!$ilDB->tableColumnExists('tst_tests','ending_time_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'ending_time_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->queryF(
		"UPDATE tst_tests SET ending_time_enabled = %s WHERE LENGTH(ending_time) > %s",
		array('integer', 'integer'), array(1, 0)
	);

	$ilDB->queryF(
		"UPDATE tst_tests SET ending_time_enabled = %s WHERE LENGTH(ending_time) = %s OR LENGTH(ending_time) IS NULL",
		array('integer', 'integer'), array(0, 0)
	);
}
?>
<#4467>
<?php
if(!$ilDB->tableColumnExists('tst_tests','password_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'password_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->queryF(
		"UPDATE tst_tests SET password_enabled = %s WHERE LENGTH(password) > %s",
		array('integer', 'integer'), array(1, 0)
	);

	$ilDB->queryF(
		"UPDATE tst_tests SET password_enabled = %s WHERE LENGTH(password) = %s OR LENGTH(password) IS NULL",
		array('integer', 'integer'), array(0, 0)
	);
}
?>
<#4468>
<?php
if(!$ilDB->tableColumnExists('tst_tests','limit_users_enabled'))
{
	$ilDB->addTableColumn('tst_tests', 'limit_users_enabled', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->queryF(
		"UPDATE tst_tests SET limit_users_enabled = %s WHERE allowedusers IS NOT NULL AND allowedusers > %s",
		array('integer', 'integer'), array(1, 0)
	);

	$ilDB->queryF(
		"UPDATE tst_tests SET limit_users_enabled = %s WHERE allowedusers IS NULL OR allowedusers <= %s",
		array('integer', 'integer'), array(0, 0)
	);
}
?>
<#4469>
<?php
// @ukonhle: Please do not commit empty database steps ;-)
?>
<#4470>
<?php
$ilDB->queryF(
	'DELETE FROM settings WHERE keyword = %s',
	array('text'),
	array('ps_export_scorm')
);
$ilDB->queryF(
	'INSERT INTO settings (module, keyword, value) VALUES (%s,%s,%s)',
	array('text','text','text'),
	array('common','ps_export_scorm','1')
);
?>
<#4471>
<?php
$ilDB->manipulate('DELETE FROM addressbook WHERE login NOT IN(SELECT login FROM usr_data) AND email IS NULL');
$ilDB->manipulate(
	'DELETE FROM addressbook_mlist_ass WHERE addr_id NOT IN(
		SELECT addr_id FROM addressbook
	)'
);
?>
<#4472>
<?php
	if(!$ilDB->indexExistsByFields('page_question',array('page_parent_type','page_id', 'page_lang')))
	{
		$ilDB->addIndex('page_question',array('page_parent_type','page_id', 'page_lang'),'i1');
	}
?>
<#4473>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4474>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');				
$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('svy');
if($lp_type_id)
{				
	$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');	

	// clone settings from "write" to "edit_learning_progress"
	$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');	
	if($tgt_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);				
		ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
	}

	// clone settings from "write" to "read_learning_progress"
	$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');	
	if($tgt_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);		
		ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
	}
}	

?>
<#4475>
<?php

if($ilDB->tableColumnExists('obj_stat', 'tstamp'))
{
	$ilDB->dropTableColumn('obj_stat', 'tstamp');
}

?>
<#4476>
<?php
if(!$ilDB->uniqueConstraintExists('usr_data', array('login')))
{
	$res = $ilDB->query("
		SELECT COUNT(*) cnt
		FROM (
			SELECT login
			FROM usr_data
			GROUP BY login
			HAVING COUNT(*) > 1
		) duplicatelogins
	");
	$data = $ilDB->fetchAssoc($res);
	if($data['cnt'] > 0)
	{
		echo "<pre>
				Dear Administrator,

				PLEASE READ THE FOLLOWING INSTRUCTIONS

				The update process has been stopped due to data inconsistency reasons.
				We found multiple ILIAS user accounts with the same login. You have to fix this issue manually.

				Database table: usr_data
				Field: login

				You can determine these accounts by executing the following SQL statement:
				SELECT * FROM usr_data WHERE login IN(SELECT login FROM usr_data GROUP BY login HAVING COUNT(*) > 1)

				Please manipulate the affected records by choosing different login names.
				If you try to rerun the update process, this warning will apear again if the issue is still not solved.

				Best regards,
				The ILIAS developers
			</pre>";
		exit();
	}

	$ilDB->addUniqueConstraint('usr_data', array('login'), 'uc1');
}
?>
<#4477>
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
<#4478>
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
<#4479>
<?php
$ilDB->manipulate("UPDATE style_data SET ".
	" uptodate = ".$ilDB->quote(0, "integer")
	);
?>
<#4480>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4481>
<?php
$ilDB->manipulate("UPDATE tst_active SET last_finished_pass = (tries - 1) WHERE last_finished_pass IS NULL");
?>
<#4482>
<?php
$ilDB->manipulate("DELETE FROM il_dcl_datatype_prop WHERE title = " . $ilDB->quote('allowed_file_types', 'text'));
?>
<#4483>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4484>
<?php
if( !$ilDB->tableColumnExists('qpl_questionpool', 'skill_service') )
{
	$ilDB->addTableColumn('qpl_questionpool', 'skill_service', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		'UPDATE qpl_questionpool SET skill_service = %s',
		array('integer'), array(0)
	);
}
?>
<#4485>
<?php
if( !$ilDB->tableExists('qpl_qst_skl_assigns') )
{
	$ilDB->createTable('qpl_qst_skl_assigns', array(
		'obj_fi' => array(
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

	$ilDB->addPrimaryKey('qpl_qst_skl_assigns', array('obj_fi', 'question_fi', 'skill_base_fi', 'skill_tref_fi'));

	if( $ilDB->tableExists('tst_skl_qst_assigns') )
	{
		$res = $ilDB->query("
			SELECT tst_skl_qst_assigns.*, tst_tests.obj_fi
			FROM tst_skl_qst_assigns
			INNER JOIN tst_tests ON test_id = test_fi
		");

		while( $row = $ilDB->fetchAssoc($res) )
		{
			$ilDB->replace('qpl_qst_skl_assigns',
				array(
					'obj_fi' => array('integer', $row['obj_fi']),
					'question_fi' => array('integer', $row['question_fi']),
					'skill_base_fi' => array('integer', $row['skill_base_fi']),
					'skill_tref_fi' => array('integer', $row['skill_tref_fi'])
				),
				array(
					'skill_points' => array('integer', $row['skill_points'])
				)
			);
		}

		$ilDB->dropTable('tst_skl_qst_assigns');
	}
}
?>
<#4486>
<?php
$setting = new ilSetting();

if( !$setting->get('dbup_tst_skl_thres_mig_done', 0) )
{
	if( !$ilDB->tableExists('tst_threshold_tmp') )
	{
		$ilDB->createTable('tst_threshold_tmp', array(
			'test_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			),
			'obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
			)
		));

		$ilDB->addPrimaryKey('tst_threshold_tmp', array('test_id'));
	}

	$res = $ilDB->query("
		SELECT DISTINCT tst_tests.test_id, obj_fi FROM tst_tests
		INNER JOIN tst_skl_thresholds ON test_fi = tst_tests.test_id
		LEFT JOIN tst_threshold_tmp ON tst_tests.test_id = tst_threshold_tmp.test_id
		WHERE tst_threshold_tmp.test_id IS NULL
	");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('tst_threshold_tmp',
			array('test_id' => array('integer', $row['test_id'])),
			array('obj_id' => array('integer', $row['obj_fi']))
		);
	}

	if( !$ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
	{
		$ilDB->addTableColumn('tst_skl_thresholds', 'tmp', array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => null
		));
	}

	$setting->set('dbup_tst_skl_thres_mig_done', 1);
}
?>
<#4487>
<?php
if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$stmtSelectSklPointSum = $ilDB->prepare(
		"SELECT skill_base_fi, skill_tref_fi, SUM(skill_points) points_sum FROM qpl_qst_skl_assigns
			WHERE obj_fi = ? GROUP BY skill_base_fi, skill_tref_fi", array('integer')
	);

	$stmtUpdatePercentThresholds = $ilDB->prepareManip(
		"UPDATE tst_skl_thresholds SET tmp = ROUND( ((threshold * 100) / ?), 0 )
			WHERE test_fi = ? AND skill_base_fi = ? AND skill_tref_fi = ?",
		array('integer', 'integer', 'integer', 'integer')
	);

	$res1 = $ilDB->query("
		SELECT DISTINCT test_id, obj_id FROM tst_threshold_tmp
		INNER JOIN tst_skl_thresholds ON test_fi = test_id
		WHERE tmp IS NULL
	");

	while( $row1 = $ilDB->fetchAssoc($res1) )
	{
		$res2 = $ilDB->execute($stmtSelectSklPointSum, array($row1['obj_id']));

		while( $row2 = $ilDB->fetchAssoc($res2) )
		{
			$ilDB->execute($stmtUpdatePercentThresholds, array(
				$row2['points_sum'], $row1['test_id'], $row2['skill_base_fi'], $row2['skill_tref_fi']
			));
		}
	}
}
?>
<#4488>
<?php
if( $ilDB->tableExists('tst_threshold_tmp') )
{
	$ilDB->dropTable('tst_threshold_tmp');
}
?>
<#4489>
<?php
if( $ilDB->tableColumnExists('tst_skl_thresholds', 'tmp') )
{
	$ilDB->manipulate("UPDATE tst_skl_thresholds SET threshold = tmp");
	$ilDB->dropTableColumn('tst_skl_thresholds', 'tmp');
}
?>
<#4490>
<?php
if( !$ilDB->tableColumnExists('qpl_qst_skl_assigns', 'eval_mode') )
{
	$ilDB->addTableColumn('qpl_qst_skl_assigns', 'eval_mode', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));

	$ilDB->manipulateF(
		"UPDATE qpl_qst_skl_assigns SET eval_mode = %s", array('text'), array('result')
	);
}
?>
<#4491>
<?php
if( !$ilDB->tableExists('qpl_qst_skl_sol_expr') )
{
	$ilDB->createTable('qpl_qst_skl_sol_expr', array(
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
		'order_index' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'expression' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => ''
		),
		'points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_skl_sol_expr', array(
		'question_fi', 'skill_base_fi', 'skill_tref_fi', 'order_index'
	));
}
?>
<#4492>
<?php
$res = $ilDB->query("
	SELECT DISTINCT(question_fi) FROM qpl_qst_skl_assigns
	LEFT JOIN qpl_questions ON question_fi = question_id
	WHERE question_id IS NULL
");

$deletedQuestionIds = array();

while($row = $ilDB->fetchAssoc($res))
{
	$deletedQuestionIds[] = $row['question_fi'];
}

$inDeletedQuestionIds = $ilDB->in('question_fi', $deletedQuestionIds, false, 'integer');

$ilDB->query("
	DELETE FROM qpl_qst_skl_assigns WHERE $inDeletedQuestionIds
");
?>
<#4493>
<?php
$row = $ilDB->fetchAssoc($ilDB->queryF(
	'SELECT COUNT(*) cnt FROM qpl_qst_skl_assigns LEFT JOIN skl_tree_node ON skill_base_fi = obj_id WHERE type = %s',
	array('text'), array('sktr')
));

if( $row['cnt'] )
{
	$res = $ilDB->queryF(
		'SELECT obj_fi, question_fi, skill_base_fi, skill_tref_fi FROM qpl_qst_skl_assigns LEFT JOIN skl_tree_node ON skill_base_fi = obj_id WHERE type = %s',
		array('text'), array('sktr')
	);

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->update('qpl_qst_skl_assigns',
			array(
				'skill_base_fi' => array('integer', $row['skill_tref_fi']),
				'skill_tref_fi' => array('integer', $row['skill_base_fi'])
			),
			array(
				'obj_fi' => array('integer', $row['obj_fi']),
				'question_fi' => array('integer', $row['question_fi']),
				'skill_base_fi' => array('integer', $row['skill_base_fi']),
				'skill_tref_fi' => array('integer', $row['skill_tref_fi'])
			)
		);
	}
}
?>
<#4494>
<?php
$ilDB->manipulateF(
	"UPDATE qpl_qst_skl_assigns SET eval_mode = %s WHERE eval_mode IS NULL", array('text'), array('result')
);
?>
<#4495>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4496>
<?php
if( !$ilDB->tableExists('mail_cron_orphaned') )
{
	$ilDB->createTable('mail_cron_orphaned', array(
		'mail_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'folder_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ts_do_delete' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	));

	$ilDB->addPrimaryKey('mail_cron_orphaned', array('mail_id', 'folder_id'));
}
?>
<#4497>
<?php
if($ilDB->tableExists('chat_blocked'))
{
	$ilDB->dropTable('chat_blocked');
}
?>
<#4498>
<?php
// Don't remove this comment
?>
<#4499>
<?php
if($ilDB->tableExists('chat_invitations'))
{
	$ilDB->dropTable('chat_invitations');
}
?>
<#4500>
<?php
if($ilDB->tableExists('chat_records'))
{
	$ilDB->dropTable('chat_records');
}
?>
<#4501>
<?php
if($ilDB->sequenceExists('chat_records'))
{
	$ilDB->dropSequence('chat_records');
}
?>
<#4502>
<?php
if($ilDB->sequenceExists('chat_rooms'))
{
	$ilDB->dropSequence('chat_rooms');
}
?>
<#4503>
<?php
if($ilDB->tableExists('chat_rooms'))
{
	$ilDB->dropTable('chat_rooms');
}
?>
<#4504>
<?php
if($ilDB->tableExists('chat_room_messages'))
{
	$ilDB->dropTable('chat_room_messages');
}
?>
<#4505>
<?php
if($ilDB->sequenceExists('chat_room_messages'))
{
	$ilDB->dropSequence('chat_room_messages');
}
?>
<#4506>
<?php
if($ilDB->sequenceExists('chat_smilies'))
{
	$ilDB->dropSequence('chat_smilies');
}
?>
<#4507>
<?php
if($ilDB->tableExists('chat_smilies'))
{
	$ilDB->dropTable('chat_smilies');
}
?>
<#4508>
<?php
if($ilDB->tableExists('chat_user'))
{
	$ilDB->dropTable('chat_user');
}
?>
<#4509>
<?php
if($ilDB->tableExists('chat_record_data'))
{
	$ilDB->dropTable('chat_record_data');
}
?>
<#4510>
<?php
if($ilDB->sequenceExists('chat_record_data'))
{
	$ilDB->dropSequence('chat_record_data');
}
?>
<#4511>
<?php
if($ilDB->tableExists('ilinc_data'))
{
	$ilDB->dropTable('ilinc_data');
}
?>
<#4512>
<?php
if($ilDB->tableExists('ilinc_registration'))
{
	$ilDB->dropTable('ilinc_registration');
}
?>
<#4513>
<?php
if($ilDB->tableColumnExists('usr_data', 'ilinc_id'))
{
	$ilDB->dropTableColumn('usr_data', 'ilinc_id');
}

if($ilDB->tableColumnExists('usr_data', 'ilinc_login'))
{
	$ilDB->dropTableColumn('usr_data', 'ilinc_login');
}

if($ilDB->tableColumnExists('usr_data', 'ilinc_passwd'))
{
	$ilDB->dropTableColumn('usr_data', 'ilinc_passwd');
}
?>
<#4514>
<?php
if( $ilDB->uniqueConstraintExists('tst_sequence', array('active_fi', 'pass')) )
{
	$ilDB->dropUniqueConstraintByFields('tst_sequence', array('active_fi', 'pass'));
	$ilDB->addPrimaryKey('tst_sequence', array('active_fi', 'pass'));
}
?>
<#4515>
<?php
if( $ilDB->uniqueConstraintExists('tst_pass_result', array('active_fi', 'pass')) )
{
	$ilDB->dropUniqueConstraintByFields('tst_pass_result', array('active_fi', 'pass'));
	$ilDB->addPrimaryKey('tst_pass_result', array('active_fi', 'pass'));
}
?>
<#4516>
<?php
$crpra_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT proom_id, user_id
    FROM chatroom_proomaccess
    GROUP BY proom_id, user_id
    HAVING COUNT(*) > 1
) duplicateChatProoms
";
$res  = $ilDB->query($crpra_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	$mopt_dup_query = "
	SELECT proom_id, user_id
	FROM chatroom_proomaccess
	GROUP BY proom_id, user_id
	HAVING COUNT(*) > 1
	";
	$res = $ilDB->query($mopt_dup_query);

	$stmt_del = $ilDB->prepareManip("DELETE FROM chatroom_proomaccess WHERE proom_id = ? AND user_id = ?", array('integer', 'integer'));
	$stmt_in  = $ilDB->prepareManip("INSERT INTO chatroom_proomaccess (proom_id, user_id) VALUES(?, ?)", array('integer', 'integer'));

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($stmt_del, array($row['proom_id'], $row['user_id']));
		$ilDB->execute($stmt_in, array($row['proom_id'], $row['user_id']));
	}
}

$res  = $ilDB->query($crpra_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'chatroom_proomaccess'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('chatroom_proomaccess', array('proom_id', 'user_id'));
?>
<#4517>
<?php
$mopt_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT user_id
    FROM mail_options
    GROUP BY user_id
    HAVING COUNT(*) > 1
) duplicateMailOptions
";
$res  = $ilDB->query($mopt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	$mopt_dup_query = "
	SELECT user_id
	FROM mail_options
	GROUP BY user_id
	HAVING COUNT(*) > 1
	";
	$res = $ilDB->query($mopt_dup_query);

	$stmt_sel = $ilDB->prepare("SELECT * FROM mail_options WHERE user_id = ?", array('integer'));
	$stmt_del = $ilDB->prepareManip("DELETE FROM mail_options WHERE user_id = ?", array('integer'));
	$stmt_in  = $ilDB->prepareManip("INSERT INTO mail_options (user_id, linebreak, signature, incoming_type, cronjob_notification) VALUES(?, ?, ?, ?, ?)", array('integer', 'integer', 'text', 'integer', 'integer'));

	while($row = $ilDB->fetchAssoc($res))
	{
		$opt_res = $ilDB->execute($stmt_sel, array($row['user_id']));
		$opt_row = $ilDB->fetchAssoc($opt_res);
		if($opt_row)
		{
			$ilDB->execute($stmt_del, array($opt_row['user_id']));
			$ilDB->execute($stmt_in, array($opt_row['user_id'], $opt_row['linebreak'], $opt_row['signature'], $opt_row['incoming_type'], $opt_row['cronjob_notification']));
		}
	}
}

$res  = $ilDB->query($mopt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'mail_options'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('mail_options', array('user_id'));
?>
<#4518>
<?php
$psc_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT psc_ps_fk, psc_pc_fk, psc_pcc_fk
    FROM payment_statistic_coup
    GROUP BY psc_ps_fk, psc_pc_fk, psc_pcc_fk
    HAVING COUNT(*) > 1
) duplicatePaymentStatistics
";
$res  = $ilDB->query($psc_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	$psc_dup_query = "
	SELECT psc_ps_fk, psc_pc_fk, psc_pcc_fk
	FROM payment_statistic_coup
	GROUP BY psc_ps_fk, psc_pc_fk, psc_pcc_fk
	HAVING COUNT(*) > 1
	";
	$res = $ilDB->query($psc_dup_query);

	$stmt_del = $ilDB->prepareManip("DELETE FROM payment_statistic_coup WHERE psc_ps_fk = ? AND psc_pc_fk = ? AND psc_pcc_fk = ?", array('integer', 'integer', 'integer'));
	$stmt_in  = $ilDB->prepareManip("INSERT INTO payment_statistic_coup (psc_ps_fk, psc_pc_fk, psc_pcc_fk) VALUES(?, ?, ?)", array('integer', 'integer', 'integer'));

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($stmt_del, array($row['psc_ps_fk'], $row['psc_pc_fk'], $row['psc_pcc_fk']));
		$ilDB->execute($stmt_in, array($row['psc_ps_fk'], $row['psc_pc_fk'], $row['psc_pcc_fk']));
	}
}

$res  = $ilDB->query($psc_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'payment_statistic_coup'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('payment_statistic_coup', array('psc_ps_fk', 'psc_pc_fk', 'psc_pcc_fk'));
?>
<#4519>
<?php
$msave_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT user_id
    FROM mail_saved
    GROUP BY user_id
    HAVING COUNT(*) > 1
) duplicateMailSaved
";
$res  = $ilDB->query($msave_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	$msave_dup_query = "
	SELECT user_id
	FROM mail_saved
	GROUP BY user_id
	HAVING COUNT(*) > 1
	";
	$res = $ilDB->query($msave_dup_query);

	$stmt_sel = $ilDB->prepare("SELECT * FROM mail_saved WHERE user_id = ?", array('integer'));
	$stmt_del = $ilDB->prepareManip("DELETE FROM mail_saved WHERE user_id = ?", array('integer'));

	while($row = $ilDB->fetchAssoc($res))
	{
		$opt_res = $ilDB->execute($stmt_sel, array($row['user_id']));
		$opt_row = $ilDB->fetchAssoc($opt_res);
		if($opt_row)
		{
			$ilDB->execute($stmt_del, array($opt_row['user_id']));
			$ilDB->insert(
				'mail_saved',
				array(
					'user_id'          => array('integer', $opt_row['user_id']),
					'm_type'           => array('text', $opt_row['m_type']),
					'm_email'          => array('integer', $opt_row['m_email']),
					'm_subject'        => array('text', $opt_row['m_subject']),
					'use_placeholders' => array('integer', $opt_row['use_placeholders']),
					'm_message'        => array('clob', $opt_row['m_message']),
					'rcp_to'           => array('clob', $opt_row['rcp_to']),
					'rcp_cc'           => array('clob', $opt_row['rcp_cc']),
					'rcp_bcc'          => array('clob', $opt_row['rcp_bcc']),
					'attachments'      => array('clob', $opt_row['attachments'])
				)
			);
		}
	}
}

$res  = $ilDB->query($msave_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	die("There are still duplicate entries in table 'mail_saved'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('mail_saved', array('user_id'));
?>
<#4520>
<?php
$chrban_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT room_id, user_id
    FROM chatroom_bans
    GROUP BY room_id, user_id
    HAVING COUNT(*) > 1
) duplicateChatroomBans
";
$res  = $ilDB->query($chrban_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	$chrban_dup_query = "
	SELECT DISTINCT finalDuplicateChatroomBans.room_id, finalDuplicateChatroomBans.user_id, finalDuplicateChatroomBans.timestamp, finalDuplicateChatroomBans.remark
	FROM (
		SELECT chatroom_bans.*
		FROM chatroom_bans
		INNER JOIN (
			SELECT room_id, user_id, MAX(timestamp) ts
			FROM chatroom_bans
			GROUP BY room_id, user_id
			HAVING COUNT(*) > 1
		) duplicateChatroomBans
			ON duplicateChatroomBans.room_id = chatroom_bans.room_id
			AND duplicateChatroomBans.user_id = chatroom_bans.user_id 
			AND duplicateChatroomBans.ts = chatroom_bans.timestamp 
	) finalDuplicateChatroomBans
	";
	$res = $ilDB->query($chrban_dup_query);

	$stmt_del = $ilDB->prepareManip("DELETE FROM chatroom_bans WHERE room_id = ? AND user_id = ?", array('integer', 'integer'));
	$stmt_in  = $ilDB->prepareManip("INSERT INTO chatroom_bans (room_id, user_id, timestamp, remark) VALUES(?, ?, ?, ?)", array('integer', 'integer',  'integer',  'text'));

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($stmt_del, array($row['room_id'], $row['user_id']));
		$ilDB->execute($stmt_in, array($row['room_id'], $row['user_id'], $row['timestamp'], $row['remark']));
	}
}

$res  = $ilDB->query($chrban_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'])
{
	die("There are still duplicate entries in table 'chatroom_bans'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('chatroom_bans', array('room_id', 'user_id'));
?>
<#4521>
<?php
if(!$ilDB->sequenceExists('chatroom_psessionstmp'))
{
	$ilDB->createSequence('chatroom_psessionstmp');
}
?>
<#4522>
<?php
if(!$ilDB->tableExists('chatroom_psessionstmp'))
{
	$fields = array(
		'psess_id'     => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
		'proom_id'     => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'connected'    => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
	);
	$ilDB->createTable('chatroom_psessionstmp', $fields);
	$ilDB->addPrimaryKey('chatroom_psessionstmp', array('psess_id'));
}
?>
<#4523>
<?php
$query = '
SELECT chatroom_psessions.proom_id, chatroom_psessions.user_id, chatroom_psessions.connected, chatroom_psessions.disconnected
FROM chatroom_psessions
LEFT JOIN chatroom_psessionstmp
	ON chatroom_psessionstmp.proom_id = chatroom_psessions.proom_id
	AND chatroom_psessionstmp.user_id = chatroom_psessions.user_id
	AND chatroom_psessionstmp.connected = chatroom_psessions.connected
	AND chatroom_psessionstmp.disconnected = chatroom_psessions.disconnected
WHERE chatroom_psessionstmp.psess_id IS NULL
GROUP BY chatroom_psessions.proom_id, chatroom_psessions.user_id, chatroom_psessions.connected, chatroom_psessions.disconnected
';
$res = $ilDB->query($query);

$stmt_in = $ilDB->prepareManip('INSERT INTO chatroom_psessionstmp (psess_id, proom_id, user_id, connected, disconnected) VALUES(?, ?, ?, ?, ?)', array('integer', 'integer', 'integer', 'integer','integer'));

while($row = $ilDB->fetchAssoc($res))
{
	$psess_id = $ilDB->nextId('chatroom_psessionstmp');
	$ilDB->execute($stmt_in, array($psess_id, (int)$row['proom_id'], (int)$row['user_id'], (int)$row['connected'], (int)$row['disconnected']));
}
?>
<#4524>
<?php
$ilDB->dropTable('chatroom_psessions');
?>
<#4525>
<?php
$ilDB->renameTable('chatroom_psessionstmp', 'chatroom_psessions');
?>
<#4526>
<?php
if(!$ilDB->sequenceExists('chatroom_psessions'))
{
	$query = "SELECT MAX(psess_id) mpsess_id FROM chatroom_psessions";
	$row = $ilDB->fetchAssoc($ilDB->query($query));
	$ilDB->createSequence('chatroom_psessions', (int)$row['mpsess_id'] + 1);
}
?>
<#4527>
<?php
if($ilDB->sequenceExists('chatroom_psessionstmp'))
{
	$ilDB->dropSequence('chatroom_psessionstmp');
}
?>
<#4528>
<?php
$ilDB->addIndex('chatroom_psessions', array('proom_id', 'user_id'), 'i1');
?>
<#4529>
<?php
$ilDB->addIndex('chatroom_psessions', array('disconnected'), 'i2');
?>
<#4530>
<?php
if(!$ilDB->sequenceExists('chatroom_sessionstmp'))
{
	$ilDB->createSequence('chatroom_sessionstmp');
}
?>
<#4531>
<?php
if(!$ilDB->tableExists('chatroom_sessionstmp'))
{
	$fields = array(
		'sess_id'     => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
		'room_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'user_id'      => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'userdata'     => array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null),
		'connected'    => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
	);
	$ilDB->createTable('chatroom_sessionstmp', $fields);
	$ilDB->addPrimaryKey('chatroom_sessionstmp', array('sess_id'));
}
?>
<#4532>
<?php
if($ilDB->getDBType() == 'innodb' || $ilDB->getDBType() == 'mysql')
{
	$query = '
	SELECT chatroom_sessions.room_id, chatroom_sessions.user_id, chatroom_sessions.connected, chatroom_sessions.disconnected, chatroom_sessions.userdata
	FROM chatroom_sessions
	LEFT JOIN chatroom_sessionstmp
		ON chatroom_sessionstmp.room_id = chatroom_sessions.room_id
		AND chatroom_sessionstmp.user_id = chatroom_sessions.user_id
		AND chatroom_sessionstmp.connected = chatroom_sessions.connected
		AND chatroom_sessionstmp.disconnected = chatroom_sessions.disconnected
		AND chatroom_sessionstmp.userdata = chatroom_sessions.userdata COLLATE utf8_general_ci
	WHERE chatroom_sessionstmp.sess_id IS NULL
	GROUP BY chatroom_sessions.room_id, chatroom_sessions.user_id, chatroom_sessions.connected, chatroom_sessions.disconnected, chatroom_sessions.userdata
	';
}
else
{
	$query = '
	SELECT chatroom_sessions.room_id, chatroom_sessions.user_id, chatroom_sessions.connected, chatroom_sessions.disconnected, chatroom_sessions.userdata
	FROM chatroom_sessions
	LEFT JOIN chatroom_sessionstmp
		ON chatroom_sessionstmp.room_id = chatroom_sessions.room_id
		AND chatroom_sessionstmp.user_id = chatroom_sessions.user_id
		AND chatroom_sessionstmp.connected = chatroom_sessions.connected
		AND chatroom_sessionstmp.disconnected = chatroom_sessions.disconnected
		AND chatroom_sessionstmp.userdata = chatroom_sessions.userdata
	WHERE chatroom_sessionstmp.sess_id IS NULL
	GROUP BY chatroom_sessions.room_id, chatroom_sessions.user_id, chatroom_sessions.connected, chatroom_sessions.disconnected, chatroom_sessions.userdata
	';
}

$res = $ilDB->query($query);

$stmt_in = $ilDB->prepareManip('INSERT INTO chatroom_sessionstmp (sess_id, room_id, user_id, connected, disconnected, userdata) VALUES(?, ?, ?, ?, ?, ?)', array('integer', 'integer', 'integer', 'integer','integer', 'text'));

while($row = $ilDB->fetchAssoc($res))
{
	$sess_id = $ilDB->nextId('chatroom_sessionstmp');
	$ilDB->execute($stmt_in, array($sess_id, (int)$row['room_id'], (int)$row['user_id'], (int)$row['connected'], (int)$row['disconnected'], (string)$row['userdata']));
}
?>
<#4533>
<?php
$ilDB->dropTable('chatroom_sessions');
?>
<#4534>
<?php
$ilDB->renameTable('chatroom_sessionstmp', 'chatroom_sessions');
?>
<#4535>
<?php
if(!$ilDB->sequenceExists('chatroom_sessions'))
{
	$query = "SELECT MAX(sess_id) msess_id FROM chatroom_sessions";
	$row = $ilDB->fetchAssoc($ilDB->query($query));
	$ilDB->createSequence('chatroom_sessions', (int)$row['msess_id'] + 1);
}
?>
<#4536>
<?php
if($ilDB->sequenceExists('chatroom_sessionstmp'))
{
	$ilDB->dropSequence('chatroom_sessionstmp');
}
?>
<#4537>
<?php
$ilDB->addIndex('chatroom_sessions', array('room_id', 'user_id'), 'i1');
?>
<#4538>
<?php
$ilDB->addIndex('chatroom_sessions', array('disconnected'), 'i2');
?>
<#4539>
<?php
$ilDB->addIndex('chatroom_sessions', array('user_id'), 'i3');
?>
<#4540>
<?php
// qpl_a_cloze_combi_res - primary key step 1/8

$dupsCountRes = $ilDB->query("
		SELECT COUNT(*) dups_cnt FROM (
			SELECT combination_id, question_fi, gap_fi, row_id
			FROM qpl_a_cloze_combi_res
			GROUP BY combination_id, question_fi, gap_fi, row_id
		HAVING COUNT(*) > 1
	) dups");

$dupsCountRow = $ilDB->fetchAssoc($dupsCountRes);

if($dupsCountRow['dups_cnt'] > 0)
{
	if( !$ilDB->tableExists('dups_clozecombis_qst') )
	{
		$ilDB->createTable('dups_clozecombis_qst', array(
			'qst' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'num' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => false
			)
		));

		$ilDB->addPrimaryKey('dups_clozecombis_qst', array('qst'));
	}

	if( !$ilDB->tableExists('dups_clozecombis_rows') )
	{
		$ilDB->createTable('dups_clozecombis_rows', array(
			'combination_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'question_fi' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'gap_fi' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'answer' => array(
				'type' => 'text',
				'length' => 1000,
				'notnull' => false
			),
			'points' => array(
				'type' => 'float',
				'notnull' => false
			),
			'best_solution' => array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => false
			),
			'row_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => false,
				'default' => 0
			)
		));

		$ilDB->addPrimaryKey('dups_clozecombis_rows', array(
			'combination_id', 'question_fi', 'gap_fi', 'row_id'
		));
	}
}
?>
<#4541>
<?php
// qpl_a_cloze_combi_res - primary key step 2/8

// break safe update step

if( $ilDB->tableExists('dups_clozecombis_qst') )
{
	$res = $ilDB->query("
			SELECT combination_id, question_fi, gap_fi, row_id, COUNT(*)
			FROM qpl_a_cloze_combi_res
			LEFT JOIN dups_clozecombis_qst ON qst = question_fi
			WHERE qst IS NULL
			GROUP BY combination_id, question_fi, gap_fi, row_id
			HAVING COUNT(*) > 1
		");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('dups_clozecombis_qst',
			array(
				'qst' => array('integer', $row['question_fi'])
			),
			array(
				'num' => array('integer', null)
			)
		);
	}
}
?>
<#4542>
<?php
// qpl_a_cloze_combi_res - primary key step 3/8

// break safe update step

if( $ilDB->tableExists('dups_clozecombis_qst') )
{
	$selectNumQuery = "
			SELECT COUNT(*) num FROM (
				SELECT question_fi FROM qpl_a_cloze_combi_res WHERE question_fi = ?
				GROUP BY combination_id, question_fi, gap_fi, row_id
			) numrows
		";
	$selectNumStmt = $ilDB->prepare($selectNumQuery, array('integer'));

	$updateNumQuery = "
			UPDATE dups_clozecombis_qst SET num = ? WHERE qst = ?
		";
	$updateNumStmt = $ilDB->prepareManip($updateNumQuery, array('integer', 'integer'));

	$qstRes = $ilDB->query("SELECT qst FROM dups_clozecombis_qst WHERE num IS NULL");

	while( $qstRow = $ilDB->fetchAssoc($qstRes) )
	{
		$selectNumRes = $ilDB->execute($selectNumStmt, array($qstRow['qst']));
		$selectNumRow = $ilDB->fetchAssoc($selectNumRes);

		$ilDB->execute($updateNumStmt, array($selectNumRow['num'], $qstRow['qst']));
	}
}
?>
<#4543>
<?php
// qpl_a_cloze_combi_res - primary key step 4/8

// break safe update step

if( $ilDB->tableExists('dups_clozecombis_qst') )
{
	$deleteRowsStmt = $ilDB->prepareManip(
		"DELETE FROM dups_clozecombis_rows WHERE question_fi = ?", array('integer')
	);

	$selectRowsStmt = $ilDB->prepare(
		"SELECT * FROM qpl_a_cloze_combi_res WHERE question_fi = ? ORDER BY combination_id, row_id, gap_fi",
		array('integer')
	);

	$insertRowStmt = $ilDB->prepareManip(
		"INSERT INTO dups_clozecombis_rows (combination_id, question_fi, gap_fi, answer, points, best_solution, row_id)
			VALUES (?, ?, ?, ?, ?, ?, ?)", array('integer', 'integer', 'integer', 'text', 'float', 'integer', 'integer')
	);

	$qstRes = $ilDB->query("
			SELECT qst, num
			FROM dups_clozecombis_qst
			LEFT JOIN dups_clozecombis_rows
			ON question_fi = qst
			GROUP BY qst, num, question_fi
			HAVING COUNT(question_fi) < num
		");

	while( $qstRow = $ilDB->fetchAssoc($qstRes) )
	{
		$ilDB->execute($deleteRowsStmt, array($qstRow['qst']));

		$selectRowsRes = $ilDB->execute($selectRowsStmt, array($qstRow['qst']));

		$existingRows = array();
		while( $selectRowsRow = $ilDB->fetchAssoc($selectRowsRes) )
		{
			$combinationId = $selectRowsRow['combination_id'];
			$rowId = $selectRowsRow['row_id'];
			$gapFi = $selectRowsRow['gap_fi'];

			if( !isset($existingRows[$combinationId]) )
			{
				$existingRows[$combinationId] = array();
			}

			if( !isset($existingRows[$combinationId][$rowId]) )
			{
				$existingRows[$combinationId][$rowId] = array();
			}

			if( !isset($existingRows[$combinationId][$rowId][$gapFi]) )
			{
				$existingRows[$combinationId][$rowId][$gapFi] = array();
			}

			$existingRows[$combinationId][$rowId][$gapFi][] = array(
				'answer' => $selectRowsRow['answer'],
				'points' => $selectRowsRow['points']
			);
		}

		$newRows = array();
		foreach($existingRows as $combinationId => $combination)
		{
			if( !isset($newRows[$combinationId]) )
			{
				$newRows[$combinationId] = array();
			}

			$maxPointsForCombination = null;
			$maxPointsRowIdForCombination = null;
			foreach($combination as $rowId => $row)
			{
				if( !isset($newRows[$combinationId][$rowId]) )
				{
					$newRows[$combinationId][$rowId] = array();
				}

				$maxPointsForRow = null;
				foreach($row as $gapFi => $gap)
				{
					foreach($gap as $dups)
					{
						if( !isset($newRows[$combinationId][$rowId][$gapFi]) )
						{
							$newRows[$combinationId][$rowId][$gapFi] = array(
								'answer' => $dups['answer']
							);

							if($maxPointsForRow === null || $maxPointsForRow < $dups['points'] )
							{
								$maxPointsForRow = $dups['points'];
							}
						}
					}
				}

				foreach($newRows[$combinationId][$rowId] as $gapFi => $gap)
				{
					$newRows[$combinationId][$rowId][$gapFi]['points'] = $maxPointsForRow;
				}

				if( $maxPointsForCombination === null || $maxPointsForCombination < $maxPointsForRow )
				{
					$maxPointsForCombination = $maxPointsForRow;
					$maxPointsRowIdForCombination = $rowId;
				}
			}

			foreach($combination as $rowId => $row)
			{
				foreach($newRows[$combinationId][$rowId] as $gapFi => $gap)
				{
					$newRows[$combinationId][$rowId][$gapFi]['best_solution'] = ($rowId == $maxPointsRowIdForCombination ? 1 : 0);
				}
			}
		}

		foreach($newRows as $combinationId => $combination)
		{
			foreach($combination as $rowId => $row)
			{
				foreach($row as $gapFi => $gap)
				{
					$ilDB->execute($insertRowStmt, array(
						$combinationId, $qstRow['qst'], $gapFi, $gap['answer'],
						$gap['points'], $gap['best_solution'], $rowId
					));
				}
			}
		}
	}
}
?>
<#4544>
<?php
// qpl_a_cloze_combi_res - primary key step 5/8

if( $ilDB->tableExists('dups_clozecombis_rows') )
{
	$ilDB->manipulate("
		DELETE FROM qpl_a_cloze_combi_res WHERE question_fi IN(
			SELECT DISTINCT question_fi FROM dups_clozecombis_rows
		)
	");
}
?>
<#4545>
<?php
// qpl_a_cloze_combi_res - primary key step 6/8

if( $ilDB->tableExists('dups_clozecombis_rows') )
{
	$ilDB->manipulate("
		INSERT INTO qpl_a_cloze_combi_res (
			combination_id, question_fi, gap_fi, answer, points, best_solution, row_id
		) SELECT combination_id, question_fi, gap_fi, answer, points, best_solution, row_id
		FROM dups_clozecombis_rows
	");
}
?>
<#4546>
<?php
// qpl_a_cloze_combi_res - primary key step 7/8

if( $ilDB->tableExists('dups_clozecombis_qst') )
{
	$ilDB->dropTable('dups_clozecombis_qst');
}

if( $ilDB->tableExists('dups_clozecombis_rows') )
{
	$ilDB->dropTable('dups_clozecombis_rows');
}
?>
<#4547>
<?php
// qpl_a_cloze_combi_res - primary key step 8/8

$ilDB->addPrimaryKey('qpl_a_cloze_combi_res', array(
	'combination_id', 'question_fi', 'gap_fi', 'row_id'
));
?>
<#4548>
<?php
if(!$ilDB->sequenceExists('chatroom_historytmp'))
{
	$ilDB->createSequence('chatroom_historytmp');
}
?>
<#4549>
<?php
if(!$ilDB->tableExists('chatroom_historytmp'))
{
	$fields = array(
		'hist_id'   => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
		'room_id'   => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'message'   => array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null),
		'timestamp' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
		'sub_room'  => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
	);
	$ilDB->createTable('chatroom_historytmp', $fields);
	$ilDB->addPrimaryKey('chatroom_historytmp', array('hist_id'));
}
?>
<#4550>
<?php
require_once 'Services/Migration/DBUpdate_4550/classes/class.ilDBUpdate4550.php';
ilDBUpdate4550::cleanupOrphanedChatRoomData();
if($ilDB->getDBType() == 'innodb' || $ilDB->getDBType() == 'mysql')
{
	$query = '
	SELECT chatroom_history.room_id, chatroom_history.timestamp, chatroom_history.sub_room, chatroom_history.message
	FROM chatroom_history
	LEFT JOIN chatroom_historytmp
		ON chatroom_historytmp.room_id = chatroom_history.room_id
		AND chatroom_historytmp.timestamp = chatroom_history.timestamp
		AND chatroom_historytmp.sub_room = chatroom_history.sub_room
		AND chatroom_historytmp.message = chatroom_history.message COLLATE utf8_general_ci
	WHERE chatroom_historytmp.hist_id IS NULL
	GROUP BY chatroom_history.room_id, chatroom_history.timestamp, chatroom_history.sub_room, chatroom_history.message
	';
}
else
{
	$query = '
	SELECT chatroom_history.room_id, chatroom_history.timestamp, chatroom_history.sub_room, chatroom_history.message
	FROM chatroom_history
	LEFT JOIN chatroom_historytmp
		ON chatroom_historytmp.room_id = chatroom_history.room_id
		AND chatroom_historytmp.timestamp = chatroom_history.timestamp
		AND chatroom_historytmp.sub_room = chatroom_history.sub_room
		AND chatroom_historytmp.message = chatroom_history.message
	WHERE chatroom_historytmp.hist_id IS NULL
	GROUP BY chatroom_history.room_id, chatroom_history.timestamp, chatroom_history.sub_room, chatroom_history.message
	';
}
$res = $ilDB->query($query);

$stmt_in = $ilDB->prepareManip('INSERT INTO chatroom_historytmp (hist_id, room_id, timestamp, sub_room, message) VALUES(?, ?, ?, ?, ?)', array('integer', 'integer', 'integer', 'integer', 'text'));

while($row = $ilDB->fetchAssoc($res))
{
	$hist_id = $ilDB->nextId('chatroom_historytmp');
	$ilDB->execute($stmt_in, array($hist_id, (int)$row['room_id'], (int)$row['timestamp'], (int)$row['sub_room'], (string)$row['message']));
}
?>
<#4551>
<?php
$ilDB->dropTable('chatroom_history');
?>
<#4552>
<?php
$ilDB->renameTable('chatroom_historytmp', 'chatroom_history');
?>
<#4553>
<?php
if(!$ilDB->sequenceExists('chatroom_history'))
{
	$query = "SELECT MAX(hist_id) mhist_id FROM chatroom_history";
	$row = $ilDB->fetchAssoc($ilDB->query($query));
	$ilDB->createSequence('chatroom_history', (int)$row['mhist_id'] + 1);
}
?>
<#4554>
<?php
if($ilDB->sequenceExists('chatroom_historytmp'))
{
	$ilDB->dropSequence('chatroom_historytmp');
}
?>
<#4555>
<?php
$ilDB->addIndex('chatroom_history', array('room_id', 'sub_room'), 'i1');
?>
<#4556>
<?php
require_once 'Services/Migration/DBUpdate_4550/classes/class.ilDBUpdate4550.php';
ilDBUpdate4550::cleanupOrphanedChatRoomData();
?>
<#4557>
<?php
if($ilDB->getDBType() == 'postgres')
{
	$ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER COLUMN parent_id SET DEFAULT 0");
	$ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER parent_id TYPE INTEGER USING (parent_id::INTEGER)");
}
else
{
	$ilDB->modifyTableColumn('chatroom_prooms', 'parent_id', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	));
}
$ilDB->addIndex('chatroom_prooms', array('parent_id'), 'i1');
?>
<#4558>
<?php
$ilDB->addIndex('chatroom_prooms', array('owner'), 'i2');
?>
<#4559>
<?php
/*
Moved to 4557
if($ilDB->getDBType() == 'postgres')
{
	$ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER COLUMN parent_id SET DEFAULT 0");
	$ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER parent_id TYPE INTEGER USING (parent_id::INTEGER)");
}
else
{
	$ilDB->modifyTableColumn('chatroom_prooms', 'parent_id', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	));
}
*/
?>
<#4560>
<?php
if($ilDB->sequenceExists('chatroom_smilies'))
{
	$ilDB->dropSequence('chatroom_smilies');
}
?>
<#4561>
<?php
$query = "SELECT MAX(smiley_id) msmiley_id FROM chatroom_smilies";
$row = $ilDB->fetchAssoc($ilDB->query($query));
$ilDB->createSequence('chatroom_smilies', (int)$row['msmiley_id'] + 1);
?>
<#4562>
<?php
if(!$ilDB->tableColumnExists('frm_settings', 'file_upload_allowed'))
{
	$ilDB->addTableColumn('frm_settings', 'file_upload_allowed',
		array(
			"type"    => "integer",
			"notnull" => true,
			"length"  => 1,
			"default" => 0
		)
	);
}
?>
<#4563>
<?php

if($ilDB->tableExists('sysc_groups'))
{
	$ilDB->dropTable('sysc_groups');
}

if(!$ilDB->tableExists('sysc_groups'))
{
	$fields = array (
    'id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true),
	'component' => array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 16,
		 	"fixed" => true),

	'last_update' => array(
			"type" => "timestamp",
			"notnull" => false),
		
	'status' => array(
			"type" => "integer",
			"notnull" => true,
			'length' => 1,
			'default' => 0)
	  );
  $ilDB->createTable('sysc_groups', $fields);
  $ilDB->addPrimaryKey('sysc_groups', array('id'));
  $ilDB->createSequence("sysc_groups");
}
?>
<#4564>
<?php

if(!$ilDB->tableExists('sysc_tasks'))
{
	$fields = array (
    'id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true),
	'grp_id' => array(
			"type" => "integer",
			"notnull" => TRUE,
		 	"length" => 4),

	'last_update' => array(
			"type" => "timestamp",
			"notnull" => false),
		
	'status' => array(
			"type" => "integer",
			"notnull" => true,
			'length' => 1,
			'default' => 0),
	'identifier' => array(
			"type" => "text",
			"notnull" => FALSE,
			'length' => 64)
	  );
	$ilDB->createTable('sysc_tasks', $fields);
	$ilDB->addPrimaryKey('sysc_tasks', array('id'));
	$ilDB->createSequence("sysc_tasks");
}

?>
<#4565>
<?php
// primary key for tst_addtime - step 1/8

$cntRes = $ilDB->query("
	SELECT COUNT(active_fi) cnt FROM (
		SELECT active_fi FROM tst_addtime
		GROUP BY active_fi HAVING COUNT(active_fi) > 1
	) actives
");

$cntRow = $ilDB->fetchAssoc($cntRes);

if( $cntRow['cnt'] > 0 )
{
	$ilDB->createTable('tst_addtime_tmp', array(
		'active_fi' => array(
			'type'  => 'integer',
			'length'=> 8,
			'notnull' => true,
			'default' => 0
		),
		'additionaltime' => array(
			'type'  => 'integer',
			'length'=> 8,
			'notnull' => false,
			'default' => null,
		),
		'tstamp' => array (
			'type' => 'integer',
			'length' => 8,
			'notnull' => false,
			'default' => null
		)
	));

	$ilDB->addPrimaryKey('tst_addtime_tmp', array('active_fi'));
}
?>
<#4566>
<?php
// primary key for tst_addtime - step 2/8

// break safe

if( $ilDB->tableExists('tst_addtime_tmp') )
{
	$res = $ilDB->query("
		SELECT orig.active_fi FROM tst_addtime orig
		LEFT JOIN tst_addtime_tmp tmp ON tmp.active_fi = orig.active_fi
		WHERE tmp.active_fi IS NULL
		GROUP BY orig.active_fi HAVING COUNT(orig.active_fi) > 1
	");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('tst_addtime_tmp',
			array(
				'additionaltime' => array('integer', null),
				'tstamp' => array('integer', null)
			),
			array(
				'active_fi' => array('integer', $row['active_fi'])
			)
		);
	}
}
?>
<#4567>
<?php
// primary key for tst_addtime - step 3/8

// break safe

if( $ilDB->tableExists('tst_addtime_tmp') )
{
	$res = $ilDB->query("
		SELECT orig.*
		FROM tst_addtime_tmp tmp
		INNER JOIN tst_addtime orig ON orig.active_fi = tmp.active_fi
		WHERE tmp.additionaltime IS NULL
		AND tmp.tstamp IS NULL
		ORDER BY tmp.active_fi ASC, orig.tstamp ASC
	");

	$active_fi = null;
	$addtime = null;
	$tstamp = null;

	while( $row = $ilDB->fetchAssoc($res) )
	{
		if( $active_fi === null )
		{
			// first loop
			$active_fi = $row['active_fi'];
		}
		elseif( $row['active_fi'] != $active_fi )
		{
			// update last active
			$ilDB->update('tst_addtime_tmp',
				array(
					'additionaltime' => array('integer', $addtime),
					'tstamp' => array('integer', $tstamp)
				),
				array(
					'active_fi' => array('integer', $active_fi)
				)
			);

			// process next active
			$active_fi = $row['active_fi'];
			$addtime = null;
			$tstamp = null;
		}

		if( $addtime === null || $row['additionaltime'] >= $addtime )
		{
			$addtime = $row['additionaltime'];
			$tstamp = $row['tstamp'];
		}
	}

	$ilDB->update('tst_addtime_tmp',
		array(
			'additionaltime' => array('integer', $addtime),
			'tstamp' => array('integer', $tstamp)
		),
		array(
			'active_fi' => array('integer', $active_fi)
		)
	);
}
?>
<#4568>
<?php
// primary key for tst_addtime - step 4/8

if( $ilDB->tableExists('tst_addtime_tmp') )
{
	$ilDB->manipulate("
		DELETE FROM tst_addtime WHERE active_fi IN(
			SELECT DISTINCT active_fi FROM tst_addtime_tmp
		)
	");
}
?>
<#4569>
<?php
// primary key for tst_addtime - step 5/8

if( $ilDB->tableExists('tst_addtime_tmp') )
{
	$ilDB->manipulate("
		INSERT INTO tst_addtime (active_fi, additionaltime, tstamp)
		SELECT active_fi, additionaltime, tstamp
		FROM tst_addtime_tmp
	");
}
?>
<#4570>
<?php
// primary key for tst_addtime - step 6/8

if( $ilDB->tableExists('tst_addtime_tmp') )
{
	$ilDB->dropTable('tst_addtime_tmp');
}
?>
<#4571>
<?php
// primary key for tst_addtime - step 7/8

if( $ilDB->indexExistsByFields('tst_addtime', array('active_fi')) )
{
	$ilDB->dropIndexByFields('tst_addtime', array('active_fi'));
}
?>
<#4572>
<?php
// primary key for tst_addtime - step 8/8

$ilDB->addPrimaryKey('tst_addtime', array('active_fi'));
?>
<#4573>
<?php 

// delete all entries
// structure reload is done at end of db update.
$query = 'DELETE from ctrl_calls';
$ilDB->manipulate($query);

if($ilDB->indexExistsByFields('ctrl_calls', array('parent')))
{
	$ilDB->dropIndexByFields('ctrl_calls', array('parent'));
}
$ilDB->addPrimaryKey('ctrl_calls', array('parent','child'));
?>
<#4574>
<?php
global $ilDB;
if(!$ilDB->tableColumnExists('il_dcl_table', 'delete_by_owner')) {
	$ilDB->addTableColumn('il_dcl_table', 'delete_by_owner',
		array(
		"type"    => "integer",
		"notnull" => true,
		"length"  => 1,
		"default" => 0
		)
	);
	// Migrate tables: Set new setting to true if "edit by owner" is true
	// Set edit permission to true if edit
	$ilDB->manipulate("UPDATE il_dcl_table SET delete_by_owner = 1, edit_perm = 1, delete_perm = 1 WHERE edit_by_owner = 1");
}
?>
<#4575>
<?php
// primary key for tst_result_cache - step 1/7

$res = $ilDB->query("
	SELECT COUNT(active_fi) cnt FROM (
		SELECT active_fi FROM tst_result_cache
		GROUP BY active_fi HAVING COUNT(active_fi) > 1
	) actives
");

$row = $ilDB->fetchAssoc($res);

if( $row['cnt'] > 0 )
{
	$ilDB->createTable('tst_result_cache_tmp', array(
		'active_fi' => array(
			'type'  => 'integer',
			'length'=> 8,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('tst_result_cache_tmp', array('active_fi'));
}
?>
<#4576>
<?php
// primary key for tst_result_cache - step 2/7

// break safe

if( $ilDB->tableExists('tst_result_cache_tmp') )
{
	$res = $ilDB->query("
		SELECT active_fi FROM tst_result_cache
		GROUP BY active_fi HAVING COUNT(active_fi) > 1
	");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		$ilDB->replace('tst_result_cache_tmp', array(), array(
			'active_fi' => array('integer', $row['active_fi'])
		));
	}
}
?>
<#4577>
<?php
// primary key for tst_result_cache - step 3/7

if( $ilDB->tableExists('tst_result_cache_tmp') )
{
	$ilDB->manipulate("
		DELETE FROM tst_result_cache WHERE active_fi IN(
			SELECT DISTINCT active_fi FROM tst_result_cache_tmp
		)
	");
}
?>
<#4578>
<?php
// primary key for tst_result_cache - step 4/7

if( $ilDB->indexExistsByFields('tst_result_cache', array('active_fi')) )
{
	$ilDB->dropIndexByFields('tst_result_cache', array('active_fi'));
}
?>
<#4579>
<?php
// primary key for tst_result_cache - step 5/7

$ilDB->addPrimaryKey('tst_result_cache', array('active_fi'));
?>
<#4580>
<?php
// primary key for tst_result_cache - step 6/7

// break safe

if( $ilDB->tableExists('tst_result_cache_tmp') )
{
	include_once 'Services/Migration/DBUpdate_4209/classes/class.DBUpdateTestResultCalculator.php';

	$res = $ilDB->query("
		SELECT tmp.active_fi, pass_scoring FROM tst_result_cache_tmp tmp
		INNER JOIN tst_active ON active_id = tmp.active_fi
		INNER JOIN tst_tests ON test_id = test_fi
		LEFT JOIN tst_result_cache orig ON orig.active_fi = tmp.active_fi
		WHERE orig.active_fi IS NULL
	");

	while( $row = $ilDB->fetchAssoc($res) )
	{
		DBUpdateTestResultCalculator::_updateTestResultCache(
			$row['active_fi'], $row['pass_scoring']
		);
	}
}
?>
<#4581>
<?php
// primary key for tst_result_cache - step 7/7

if( $ilDB->tableExists('tst_result_cache_tmp') )
{
	$ilDB->dropTable('tst_result_cache_tmp');
}
?>
<#4582>
<?php
$ilDB->addIndex('mail_obj_data', array('obj_id', 'user_id'), 'i2');
?>
<#4583>
<?php
$ilDB->dropPrimaryKey('mail_obj_data');
?>
<#4584>
<?php
$mod_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT obj_id
    FROM mail_obj_data
    GROUP BY obj_id
    HAVING COUNT(*) > 1
) duplicateMailFolders
";

$res  = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);

$ilSetting = new ilSetting();
$setting   = $ilSetting->get('mail_mod_dupl_warn_51x_shown', 0);
if($data['cnt'] > 0 && !(int)$setting)
{
	echo "<pre>

		Dear Administrator,
		
		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS
		
		The update process has been stopped due to a data consistency issue in table 'mail_obj_data'.
		The values in field 'obj_id' should be unique, but there are different values in field 'user_id', associated to the same 'obj_id'.
		You have the opportunity to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT mail_obj_data.* FROM mail_obj_data INNER JOIN (SELECT obj_id FROM mail_obj_data GROUP BY obj_id HAVING COUNT(*) > 1) duplicateMailFolders ON duplicateMailFolders.obj_id = mail_obj_data.obj_id ORDER BY mail_obj_data.obj_id
		
		If you try to rerun the update process, this warning will be skipped.
		The remaining duplicates will be removed automatically by the criteria documented below.

		Foreach each duplicate record, ...
		
		1. ILIAS temporarily stores the value of the duplicate 'obj_id' in a variable: \$old_folder_id .
		2. ILIAS deletes every duplicate row in table 'mail_obj_data' determined by \$old_folder_id (field: 'obj_id') and the respective 'user_id'.
		3. ILIAS creates a new record for the user account (with a unique 'obj_id') and stores this value in a variable: \$new_folder_id .
		4. All messages of the user stored in table 'mail' and related to the \$old_folder_id will be updated to \$new_folder_id (field: 'folder_id').
		5. The existing tree entries of the old \$old_folder_id in table 'mail_tree' will be replaced by the \$new_folder_id (fields: 'child' and 'parent').

		Please ensure to backup your current database before reloading this page or executing the database update in general.
		Furthermore disable your client while executing the following 2 update steps.

		Best regards,
		The mail system maintainer
		
	</pre>";

	$ilSetting->set('mail_mod_dupl_warn_51x_shown', 1);
	exit();
}


if($data['cnt'] > 0)
{
	$db_step = $nr;

	$ps_delete_mf_by_obj_and_usr = $ilDB->prepareManip(
		"DELETE FROM mail_obj_data WHERE obj_id = ? AND user_id = ?",
		array('integer', 'integer')
	);

	$ps_create_mf_by_obj_and_usr = $ilDB->prepareManip(
		"INSERT INTO mail_obj_data (obj_id, user_id, title, m_type) VALUES(?, ?, ?, ?)",
		array('integer','integer', 'text', 'text')
	);

	$ps_update_mail_by_usr_and_folder = $ilDB->prepareManip(
		"UPDATE mail SET folder_id = ? WHERE folder_id = ? AND user_id = ?",
		array('integer', 'integer', 'integer')
	);

	$ps_update_tree_entry_by_child_and_usr = $ilDB->prepareManip(
		"UPDATE mail_tree SET child = ? WHERE child = ? AND tree = ?",
		array('integer', 'integer', 'integer')
	);

	$ps_update_tree_par_entry_by_child_and_usr = $ilDB->prepareManip(
		"UPDATE mail_tree SET parent = ? WHERE parent = ? AND tree = ?",
		array('integer', 'integer', 'integer')
	);

	$mod_dup_query = "
	SELECT mail_obj_data.*
	FROM mail_obj_data
	INNER JOIN (
		SELECT obj_id
		FROM mail_obj_data
		GROUP BY obj_id
		HAVING COUNT(*) > 1
	) duplicateMailFolders ON duplicateMailFolders.obj_id = mail_obj_data.obj_id
	ORDER BY mail_obj_data.obj_id
	";
	$res = $ilDB->query($mod_dup_query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$old_folder_id = $row['obj_id'];
		$user_id       = $row['user_id'];
		$title         = $row['title'];
		$type          = $row['m_type'];

		// Delete old folder entry
		$ilDB->execute($ps_delete_mf_by_obj_and_usr, array($old_folder_id, $user_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted folder %s of user %s .",
			$db_step, $old_folder_id, $user_id
		));

		$new_folder_id = $ilDB->nextId('mail_obj_data');
		// create new folder entry
		$ilDB->execute($ps_create_mf_by_obj_and_usr, array($new_folder_id, $user_id, $title, $type));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Created new folder %s for user %s .",
			$db_step, $new_folder_id, $user_id
		));

		// Move mails to new folder
		$ilDB->execute($ps_update_mail_by_usr_and_folder, array($new_folder_id, $old_folder_id, $user_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Moved mails from %s to %s for user %s .",
			$db_step, $old_folder_id, $new_folder_id,  $user_id
		));

		// Change existing tree entry
		$ilDB->execute($ps_update_tree_entry_by_child_and_usr, array($new_folder_id, $old_folder_id, $user_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Changed child in table 'mail_tree' from %s to %s for tree %s .",
			$db_step, $old_folder_id, $new_folder_id, $user_id
		));
		// Change existing tree parent entry
		$ilDB->execute($ps_update_tree_par_entry_by_child_and_usr, array($new_folder_id, $old_folder_id, $user_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Changed parent in table 'mail_tree' from %s to %s for tree %s .",
			$db_step, $old_folder_id, $new_folder_id, $user_id
		));
	}
}

$res  = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'mail_obj_data'. Please execute this database update step again.");
}
$ilSetting->delete('mail_mod_dupl_warn_51x_shown');
?>
<#4585>
<?php
$mod_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT obj_id
    FROM mail_obj_data
    GROUP BY obj_id
    HAVING COUNT(*) > 1
) duplicateMailFolders
";
$res  = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'mail_obj_data'. Please execute database update step 4584 again. Execute the following SQL string manually: UPDATE settings SET value = 4583 WHERE keyword = 'db_version'; ");
}
$ilDB->addPrimaryKey('mail_obj_data', array('obj_id'));
?>
<#4586>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '8',
		),
	'status' => array(
		'type' => 'integer',
		'length' => '1',
		),
	'host' => array(
		'type' => 'text',
		'length' => '256',
		),
	'port' => array(
		'type' => 'integer',
		'length' => '8',
		),
	'weight' => array(
		'type' => 'integer',
		'length' => '2',
		),
	'flush_needed' => array(
		'type' => 'integer',
		'length' => '1',
		),
	);
if (! $ilDB->tableExists('il_gc_memcache_server')) {
	$ilDB->createTable('il_gc_memcache_server', $fields);
	$ilDB->addPrimaryKey('il_gc_memcache_server', array( 'id' ));
	$ilDB->createSequence('il_gc_memcache_server');
}
?>
<#4587>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("Sup", "sup", "sup",
	array());
ilDBUpdate3136::addStyleClass("Sub", "sub", "sub",
	array());
?>
<#4588>
<?php
$ilDB->addTableColumn("il_wiki_data", "link_md_values",array (
	"type" => "integer",
	"length" => 1,
	"notnull" => false,
	"default" => 0,
));
?>
<#4589>
<?php
$mt_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
    SELECT child
    FROM mail_tree
    GROUP BY child
    HAVING COUNT(*) > 1
) duplicateMailFolderNodes
";
$res  = $ilDB->query($mt_dup_query_num);
$data = $ilDB->fetchAssoc($res);

$ilSetting = new ilSetting();
$setting   = $ilSetting->get('mail_mt_dupl_warn_51x_shown', 0);
if($data['cnt'] > 0 && !(int)$setting)
{
	echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'mail_tree'.
		The values in field 'child' should be unique, but there are different values in field 'tree', associated to the same 'child'.
		You have the opportunity to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT * FROM mail_tree INNER JOIN (SELECT child FROM mail_tree GROUP BY child HAVING COUNT(*) > 1) duplicateMailFolderNodes ON duplicateMailFolderNodes.child = mail_tree.child

		If you try to rerun the update process, this warning will be skipped.
		The remaining duplicates will be removed automatically by the criteria documented below.
		
		1. ILIAS determines the relevant unique users for the duplicate entries (field: tree)
		2. ILIAS ensures that the default folders (root, inbox, trash, drafts, sent, local) exist in table 'mail_obj_data'
		3. For every affected user ILIAS deletes all records in table 'mail_tree'
		4. For every affected user ILIAS creates a new root node in table 'mail_tree'
		5. For every affected user ILIAS creates new nodes (inbox, trash, drafts, sent, local) below the root node
		6. For every affected user ILIAS creates new nodes for the custom folters below the 'local' folder

		Please ensure to backup your current database before reloading this page or executing the database update in general.
		Furthermore disable your client while executing the following 3 update steps.

		Best regards,
		The mail system maintainer
		
	</pre>";

	$ilSetting->set('mail_mt_dupl_warn_51x_shown', 1);
	exit();
}

if($data['cnt'] > 0)
{
	if(!$ilDB->tableExists('mail_tree_migr'))
	{
		$ilDB->createTable('mail_tree_migr', array(
			'usr_id' => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			)
		));
		$ilDB->addPrimaryKey('mail_tree_migr', array('usr_id'));
	}
}
?>
<#4590>
<?php
if($ilDB->tableExists('mail_tree_migr'))
{
	$db_step = $nr;

	$ps_create_mtmig_rec = $ilDB->prepareManip(
		"INSERT INTO mail_tree_migr (usr_id) VALUES(?)",
		array('integer')
	);

	$mt_dup_query = "
	SELECT DISTINCT mail_tree.tree
	FROM mail_tree
	INNER JOIN (
		SELECT child
		FROM mail_tree
		GROUP BY child
		HAVING COUNT(*) > 1
	) duplicateMailFolderNodes ON duplicateMailFolderNodes.child = mail_tree.child
	LEFT JOIN mail_tree_migr ON mail_tree_migr.usr_id = mail_tree.tree
	WHERE mail_tree_migr.usr_id IS NULL
	";
	$res = $ilDB->query($mt_dup_query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($ps_create_mtmig_rec, array($row['tree']));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Detected duplicate entries (field: child) in table 'mail_tree' for user (field: tree) %s .",
			$db_step, $row['tree']
		));
	}
}
?>
<#4591>
<?php
if($ilDB->tableExists('mail_tree_migr'))
{
	$db_step = $nr;

	$ps_del_tree_entries = $ilDB->prepareManip(
		"DELETE FROM mail_tree WHERE tree = ?",
		array('integer')
	);

	$ps_sel_fold_entries = $ilDB->prepare(
		"SELECT obj_id, title, m_type FROM mail_obj_data WHERE user_id = ?",
		array('integer')
	);

	$default_folders_title_to_type_map = array(
		'a_root'   => 'root',
		'b_inbox'  => 'inbox',
		'c_trash'  => 'trash',
		'd_drafts' => 'drafts',
		'e_sent'   => 'sent',
		'z_local'  => 'local'
	);
	$default_folder_type_to_title_map = array_flip($default_folders_title_to_type_map);

	$ps_in_fold_entry = $ilDB->prepareManip(
		"INSERT INTO mail_obj_data (obj_id, user_id, title, m_type) VALUES(?, ?, ?, ?)",
		array('integer','integer', 'text', 'text')
	);
	
	$ps_in_tree_entry = $ilDB->prepareManip(
		"INSERT INTO mail_tree (tree, child, parent, lft, rgt, depth) VALUES(?, ?, ?, ?, ?, ?)",
		array('integer', 'integer', 'integer', 'integer', 'integer', 'integer')
	);
	
	$ps_sel_tree_entry = $ilDB->prepare(
		"SELECT rgt, lft, parent FROM mail_tree WHERE child = ? AND tree = ?",
		array('integer', 'integer')
	);

	$ps_up_tree_entry = $ilDB->prepareManip(
		"UPDATE mail_tree SET lft = CASE WHEN lft > ? THEN lft + 2 ELSE lft END, rgt = CASE WHEN rgt >= ? THEN rgt + 2 ELSE rgt END WHERE tree = ?",
		array('integer', 'integer', 'integer')
	);

	$ps_del_mtmig_rec = $ilDB->prepareManip(
		"DELETE FROM mail_tree_migr WHERE usr_id = ?",
		array('integer')
	);

	$res = $ilDB->query("SELECT usr_id FROM mail_tree_migr");
	$num = $ilDB->numRows($res);

	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Found %s duplicates in table 'mail_tree'.",
		$db_step, $num
	));

	$i = 0;
	while($row = $ilDB->fetchAssoc($res))
	{
		++$i;

		$usr_id = $row['usr_id'];

		$ilDB->execute($ps_del_tree_entries, array($usr_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Started 'mail_tree' migration for user %s. Deleted all records referring this user (field: tree)",
			$db_step, $usr_id
		));

		$fold_res = $ilDB->execute($ps_sel_fold_entries, array($usr_id));
		$user_folders         = array();
		$user_default_folders = array();
		while($fold_row = $ilDB->fetchAssoc($fold_res))
		{
			$user_folders[$fold_row['obj_id']] = $fold_row;
			if(isset($default_folder_type_to_title_map[strtolower($fold_row['m_type'])]))
			{
				$user_default_folders[$fold_row['m_type']] = $fold_row['title'];
			}
		}

		// Create missing default folders
		$folders_to_create = array_diff_key($default_folder_type_to_title_map, $user_default_folders);
		foreach($folders_to_create as $type => $title)
		{
			$folder_id = $ilDB->nextId('mail_obj_data');
			$ilDB->execute($ps_in_fold_entry, array($folder_id, $usr_id, $title, $type));

			$user_folders[$folder_id] = array(
				'obj_id' => $folder_id,
				'user_id'=> $usr_id,
				'title'  => $title,
				'm_type' => $type
			);
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created 'mail_obj_data' record (missing folder type): %s, %s, %s, %s .",
				$db_step, $i, $folder_id, $usr_id, $title, $type
			));
		}		

		// Create a new root folder node
		$root_id  = null;
		foreach($user_folders as $folder_id => $data)
		{
			if('root' != $data['m_type'])
			{
				continue;
			}

			$root_id = $folder_id;
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $root_id, 0, 1, 2, 1));

			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created root node with id %s for user %s in 'mail_tree'.",
				$db_step, $i, $root_id, $usr_id
			));
			break;
		}

		if(!$root_id)
		{
			// Did not find root folder, skip user and move to the next one
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: No root folder found for user %s . Skipped user.",
				$db_step, $i, $usr_id
			));
			continue;
		}

		$custom_folder_root_id = null;
		// Create all default folders below 'root'
		foreach($user_folders as $folder_id => $data)
		{
			if('root' == $data['m_type'] || !isset($default_folder_type_to_title_map[strtolower($data['m_type'])]))
			{
				continue;
			}

			if(null === $custom_folder_root_id && 'local' == $data['m_type'])
			{
				$custom_folder_root_id = $folder_id;
			}

			$res_parent = $ilDB->execute($ps_sel_tree_entry, array($root_id, $usr_id));
			$parent_row = $ilDB->fetchAssoc($res_parent);

			$right = $parent_row['rgt'];
			$lft   = $right;
			$rgt   = $right + 1;

			$ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $root_id, $lft, $rgt, 2));
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created node with id %s (lft: %s | rgt: %s) for user %s in 'mail_tree'.",
				$db_step, $i, $folder_id, $lft, $rgt, $usr_id
			));

		}

		if(!$custom_folder_root_id)
		{
			// Did not find custom folder root, skip user and move to the next one
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: No custom folder root found for user %s . Skipped user.",
				$db_step, $i, $usr_id
			));
			continue;
		}

		// Create all custom folders below 'local'
		foreach($user_folders as $folder_id => $data)
		{
			if(isset($default_folder_type_to_title_map[strtolower($data['m_type'])]))
			{
				continue;
			}

			$res_parent = $ilDB->execute($ps_sel_tree_entry, array($custom_folder_root_id, $usr_id));
			$parent_row = $ilDB->fetchAssoc($res_parent);

			$right = $parent_row['rgt'];
			$lft   = $right;
			$rgt   = $right + 1;

			$ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $custom_folder_root_id, $lft, $rgt, 3));
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created custom folder node with id %s (lft: %s | rgt: %s) for user % in 'mail_tree'.",
				$db_step, $i, $folder_id, $lft, $rgt, $usr_id
			));
		}

		// Tree completely created, remove migration record
		$ilDB->execute($ps_del_mtmig_rec, array($usr_id));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s, iteration %s: Finished 'mail_tree' migration for user %s .",
			$db_step, $i, $usr_id
		));
	}

	$res = $ilDB->query("SELECT usr_id FROM mail_tree_migr");
	$num = $ilDB->numRows($res);
	if($num > 0)
	{
		die("There are still duplicate entries in table 'mail_tree'. Please execute this database update step again.");
	}
}
?>
<#4592>
<?php
if($ilDB->tableExists('mail_tree_migr'))
{
	$ilDB->dropTable('mail_tree_migr');
}

$ilSetting = new ilSetting();
$ilSetting->delete('mail_mt_dupl_warn_51x_shown');

$mt_dup_query_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT child
	FROM mail_tree
	GROUP BY child
	HAVING COUNT(*) > 1
) duplicateMailFolderNodes
";
$res  = $ilDB->query($mt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still duplicate entries in table 'mail_tree'. Please execute database update step 4589 again. Execute the following SQL string manually: UPDATE settings SET value = 4588 WHERE keyword = 'db_version'; ");
}

$ilDB->addPrimaryKey('mail_tree', array('child'));
?>
<#4593>
<?php
$ilDB->dropIndex('mail_tree', 'i1');
?>
<#4594>
<?php
	if (!$ilDB->tableColumnExists("booking_schedule", "av_from"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_from", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
	if (!$ilDB->tableColumnExists("booking_schedule", "av_to"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_to", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>
<#4595>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselCntr", "ca_cntr", "div",
	array());
?>
<#4596>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselICntr", "ca_icntr", "div",
	array());
?>
<#4597>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselIHead", "ca_ihead", "div",
	array());
?>
<#4598>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselICont", "ca_icont", "div",
	array());
?>
<#4599>
<?php

if( !$ilDB->tableExists('member_noti') )
{
	$ilDB->createTable('member_noti', array(
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'nmode' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
		
	$ilDB->addPrimaryKey('member_noti', array('ref_id'));
}

?>
<#4600>
<?php

if( !$ilDB->tableExists('member_noti_user') )
{
	$ilDB->createTable('member_noti_user', array(
		'ref_id' => array(
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
		'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
		
	$ilDB->addPrimaryKey('member_noti_user', array('ref_id', 'user_id'));
}

?>
<#4601>
<?php
if(!$ilDB->tableColumnExists('frm_posts', 'pos_cens_date'))
{
	$ilDB->addTableColumn('frm_posts', 'pos_cens_date', array(
			'type'    => 'timestamp',
			'notnull' => false)
	);
}
?>
<#4602>
<?php
if(!$ilDB->tableExists('frm_posts_deleted'))
{
	$ilDB->createTable('frm_posts_deleted',
		array(
			'deleted_id'          => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'deleted_date'        => array(
				'type'    => 'timestamp',
				'notnull' => true
			),
			'deleted_by'          => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'forum_title'         => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'thread_title'        => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'post_title'          => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'post_message'        => array(
				'type'    => 'clob',
				'notnull' => true
			),
			'post_date'           => array(
				'type'    => 'timestamp',
				'notnull' => true
			),
			'obj_id'              => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'ref_id'              => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'thread_id'           => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'forum_id'            => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'pos_display_user_id' => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			),
			'pos_usr_alias'       => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => false
			)
		));

	$ilDB->addPrimaryKey('frm_posts_deleted', array('deleted_id'));
	$ilDB->createSequence('frm_posts_deleted');
}
?>
<#4603>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4604>
<?php
if(!$ilDB->tableColumnExists('frm_posts_deleted','is_thread_deleted'))
{
	$ilDB->addTableColumn('frm_posts_deleted', 'is_thread_deleted', array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0)
	);
}
?>
<#4605>
<?php

$res = $ilDB->query("SELECT a.id, a.tpl_id, od.obj_id , od.title FROM ".
	"(didactic_tpl_a a JOIN ".
	"(didactic_tpl_alr alr JOIN ".
	"object_data od ".
	"ON (alr.role_template_id = od.obj_id)) ".
	"ON ( a.id = alr.action_id)) ".
	"WHERE a.type_id = " . $ilDB->quote(2,'integer'));

$names = array();
$templates = array();

while($row = $ilDB->fetchAssoc($res))
{
	$names[$row["tpl_id"]][$row["id"]] = array(
		"action_id" => $row["id"],
		"role_template_id" => $row["obj_id"],
		"role_title" => $row["title"]);

	$templates[$row["tpl_id"]] = $row["tpl_id"];
}

$res = $ilDB->query("SELECT * FROM didactic_tpl_objs");

while($row = $ilDB->fetchAssoc($res))
{
	if(in_array($row["tpl_id"],$templates))
	{
		$roles = array();
		$rol_res = $ilDB->query("SELECT rol_id FROM rbac_fa ".
			"WHERE parent = ".$ilDB->quote($row["ref_id"],'integer'). " AND assign = ".$ilDB->quote('y','text'));

		while($rol_row = $ilDB->fetchObject($rol_res))
		{
			$roles[] = $rol_row->rol_id;
		}

		foreach($names[$row["tpl_id"]] as $name)
		{
			$concat = $ilDB->concat(array(
				array("title", "text"),
				array($ilDB->quote("_".$row["ref_id"], "text"), "text")
			), false);

			$ilDB->manipulate("UPDATE object_data".
				" SET title = ".$concat .
				" WHERE ".$ilDB->in("obj_id",$roles, "", "integer").
				" AND title = " . $ilDB->quote($name['role_title']));
		}
	}
}
?>
<#4606>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_char'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_char', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false
	));
}
?>
<#4607>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_unlock'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_unlock', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#4608>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_valid'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_valid', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}
?>
<#4609>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','team_tutor'))
{
	$ilDB->addTableColumn('exc_assignment', 'team_tutor', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#4610>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','max_file'))
{
	$ilDB->addTableColumn('exc_assignment', 'max_file', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	));
}
?>
<#4611>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','deadline2'))
{
	$ilDB->addTableColumn('exc_assignment', 'deadline2', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}
?>
<#4612>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4613>
<?php
if(!$ilDB->tableColumnExists('exc_returned','late'))
{
	$ilDB->addTableColumn('exc_returned', 'late', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#4614>
<?php

if(!$ilDB->tableExists('exc_crit_cat'))
{
	$ilDB->createTable('exc_crit_cat', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit_cat',array('id'));
	$ilDB->createSequence('exc_crit_cat');
}

?>
<#4615>
<?php

if(!$ilDB->tableExists('exc_crit'))
{
	$ilDB->createTable('exc_crit', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'descr' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit',array('id'));
	$ilDB->createSequence('exc_crit');
}

?>
<#4616>
<?php

if(!$ilDB->tableColumnExists('exc_crit','required'))
{
	$ilDB->addTableColumn('exc_crit', 'required', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}

?>
<#4617>
<?php

if(!$ilDB->tableColumnExists('exc_crit','def'))
{
	$ilDB->addTableColumn('exc_crit', 'def', array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false
	));
}

?>
<#4618>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4619>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_text'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_text', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#4620>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_rating'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_rating', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#4621>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_crit_cat'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_crit_cat', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}

?>
<#4622>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');				
$blog_type_id = ilDBUpdateNewObjectType::getObjectTypeId('blog');
if($blog_type_id)
{					
	// not sure if we want to clone "write" or "contribute"?
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('redact', 'Redact', 'object', 6100);	
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($blog_type_id, $new_ops_id);						
	}
}	

?>
<#4623>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$redact_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('redact');
if($redact_ops_id)
{
	ilDBUpdateNewObjectType::addRBACTemplate('blog', 'il_blog_editor', 'Editor template for blogs', 
		array(
			ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
			ilDBUpdateNewObjectType::RBAC_OP_READ,
			ilDBUpdateNewObjectType::RBAC_OP_WRITE,
			$redact_ops_id)
	);
}

?>
<#4624>
<?php

if (!$ilDB->tableColumnExists('adv_md_record_objs', 'optional'))
{
	$ilDB->addTableColumn('adv_md_record_objs', 'optional', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
}
	
?>
<#4625>
<?php

if (!$ilDB->tableColumnExists('adv_md_record', 'parent_obj'))
{
	$ilDB->addTableColumn('adv_md_record', 'parent_obj', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4
	));
}
	
?>
<#4626>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4627>
<?php
	if (!$ilDB->tableExists("copg_section_timings"))
	{
		$fields = array (
			'pm_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
			'pm_title'   => array ('type' => 'text', 'notnull' => true, 'length' => 60, 'fixed' => false),
			'pm_enabled'    => array ('type' => 'integer', 'length'  => 1,"notnull" => true,"default" => 0),
			'save_usr_adr'  => array ('type' => 'integer', 'length'  => 1,"notnull" => true,"default" => 0)
		);


		$fields = array(
			"page_id" => array (
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"parent_type" => array (
				"type" => "text",
				"length" => 10,
				"notnull" => true
			),
			"utc_ts" => array (
				"type" => "timestamp",
				"notnull" => true
			)
		);

		$ilDB->createTable("copg_section_timings", $fields);
	}
?>
<#4628>
<?php
	$ilDB->dropTableColumn("copg_section_timings", "utc_ts");
	$ilDB->addTableColumn('copg_section_timings', 'unix_ts',
		array(
			"type"    => "integer",
			"notnull" => true,
			"length"  => 4,
			"default" => 0
		)
	);
?>
<#4629>
<?php
if(!$ilDB->tableColumnExists('skl_user_skill_level', 'unique_identifier'))
{
    $ilDB->addTableColumn('skl_user_skill_level', 'unique_identifier', array(
        'type' => 'text',
        'length' => 80,
        'notnull' => false
    ));
}
?>
<#4630>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4631>
<?php
	if (!$ilDB->tableColumnExists('crs_settings', 'crs_start'))
	{
		$ilDB->addTableColumn('crs_settings', 'crs_start', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
	if (!$ilDB->tableColumnExists('crs_settings', 'crs_end'))
	{
		$ilDB->addTableColumn('crs_settings', 'crs_end', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>
<#4632>
<?php
	if (!$ilDB->tableColumnExists('crs_settings', 'leave_end'))
	{
		$ilDB->addTableColumn('crs_settings', 'leave_end', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>
<#4633>
<?php
	if (!$ilDB->tableColumnExists('crs_settings', 'auto_wait'))
	{
		$ilDB->addTableColumn('crs_settings', 'auto_wait', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4634>
<?php
	if (!$ilDB->tableColumnExists('crs_settings', 'min_members'))
	{
		$ilDB->addTableColumn('crs_settings', 'min_members', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 2
		));
	}
?>
<#4635>
<?php
	if (!$ilDB->tableColumnExists('grp_settings', 'registration_min_members'))
	{
		$ilDB->addTableColumn('grp_settings', 'registration_min_members', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 2
		));
	}
?>
<#4636>
<?php
	if (!$ilDB->tableColumnExists('grp_settings', 'leave_end'))
	{
		$ilDB->addTableColumn('grp_settings', 'leave_end', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>
<#4637>
<?php
	if (!$ilDB->tableColumnExists('grp_settings', 'auto_wait'))
	{
		$ilDB->addTableColumn('grp_settings', 'auto_wait', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4638>
<?php
	if (!$ilDB->tableColumnExists('event', 'reg_min_users'))
	{
		$ilDB->addTableColumn('event', 'reg_min_users', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 2
		));
	}
?>
<#4639>
<?php
	if (!$ilDB->tableColumnExists('event', 'reg_auto_wait'))
	{
		$ilDB->addTableColumn('event', 'reg_auto_wait', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4640>
<?php
if(!$ilDB->tableExists('mail_man_tpl'))
{
	$ilDB->createTable('mail_man_tpl', array(
		'tpl_id'    => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'title'     => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'context'   => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'lang'      => array(
			'type'    => 'text',
			'length'  => 2,
			'notnull' => true
		),
		'm_subject' => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'm_message' => array(
			'type'    => 'clob',
			'notnull' => false,
			'default' => null
		)
	));

	$ilDB->addPrimaryKey('mail_man_tpl', array('tpl_id'));
	$ilDB->createSequence('mail_man_tpl');
}
?>
<#4641>
<?php
if(!$ilDB->tableExists('mail_tpl_ctx'))
{
	$ilDB->createTable('mail_tpl_ctx', array(
		'id'             => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'component'      => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'class' => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'path'           => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		)
	));
	$ilDB->addPrimaryKey('mail_tpl_ctx', array('id'));
}
?>
<#4642>
<?php
$ilDB->addIndex('mail_man_tpl', array('context'), 'i1');
?>
<#4643>
<?php
if(!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_id'))
{
	$ilDB->addTableColumn(
		'mail_saved',
		'tpl_ctx_id',
		array(
			'type'    => 'text',
			'length'  => '100',
			'notnull' => false,
			'default' => null
		)
	);
}

if(!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_params'))
{
	$ilDB->addTableColumn(
		'mail_saved',
		'tpl_ctx_params',
		array(
			'type'    => 'blob',
			'notnull' => false,
			'default' => null
		)
	);
}
?>
<#4644>
<?php
if(!$ilDB->tableColumnExists('mail', 'tpl_ctx_id'))
{
	$ilDB->addTableColumn(
		'mail',
		'tpl_ctx_id',
		array(
			'type'    => 'text',
			'length'  => '100',
			'notnull' => false,
			'default' => null
		)
	);
}

if(!$ilDB->tableColumnExists('mail', 'tpl_ctx_params'))
{
	$ilDB->addTableColumn(
		'mail',
		'tpl_ctx_params',
		array(
			'type'    => 'blob',
			'notnull' => false,
			'default' => null
		)
	);
}
?>
<#4645>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4646>
<?php
if(!$ilDB->tableExists('itgr_data'))
{
	$ilDB->createTable('itgr_data', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'hide_title' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('itgr_data',array('id'));
}
?>
<#4647>
<?php
$set = $ilDB->query("SELECT * FROM object_data ".
	" WHERE type = ".$ilDB->quote("itgr", "text")
	);
while ($rec = $ilDB->fetchAssoc($set))
{
	$ilDB->manipulate("INSERT INTO itgr_data ".
		"(id, hide_title) VALUES (".
		$ilDB->quote($rec["obj_id"], "integer").",".
		$ilDB->quote(0, "integer").
		")");
}
?>
<#4648>
<?php
//$ilDB->query('ALTER TABLE il_dcl_record_field ADD INDEX (record_id)');
//$ilDB->query('ALTER TABLE il_dcl_record_field ADD INDEX (field_id)');
//$ilDB->query('ALTER TABLE il_dcl_record ADD INDEX (table_id)');
//$ilDB->query('ALTER TABLE il_dcl_stloc1_value ADD INDEX (record_field_id)');
//$ilDB->query('ALTER TABLE il_dcl_stloc2_value ADD INDEX (record_field_id)');
//$ilDB->query('ALTER TABLE il_dcl_stloc3_value ADD INDEX (record_field_id)');
//$ilDB->query('ALTER TABLE il_dcl_field ADD INDEX (table_id)');
//$ilDB->query('ALTER TABLE il_dcl_field_prop ADD INDEX (field_id)');
//$ilDB->query('ALTER TABLE il_dcl_field_prop ADD INDEX (datatype_prop_id)');
//$ilDB->query('ALTER TABLE il_dcl_viewdefinition ADD INDEX (view_id)');
//$ilDB->query('ALTER TABLE il_dcl_view ADD INDEX (table_id)');
//$ilDB->query('ALTER TABLE il_dcl_view ADD INDEX (type)');
//$ilDB->query('ALTER TABLE il_dcl_data ADD INDEX (main_table_id)');
//$ilDB->query('ALTER TABLE il_dcl_table ADD INDEX (obj_id)');
?>
<#4649>
<?php
if (!$ilDB->tableColumnExists("content_object", "for_translation"))
{
	$ilDB->addTableColumn("content_object", "for_translation", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
}
?>
<#4650>
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
<#4651>
<?php
if (!$ilDB->tableColumnExists("mep_data", "for_translation"))
{
	$ilDB->addTableColumn("mep_data", "for_translation", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
}
?>
<#4652>
<?php
if (!$ilDB->tableColumnExists("mep_item", "import_id"))
{
	$ilDB->addTableColumn("mep_item", "import_id", array(
		"type" => "text",
		"notnull" => false,
		"length" => 50));
}
?>
<#4653>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4654>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('edit_wiki_navigation', 'Edit Wiki Navigation', 'object', 3220);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}
?>
<#4655>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('delete_wiki_pages', 'Delete Wiki Pages', 'object', 3300);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>
<#4656>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('activate_wiki_protection', 'Set Read-Only', 'object', 3240);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>
<#4657>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4658>
<?php
	if(!$ilDB->tableExists('wiki_user_html_export') )
	{
		$ilDB->createTable('wiki_user_html_export', array(
			'wiki_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'usr_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'progress' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'start_ts' => array(
				'type' => 'timestamp',
				'notnull' => false
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => true,
				'default' => 0
			)
		));
		$ilDB->addPrimaryKey('wiki_user_html_export', array('wiki_id'));
	}
?>
<#4659>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('wiki_html_export', 'Wiki HTML Export', 'object', 3242);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>

<#4660>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 5
        ));
}
?>
<#4661>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_type')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_type',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#4662>
<?php

if(!$ilDB->tableColumnExists('loc_settings','it_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'it_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}

?>

<#4663>
<?php

if(!$ilDB->tableColumnExists('loc_settings','qt_start')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_start',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>

<#4664>
<?php


$query = 'UPDATE loc_settings SET it_type = '.$ilDB->quote(1,'integer').' WHERE type = '.$ilDB->quote(1,'integer');
$res = $ilDB->manipulate($query);

?>

<#4665>
<?php


$query = 'UPDATE loc_settings SET qt_start = '.$ilDB->quote(0,'integer').' WHERE type = '.$ilDB->quote(4,'integer');
$res = $ilDB->manipulate($query);

?>

<#4666>
<?php

if(!$ilDB->tableExists('loc_tst_assignments'))
{
	$ilDB->createTable('loc_tst_assignments', array(
		'assignment_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'assignment_type' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'objective_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tst_ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('loc_tst_assignments', array('assignment_id'));
	$ilDB->createSequence('loc_tst_assignments');

}
?>


<#4667>
<?php

if(!$ilDB->tableColumnExists('loc_settings','passed_obj_mode')) 
{
    $ilDB->addTableColumn(
        'loc_settings',
        'passed_obj_mode',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 1
        ));
}
?>

<#4668>
<?php
if( !$ilDB->tableExists('tst_seq_qst_optional') )
{
	$ilDB->createTable('tst_seq_qst_optional', array(
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
	
	$ilDB->addPrimaryKey('tst_seq_qst_optional', array(
		'active_fi', 'pass', 'question_fi'
	));
}	
?>

<#4669>
<?php
if( !$ilDB->tableColumnExists('tst_sequence', 'ans_opt_confirmed') )
{
	$ilDB->addTableColumn('tst_sequence', 'ans_opt_confirmed', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>

<#4670>
<?php
if (! $ilDB->tableExists('il_wac_secure_path')) {
	$fields = array(
		'path' => array(
			'type' => 'text',
			'length' => '64',

		),
		'component_directory' => array(
			'type' => 'text',
			'length' => '256',

		),
		'checking_class' => array(
			'type' => 'text',
			'length' => '256',

		),
		'in_sec_folder' => array(
			'type' => 'integer',
			'length' => '1',
		),

	);

	$ilDB->createTable('il_wac_secure_path', $fields);
	$ilDB->addPrimaryKey('il_wac_secure_path', array( 'path' ));
}
?>
<#4671>
	<?php
	//step 1/5 search for dublicates and store it in desktop_item_tmp

	if ($ilDB->tableExists('desktop_item'))
	{
		$res = $ilDB->query("
		SELECT first.item_id, first.user_id
		FROM desktop_item first
		WHERE EXISTS (
			SELECT second.item_id, second.user_id
			FROM desktop_item second
			WHERE first.item_id = second.item_id AND first.user_id = second.user_id
			GROUP BY second.item_id, second.user_id
			HAVING COUNT(second.item_id) > 1
		)
		GROUP BY first.item_id, first.user_id
	");

		if($ilDB->numRows($res))
		{
			if(!$ilDB->tableExists('desktop_item_tmp'))
			{
				$ilDB->createTable('desktop_item_tmp', array(
					'item_id' => array(
						'type'  => 'integer',
						'length'=> 8,
						'notnull' => true,
						'default' => 0
					),
					'user_id' => array(
						'type'  => 'integer',
						'length'=> 8,
						'notnull' => true,
						'default' => 0
					)
				));
				$ilDB->addPrimaryKey('desktop_item_tmp', array('item_id','user_id'));
			}

			while($row = $ilDB->fetchAssoc($res))
			{
				$ilDB->replace('desktop_item_tmp', array(), array(
					'item_id' => array('integer', $row['item_id']),
					'user_id' => array('integer', $row['user_id'])
				));
			}
		}
	}
	?>
<#4672>
	<?php
	//step 2/5 deletes dublicates stored in desktop_item_tmp

	if ($ilDB->tableExists('desktop_item_tmp'))
	{
		$res = $ilDB->query("
		SELECT item_id, user_id
		FROM desktop_item_tmp
	");

		while($row = $ilDB->fetchAssoc($res))
		{
			$res_data = $ilDB->query("
			SELECT *
			FROM desktop_item
			WHERE
			item_id = ".$ilDB->quote($row['item_id'] ,'integer')." AND
			user_id = ".$ilDB->quote($row['user_id'] ,'integer')
			);
			$data = $ilDB->fetchAssoc($res_data);

			$ilDB->manipulate("DELETE FROM desktop_item WHERE".
				" item_id = " . $ilDB->quote($row['item_id'] ,'integer').
				" AND user_id = " . $ilDB->quote($row['user_id'] ,'integer')
			);

			$ilDB->manipulate("INSERT INTO desktop_item (item_id,user_id,type,parameters) ".
				"VALUES ( ".
				$ilDB->quote($data['item_id'] ,'integer').', '.
				$ilDB->quote($data['user_id'] ,'integer').', '.
				$ilDB->quote($data['type'] ,'text').', '.
				$ilDB->quote($data['parameters'] ,'text').
				")");
		}
	}
	?>
<#4673>
	<?php
	//step 3/5 drop desktop_item_tmp

	if( $ilDB->tableExists('desktop_item_tmp') )
	{
		$ilDB->dropTable('desktop_item_tmp');
	}
	?>
<#4674>
	<?php
	//step 4/5 drops not used indexes

	if( $ilDB->indexExistsByFields('desktop_item', array('item_id')) )
	{
		$ilDB->dropIndexByFields('desktop_item', array('item_id'));
	}
	if( $ilDB->indexExistsByFields('desktop_item', array('user_id')) )
	{
		$ilDB->dropIndexByFields('desktop_item', array('user_id'));
	}
	?>
<#4675>
<?php
//step 5/5 adding primary keys and useful indexes

if($ilDB->tableExists('desktop_item'))
{
	$ilDB->addPrimaryKey('desktop_item', array('user_id', 'item_id'));
}
?>
<#4676>
<?php
if(!$ilDB->tableExists('buddylist'))
{
	$ilDB->createTable('buddylist', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist', array('usr_id', 'buddy_usr_id'));
}
?>
<#4677>
<?php
if(!$ilDB->tableExists('buddylist_requests'))
{
	$ilDB->createTable('buddylist_requests', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ignored' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist_requests', array('usr_id', 'buddy_usr_id'));
	$ilDB->addIndex('buddylist_requests', array('buddy_usr_id', 'ignored'), 'i1');
}
?>
<#4678>
<?php
$ilDB->manipulate('DELETE FROM addressbook_mlist_ass');
if($ilDB->tableColumnExists('addressbook_mlist_ass', 'addr_id'))
{
	$ilDB->renameTableColumn('addressbook_mlist_ass', 'addr_id', 'usr_id');
}
?>
<#4679>
<?php
if($ilDB->tableExists('addressbook'))
{
	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		INNER JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE ud1.usr_id != ud2.usr_id
	";
	$res = $ilDB->query($query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts' => array('integer', time())
			)
		);

		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u2']),
				'buddy_usr_id' => array('integer', $row['u1'])
			),
			array(
				'ts' => array('integer', time())
			)
		);
	}

	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		LEFT JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE a2.addr_id IS NULL AND ud1.usr_id != ud2.usr_id
	";
	$res = $ilDB->query($query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist_requests',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts'      => array('integer', time()),
				'ignored' => array('integer', 0)
			)
		);
	}

	$ilDB->dropTable('addressbook');
}
?>
<#4680>
<?php
if($ilDB->sequenceExists('addressbook'))
{
	$ilDB->dropSequence('addressbook');
}
?>
<#4681>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4682>
<?php
$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'text', 'text'),
	array(-1,  'buddysystem_request', 'mail')
);
$num = $ilDB->numRows($res);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'mail')
		)
	);
}

$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'text', 'text'),
	array(-1,  'buddysystem_request', 'osd')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'osd')
		)
	);
}
?>
<#4683>
<?php
if(!$ilDB->tableColumnExists('obj_members','contact'))
{
	$ilDB->addTableColumn(
		'obj_members',
		'contact',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#4684>
<?php
	// register new object type 'awra' for awareness tool administration
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "typ", "awra", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now()));
	$typ_id = $id;

	// create object data entry
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "awra", "__AwarenessToolAdministration", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now()));

	// create object reference entry
	$ref_id = $ilDB->nextId('object_reference');
	$res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($ref_id, $id));

	// put in tree
	$tree = new ilTree(ROOT_FOLDER_ID);
	$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

	// add rbac operations
	// 1: edit_permissions, 2: visible, 3: read, 4:write
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 1));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 2));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 3));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 4));
?>
<#4685>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4686>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4687>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4688>
<?php
	$s = new ilSetting("awrn");
	$s->set("max_nr_entries", 50);
?>
<#4689>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4690>
<?php
//step 1/4 rbac_log renames old table

if ($ilDB->tableExists('rbac_log') && !$ilDB->tableExists('rbac_log_old'))
{
	$ilDB->renameTable("rbac_log", "rbac_log_old");
}
?>
<#4691>
<?php
//step 2/4 rbac_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('rbac_log'))
{
	$ilDB->createTable('rbac_log',array(
		'log_id'		=> array(
			'type'	=> 'integer',
			'length' => 4,
			'notnull' => true
		),
		'user_id'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'created'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'ref_id'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'action'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'data' 		=> array(
			'type'    => 'clob',
			'notnull' => false,
			'default' => null
		)
	));
	$ilDB->addPrimaryKey('rbac_log', array('log_id'));
	$ilDB->addIndex('rbac_log',array('ref_id'),'i1');
	$ilDB->createSequence('rbac_log');
}
?>
<#4692>
<?php
//step 3/4 rbac_log moves all data to new table

if ($ilDB->tableExists('rbac_log') && $ilDB->tableExists('rbac_log_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM rbac_log_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('rbac_log');

		$ilDB->manipulate("INSERT INTO rbac_log (log_id, user_id, created, ref_id, action, data)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['user_id'], "integer").
			",".$ilDB->quote($row['created'], "integer").
			",".$ilDB->quote($row['ref_id'], "integer").
			",".$ilDB->quote($row['action'], "integer").
			",".$ilDB->quote($row['data'], "text").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM rbac_log_old WHERE user_id = %s AND created = %s AND ref_id = %s AND action = %s",
			array('integer', 'integer', 'integer', 'integer'),
			array($row['user_id'], $row['created'], $row['ref_id'], $row['action'])
        );
	}
}
?>
<#4693>
<?php
//step 4/4 rbac_log removes all table

if ($ilDB->tableExists('rbac_log_old'))
{
	$ilDB->dropTable('rbac_log_old');
}
?>
<#4694>
<?php
//step 1/3 rbac_templates removes all dublicates
if ($ilDB->tableExists('rbac_templates'))
{
	$res = $ilDB->query(
		'select * from rbac_templates GROUP BY rol_id, type, ops_id, parent '.
		'having count(*) > 1'
	);
	
	
	/*
	$res = $ilDB->query("
		SELECT first.rol_id rol_id, first.type type, first.ops_id ops_id, first.parent parent
		FROM rbac_templates first
		WHERE EXISTS (
			SELECT second.rol_id, second.type, second.ops_id, second.parent
			FROM rbac_templates second
			WHERE first.rol_id = second.rol_id
				AND first.type = second.type
				AND first.ops_id = second.ops_id
				AND first.parent = second.parent
			GROUP BY second.rol_id, second.type, second.ops_id, second.parent
			HAVING COUNT(second.rol_id) > 1
		)
		GROUP BY first.rol_id, first.type, first.ops_id, first.parent
	");
	 */

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->manipulateF(
			"DELETE FROM rbac_templates WHERE rol_id = %s AND type = %s AND ops_id = %s AND parent = %s",
			array('integer', 'text', 'integer', 'integer'),
			array($row['rol_id'], $row['type'], $row['ops_id'], $row['parent'])
		);
		;		
		$sql = "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)".
			" VALUES (".
			$ilDB->quote($row['rol_id'], "integer").
			",".$ilDB->quote($row['type'], "text").
			",".$ilDB->quote($row['ops_id'], "integer").
			",".$ilDB->quote($row['parent'], "integer").
			")";
		$ilDB->manipulate($sql);
	}
}
?>
<#4695>
<?php
//step 2/3 rbac_templates remove indexes
if( $ilDB->indexExistsByFields('rbac_templates', array('rol_id')) )
{
	$ilDB->dropIndexByFields('rbac_templates', array('rol_id'));
}
if( $ilDB->indexExistsByFields('rbac_templates', array('type')) )
{
	$ilDB->dropIndexByFields('rbac_templates', array('type'));
}
if( $ilDB->indexExistsByFields('rbac_templates', array('ops_id')) )
{
	$ilDB->dropIndexByFields('rbac_templates', array('ops_id'));
}
if( $ilDB->indexExistsByFields('rbac_templates', array('parent')) )
{
	$ilDB->dropIndexByFields('rbac_templates', array('parent'));
}
if( $ilDB->indexExistsByFields('rbac_templates', array('rol_id','parent')) )
{
	$ilDB->dropIndexByFields('rbac_templates', array('rol_id','parent'));
}
?>
<#4696>
<?php
//step 3/3 rbac_templates add primary
if ($ilDB->tableExists('rbac_templates'))
{
	$ilDB->addPrimaryKey('rbac_templates', array('rol_id','parent', 'type', 'ops_id'));
}
?>
<#4697>
<?php
//remove unused table search_tree
if ($ilDB->tableExists('search_tree'))
{
	$ilDB->dropTable('search_tree');
}
?>
<#4698>
<?php
	if(!$ilDB->tableColumnExists('sahs_lm','mastery_score'))
	{
		$ilDB->addTableColumn(
			'sahs_lm',
			'mastery_score',
			array(
				'type' 		=> 'integer',
				'length' 	=> 1,
				'notnull'	=> false
			)
		);
	}
?>
<#4699>
<?php
//step 1/2 adm_set_templ_hide_tab removes all dublicates
if ($ilDB->tableExists('adm_set_templ_hide_tab'))
{
	$res = $ilDB->query("
		SELECT first.template_id template_id, first.tab_id tab_id
		FROM adm_set_templ_hide_tab first
		WHERE EXISTS (
			SELECT second.template_id, second.tab_id
			FROM adm_set_templ_hide_tab second
			WHERE first.template_id = second.template_id AND first.tab_id = second.tab_id
			GROUP BY second.template_id, second.tab_id HAVING COUNT(second.template_id) > 1
		)
		GROUP BY first.template_id, first.tab_id;
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->manipulateF(
			"DELETE FROM adm_set_templ_hide_tab WHERE template_id = %s AND tab_id = %s",
			array('integer', 'text'),
			array($row['template_id'], $row['tab_id'])
		);

		$ilDB->manipulate("INSERT INTO adm_set_templ_hide_tab (template_id, tab_id)".
			" VALUES (".
			$ilDB->quote($row['template_id'], "integer").
			", ".$ilDB->quote($row['tab_id'], "text").
			")"
		);
	}
}
?>
<#4700>
<?php
//step 2/2 adm_set_templ_hide_tab add primary
if ($ilDB->tableExists('adm_set_templ_hide_tab'))
{
	$ilDB->addPrimaryKey('adm_set_templ_hide_tab', array('template_id','tab_id'));
}
?>
<#4701>
<?php
//step 1/4 adm_set_templ_value search for dublicates and store it in adm_set_tpl_val_tmp

if ($ilDB->tableExists('adm_set_templ_value'))
{
	$res = $ilDB->query("
		SELECT first.template_id template_id, first.setting setting
		FROM adm_set_templ_value first
		WHERE EXISTS (
			SELECT second.template_id, second.setting
			FROM adm_set_templ_value second
			WHERE first.template_id = second.template_id AND first.setting = second.setting
			GROUP BY second.template_id, second.setting
			HAVING COUNT(second.template_id) > 1
		)
		GROUP BY first.template_id, first.setting
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('adm_set_tpl_val_tmp'))
		{
			$ilDB->createTable('adm_set_tpl_val_tmp', array(
				'template_id' => array(
					'type'  => 'integer',
					'length'=> 8,
					'notnull' => true,
					'default' => 0
				),
				'setting' => array(
					'type'  => 'text',
					'length'=> 40,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('adm_set_tpl_val_tmp', array('template_id','setting'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('adm_set_tpl_val_tmp', array(), array(
				'template_id' => array('integer', $row['template_id']),
				'setting' => array('text', $row['setting'])
			));
		}
	}
}
?>
<#4702>
<?php
//step 2/4 adm_set_templ_value deletes dublicates stored in adm_set_tpl_val_tmp

if ($ilDB->tableExists('adm_set_tpl_val_tmp'))
{
	$res = $ilDB->query("
		SELECT template_id, setting
		FROM adm_set_tpl_val_tmp
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM adm_set_templ_value
			WHERE
			template_id = ".$ilDB->quote($row['template_id'] ,'integer')." AND
			setting = ".$ilDB->quote($row['setting'] ,'text')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM adm_set_templ_value WHERE".
			" template_id = " . $ilDB->quote($row['template_id'] ,'integer').
			" AND setting = " . $ilDB->quote($row['setting'] ,'text')
		);

		$ilDB->manipulate("INSERT INTO adm_set_templ_value (template_id,setting,value,hide) ".
			"VALUES ( ".
			$ilDB->quote($data['template_id'] ,'integer').', '.
			$ilDB->quote($data['setting'] ,'text').', '.
			$ilDB->quote($data['value'] ,'text').', '.
			$ilDB->quote($data['hide'] ,'integer').
		")");

		$ilDB->manipulate("DELETE FROM adm_set_tpl_val_tmp WHERE".
			" template_id = " . $ilDB->quote($row['template_id'] ,'integer').
			" AND setting = " . $ilDB->quote($row['setting'] ,'text')
		);
	}
}
?>
<#4703>
<?php
//step 3/4 adm_set_templ_value drop adm_set_tpl_val_tmp

if( $ilDB->tableExists('adm_set_tpl_val_tmp') )
{
	$ilDB->dropTable('adm_set_tpl_val_tmp');
}
?>
<#4704>
<?php
//step 4/4 adm_set_templ_value adding primary keys

if($ilDB->tableExists('adm_set_templ_value'))
{
	$ilDB->addPrimaryKey('adm_set_templ_value', array('template_id', 'setting'));
}
?>
<#4705>
<?php
//step 1/4 svy_times renames old table

if ($ilDB->tableExists('svy_times') && !$ilDB->tableExists('svy_times_old'))
{
	$ilDB->renameTable("svy_times", "svy_times_old");
}
?>
<#4706>
<?php
//step 2/4 svy_times creates new table with unique id, sequenz and index

if (!$ilDB->tableExists('svy_times'))
{
	$ilDB->createTable('svy_times',array(
		'id'		=> array(
			'type'	=> 'integer',
			'length' => 4,
			'notnull' => true
		),
		'finished_fi'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'entered_page'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
		),
		'left_page'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
		),
		'first_question'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
		)
	));
	$ilDB->addPrimaryKey('svy_times', array('id'));
	$ilDB->addIndex('svy_times',array('finished_fi'),'i1');
	$ilDB->createSequence('svy_times');
}
?>
<#4707>
<?php
//step 3/4 svy_times moves all data to new table

if ($ilDB->tableExists('svy_times') && $ilDB->tableExists('svy_times_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM svy_times_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('svy_times');

		$ilDB->manipulate("INSERT INTO svy_times (id, finished_fi, entered_page, left_page, first_question)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['finished_fi'], "integer").
			",".$ilDB->quote($row['entered_page'], "integer").
			",".$ilDB->quote($row['left_page'], "integer").
			",".$ilDB->quote($row['first_question'], "integer").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM svy_times_old WHERE finished_fi = %s AND entered_page = %s AND left_page = %s AND first_question = %s",
			array('integer', 'integer', 'integer', 'integer'),
			array($row['finished_fi'], $row['entered_page'], $row['left_page'], $row['first_question'])
		);
	}
}
?>
<#4708>
<?php
//step 4/4 svy_times removes old table

if ($ilDB->tableExists('svy_times_old'))
{
	$ilDB->dropTable('svy_times_old');
}
?>

<#4709>
<?php

if(!$ilDB->tableColumnExists("ldap_server_settings", "username_filter"))
{
        $ilDB->addTableColumn("ldap_server_settings", "username_filter", array(
                'type' => 'text',
                'length' => 255,
        ));
}
?>
<#4710>
<?php
$query = "SELECT max(server_id) id FROM ldap_server_settings";
$res = $ilDB->query($query);
$set = $res->fetchRow(DB_FETCHMODE_OBJECT);

if(!$set->id)
{
        $set->id = 1;
}

$query = "UPDATE ldap_role_assignments ".
        "SET server_id = ".$set->id.
        " WHERE server_id = 0";
$ilDB->manipulate($query);

?>
<#4711>
<?php
if(!$ilDB->tableColumnExists('usr_search','creation_filter'))
{
                $ilDB->addTableColumn("usr_search", "creation_filter", array(
                        "type" => "text",
                        "notnull" => false,
                        "length" => 1000,
                        "fixed" => false));
}
?>
<#4712>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4713>
<?php
        // register new object type 'logs' for Logging administration
        $id = $ilDB->nextId("object_data");
        $ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
                array($id, "typ", "logs", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));
        $typ_id = $id;

        // create object data entry
        $id = $ilDB->nextId("object_data");
        $ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
                array($id, "logs", "__LoggingSettings", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));

        // create object reference entry
        $ref_id = $ilDB->nextId('object_reference');
        $res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($ref_id, $id));

        // put in tree
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

        // add rbac operations
        // 1: edit_permissions, 2: visible, 3: read, 4:write
        $ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($typ_id, 1));
        $ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($typ_id, 2));
        $ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($typ_id, 3));
        $ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($typ_id, 4));


?>
<#4714>
<?php
        $ilCtrlStructureReader->getStructure();
?>

<#4715>
<?php

        if(!$ilDB->tableExists('log_components'))
        {
                $ilDB->createTable('log_components', array(
                        'component_id' => array(
                                'type' => 'text',
                                'length' => 20,
                                'notnull' => FALSE
                        ),
                        'log_level' => array(
                                'type' => 'integer',
                                'length' => 4,
                                'notnull' => FALSE,
                                'default' => null
                        )
                ));

                $ilDB->addPrimaryKey('log_components',array('component_id'));
        }
?>
<#4716>
<?php
        $ilCtrlStructureReader->getStructure();
?>
<#4717>
<?php
        $ilCtrlStructureReader->getStructure();
?>
<#4718>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4719>
<?php

$res = $ilDB->queryF(
	"SELECT COUNT(*) cnt FROM qpl_qst_type WHERE type_tag = %s", array('text'), array('assLongMenu')
);

$row = $ilDB->fetchAssoc($res);

if( !$row['cnt'] )
{
	$res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
	$data = $ilDB->fetchAssoc($res);
	$nextId = $data['maxid'] + 1;

	$ilDB->insert('qpl_qst_type', array(
		'question_type_id' => array('integer', $nextId),
		'type_tag' => array('text', 'assLongMenu'),
		'plugin' => array('integer', 0)
	));
}

?>
<#4720>
<?php
if( !$ilDB->tableExists('qpl_qst_lome') )
{
	$ilDB->createTable('qpl_qst_lome', array(
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
		'feedback_setting' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1
		),
		'long_menu_text' =>	 array(
			"type" => "clob",
			"notnull" => false,
			"default" => null
		)
	));

	$ilDB->addPrimaryKey('qpl_qst_lome', array('question_fi'));
}
?>
<#4721>
<?php
if( !$ilDB->tableExists('qpl_a_lome') )
{
	$ilDB->createTable('qpl_a_lome', array(
		'question_fi' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'gap_number' => array(
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
		'answer_text' => array(
			'type' => 'text',
			'length' => 1000
		),
		'points' => array(
			'type' => 'float'
		),
		'type' => array(
			'type' => 'integer',
			'length' => 4
		)
	));
	$ilDB->addPrimaryKey('qpl_a_lome', array('question_fi', 'gap_number', 'position'));
}
?>
<#4722>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4723>
<?php
	
	$query = 'SELECT child FROM tree group by child having count(child) > 1';
	$res = $ilDB->query($query);
	
	$found_dup = FALSE;
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$found_dup = TRUE;
	}
	
	if(!$found_dup)
	{
		$ilDB->addPrimaryKey('tree',array('child'));
	}
	else
	{
		$ilSetting = new ilSetting();
		$is_read = $ilSetting->get('tree_dups', 0);

		if(!$is_read)
			{
				echo "<pre>
					Dear Administrator,

					DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

					The update process has been stopped due to an invalid data structure of the repository tree. 
					Duplicates have been detected in your installation.
					
					You can continue with the update process.
					But you should perform a system check and repair the tree structure in \"Adminstration -> Systemcheck -> Tree\"

					Best regards,
					The Tree Maintainer
				</pre>";

				$ilSetting->set('tree_dups', 1);
				exit;
			}
	}
?>
<#4724>
<?php
//step 1/4 usr_data_multi renames old table

if ($ilDB->tableExists('usr_data_multi') && !$ilDB->tableExists('usr_data_multi_old'))
{
	$ilDB->renameTable("usr_data_multi", "usr_data_multi_old");
}
?>
<#4725>
<?php
//step 2/4 usr_data_multi creates new table with unique id, sequenz and index

if (!$ilDB->tableExists('usr_data_multi'))
{
	$ilDB->createTable('usr_data_multi',array(
		'id'		=> array(
			'type'	=> 'integer',
			'length' => 4,
			'notnull' => true
		),
		'usr_id'	=> array(
			'type'	=> 'integer',
			'length'=> 4,
			'notnull' => TRUE
		),
		'field_id'	=> array(
			'type'	=> 'text',
			'length'=> 255,
			'notnull' => TRUE
		),
		'value'	=> array(
			'type'	=> 'text',
			'length'=> 1000,
			'default' => ''
		)
	));
	$ilDB->addPrimaryKey('usr_data_multi', array('id'));
	$ilDB->addIndex('usr_data_multi',array('usr_id'),'i1');
	$ilDB->createSequence('usr_data_multi');
}
?>
<#4726>
<?php
//step 3/4 usr_data_multi moves all data to new table

if ($ilDB->tableExists('usr_data_multi') && $ilDB->tableExists('usr_data_multi_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM usr_data_multi_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('usr_data_multi');

		$ilDB->manipulate("INSERT INTO usr_data_multi (id, usr_id, field_id, value)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['usr_id'], "integer").
			",".$ilDB->quote($row['field_id'], "text").
			",".$ilDB->quote($row['value'], "text").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM usr_data_multi_old WHERE usr_id = %s AND field_id = %s AND value = %s",
			array('integer', 'text', 'text'),
			array($row['usr_id'], $row['field_id'], $row['value'])
		);
	}
}
?>
<#4727>
<?php
//step 4/4 usr_data_multi removes old table

if ($ilDB->tableExists('usr_data_multi_old'))
{
	$ilDB->dropTable('usr_data_multi_old');
}
?>
<#4728>
<?php
//step 1/4 xmlnestedset renames old table

if ($ilDB->tableExists('xmlnestedset') && !$ilDB->tableExists('xmlnestedset_old'))
{
	$ilDB->renameTable("xmlnestedset", "xmlnestedset_old");
}
?>
<#4729>
<?php
//step 2/4 xmlnestedset creates new table with unique id and sequenz

if (!$ilDB->tableExists('xmlnestedset'))
{
	$ilDB->createTable("xmlnestedset",
		array(
			"ns_id" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_book_fk" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_type" => array(
				"type" => "text",
				"length" => 50,
				"notnull" => true
			),
			"ns_tag_fk" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_l" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_r" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			)
		)
	);
	$ilDB->addIndex("xmlnestedset", array("ns_tag_fk"), 'i1');
	$ilDB->addIndex("xmlnestedset", array("ns_l"), 'i2');
	$ilDB->addIndex("xmlnestedset", array("ns_r"), 'i3');
	$ilDB->addIndex("xmlnestedset", array("ns_book_fk"), 'i4');
	$ilDB->addPrimaryKey('xmlnestedset', array('ns_id'));
	$ilDB->createSequence('xmlnestedset');
}
?>
<#4730>
<?php
//step 3/4 xmlnestedset moves all data to new table

if ($ilDB->tableExists('xmlnestedset') && $ilDB->tableExists('xmlnestedset_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM xmlnestedset_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('xmlnestedset');

		$ilDB->manipulate("INSERT INTO xmlnestedset (ns_id, ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['ns_book_fk'], "integer").
			",".$ilDB->quote($row['ns_type'], "text").
			",".$ilDB->quote($row['ns_tag_fk'], "integer").
			",".$ilDB->quote($row['ns_l'], "integer").
			",".$ilDB->quote($row['ns_r'], "integer").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM xmlnestedset_old WHERE ns_book_fk = %s AND ns_type = %s AND ns_tag_fk = %s AND ns_l = %s AND ns_r = %s",
			array('integer', 'text', 'integer', 'integer', 'integer'),
			array($row['ns_book_fk'], $row['ns_type'], $row['ns_tag_fk'], $row['ns_l'], $row['ns_r'])
		);
	}
}
?>
<#4731>
<?php
//step 4/4 xmlnestedset removes old table

if ($ilDB->tableExists('xmlnestedset_old'))
{
	$ilDB->dropTable('xmlnestedset_old');
}
?>
<#4732>
<?php
//step 1/4 xmlnestedsettmp renames old table

if ($ilDB->tableExists('xmlnestedsettmp') && !$ilDB->tableExists('xmlnestedsettmp_old'))
{
	$ilDB->renameTable("xmlnestedsettmp", "xmlnestedsettmp_old");
}
?>
<#4733>
<?php
//step 2/4 xmlnestedsettmp creates new table with unique id and sequenz

if (!$ilDB->tableExists('xmlnestedsettmp'))
{
	$ilDB->createTable("xmlnestedsettmp",
		array(
			"ns_id" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_unique_id" => array(// text because maybe we have to store a session_id in future e.g.
				"type" => "text",
				"length" => 32,
				"notnull" => true
			),
			"ns_book_fk" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_type" => array(
				"type" => "text",
				"length" => 50,
				"notnull" => true
			),
			"ns_tag_fk" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_l" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			),
			"ns_r" => array(
				"type" => "integer",
				"length" => 4,
				"notnull" => true
			)
		)
	);
	$ilDB->addIndex("xmlnestedsettmp", array("ns_tag_fk"), 'i1');
	$ilDB->addIndex("xmlnestedsettmp", array("ns_l"), 'i2');
	$ilDB->addIndex("xmlnestedsettmp", array("ns_r"), 'i3');
	$ilDB->addIndex("xmlnestedsettmp", array("ns_book_fk"), 'i4');
	$ilDB->addIndex("xmlnestedsettmp", array("ns_unique_id"), 'i5');
	$ilDB->addPrimaryKey('xmlnestedsettmp', array('ns_id'));
	$ilDB->createSequence('xmlnestedsettmp');
}
?>
<#4734>
<?php
//step 3/4 xmlnestedsettmp moves all data to new table

if ($ilDB->tableExists('xmlnestedsettmp') && $ilDB->tableExists('xmlnestedsettmp_old'))
{
	$res = $ilDB->query("
		SELECT *
		FROM xmlnestedsettmp_old
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$id = $ilDB->nextId('xmlnestedsettmp');

		$ilDB->manipulate("INSERT INTO xmlnestedsettmp (ns_id, ns_unique_id, ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r)".
			" VALUES (".
			$ilDB->quote($id, "integer").
			",".$ilDB->quote($row['ns_unique_id'], "text").
			",".$ilDB->quote($row['ns_book_fk'], "integer").
			",".$ilDB->quote($row['ns_type'], "text").
			",".$ilDB->quote($row['ns_tag_fk'], "integer").
			",".$ilDB->quote($row['ns_l'], "integer").
			",".$ilDB->quote($row['ns_r'], "integer").
			")"
		);

		$ilDB->manipulateF(
			"DELETE FROM xmlnestedsettmp_old WHERE ns_unique_id = %s AND ns_book_fk = %s AND ns_type = %s AND ns_tag_fk = %s AND ns_l = %s AND ns_r = %s",
			array('text', 'integer', 'text', 'integer', 'integer', 'integer'),
			array($row['ns_unique_id'], $row['ns_book_fk'], $row['ns_type'], $row['ns_tag_fk'], $row['ns_l'], $row['ns_r'])
		);
	}
}
?>
<#4735>
<?php
//step 4/4 xmlnestedset_tmp removes old table

if ($ilDB->tableExists('xmlnestedsettmp_old'))
{
	$ilDB->dropTable('xmlnestedsettmp_old');
}
?>
<#4736>
<?php
//step 1/5 xmlparam search for dublicates and store it in xmlparam_tmp

if ($ilDB->tableExists('xmlparam'))
{
	$res = $ilDB->query("
		SELECT first.tag_fk tag_fk, first.param_name param_name
		FROM xmlparam first
		WHERE EXISTS (
			SELECT second.tag_fk, second.param_name
			FROM xmlparam second
			WHERE first.tag_fk = second.tag_fk AND first.param_name = second.param_name
			GROUP BY second.tag_fk, second.param_name
			HAVING COUNT(second.tag_fk) > 1
		)
		GROUP BY first.tag_fk, first.param_name
	");

	if($ilDB->numRows($res))
	{
		if(!$ilDB->tableExists('xmlparam_tmp'))
		{
			$ilDB->createTable('xmlparam_tmp', array(
				'tag_fk' => array(
					'type'  => 'integer',
					'length'=> 4,
					'notnull' => true,
					'default' => 0
				),
				'param_name' => array(
					'type'  => 'text',
					'length'=> 50,
					'notnull' => true,
					'default' => 0
				)
			));
			$ilDB->addPrimaryKey('xmlparam_tmp', array('tag_fk','param_name'));
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			$ilDB->replace('xmlparam_tmp', array(), array(
				'tag_fk' => array('integer', $row['tag_fk']),
				'param_name' => array('text', $row['param_name'])
			));
		}
	}
}
?>
<#4737>
<?php
//step 2/5 xmlparam deletes dublicates stored in xmlparam_tmp

if ($ilDB->tableExists('xmlparam_tmp'))
{
	$res = $ilDB->query("
		SELECT tag_fk, param_name
		FROM xmlparam_tmp
	");

	while($row = $ilDB->fetchAssoc($res))
	{
		$res_data = $ilDB->query("
			SELECT *
			FROM xmlparam
			WHERE
			tag_fk = ".$ilDB->quote($row['tag_fk'] ,'integer')." AND
			param_name = ".$ilDB->quote($row['param_name'] ,'text')
		);
		$data = $ilDB->fetchAssoc($res_data);

		$ilDB->manipulate("DELETE FROM xmlparam WHERE".
			" tag_fk = " . $ilDB->quote($row['tag_fk'] ,'integer').
			" AND param_name = " . $ilDB->quote($row['param_name'] ,'text')
		);

		$ilDB->manipulate("INSERT INTO xmlparam (tag_fk,param_name,param_value) ".
			"VALUES ( ".
			$ilDB->quote($data['tag_fk'] ,'integer').', '.
			$ilDB->quote($data['param_name'] ,'text').', '.
			$ilDB->quote($data['param_value'] ,'text').
			")");

		$ilDB->manipulate("DELETE FROM xmlparam_tmp WHERE".
			" tag_fk = " . $ilDB->quote($row['tag_fk'] ,'integer').
			" AND param_name = " . $ilDB->quote($row['param_name'] ,'text')
		);
	}
}
?>
<#4738>
<?php
//step 3/5 xmlparam drop xmlparam_tmp

if( $ilDB->tableExists('xmlparam_tmp') )
{
	$ilDB->dropTable('xmlparam_tmp');
}
?>
<#4739>
<?php
//step 4/5 xmlparam drops not used indexes

if( $ilDB->indexExistsByFields('xmlparam', array('tag_fk')) )
{
	$ilDB->dropIndexByFields('xmlparam', array('tag_fk'));
}
?>
<#4740>
<?php
//step 5/5 xmlparam adding primary keys

if($ilDB->tableExists('xmlparam'))
{
	$ilDB->addPrimaryKey('xmlparam', array('tag_fk', 'param_name'));
}
?>
<#4741>
<?php
//step 1/1 tree_workspace adding primary key

if($ilDB->tableExists('tree_workspace'))
{
	if( $ilDB->indexExistsByFields('tree_workspace', array('child')) )
	{
		$ilDB->dropIndexByFields('tree_workspace', array('child'));
	}

	$ilDB->addPrimaryKey('tree_workspace', array('child'));
}
?>
<#4742>
<?php
if( !$ilDB->tableColumnExists('tst_active', 'last_pmode') )
{
	$ilDB->addTableColumn('tst_active', 'last_pmode', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));
}
?>
<#4743>
<?php
if( !$ilDB->tableColumnExists('tst_solutions', 'authorized') )
{
	$ilDB->addTableColumn('tst_solutions', 'authorized', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 1
	));

	$ilDB->queryF("UPDATE tst_solutions SET authorized = %s", array('integer'), array(1));
}
?>
<#4744>
<?php
if( $ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled') )
{
	$ilDB->dropTableColumn('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled');
}
?>
<#4745>
<?php
if( !$ilDB->tableColumnExists('tst_tests', 'force_inst_fb') )
{
	$ilDB->addTableColumn('tst_tests', 'force_inst_fb', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 0
	));
}
?>
<#4746>
<?php
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgramme.php");
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeAssignment.php");
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeProgress.php");

//ilStudyProgramme::installDB();

$fields = array(
	'obj_id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'last_change' => array(
		'notnull' => '1',
		'type' => 'timestamp',

	),
	'subtype_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'points' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'lp_mode' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'status' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),

);
/**
 * @var $ilDB ilDB
 */
if (! $ilDB->tableExists('prg_settings')) {
	$ilDB->createTable('prg_settings', $fields);
	$ilDB->addPrimaryKey('prg_settings', array( 'obj_id' ));
	if(!$ilDB->sequenceExists('prg_settings')) {
		$ilDB->createSequence('prg_settings');
	}
}

$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'usr_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'root_prg_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'last_change' => array(
		'notnull' => '1',
		'type' => 'timestamp',

	),
	'last_change_by' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),

);
if (! $ilDB->tableExists('prg_usr_assignments')) {
	$ilDB->createTable('prg_usr_assignments', $fields);
	$ilDB->addPrimaryKey('prg_usr_assignments', array( 'id' ));

	if (! $ilDB->sequenceExists('prg_usr_assignments')) {
		$ilDB->createSequence('prg_usr_assignments');
	}

}


$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'assignment_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'prg_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'usr_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'points' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'points_cur' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'status' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'completion_by' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'last_change' => array(
		'notnull' => '1',
		'type' => 'timestamp',

	),
	'last_change_by' => array(
		'type' => 'integer',
		'length' => '4',

	),

);
if (! $ilDB->tableExists('prg_usr_progress')) {
	$ilDB->createTable('prg_usr_progress', $fields);
	$ilDB->addPrimaryKey('prg_usr_progress', array( 'id' ));

	if (! $ilDB->sequenceExists('prg_usr_progress')) {
		$ilDB->createSequence('prg_usr_progress');
	}

}

// Active Record does not support tuples as primary keys, so we have to
// set those on our own.
$ilDB->addUniqueConstraint( ilStudyProgrammeProgress::returnDbTableName()
						  , array("assignment_id", "prg_id", "usr_id")
						  );

// ActiveRecord seems to not interpret con_is_null correctly, so we have to set
// it manually.
$ilDB->modifyTableColumn( ilStudyProgrammeProgress::returnDbTableName()
						, "completion_by"
						, array( "notnull" => false
							   , "default" => null
							   )
						);
$ilDB->modifyTableColumn( ilStudyProgrammeProgress::returnDbTableName()
						, "last_change_by"
						, array( "notnull" => false
							   , "default" => null
							   )
						);

require_once("./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php");
$obj_type_id = ilDBUpdateNewObjectType::addNewType("prg", "StudyProgramme");
$existing_ops = array("visible", "write", "copy", "delete", "edit_permission");
foreach ($existing_ops as $op) {
	$op_id = ilDBUpdateNewObjectType::getCustomRBACOperationId($op);
	ilDBUpdateNewObjectType::addRBACOperation($obj_type_id, $op_id);		
}

require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeAdvancedMetadataRecord.php");
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeType.php");
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeTypeTranslation.php");

$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'type_id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'rec_id' => array(
		'type' => 'integer',
		'length' => '4',

	),

);
if (! $ilDB->tableExists('prg_type_adv_md_rec')) {
	$ilDB->createTable('prg_type_adv_md_rec', $fields);
	$ilDB->addPrimaryKey('prg_type_adv_md_rec', array( 'id' ));

	if (! $ilDB->sequenceExists('prg_type_adv_md_rec')) {
		$ilDB->createSequence('prg_type_adv_md_rec');
	}

}

$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'default_lang' => array(
		'type' => 'text',
		'length' => '4',

	),
	'owner' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'create_date' => array(
		'notnull' => '1',
		'type' => 'timestamp',

	),
	'last_update' => array(
		'type' => 'timestamp',

	),
	'icon' => array(
		'type' => 'text',
		'length' => '255',

	),

);
if (! $ilDB->tableExists('prg_type')) {
	$ilDB->createTable('prg_type', $fields);
	$ilDB->addPrimaryKey('prg_type', array( 'id' ));

	if (! $ilDB->sequenceExists('prg_type')) {
		$ilDB->createSequence('prg_type');
	}

}

$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'prg_type_id' => array(
		'type' => 'integer',
		'length' => '4',

	),
	'lang' => array(
		'type' => 'text',
		'length' => '4',

	),
	'member' => array(
		'type' => 'text',
		'length' => '32',

	),
	'value' => array(
		'type' => 'text',
		'length' => '3500',

	),

);
if (! $ilDB->tableExists('prg_translations')) {
	$ilDB->createTable('prg_translations', $fields);
	$ilDB->addPrimaryKey('prg_translations', array( 'id' ));

	if (! $ilDB->sequenceExists('prg_translations')) {
		$ilDB->createSequence('prg_translations');
	}

}



?>
<#4747>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

// workaround to avoid error when using addAdminNode. Bug?
class EventHandler {
	public function raise($a_component, $a_event, $a_parameter = "") {
		// nothing to do...
	}
}
$GLOBALS['ilAppEventHandler'] = new EventHandler();

ilDBUpdateNewObjectType::addAdminNode('prgs', 'StudyProgrammeAdmin');

?>
<#4748>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4749>
<?php
if(!$ilDB->tableColumnExists("obj_members", "admin"))
{
        $ilDB->addTableColumn(
				"obj_members", 
				"admin", 
				array(
					'type' => 'integer',
					'length' => 1,
					'notnull' => FALSE,
					'default' => 0
        ));
}
if(!$ilDB->tableColumnExists("obj_members", "tutor"))
{
        $ilDB->addTableColumn(
				"obj_members", 
				"tutor", 
				array(
					'type' => 'integer',
					'length' => 1,
					'notnull' => FALSE,
					'default' => 0
        ));
}
if(!$ilDB->tableColumnExists("obj_members", "member"))
{
        $ilDB->addTableColumn(
				"obj_members", 
				"member", 
				array(
					'type' => 'integer',
					'length' => 2,
					'notnull' => FALSE,
					'default' => 0
        ));
}
?>
<#4750>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4751>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4752>
<?php
if(!$ilDB->sequenceExists('prg_settings')) {
	$ilDB->createSequence('prg_settings');
}
if (! $ilDB->sequenceExists('prg_usr_assignments')) {
	$ilDB->createSequence('prg_usr_assignments');
}
if (! $ilDB->sequenceExists('prg_usr_progress')) {
	$ilDB->createSequence('prg_usr_progress');
}
if (! $ilDB->sequenceExists('prg_type_adv_md_rec')) {
	$ilDB->createSequence('prg_type_adv_md_rec');
}
if (! $ilDB->sequenceExists('prg_type')) {
	$ilDB->createSequence('prg_type');
}
if (! $ilDB->sequenceExists('prg_translations')) {
	$ilDB->createSequence('prg_translations');
}
?>
<#4753>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$parent_types = array('root', 'cat', 'prg');
ilDBUpdateNewObjectType::addRBACCreate('create_prg', 'Create Study Programme', $parent_types);
?>
<#4754>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4755>
<?php
$ilDB->modifyTableColumn('il_wac_secure_path', 'path', array(
	'length'  => 64,
));
?>
<#4756>
<?php
$obj_type = 'icla'; 
$set      = $ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s",
	array('text'),
	array($obj_type)
);
while($row = $ilDB->fetchAssoc($set))
{
	$obj_id = $row['obj_id'];

	$refset = $ilDB->queryF(
		"SELECT ref_id FROM object_reference WHERE obj_id = %s",
		array('integer'),
		array($obj_id)
	);
	while($refrow = $ilDB->fetchAssoc($refset))
	{
		$ref_id = $refrow['ref_id'];

		$ilDB->manipulate("DELETE FROM crs_items WHERE obj_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM crs_items WHERE parent_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM rbac_log WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM rbac_pa WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM desktop_item WHERE item_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM conditions WHERE  target_ref_id = " . $ilDB->quote($ref_id, 'integer') . " OR trigger_ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM didactic_tpl_objs WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		// We know that all of these objects are leafs, so we can delete the records without determining the tree impl. and processing additional checks
		$ilDB->manipulate("DELETE FROM tree WHERE child = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM object_reference WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted object reference of type %s with ref_id %s.",
			$nr, $obj_type, $ref_id
		));
	}

	$ilDB->manipulate("DELETE FROM il_news_item WHERE context_obj_id = " . $ilDB->quote($obj_id, "integer") . " AND context_obj_type = " . $ilDB->quote($obj_type, "text"));
	$ilDB->manipulate("DELETE FROM il_block_setting WHERE block_id = " . $ilDB->quote($obj_id, "integer") . " AND type = " . $ilDB->quote("news", "text"));
	$ilDB->manipulate("DELETE FROM ut_lp_settings WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM ecs_import WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM dav_property WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM didactic_tpl_objs WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM object_description WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));

	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Deleted object of type %s with obj_id %s.",
		$nr, $obj_type, $obj_id
	));
}
?>
<#4757>
<?php
$obj_type = 'icrs';
$set      = $ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s",
	array('text'),
	array($obj_type)
);
while($row = $ilDB->fetchAssoc($set))
{
	$obj_id = $row['obj_id'];

	$refset = $ilDB->queryF(
		"SELECT ref_id FROM object_reference WHERE obj_id = %s",
		array('integer'),
		array($obj_id)
	);
	while($refrow = $ilDB->fetchAssoc($refset))
	{
		$ref_id = $refrow['ref_id'];

		$ilDB->manipulate("DELETE FROM crs_items WHERE obj_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM crs_items WHERE parent_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM rbac_log WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM rbac_pa WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM desktop_item WHERE item_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM conditions WHERE  target_ref_id = " . $ilDB->quote($ref_id, 'integer') . " OR trigger_ref_id = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM didactic_tpl_objs WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));
		// We know that all of these objects are leafs, so we can delete the records without determining the tree impl. and processing additional checks
		$ilDB->manipulate("DELETE FROM tree WHERE child = " . $ilDB->quote($ref_id, 'integer'));
		$ilDB->manipulate("DELETE FROM object_reference WHERE ref_id = " . $ilDB->quote($ref_id, 'integer'));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted object reference of type %s with ref_id %s.",
			$nr, $obj_type, $ref_id
		));
	}

	$ilDB->manipulate("DELETE FROM il_news_item WHERE context_obj_id = " . $ilDB->quote($obj_id, "integer") . " AND context_obj_type = " . $ilDB->quote($obj_type, "text"));
	$ilDB->manipulate("DELETE FROM il_block_setting WHERE block_id = " . $ilDB->quote($obj_id, "integer") . " AND type = " . $ilDB->quote("news", "text"));
	$ilDB->manipulate("DELETE FROM ut_lp_settings WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM ecs_import WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM dav_property WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM didactic_tpl_objs WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM object_description WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));
	$ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($obj_id, 'integer'));

	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Deleted object of type %s with obj_id %s.",
		$nr, $obj_type, $obj_id
	));
}
?>
<#4758>
<?php
$a_type = 'icla';
$set = $ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
	array('text', 'text'),
	array('typ', $a_type)
);
$row     = $ilDB->fetchAssoc($set);
$type_id = $row['obj_id'];
if($type_id)
{
	// RBAC

	// basic operations
	$ilDB->manipulate("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($type_id, "integer"));

	// creation operation
	$set           = $ilDB->query("SELECT ops_id" .
		" FROM rbac_operations " .
		" WHERE class = " . $ilDB->quote("create", "text") .
		" AND operation = " . $ilDB->quote("create_" . $a_type, "text"));
	$row           = $ilDB->fetchAssoc($set);
	$create_ops_id = $row["ops_id"];
	if($create_ops_id)
	{
		$ilDB->manipulate("DELETE FROM rbac_templates WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted rbac_templates create operation with ops_id %s for object type %s with obj_id %s.",
			$nr, $create_ops_id, $a_type, $type_id
		));

		// container create
		foreach(array("icrs") as $parent_type)
		{
			$pset = $ilDB->queryF(
				"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
				array('text', 'text'),
				array('typ', $parent_type)
			);
			$prow = $ilDB->fetchAssoc($pset);
			$parent_type_id = $prow['obj_id'];
			if($parent_type_id)
			{
				$ilDB->manipulate("DELETE FROM rbac_ta".
					" WHERE typ_id = ".$ilDB->quote($parent_type_id, "integer").
					" AND ops_id = ".$ilDB->quote($create_ops_id, "integer"));
			}
		}

		$ilDB->manipulate("DELETE FROM rbac_operations WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted create operation with ops_id %s for object type %s with obj_id %s.",
			$nr, $create_ops_id, $a_type, $type_id
		));
	}

	// Type
	$ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($type_id, "integer"));
	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Deleted object type %s with obj_id %s.",
		$nr, $a_type, $type_id
	));
}

$set = new ilSetting();
$set->delete("obj_dis_creation_".$a_type);
$set->delete("obj_add_new_pos_".$a_type);
$set->delete("obj_add_new_pos_grp_".$a_type);
?>
<#4759>
<?php
$a_type = 'icrs';
$set = $ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
	array('text', 'text'),
	array('typ', $a_type)
);
$row     = $ilDB->fetchAssoc($set);
$type_id = $row['obj_id'];
if($type_id)
{
	// RBAC

	// basic operations
	$ilDB->manipulate("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($type_id, "integer"));

	// creation operation
	$set           = $ilDB->query("SELECT ops_id" .
		" FROM rbac_operations " .
		" WHERE class = " . $ilDB->quote("create", "text") .
		" AND operation = " . $ilDB->quote("create_" . $a_type, "text"));
	$row           = $ilDB->fetchAssoc($set);
	$create_ops_id = $row["ops_id"];
	if($create_ops_id)
	{
		$ilDB->manipulate("DELETE FROM rbac_templates WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted rbac_templates create operation with ops_id %s for object type %s with obj_id %s.",
			$nr, $create_ops_id, $a_type, $type_id
		));

		// container create
		foreach(array("root", "cat", "crs", "grp", "fold") as $parent_type)
		{
			$pset = $ilDB->queryF(
				"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
				array('text', 'text'),
				array('typ', $parent_type)
			);
			$prow = $ilDB->fetchAssoc($pset);
			$parent_type_id = $prow['obj_id'];
			if($parent_type_id)
			{
				$ilDB->manipulate("DELETE FROM rbac_ta".
					" WHERE typ_id = ".$ilDB->quote($parent_type_id, "integer").
					" AND ops_id = ".$ilDB->quote($create_ops_id, "integer"));
			}
		}

		$ilDB->manipulate("DELETE FROM rbac_operations WHERE ops_id = ".$ilDB->quote($create_ops_id, "integer"));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Deleted create operation with ops_id %s for object type %s with obj_id %s.",
			$nr, $create_ops_id, $a_type, $type_id
		));
	}

	// Type
	$ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($type_id, "integer"));
	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Deleted object type %s with obj_id %s.",
		$nr, $a_type, $type_id
	));
}

$set = new ilSetting();
$set->delete("obj_dis_creation_".$a_type);
$set->delete("obj_add_new_pos_".$a_type);
$set->delete("obj_add_new_pos_grp_".$a_type);
?>
<#4760>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4761>
<?php
$mt_mod_incon_query_num = "
	SELECT COUNT(*) cnt
	FROM mail_obj_data
	INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id
	WHERE mail_tree.tree != mail_obj_data.user_id
";
$res  = $ilDB->query($mt_mod_incon_query_num);
$data = $ilDB->fetchAssoc($res);

if($data['cnt'] > 0)
{
	if(!$ilDB->tableExists('mail_tree_mod_migr'))
	{
		$ilDB->createTable('mail_tree_mod_migr', array(
			'usr_id' => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			)
		));
		$ilDB->addPrimaryKey('mail_tree_mod_migr', array('usr_id'));
	}
}
?>
<#4762>
<?php
if($ilDB->tableExists('mail_tree_mod_migr'))
{
	$db_step = $nr;

	$ps_create_mtmig_rec = $ilDB->prepareManip(
		"INSERT INTO mail_tree_mod_migr (usr_id) VALUES(?)",
		array('integer')
	);

	// Important: Use the field "tree" (usr_id in table: tree) AND the "user_id" (table: mail_obj_data)
	$mt_mod_incon_query = "
		SELECT DISTINCT(mail_tree.tree)
		FROM mail_obj_data
		INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id
		LEFT JOIN mail_tree_mod_migr ON mail_tree_mod_migr.usr_id = mail_tree.tree
		WHERE mail_tree.tree != mail_obj_data.user_id AND mail_tree_mod_migr.usr_id IS NULL
	";
	$res = $ilDB->query($mt_mod_incon_query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($ps_create_mtmig_rec, array($row['tree']));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Detected wrong child in table 'mail_tree' for user (field: tree) %s .",
			$db_step, $row['tree']
		));
	}

	$mt_mod_incon_query = "
		SELECT DISTINCT(mail_obj_data.user_id)
		FROM mail_obj_data
		INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id
		LEFT JOIN mail_tree_mod_migr ON mail_tree_mod_migr.usr_id = mail_obj_data.user_id
		WHERE mail_tree.tree != mail_obj_data.user_id AND mail_tree_mod_migr.usr_id IS NULL
	";
	$res = $ilDB->query($mt_mod_incon_query);
	while($row = $ilDB->fetchAssoc($res))
	{
		$ilDB->execute($ps_create_mtmig_rec, array($row['user_id']));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Detected missing child in table 'mail_tree' for user (field: tree) %s .",
			$db_step, $row['user_id']
		));
	}
}
?>
<#4763>
<?php
if($ilDB->tableExists('mail_tree_mod_migr'))
{
	$db_step = $nr;

	$ps_del_tree_entries = $ilDB->prepareManip(
		"DELETE FROM mail_tree WHERE tree = ?",
		array('integer')
	);

	$ps_sel_fold_entries = $ilDB->prepare(
		"SELECT obj_id, title, m_type FROM mail_obj_data WHERE user_id = ?",
		array('integer')
	);

	$default_folders_title_to_type_map = array(
		'a_root'   => 'root',
		'b_inbox'  => 'inbox',
		'c_trash'  => 'trash',
		'd_drafts' => 'drafts',
		'e_sent'   => 'sent',
		'z_local'  => 'local'
	);
	$default_folder_type_to_title_map = array_flip($default_folders_title_to_type_map);

	$ps_in_fold_entry = $ilDB->prepareManip(
		"INSERT INTO mail_obj_data (obj_id, user_id, title, m_type) VALUES(?, ?, ?, ?)",
		array('integer','integer', 'text', 'text')
	);

	$ps_in_tree_entry = $ilDB->prepareManip(
		"INSERT INTO mail_tree (tree, child, parent, lft, rgt, depth) VALUES(?, ?, ?, ?, ?, ?)",
		array('integer', 'integer', 'integer', 'integer', 'integer', 'integer')
	);

	$ps_sel_tree_entry = $ilDB->prepare(
		"SELECT rgt, lft, parent FROM mail_tree WHERE child = ? AND tree = ?",
		array('integer', 'integer')
	);

	$ps_up_tree_entry = $ilDB->prepareManip(
		"UPDATE mail_tree SET lft = CASE WHEN lft > ? THEN lft + 2 ELSE lft END, rgt = CASE WHEN rgt >= ? THEN rgt + 2 ELSE rgt END WHERE tree = ?",
		array('integer', 'integer', 'integer')
	);

	$ps_del_mtmig_rec = $ilDB->prepareManip(
		"DELETE FROM mail_tree_mod_migr WHERE usr_id = ?",
		array('integer')
	);

	$res = $ilDB->query("SELECT usr_id FROM mail_tree_mod_migr");
	$num = $ilDB->numRows($res);

	$GLOBALS['ilLog']->write(sprintf(
		"DB Step %s: Found %s duplicates in table 'mail_tree'.",
		$db_step, $num
	));

	// We need a first loop to delete all affected mail trees
	$i = 0;
	while($row = $ilDB->fetchAssoc($res))
	{
		++$i;

		$usr_id = $row['usr_id'];

		$ilDB->execute($ps_del_tree_entries, array($usr_id));
		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s: Started 'mail_tree' migration for user %s. Deleted all records referring this user (field: tree)",
			$db_step, $usr_id
		));
	}

	$res = $ilDB->query("SELECT usr_id FROM mail_tree_mod_migr");

	$i = 0;
	while($row = $ilDB->fetchAssoc($res))
	{
		++$i;

		$usr_id = $row['usr_id'];

		$fold_res = $ilDB->execute($ps_sel_fold_entries, array($usr_id));
		$user_folders         = array();
		$user_default_folders = array();
		while($fold_row = $ilDB->fetchAssoc($fold_res))
		{
			$user_folders[$fold_row['obj_id']] = $fold_row;
			if(isset($default_folder_type_to_title_map[strtolower($fold_row['m_type'])]))
			{
				$user_default_folders[$fold_row['m_type']] = $fold_row['title'];
			}
		}

		// Create missing default folders
		$folders_to_create = array_diff_key($default_folder_type_to_title_map, $user_default_folders);
		foreach($folders_to_create as $type => $title)
		{
			$folder_id = $ilDB->nextId('mail_obj_data');
			$ilDB->execute($ps_in_fold_entry, array($folder_id, $usr_id, $title, $type));

			$user_folders[$folder_id] = array(
				'obj_id' => $folder_id,
				'user_id'=> $usr_id,
				'title'  => $title,
				'm_type' => $type
			);
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created 'mail_obj_data' record (missing folder type): %s, %s, %s, %s .",
				$db_step, $i, $folder_id, $usr_id, $title, $type
			));
		}

		// Create a new root folder node
		$root_id  = null;
		foreach($user_folders as $folder_id => $data)
		{
			if('root' != $data['m_type'])
			{
				continue;
			}

			$root_id = $folder_id;
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $root_id, 0, 1, 2, 1));

			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created root node with id %s for user %s in 'mail_tree'.",
				$db_step, $i, $root_id, $usr_id
			));
			break;
		}

		if(!$root_id)
		{
			// Did not find root folder, skip user and move to the next one
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: No root folder found for user %s . Skipped user.",
				$db_step, $i, $usr_id
			));
			continue;
		}

		$custom_folder_root_id = null;
		// Create all default folders below 'root'
		foreach($user_folders as $folder_id => $data)
		{
			if('root' == $data['m_type'] || !isset($default_folder_type_to_title_map[strtolower($data['m_type'])]))
			{
				continue;
			}

			if(null === $custom_folder_root_id && 'local' == $data['m_type'])
			{
				$custom_folder_root_id = $folder_id;
			}

			$res_parent = $ilDB->execute($ps_sel_tree_entry, array($root_id, $usr_id));
			$parent_row = $ilDB->fetchAssoc($res_parent);

			$right = $parent_row['rgt'];
			$lft   = $right;
			$rgt   = $right + 1;

			$ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $root_id, $lft, $rgt, 2));
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created node with id %s (lft: %s | rgt: %s) for user %s in 'mail_tree'.",
				$db_step, $i, $folder_id, $lft, $rgt, $usr_id
			));

		}

		if(!$custom_folder_root_id)
		{
			// Did not find custom folder root, skip user and move to the next one
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: No custom folder root found for user %s . Skipped user.",
				$db_step, $i, $usr_id
			));
			continue;
		}

		// Create all custom folders below 'local'
		foreach($user_folders as $folder_id => $data)
		{
			if(isset($default_folder_type_to_title_map[strtolower($data['m_type'])]))
			{
				continue;
			}

			$res_parent = $ilDB->execute($ps_sel_tree_entry, array($custom_folder_root_id, $usr_id));
			$parent_row = $ilDB->fetchAssoc($res_parent);

			$right = $parent_row['rgt'];
			$lft   = $right;
			$rgt   = $right + 1;

			$ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
			$ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $custom_folder_root_id, $lft, $rgt, 3));
			$GLOBALS['ilLog']->write(sprintf(
				"DB Step %s, iteration %s: Created custom folder node with id %s (lft: %s | rgt: %s) for user % in 'mail_tree'.",
				$db_step, $i, $folder_id, $lft, $rgt, $usr_id
			));
		}

		// Tree completely created, remove migration record
		$ilDB->execute($ps_del_mtmig_rec, array($usr_id));

		$GLOBALS['ilLog']->write(sprintf(
			"DB Step %s, iteration %s: Finished 'mail_tree' migration for user %s .",
			$db_step, $i, $usr_id
		));
	}

	$res = $ilDB->query("SELECT usr_id FROM mail_tree_mod_migr");
	$num = $ilDB->numRows($res);
	if($num > 0)
	{
		die("There are still wrong child entries in table 'mail_tree'. Please execute this database update step again.");
	}
}

if($ilDB->tableExists('mail_tree_mod_migr'))
{
	$ilDB->dropTable('mail_tree_mod_migr');
}

$mt_mod_incon_query_num = "
	SELECT COUNT(*) cnt
	FROM mail_obj_data
	INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id
	WHERE mail_tree.tree != mail_obj_data.user_id
";
$res  = $ilDB->query($mt_mod_incon_query_num);
$data = $ilDB->fetchAssoc($res);
if($data['cnt'] > 0)
{
	die("There are still wrong child entries in table 'mail_tree'. Please execute database update step 4761 again. Execute the following SQL string manually: UPDATE settings SET value = 4760 WHERE keyword = 'db_version'; ");
}
?>
<#4764>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4765>
<?php
if(!$ilDB->indexExistsByFields('frm_posts_tree', array('thr_fk')))
{
	$ilDB->addIndex('frm_posts_tree', array('thr_fk'), 'i1');
}
?>
<#4766>
<?php
if(!$ilDB->indexExistsByFields('frm_posts_tree', array('pos_fk')))
{
	$ilDB->addIndex('frm_posts_tree', array('pos_fk'), 'i2');
}
?>
<#4767>
<?php

	if(!$ilDB->indexExistsByFields('role_data',array('auth_mode')))
	{
		$ilDB->addIndex('role_data',array('auth_mode'),'i1');
	}
?>
<#4768>
<?php
$ilDB->modifyTableColumn('cmi_gobjective', 'objective_id', array(
	'length'  => 253,
));
?>
<#4769>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4770>
<?php
	$query = 'INSERT INTO log_components (component_id) VALUES ('.$ilDB->quote('log_root', 'text').')';
	$ilDB->manipulate($query);
?>

<#4771>
<?php

// remove role entries in obj_members
$query = 'update obj_members set admin = '.$ilDB->quote(0,'integer').', '.
		'tutor = '.$ilDB->quote(0,'integer').', member = '.$ilDB->quote(0,'integer');
$ilDB->manipulate($query);

// iterate through all courses
$offset = 0;
$limit = 100;
do
{
	$query = 'SELECT obr.ref_id, obr.obj_id FROM object_reference obr '.
			'join object_data obd on obr.obj_id = obd.obj_id where (type = '.$ilDB->quote('crs','text').' or type = '.$ilDB->quote('grp','text').') '.
			$ilDB->setLimit($limit, $offset);
	$res = $ilDB->query($query);
	
	if(!$res->numRows())
	{
		break;
	}
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		// find course members roles
		$query = 'select rol_id, title from rbac_fa '.
				'join object_data on rol_id = obj_id '.
				'where parent = '.$ilDB->quote($row->ref_id,'integer').' '.
				'and assign = '.$ilDB->quote('y','text');
		$rol_res = $ilDB->query($query);
		while($rol_row = $rol_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// find users which are not assigned to obj_members and create a default entry
			$query = 'select ua.usr_id from rbac_ua ua '.
					'left join obj_members om on ua.usr_id = om.usr_id '.
					'where om.usr_id IS NULL '.
					'and rol_id = '.$ilDB->quote($rol_row->rol_id,'integer').' '.
					'and om.obj_id = '.$ilDB->quote($row->obj_id,'integer');
			$ua_res = $ilDB->query($query);
			while($ua_row = $ua_res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$query = 'insert into obj_members (obj_id, usr_id) '.
						'values('.
						$ilDB->quote($row->obj_id,'integer').', '.
						$ilDB->quote($ua_row->usr_id,'integer').' '.
						')';
				$ilDB->manipulate($query);
						
			}
			
			// find users which are assigned to obj_members and update their role assignment
			$query = 'select * from rbac_ua ua '.
					'left join obj_members om on ua.usr_id = om.usr_id '.
					'where om.usr_id IS NOT NULL '.
					'and rol_id = '.$ilDB->quote($rol_row->rol_id,'integer').' '.
					'and om.obj_id = '.$ilDB->quote($row->obj_id,'integer');
			$ua_res = $ilDB->query($query);
			while($ua_row = $ua_res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$admin = $tutor = $member = 0;
				switch(substr($rol_row->title,0,8))
				{
					case 'il_crs_a':
					case 'il_grp_a':
						$admin = 1;
						break;
					
					case 'il_crs_t':
						$tutor = 1;
						break;
					
					default:
					case 'il_grp_m':
					case 'il_crs_m':
						$member = 1;
						break;
				}
				
				$query = 'update obj_members '.
						'set admin = admin  + '.$ilDB->quote($admin,'integer').', '.
						'tutor = tutor + '.$ilDB->quote($tutor,'integer').', '.
						'member = member + '.$ilDB->quote($member,'integer').' '.
						'WHERE usr_id = '.$ilDB->quote($ua_row->usr_id,'integer').' '.
						'AND obj_id = '.$ilDB->quote($row->obj_id,'integer');
				$ilDB->manipulate($query);
			}
		}
	}
		// increase offset
	$offset += $limit;
}
while(TRUE);

?>
<#4772>
<?php

if(!$ilDB->indexExistsByFields('obj_members',array('usr_id')))
{
	$ilDB->addIndex('obj_members',array('usr_id'),'i1');
}

?>
<#4773>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4774>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4775>
<?php
$ilDB->modifyTableColumn(
	'il_dcl_field',
	'description',
	array("type" => "clob")
);
?>
<#4776>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4777>
<?php

	// see #3172
	if($ilDB->getDBType() == 'oracle')
	{
		if(!$ilDB->tableColumnExists('svy_qst_matrixrows', 'title_tmp'))
		{
			$ilDB->addTableColumn('svy_qst_matrixrows', 'title_tmp', array(
				"type" => "text",
				"length" => 1000,
				"notnull" => false,
				"default" => null)
			);			
			$ilDB->manipulate('UPDATE svy_qst_matrixrows SET title_tmp = title');			
			$ilDB->dropTableColumn('svy_qst_matrixrows', 'title');			
			$ilDB->renameTableColumn('svy_qst_matrixrows', 'title_tmp', 'title');
		}			
	}
	else
	{
		$ilDB->modifyTableColumn('svy_qst_matrixrows','title', array(
			"type" => "text", 
			"length" => 1000, 
			"default" => null, 
			"notnull" => false)
		);
	}
	
?>
<#4778>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4779>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4780>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4781>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4782>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4783>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4784>
<?php
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	$obj_type_id = ilDBUpdateNewObjectType::getObjectTypeId("prg");
	$existing_ops = array("read");
	foreach ($existing_ops as $op) {
		$op_id = ilDBUpdateNewObjectType::getCustomRBACOperationId($op);
		ilDBUpdateNewObjectType::addRBACOperation($obj_type_id, $op_id);
	}
?>
<#4785>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4786>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4787>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('cadm', 'Contact');
?>
<#4788>
<?php
$ilSetting = new ilSetting('buddysystem');
$ilSetting->set('enabled', 1);
?>
<#4789>
<?php
$stmt = $ilDB->prepareManip('INSERT INTO usr_pref (usr_id, keyword, value) VALUES(?, ?, ?)', array('integer', 'text', 'text'));

$notin = $ilDB->in('usr_data.usr_id', array(13), true, 'integer');
$query = 'SELECT usr_data.usr_id FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s WHERE usr_pref.keyword IS NULL AND ' . $notin;
$res   = $ilDB->queryF($query, array('text'), array('bs_allow_to_contact_me'));
while($row = $ilDB->fetchAssoc($res))
{
	$ilDB->execute($stmt, array($row['usr_id'], 'bs_allow_to_contact_me', 'y'));
}
?>
<#4790>
<?php

	if(!$ilDB->indexExistsByFields('page_question',array('question_id')))
	{
		$ilDB->addIndex('page_question',array('question_id'),'i2');
	}
?>
<#4791>
<?php
	if(!$ilDB->indexExistsByFields('help_tooltip', array('tt_id', 'module_id')))
	{
		$ilDB->addIndex('help_tooltip', array('tt_id', 'module_id'), 'i1');
	}
?>
<#4792>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4793>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4794>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4795>
<?php

	$query = 'SELECT server_id FROM ldap_server_settings';
	$res = $ilDB->query($query);
	
	$server_id = 0;
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$server_id = $row->server_id;
	}
	
	if($server_id)
	{
		$query = 'UPDATE usr_data SET auth_mode = '.$ilDB->quote('ldap_'.(int) $server_id,'text').' '.
				'WHERE auth_mode = '.$ilDB->quote('ldap','text');
		$ilDB->manipulate($query);
	}
?>
<#4796>
<?php
$delQuery = "
	DELETE FROM tax_node_assignment
	WHERE node_id = %s
	AND component = %s
	AND obj_id = %s
	AND item_type = %s
	AND item_id = %s
";

$types = array('integer', 'text', 'integer', 'text', 'integer');

$selQuery = "
	SELECT tax_node_assignment.* FROM tax_node_assignment
	LEFT JOIN qpl_questions ON question_id = item_id
	WHERE component = %s
	AND item_type = %s
	AND question_id IS NULL
";

$res = $ilDB->queryF($selQuery, array('text', 'text'), array('qpl', 'quest'));

while($row = $ilDB->fetchAssoc($res))
{
	$ilDB->manipulateF($delQuery, $types, array(
		$row['node_id'], $row['component'], $row['obj_id'], $row['item_type'], $row['item_id'] 
	));
}
?>
<#4797>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4798>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4799>
<?php

	if(!$ilDB->tableColumnExists('rbac_fa', 'blocked'))
	{
		$ilDB->addTableColumn('rbac_fa', 'blocked', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0)
		);
	}
?>
<#4800>
<?php
$indices = array(
	'il_dcl_record_field' => array(
		'record_id',
		'field_id'
	),
	'il_dcl_record' => array( 'table_id' ),
	'il_dcl_stloc1_value' => array( 'record_field_id' ),
	'il_dcl_stloc2_value' => array( 'record_field_id' ),
	'il_dcl_stloc3_value' => array( 'record_field_id' ),
	'il_dcl_field' => array(
		'datatype_id',
		'table_id'
	),
	'il_dcl_field_prop' => array(
		'field_id',
		'datatype_prop_id'
	),
	'il_dcl_viewdefinition' => array( 'view_id' ),
	'il_dcl_view' => array(
		'table_id',
		'type'
	),
	'il_dcl_data' => array( 'main_table_id' ),
	'il_dcl_table' => array( 'obj_id' ),
);

$manager = $ilDB->db->loadModule('Manager');

foreach ($indices as $table_name => $field_names) {
	if ($manager) {
		foreach ($manager->listTableIndexes($table_name) as $idx_name) {
			if ($ilDB->getDbType() == 'oracle' || $ilDB->getDbType() == 'postgres') {
				$manager->getDBInstance()->exec('DROP INDEX ' . $idx_name);
				$manager->getDBInstance()->exec('DROP INDEX ' . $idx_name . '_idx');
			} else {
				$manager->getDBInstance()->exec('DROP INDEX ' . $idx_name . ' ON ' . $table_name);
				$manager->getDBInstance()->exec('DROP INDEX ' . $idx_name . '_idx ON ' . $table_name);
			}
		}
		foreach ($field_names as $i => $field_name) {
			$ilDB->addIndex($table_name, array( $field_name ), 'i' . ($i + 1));
		}
	}
}
?>
<#4801>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4802>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4803>
<?php
if(!$ilDB->tableColumnExists('adl_shared_data','cp_node_id')) 
{
	$ilDB->addTableColumn(
        'adl_shared_data',
        'cp_node_id',
	array (
		"type" => "integer",
		"length" => 4,
		"notnull" => true,
		"default" => "0"
        ));

	$dataRes = $ilDB->query(
		"select cp_datamap.cp_node_id, cp_datamap.slm_id, cp_datamap.target_id from cp_datamap, adl_shared_data "
		."WHERE cp_datamap.slm_id = adl_shared_data.slm_id AND cp_datamap.target_id = adl_shared_data.target_id"
		);
	while( $row = $ilDB->fetchAssoc($dataRes) )
	{
		$ilDB->manipulateF(
			"UPDATE adl_shared_data SET cp_node_id = %s WHERE slm_id = %s AND target_id = %s",
			array("integer","integer","text"),
			array($row["cp_node_id"],$row["slm_id"],$row["target_id"])
		);
	}
	$ilDB->manipulate("delete from adl_shared_data WHERE cp_node_id = 0");
	
	$ilDB->addPrimaryKey("adl_shared_data", array('cp_node_id','user_id'));
}
?>
<#4804>
<?php
	$query = "show index from sahs_sc13_seq_templ where Key_name = 'PRIMARY'";
	$res = $ilDB->query($query);
	if (!$ilDB->numRows($res)) {
		$ilDB->addPrimaryKey('sahs_sc13_seq_templ', array('seqnodeid','id'));
	}
?>
<#4805>
<?php
	$query = "show index from sahs_sc13_seq_tree where Key_name = 'PRIMARY'";
	$res = $ilDB->query($query);
	if (!$ilDB->numRows($res)) {
		$ilDB->addPrimaryKey('sahs_sc13_seq_tree', array('child','importid','parent'));
	}
?>
<#4806>
<?php
	$query = "show index from sahs_sc13_tree where Key_name = 'PRIMARY'";
	$res = $ilDB->query($query);
	if (!$ilDB->numRows($res)) {
		$ilDB->addPrimaryKey('sahs_sc13_tree', array('child','parent','slm_id'));
	}
?>
<#4807>
<?php
	$query = "show index from scorm_tree where Key_name = 'PRIMARY'";
	$res = $ilDB->query($query);
	if (!$ilDB->numRows($res)) {
		$ilDB->addPrimaryKey('scorm_tree', array('slm_id','child'));
	}
?>
<#4808>
<?php
	$ilDB->modifyTableColumn('cp_tree', 'obj_id', array(
		"notnull" => true,
		"default" => "0"
	));
	$ilDB->modifyTableColumn('cp_tree', 'child', array(
		"notnull" => true,
		"default" => "0"
	));

	$query = "show index from cp_tree where Key_name = 'PRIMARY'";
	$res = $ilDB->query($query);
	if (!$ilDB->numRows($res)) {
		$ilDB->addPrimaryKey('cp_tree', array('obj_id','child'));
	}

	
	

//////////////////////////////////////////////////////////////////
//
//      PLEASE DO  NOT ADD ANY ADDITIONAL STEPS IN THIS BRANCH
//
//      USE 5_1_hotfixes.php (and corresponding trunk steps) 
//
//////////////////////////////////////////////////////////////////

?>
