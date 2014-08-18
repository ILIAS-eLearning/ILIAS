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
$ilDB->insert('il_dcl_datatype_prop', array(
    'id' => array('integer', 14),
    'datatype_id' => array('integer', 2),
    'title' => array('text', 'link_detail_page'),
    'inputformat' => array('integer', 4),
));
$ilDB->insert('il_dcl_datatype_prop', array(
    'id' => array('integer', 15),
    'datatype_id' => array('integer', 9),
    'title' => array('text', 'link_detail_page'),
    'inputformat' => array('integer', 4),
));
?>
<#4274>
<?php>

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