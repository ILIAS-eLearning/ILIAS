<#4183>
<?php
    if (!$ilDB->tableColumnExists('il_poll', 'result_sort')) {
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
    if (!$ilDB->tableColumnExists('il_poll', 'non_anon')) {
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

if (!$ilDB->tableColumnExists('il_blog', 'abs_shorten')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_shorten_len')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten_len',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_image')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_image',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_img_width')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_width',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_img_height')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_height',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

?>
<#4186>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4187>
<?php

if (!$ilDB->tableExists('usr_data_multi')) {
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
$set = $ilDB->query("SELECT od.owner, prtf.id prtf_id, pref.value public" .
    ", MIN(acl.object_id) acl_type" .
    " FROM usr_portfolio prtf" .
    " JOIN object_data od ON (od.obj_id = prtf.id" .
    " AND od.type = " . $ilDB->quote("prtf", "text") . ")" .
    " LEFT JOIN usr_portf_acl acl ON (acl.node_id = prtf.id)" .
    " LEFT JOIN usr_pref pref ON (pref.usr_id = od.owner" .
    " AND pref.keyword = " . $ilDB->quote("public_profile", "text") . ")" .
    " WHERE prtf.is_default = " . $ilDB->quote(1, "integer") .
    " GROUP BY od.owner, prtf.id, pref.value");
while ($row = $ilDB->fetchAssoc($set)) {
    $acl_type = (int) $row["acl_type"];
    $pref = trim($row["public"]);
    $user_id = (int) $row["owner"];
    $prtf_id = (int) $row["prtf_id"];

    if (!$user_id || !$prtf_id) { // #12862
        continue;
    }

    // portfolio is not published, remove as profile
    if ($acl_type >= 0) {
        $ilDB->manipulate("UPDATE usr_portfolio" .
            " SET is_default = " . $ilDB->quote(0, "integer") .
            " WHERE id = " . $ilDB->quote($prtf_id, "integer"));
        $new_pref = "n";
    }
    // check if portfolio sharing matches user preference
    else {
        // registered vs. published
        $new_pref = ($acl_type < -1)
            ? "g"
            : "y";
    }

    if ($pref) {
        if ($pref != $new_pref) {
            $ilDB->manipulate("UPDATE usr_pref" .
                " SET value = " . $ilDB->quote($new_pref, "text") .
                " WHERE usr_id = " . $ilDB->quote($user_id, "integer") .
                " AND keyword = " . $ilDB->quote("public_profile", "text"));
        }
    } else {
        $ilDB->manipulate("INSERT INTO usr_pref (usr_id, keyword, value) VALUES" .
            " (" . $ilDB->quote($user_id, "integer") .
            ", " . $ilDB->quote("public_profile", "text") .
            ", " . $ilDB->quote($new_pref, "text") . ")");
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
if (!$ilDB->tableColumnExists('tst_active', 'last_finished_pass')) {
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

if (!$ilDB->uniqueConstraintExists('tst_pass_result', array('active_fi', 'pass'))) {
    $groupRes = $ilDB->query("
		SELECT COUNT(*), active_fi, pass FROM tst_pass_result GROUP BY active_fi, pass HAVING COUNT(*) > 1
	");

    $ilSetting = new ilSetting();

    $setting = $ilSetting->get('tst_passres_dupl_del_warning', 0);

    while ($groupRow = $ilDB->fetchAssoc($groupRes)) {
        if (!$setting) {
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
            array('integer', 'integer'),
            array($groupRow['active_fi'], $groupRow['pass'])
        );

        $passResults = array();
        $latestTimstamp = 0;

        while ($dataRow = $ilDB->fetchAssoc($dataRes)) {
            if ($latestTimstamp < $dataRow['tstamp']) {
                $latestTimstamp = $dataRow['tstamp'];
                $passResults = array();
            }

            $passResults[] = $dataRow;
        }

        $bestPointsRatio = 0;
        $bestPassResult = null;

        foreach ($passResults as $passResult) {
            if ($passResult['maxpoints'] > 0) {
                $pointsRatio = $passResult['points'] / $passResult['maxpoints'];
            } else {
                $pointsRatio = 0;
            }

            if ($bestPointsRatio <= $pointsRatio) {
                $bestPointsRatio = $pointsRatio;
                $bestPassResult = $passResult;
            }
        }

        $dataRes = $ilDB->manipulateF(
            "DELETE FROM tst_pass_result WHERE active_fi = %s AND pass = %s",
            array('integer', 'integer'),
            array($groupRow['active_fi'], $groupRow['pass'])
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
if (!$ilDB->uniqueConstraintExists('tst_sequence', array('active_fi', 'pass'))) {
    $groupRes = $ilDB->query("
		SELECT COUNT(*), active_fi, pass FROM tst_sequence GROUP BY active_fi, pass HAVING COUNT(*) > 1
	");

    $ilSetting = new ilSetting();

    $setting = $ilSetting->get('tst_seq_dupl_del_warning', 0);

    while ($groupRow = $ilDB->fetchAssoc($groupRes)) {
        if (!$setting) {
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
            array('integer', 'integer'),
            array($groupRow['active_fi'], $groupRow['pass'])
        );

        while ($dataRow = $ilDB->fetchAssoc($dataRes)) {
            $ilDB->manipulateF(
                "DELETE FROM tst_sequence WHERE active_fi = %s AND pass = %s",
                array('integer', 'integer'),
                array($groupRow['active_fi'], $groupRow['pass'])
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

    $ilDB->dropIndexByFields('cal_auth_token', array('user_id'));

?>

<#4195>
<?php

    if (!$ilDB->indexExistsByFields('cal_shared', array('obj_id','obj_type'))) {
        $ilDB->addIndex('cal_shared', array('obj_id','obj_type'), 'i1');
    }
?>
<#4196>
<?php

    $ilDB->dropIndexByFields('cal_entry_responsible', array('cal_id','user_id'));
    $ilDB->addPrimaryKey('cal_entry_responsible', array('cal_id','user_id'));
?>
<#4197>
<?php

    $ilDB->dropIndexByFields('cal_entry_responsible', array('cal_id'));
    $ilDB->dropIndexByFields('cal_entry_responsible', array('user_id'));

?>
<#4198>
<?php

    $ilDB->dropIndexByFields('cal_cat_assignments', array('cal_id','cat_id'));
    $ilDB->addPrimaryKey('cal_cat_assignments', array('cal_id','cat_id'));

?>

<#4199>
<?php
    if (!$ilDB->indexExistsByFields('cal_entries', array('last_update'))) {
        $ilDB->addIndex('cal_entries', array('last_update'), 'i1');
    }
?>
<#4200>
<?php

    $query = 'SELECT value from settings where module = ' . $ilDB->quote('common', 'text') .
            'AND keyword = ' . $ilDB->quote('main_tree_impl', 'text');
    $res = $ilDB->query($query);

    $tree_impl = 'ns';
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $tree_impl = $row->value;
    }

    if ($tree_impl == 'mp') {
        if (!$ilDB->indexExistsByFields('tree', array('path'))) {
            $ilDB->dropIndexByFields('tree', array('lft'));
            $ilDB->addIndex('tree', array('path'), 'i4');
        }
    }
?>
<#4201>
<?php
    if (!$ilDB->indexExistsByFields('booking_reservation', array('user_id'))) {
        $ilDB->addIndex('booking_reservation', array('user_id'), 'i1');
    }
?>
<#4202>
<?php
    if (!$ilDB->indexExistsByFields('booking_reservation', array('object_id'))) {
        $ilDB->addIndex('booking_reservation', array('object_id'), 'i2');
    }
?>
<#4203>
<?php
    if (!$ilDB->indexExistsByFields('cal_entries', array('context_id'))) {
        $ilDB->addIndex('cal_entries', array('context_id'), 'i2');
    }
?>
<#4204>
<?php
if (!$ilDB->tableColumnExists('il_poll', 'show_results_as')) {
    $ilDB->addTableColumn('il_poll', 'show_results_as', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 1
    ));
}
if (!$ilDB->tableColumnExists('il_poll', 'show_comments')) {
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

        while ($row = $ilDB->fetchAssoc($result)) {
            $broken_sequences[] = array('active' => $row['active'], 'holes' => abs($row['pass']));
        }

        $stmt_inc_pass_res = $ilDB->prepareManip('UPDATE tst_pass_result 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
        $stmt_inc_man_fb = $ilDB->prepareManip('UPDATE tst_manual_fb 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
        $stmt_inc_seq = $ilDB->prepareManip('UPDATE tst_sequence 		SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
        $stmt_inc_sol = $ilDB->prepareManip('UPDATE tst_solutions 	SET pass = pass + 1 WHERE active_fi = ?', array('integer'));
        $stmt_inc_times = $ilDB->prepareManip('UPDATE tst_times 		SET pass = pass + 1 WHERE active_fi = ?', array('integer'));

        $stmt_sel_passes = $ilDB->prepare('SELECT pass FROM tst_pass_result WHERE active_fi = ? ORDER BY pass', array('integer'));

        $stmt_dec_pass_res = $ilDB->prepareManip('UPDATE tst_pass_result 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
        $stmt_dec_man_fb = $ilDB->prepareManip('UPDATE tst_manual_fb 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
        $stmt_dec_seq = $ilDB->prepareManip('UPDATE tst_sequence 		SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
        $stmt_dec_sol = $ilDB->prepareManip('UPDATE tst_solutions 	SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));
        $stmt_dec_times = $ilDB->prepareManip('UPDATE tst_times 		SET pass = pass - 1 WHERE active_fi = ? AND pass > ?', array('integer', 'integer'));

        // Iterate over affected passes
        foreach ($broken_sequences as $broken_sequence) {
            // Recreate the unbroken, pre-renumbering state by incrementing all passes on all affected tables for the detected broken active_fi.
            for ($i = 1; $i <= $broken_sequence['holes']; $i++) {
                $ilDB->execute($stmt_inc_pass_res, array($broken_sequence['active']));
                $ilDB->execute($stmt_inc_man_fb, array($broken_sequence['active']));
                $ilDB->execute($stmt_inc_seq, array($broken_sequence['active']));
                $ilDB->execute($stmt_inc_sol, array($broken_sequence['active']));
                $ilDB->execute($stmt_inc_times, array($broken_sequence['active']));
            }

            // Detect the holes and renumber correctly on all affected tables.
            for ($i = 1; $i <= $broken_sequence['holes']; $i++) {
                $result = $ilDB->execute($stmt_sel_passes, array($broken_sequence['active']));
                $index = 0;
                while ($row = $ilDB->fetchAssoc($result)) {
                    if ($row['pass'] == $index) {
                        $index++;
                        continue;
                    }

                    // Reaching here, there is a missing index, now decrement all higher passes, preserving additional holes.
                    $ilDB->execute($stmt_dec_pass_res, array($broken_sequence['active'], $index));
                    $ilDB->execute($stmt_dec_man_fb, array($broken_sequence['active'], $index));
                    $ilDB->execute($stmt_dec_seq, array($broken_sequence['active'], $index));
                    $ilDB->execute($stmt_dec_sol, array($broken_sequence['active'], $index));
                    $ilDB->execute($stmt_dec_times, array($broken_sequence['active'], $index));
                    break;
                    // Hole detection will start over.
                }
            }
        }
        ?>
<#4208>
<?php

if (!$ilDB->tableExists('tmp_tst_to_recalc')) {
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

if ((int) $numRow['num'] && !(int) $setting) {
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

if ((int) $numRow['num']) {
    $groupRes = $ilDB->queryF($groupQuery, array('text'), array('tst'));

    $deleteStmt = $ilDB->prepareManip(
        "DELETE FROM tst_test_result WHERE active_fi = ? AND pass = ? AND question_fi = ? AND test_result_id != ?",
        array('integer', 'integer', 'integer', 'integer')
    );

    while ($groupRow = $ilDB->fetchAssoc($groupRes)) {
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

if ($ilDB->tableExists('tmp_tst_to_recalc')) {
    $deleteStmt = $ilDB->prepareManip(
        "DELETE FROM tmp_tst_to_recalc WHERE active_fi = ? AND pass = ?",
        array('integer', 'integer')
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

    while ($row = $ilDB->fetchAssoc($res)) {
        DBUpdateTestResultCalculator::_updateTestPassResults(
            $row['active_fi'],
            $row['pass'],
            $row['obligations_enabled'],
            $row['question_set_type'],
            $row['obj_fi']
        );

        DBUpdateTestResultCalculator::_updateTestResultCache(
            $row['active_fi'],
            $row['pass_scoring']
        );

        $ilDB->execute($deleteStmt, array($row['active_fi'], $row['pass']));
    }

    $ilDB->dropTable('tmp_tst_to_recalc');
}

?>
<#4210>
<?php
$ilSetting = new ilSetting();
if ((int) $ilSetting->get('lm_qst_imap_migr_run') == 0) {
    // get all imagemap questions in ILIAS learning modules or scorm learning modules
    $set = $ilDB->query(
        "SELECT pq.question_id FROM page_question pq JOIN qpl_qst_imagemap im ON (pq.question_id = im.question_fi) " .
        " WHERE pq.page_parent_type = " . $ilDB->quote("lm", "text") .
        " OR pq.page_parent_type = " . $ilDB->quote("sahs", "text")
    );
    while ($rec = $ilDB->fetchAssoc($set)) {
        // now cross-check against qpl_questions to ensure that this is neither a test nor a question pool question
        $set2 = $ilDB->query(
            "SELECT obj_fi FROM qpl_questions " .
            " WHERE question_id = " . $ilDB->quote($rec["question_id"], "integer")
        );
        if ($rec2 = $ilDB->fetchAssoc($set2)) {
            // this should not be the case for question pool or test questions
            if ($rec2["obj_fi"] == 0) {
                $q = "UPDATE qpl_qst_imagemap SET " .
                    " is_multiple_choice = " . $ilDB->quote(1, "integer") .
                    " WHERE question_fi = " . $ilDB->quote($rec["question_id"], "integer");
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
if (!$ilDB->tableColumnExists('qpl_a_cloze', 'gap_size')) {
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
if (!$ilDB->tableColumnExists('qpl_qst_cloze', 'cloze_text')) {
    $ilDB->addTableColumn('qpl_qst_cloze', 'cloze_text', array('type' => 'clob'));

    $clean_qst_txt = $ilDB->prepareManip('UPDATE qpl_questions SET question_text = "&nbsp;" WHERE question_id = ?', array('integer'));

    $result = $ilDB->query('SELECT question_id, question_text FROM qpl_questions WHERE question_type_fi = 3');

    /** @noinspection PhpAssignmentInConditionInspection */
    while ($row = $ilDB->fetchAssoc($result)) {
        $ilDB->update(
            'qpl_qst_cloze',
            array(
                'cloze_text' => array('clob', $row['question_text'] )
            ),
            array(
                'question_fi' => array('integer', $row['question_id'] )
            )
        );
        $ilDB->execute($clean_qst_txt, array($row['question_id']));
    }
}
?>
<#4213>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4214>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_matching', 'matching_mode')) {
    $ilDB->addTableColumn('qpl_qst_matching', 'matching_mode', array(
        'type' => 'text',
        'length' => 3,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->manipulateF(
        'UPDATE qpl_qst_matching SET matching_mode = %s',
        array('text'),
        array('1:1')
    );
}

if ($ilDB->tableColumnExists('qpl_qst_matching', 'element_height')) {
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
    if (!$ilDB->tableColumnExists('il_dcl_table', 'default_sort_field_id')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'default_sort_field_id',
            array(
                'type' => 'text',
                'length' => 16,
                'notnull' => true,
                'default' => '0',
            )
        );
    }
    if (!$ilDB->tableColumnExists('il_dcl_table', 'default_sort_field_order')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'default_sort_field_order',
            array(
                'type' => 'text',
                'length' => 4,
                'notnull' => true,
                'default' => 'asc',
            )
        );
    }
    if (!$ilDB->tableColumnExists('il_dcl_table', 'public_comments')) {
        $ilDB->addTableColumn(
            'il_dcl_table',
            'public_comments',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0,
            )
        );
    }
?>
<#4218>
<?php
if (!$ilDB->tableColumnExists('il_dcl_table', 'view_own_records_perm')) {
    $ilDB->addTableColumn(
        'il_dcl_table',
        'view_own_records_perm',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        )
    );
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

if (!$ilDB->tableExists('adv_md_values_text')) {
    $ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#4222>
<?php

if (!$ilDB->tableExists('adv_md_values_int')) {
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

if (!$ilDB->tableExists('adv_md_values_float')) {
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

if (!$ilDB->tableExists('adv_md_values_date')) {
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

if (!$ilDB->tableExists('adv_md_values_datetime')) {
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

if (!$ilDB->tableExists('adv_md_values_location')) {
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

    if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled')) {
        $ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ));
    }
    if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled')) {
        $ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ));
    }
    if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled')) {
        $ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ));
    }
    if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled')) {
        $ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ));
    }
    if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled')) {
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

$set = $ilDB->query("SELECT field_id,field_type FROM adv_mdf_definition" .
    " WHERE " . $ilDB->in("field_type", array(3,4), "", "integer"));
while ($row = $ilDB->fetchAssoc($set)) {
    $field_map[$row["field_id"]] = $row["field_type"];
}

if (sizeof($field_map)) {
    $set = $ilDB->query("SELECT * FROM adv_md_values_text" .
        " WHERE " . $ilDB->in("field_id", array_keys($field_map), "", "integer"));
    while ($row = $ilDB->fetchAssoc($set)) {
        if ($row["value"]) {
            // date
            if ($field_map[$row["field_id"]] == 3) {
                $table = "adv_md_values_date";
                $value = date("Y-m-d", $row["value"]);
                $type = "date";
            }
            // datetime
            else {
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

    $ilDB->manipulate("DELETE FROM adv_md_values_text" .
        " WHERE " . $ilDB->in("field_id", array_keys($field_map), "", "integer"));
}

?>
<#4230>
<?php

if (!$ilDB->tableColumnExists('il_blog', 'keywords')) {
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

if (!$ilDB->tableColumnExists('il_blog', 'nav_order')) {
    $ilDB->addTableColumn('il_blog', 'nav_order', array(
        "type" => "text",
        "length" => 255,
        "notnull" => false
    ));
}

?>
<#4232>
<?php

if (!$ilDB->tableColumnExists('svy_svy', 'own_results_view')) {
    $ilDB->addTableColumn('svy_svy', 'own_results_view', array(
        "type" => "integer",
        "length" => 1,
        "notnull" => false,
        "default" => 0
    ));
}
if (!$ilDB->tableColumnExists('svy_svy', 'own_results_mail')) {
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

if (!$ilDB->tableColumnExists('exc_data', 'add_desktop')) {
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
if (!$ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'answer_filter_enabled')) {
    $ilDB->addTableColumn('tst_dyn_quest_set_cfg', 'answer_filter_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));
}
if (!$ilDB->tableColumnExists('tst_active', 'answerstatusfilter')) {
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

if (!$ilDB->tableExists('pg_amd_page_list')) {
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
if (!$ilDB->tableColumnExists('tst_tests', 'skill_service')) {
    $ilDB->addTableColumn('tst_tests', 'skill_service', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->manipulateF(
        'UPDATE tst_tests SET skill_service = %s',
        array('integer'),
        array(0)
    );
}

if (!$ilDB->tableExists('tst_skl_qst_assigns')) {
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

if (!$ilDB->tableExists('tst_skl_thresholds')) {
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

if (!$ilDB->tableColumnExists('tst_active', 'last_finished_pass')) {
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
if (!$ilDB->tableColumnExists('tst_tests', 'result_tax_filters')) {
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
if (!$ilDB->tableColumnExists('tst_test_rnd_qst', 'src_pool_def_fi')) {
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

if (!$ilDB->tableExists('ecs_remote_user')) {
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

if ($ilDB->tableExists('ecs_remote_user')) {
    $ilDB->dropTable('ecs_remote_user');
}

?>
<#4247>
<?php
if (!$ilDB->tableExists('ecs_remote_user')) {
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
            'fixed' => true
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

if ($ilDB->tableColumnExists('exc_data', 'add_desktop')) {
    $ilDB->dropTableColumn('exc_data', 'add_desktop');
}

?>
<#4250>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'show_grading_status')) {
    $ilDB->addTableColumn('tst_tests', 'show_grading_status', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => 0
    ));

    $ilDB->queryF("UPDATE tst_tests SET show_grading_status = %s", array('integer'), array(1));
}

if (!$ilDB->tableColumnExists('tst_tests', 'show_grading_mark')) {
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

if (!$ilDB->tableColumnExists('booking_settings', 'ovlimit')) {
    $ilDB->addTableColumn('booking_settings', 'ovlimit', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false
    ));
}

?>
<#4254>
<?php
if ($ilDB->tableColumnExists('qpl_qst_essay', 'keyword_relation')) {
    $ilDB->queryF(
        "UPDATE qpl_qst_essay SET keyword_relation = %s WHERE keyword_relation = %s",
        array('text', 'text'),
        array('non', 'none')
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
if (!$ilDB->tableExists('wiki_stat')) {
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
if (!$ilDB->tableExists('wiki_stat_page_user')) {
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
if (!$ilDB->tableExists('wiki_stat_user')) {
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
if (!$ilDB->tableExists('wiki_stat_page')) {
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
if (!$ilDB->tableColumnExists('wiki_stat_page', 'avg_rating')) {
    $ilDB->addTableColumn(
        'wiki_stat_page',
        'avg_rating',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        )
    );
}
?>
<#4261>
<?php

if (!$ilDB->tableColumnExists('wiki_stat', 'ts_day')) {
    $ilDB->addTableColumn(
        'wiki_stat',
        'ts_day',
        array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => false
        )
    );
    $ilDB->addTableColumn(
        'wiki_stat',
        'ts_hour',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        )
    );
}

if (!$ilDB->tableColumnExists('wiki_stat_page', 'ts_day')) {
    $ilDB->addTableColumn(
        'wiki_stat_page',
        'ts_day',
        array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => false
        )
    );
    $ilDB->addTableColumn(
        'wiki_stat_page',
        'ts_hour',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        )
    );
}

if (!$ilDB->tableColumnExists('wiki_stat_user', 'ts_day')) {
    $ilDB->addTableColumn(
        'wiki_stat_user',
        'ts_day',
        array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => false
        )
    );
    $ilDB->addTableColumn(
        'wiki_stat_user',
        'ts_hour',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        )
    );
}

if (!$ilDB->tableColumnExists('wiki_stat_page_user', 'ts_day')) {
    $ilDB->addTableColumn(
        'wiki_stat_page_user',
        'ts_day',
        array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => false
        )
    );
    $ilDB->addTableColumn(
        'wiki_stat_page_user',
        'ts_hour',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        )
    );
}

?>
<#4262>
<?php
    if (!$ilDB->tableExists('wiki_page_template')) {
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
if (!$ilDB->tableColumnExists('wiki_page_template', 'new_pages')) {
    $ilDB->addTableColumn(
        'wiki_page_template',
        'new_pages',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->addTableColumn(
        'wiki_page_template',
        'add_to_page',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#4264>
<?php
if (!$ilDB->tableColumnExists('il_wiki_data', 'empty_page_templ')) {
    $ilDB->addTableColumn(
        'il_wiki_data',
        'empty_page_templ',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        )
    );
}
?>
<#4265>
<?php

if (!$ilDB->tableColumnExists('wiki_stat_page', 'deleted')) {
    $ilDB->addTableColumn(
        'wiki_stat_page',
        'deleted',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}

?>
<#4266>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if ($wiki_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('statistics_read', 'Read Statistics', 'object', 2200);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);

        $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
        if ($src_ops_id) {
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
            "notnull" => true,
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
            "notnull" => true,
            'fixed' => false
        )
);
?>
<#4271>
<?php

$client_id = basename(CLIENT_DATA_DIR);
$web_path = ilUtil::getWebspaceDir() . $client_id;
$sec_path = $web_path . "/sec";

if (!file_exists($sec_path)) {
    ilUtil::makeDir($sec_path);
}

$mods = array("ilBlog", "ilPoll", "ilPortfolio");
foreach ($mods as $mod) {
    $mod_path = $web_path . "/" . $mod;
    if (file_exists($mod_path)) {
        $mod_sec_path = $sec_path . "/" . $mod;
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

if (!$ilDB->tableExists('obj_user_data_hist')) {
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
    $ilDB->addPrimaryKey('obj_user_data_hist', array('obj_id','usr_id'));
}

?>
<#4276>
<?php
if (!$ilDB->tableColumnExists('frm_threads', 'avg_rating')) {
    $ilDB->addTableColumn(
        'frm_threads',
        'avg_rating',
        array(
            'type' => 'float',
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#4277>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4278>
<?php
if (!$ilDB->tableColumnExists('frm_settings', 'thread_rating')) {
    $ilDB->addTableColumn(
        'frm_settings',
        'thread_rating',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#4279>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_file')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'peer_file',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#4280>
<?php
if (!$ilDB->tableColumnExists('exc_assignment_peer', 'upload')) {
    $ilDB->addTableColumn(
        'exc_assignment_peer',
        'upload',
        array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'fixed' => false
        )
    );
}
?>
<#4281>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_prsl')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'peer_prsl',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#4282>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'fb_date')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'fb_date',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        )
    );
}
?>
<#4283>
<?php
if (!$ilDB->tableColumnExists('container_sorting_set', 'sort_direction')) {
    $ilDB->addTableColumn(
        'container_sorting_set',
        'sort_direction',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#4284>
<?php
if (!$ilDB->tableExists('tst_seq_qst_checked')) {
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

    $ilDB->addPrimaryKey('tst_seq_qst_checked', array('active_fi','pass', 'question_fi'));
}

if (!$ilDB->tableColumnExists('tst_tests', 'inst_fb_answer_fixation')) {
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
if (!$ilDB->tableExists('container_sorting_bl')) {
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

    $ilDB->addPrimaryKey('container_sorting_bl', array('obj_id'));
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
if (!$tgt_ops_id) {
    $tgt_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('read_learning_progress', 'Read Learning Progress', 'object', 2300);
}

$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
if ($src_ops_id &&
    $tgt_ops_id) {
    // see ilObjectLP
    $lp_types = array("crs", "grp", "fold", "lm", "htlm", "sahs", "tst", "exc", "sess");

    foreach ($lp_types as $lp_type) {
        $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
        if ($lp_type_id) {
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
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    );
$ilDB->addTableColumn("content_object", "progr_icons", $def);
?>
<#4290>
<?php
$def = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    );
$ilDB->addTableColumn("content_object", "store_tries", $def);
?>
<#4291>
<?php
    $query = 'DELETE FROM rbac_fa WHERE parent = ' . $ilDB->quote(0, 'integer');
    $ilDB->manipulate($query);


    /*$query = 'UPDATE rbac_fa f '.
            'SET parent  = '.
                '(SELECT t.parent FROM tree t where t.child = f.parent) '.
            'WHERE f.parent != '.$ilDB->quote(8,'integer').' '.
            'AND EXISTS (SELECT t.parent FROM tree t where t.child = f.parent) ';
    $ilDB->manipulate($query);*/

    global $ilLog;

    if (!$ilDB->tableColumnExists('rbac_fa', 'old_parent')) {
        $ilDB->addTableColumn(
            'rbac_fa',
            'old_parent',
            array(
                "type" => "integer",
                "notnull" => true,
                "length" => 8,
                "default" => 0
            )
        );
        $ilLog->write("Created new temporary column: rbac_fa->old_parent");
    }

    if (!$ilDB->tableExists('rbac_fa_temp')) {
        $fields = array(
            'role_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
            'parent_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0)
        );
        $ilDB->createTable('rbac_fa_temp', $fields);
        $ilDB->addPrimaryKey('rbac_fa_temp', array('role_id', 'parent_id'));
        $ilLog->write("Created new temporary table: rbac_fa_temp");
    }


    $stmt = $ilDB->prepareManip("UPDATE rbac_fa SET parent = ?, old_parent = ? WHERE  rol_id = ? AND parent = ?", array("integer", "integer", "integer", "integer"));
    $stmt2 = $ilDB->prepareManip("INSERT INTO rbac_fa_temp (role_id, parent_id) VALUES(?, ?)", array("integer", "integer"));
    $stmt3 = $ilDB->prepare("SELECT object_data.type FROM object_reference INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id WHERE ref_id = ?", array("integer"));

    $query = "
	    SELECT f.*, t.parent grandparent
	    FROM rbac_fa f
	    INNER JOIN tree t ON t.child = f.parent
	    LEFT JOIN rbac_fa_temp
	        ON rbac_fa_temp.role_id = f.rol_id
	        AND rbac_fa_temp.parent_id = old_parent
	    WHERE f.parent != 8 AND rbac_fa_temp.role_id IS NULL
	    ORDER BY f.rol_id, f.parent
	";
    $res = $ilDB->query($query);

    $handled_roles_by_parent = array();

    while ($row = $ilDB->fetchAssoc($res)) {
        $role_id = $row["rol_id"];
        $parent_id = $row["parent"];

        if ($handled_roles_by_parent[$role_id][$parent_id]) {
            continue;
        }

        $new_parent_id = $row['grandparent'];

        $parent_res = $ilDB->execute($stmt3, array($parent_id));
        $parent_row = $ilDB->fetchAssoc($parent_res);
        if ($parent_row['type'] != 'rolf') {
            $ilLog->write(sprintf("Parent of role with id %s is not a 'rolf' (obj_id: %s, type: %s), so skip record", $role_id, $parent_row['obj_id'], $parent_row['type']));
            continue;
        }

        if ($new_parent_id <= 0) {
            $ilLog->write(sprintf("Could not migrate record with role_id %s and parent id %s because the grandparent is 0", $role_id, $parent_id));
            continue;
        }

        $ilDB->execute($stmt, array($new_parent_id, $parent_id , $role_id, $parent_id));
        $ilDB->execute($stmt2, array($role_id, $parent_id));
        $ilLog->write(sprintf("Migrated record with role_id %s and parent id %s to parent with id %s", $role_id, $parent_id, $new_parent_id));

        $handled_roles_by_parent[$role_id][$parent_id] = true;
    }

    if ($ilDB->tableColumnExists('rbac_fa', 'old_parent')) {
        $ilDB->dropTableColumn('rbac_fa', 'old_parent');
        $ilLog->write("Dropped new temporary column: rbac_fa->old_parent");
    }

    if ($ilDB->tableExists('rbac_fa_temp')) {
        $ilDB->dropTable('rbac_fa_temp');
        $ilLog->write("Dropped new temporary table: rbac_fa_temp");
    }
?>

<#4292>
<?php
    $query = 'DELETE FROM rbac_templates WHERE parent = ' . $ilDB->quote(0, 'integer');
    $ilDB->manipulate($query);

    /*$query = 'UPDATE rbac_templates rt '.
            'SET parent = '.
            '(SELECT t.parent FROM tree t WHERE t.child = rt.parent) '.
            'WHERE rt.parent != '.$ilDB->quote(8,'integer').' '.
            'AND EXISTS (SELECT t.parent FROM tree t WHERE t.child = rt.parent) ';
    $ilDB->manipulate($query);*/

    global $ilLog;

    if (!$ilDB->tableColumnExists('rbac_templates', 'old_parent')) {
        $ilDB->addTableColumn(
            'rbac_templates',
            'old_parent',
            array(
                "type" => "integer",
                "notnull" => true,
                "length" => 8,
                "default" => 0
            )
        );
        $ilLog->write("Created new temporary column: rbac_templates->old_parent");
    }

    if (!$ilDB->tableExists('rbac_templates_temp')) {
        $fields = array(
            'role_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
            'parent_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0)
        );
        $ilDB->createTable('rbac_templates_temp', $fields);
        $ilDB->addPrimaryKey('rbac_templates_temp', array('role_id', 'parent_id'));
        $ilLog->write("Created new temporary table: rbac_templates_temp");
    }


    $stmt = $ilDB->prepareManip("UPDATE rbac_templates SET parent = ?, old_parent = ? WHERE  rol_id = ? AND parent = ?", array("integer", "integer", "integer", "integer"));
    $stmt2 = $ilDB->prepareManip("INSERT INTO rbac_templates_temp (role_id, parent_id) VALUES(?, ?)", array("integer", "integer"));
    $stmt3 = $ilDB->prepare("SELECT object_data.type FROM object_reference INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id WHERE ref_id = ?", array("integer"));

    $query = "
	    SELECT f.*, t.parent grandparent
	    FROM rbac_templates f
	    INNER JOIN tree t ON t.child = f.parent
	    LEFT JOIN rbac_templates_temp
	        ON rbac_templates_temp.role_id = f.rol_id
	        AND rbac_templates_temp.parent_id = old_parent
	    WHERE f.parent != 8 AND rbac_templates_temp.role_id IS NULL
	    ORDER BY f.rol_id, f.parent
	";
    $res = $ilDB->query($query);

    $handled_roles_by_parent = array();

    while ($row = $ilDB->fetchAssoc($res)) {
        $role_id = $row["rol_id"];
        $parent_id = $row["parent"];

        if ($handled_roles_by_parent[$role_id][$parent_id]) {
            continue;
        }

        $new_parent_id = $row['grandparent'];

        $parent_res = $ilDB->execute($stmt3, array($parent_id));
        $parent_row = $ilDB->fetchAssoc($parent_res);
        if ($parent_row['type'] != 'rolf') {
            $ilLog->write(sprintf("Parent of role with id %s is not a 'rolf' (obj_id: %s, type: %s), so skip record", $role_id, $parent_row['obj_id'], $parent_row['type']));
            continue;
        }

        if ($new_parent_id <= 0) {
            $ilLog->write(sprintf("Could not migrate record with role_id %s and parent id %s because the grandparent is 0", $role_id, $parent_id));
            continue;
        }

        $ilDB->execute($stmt, array($new_parent_id, $parent_id , $role_id, $parent_id));
        $ilDB->execute($stmt2, array($role_id, $parent_id));
        $ilLog->write(sprintf("Migrated record with role_id %s and parent id %s to parent with id %s", $role_id, $parent_id, $new_parent_id));

        $handled_roles_by_parent[$role_id][$parent_id] = true;
    }

    if ($ilDB->tableColumnExists('rbac_templates', 'old_parent')) {
        $ilDB->dropTableColumn('rbac_templates', 'old_parent');
        $ilLog->write("Dropped new temporary column: rbac_templates->old_parent");
    }

    if ($ilDB->tableExists('rbac_templates_temp')) {
        $ilDB->dropTable('rbac_templates_temp');
        $ilLog->write("Dropped new temporary table: rbac_templates_temp");
    }
?>
<#4293>
<?php
$def = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    );
$ilDB->addTableColumn("content_object", "restrict_forw_nav", $def);
?>
<#4294>
<?php

// category taxonomy custom blocks are obsolete
$ilDB->manipulate("DELETE FROM il_custom_block" .
    " WHERE context_obj_type = " . $ilDB->quote("cat", "text") .
    " AND context_sub_obj_type = " . $ilDB->quote("tax", "text"));

?>
<#4295>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4296>
<?php
if (!$ilDB->tableColumnExists('container_sorting_set', 'new_items_position')) {
    $def = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 1
    );
    $ilDB->addTableColumn('container_sorting_set', 'new_items_position', $def);
}

if (!$ilDB->tableColumnExists('container_sorting_set', 'new_items_order')) {
    $def = array(
        'type' => 'integer',
        'length' => 1,
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
if (!$ilDB->tableExists('usr_cron_mail_reminder')) {
    $fields = array(
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'default' => 0,
            'notnull' => true
        ),
        'ts' => array(
            'type' => 'integer',
            'length' => 4,
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
    if (!$ilDB->tableExists('orgu_types')) {
        $fields = array(
            'id' => array('type' => 'integer', 'length' => 4,'notnull' => true, 'default' => 0),
            'default_lang' => array('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
            'icon' => array('type' => 'text', 'length' => 256, 'notnull' => false),
            'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
            'create_date' => array('type' => 'timestamp'),
            'last_update' => array('type' => 'timestamp'),
        );
        $ilDB->createTable('orgu_types', $fields);
        $ilDB->addPrimaryKey('orgu_types', array('id'));
        $ilDB->createSequence('orgu_types');
    }
    ?>
<#4300>
    <?php
    if (!$ilDB->tableExists('orgu_data')) {
        $fields = array(
            'orgu_id' => array('type' => 'integer', 'length' => 4,'notnull' => true, 'default' => 0),
            'orgu_type_id' => array('type' => 'integer', 'notnull' => false, 'length' => 4),
        );
        $ilDB->createTable('orgu_data', $fields);
        $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
    }
    ?>
<#4301>
    <?php
    if (!$ilDB->tableExists('orgu_types_trans')) {
        $fields = array(
            'orgu_type_id' => array('type' => 'integer', 'length' => 4,'notnull' => true),
            'lang' => array('type' => 'text', 'notnull' => true, 'length' => 4),
            'member' => array('type' => 'text', 'length' => 32, 'notnull' => true),
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
    if (!$ilDB->tableExists('orgu_types_adv_md_rec')) {
        $fields = array(
            'type_id' => array('type' => 'integer', 'length' => 4,'notnull' => true),
            'rec_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
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
    'type' => 'text',
    'length' => 80,
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
if (!$ilDB->tableColumnExists('usr_data', 'passwd_enc_type')) {
    $ilDB->addTableColumn('usr_data', 'passwd_enc_type', array(
        'type' => 'text',
        'length' => 10,
        'notnull' => false,
        'default' => null
    ));
}
?>
<#4309>
<?php
// We have to handle alle users with a password. We cannot rely on the auth_mode information.
$ilDB->manipulateF(
    '
	UPDATE usr_data
	SET passwd_enc_type = %s
	WHERE (SUBSTR(passwd, 1, 4) = %s OR SUBSTR(passwd, 1, 4) = %s) AND passwd IS NOT NULL
	',
    array('text', 'text', 'text'),
    array('bcrypt', '$2a$', '$2y$')
);
$ilDB->manipulateF(
    '
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
if (!$ilDB->tableColumnExists('usr_data', 'passwd_salt')) {
    $ilDB->addTableColumn('usr_data', 'passwd_salt', array(
        'type' => 'text',
        'length' => 32,
        'notnull' => false,
        'default' => null
    ));
}
?>
<#4311>
<?php
if ($ilDB->tableColumnExists('usr_data', 'i2passwd')) {
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
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_obj_id[] = $data['targetobjectiveid'];
    }
    //make arrays
    for ($i = 0;$i < count($a_obj_id);$i++) {
        $a_scope_id[$a_obj_id[$i]] = array();
        $a_scope_id_one[$a_obj_id[$i]] = array();
    }
    //only global_to_system=0 -> should be updated
    $res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id
		FROM cp_package, cp_mapinfo, cp_node
		WHERE cp_package.global_to_system = 0 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_scope_id[$data['targetobjectiveid']][] = $data['obj_id'];
    }
    //only global_to_system=1 -> should maintain
    $res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id
		FROM cp_package, cp_mapinfo, cp_node
		WHERE cp_package.global_to_system = 1 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_scope_id_one[$data['targetobjectiveid']][] = $data['obj_id'];
    }

    //for all targetobjectiveid
    for ($i = 0;$i < count($a_obj_id);$i++) {
        $a_toupdate = array();
        //get old data without correct scope_id
        $res = $ilDB->queryF(
            "SELECT * FROM cmi_gobjective WHERE scope_id = %s AND objective_id = %s",
            array('integer', 'text'),
            array(0, $a_obj_id[$i])
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $a_toupdate[] = $data;
        }
        //check specific possible scope_ids with global_to_system=0 -> a_o
        $a_o = $a_scope_id[$a_obj_id[$i]];
        for ($z = 0; $z < count($a_o); $z++) {
            //for all existing entries
            for ($y = 0; $y < count($a_toupdate); $y++) {
                $a_t = $a_toupdate[$y];
                //only users attempted
                $res = $ilDB->queryF(
                    'SELECT user_id FROM sahs_user WHERE obj_id=%s AND user_id=%s',
                    array('integer', 'integer'),
                    array($a_o[$z], $a_t['user_id'])
                );
                if ($ilDB->numRows($res)) {
                    //check existing entry
                    $res = $ilDB->queryF(
                        'SELECT user_id FROM cmi_gobjective WHERE scope_id=%s AND user_id=%s AND objective_id=%s',
                        array('integer', 'integer','text'),
                        array($a_o[$z], $a_t['user_id'],$a_t['objective_id'])
                    );
                    if (!$ilDB->numRows($res)) {
                        $ilDB->manipulate("INSERT INTO cmi_gobjective (user_id, satisfied, measure, scope_id, status, objective_id, score_raw, score_min, score_max, progress_measure, completion_status) VALUES"
                        . " (" . $ilDB->quote($a_t['user_id'], "integer")
                        . ", " . $ilDB->quote($a_t['satisfied'], "text")
                        . ", " . $ilDB->quote($a_t['measure'], "text")
                        . ", " . $ilDB->quote($a_o[$z], "integer")
                        . ", " . $ilDB->quote($a_t['status'], "text")
                        . ", " . $ilDB->quote($a_t['objective_id'], "text")
                        . ", " . $ilDB->quote($a_t['score_raw'], "text")
                        . ", " . $ilDB->quote($a_t['score_min'], "text")
                        . ", " . $ilDB->quote($a_t['score_max'], "text")
                        . ", " . $ilDB->quote($a_t['progress_measure'], "text")
                        . ", " . $ilDB->quote($a_t['completion_status'], "text")
                        . ")");
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
if ($ilDB->tableColumnExists('exc_assignment_peer', 'upload')) {
    $ilDB->dropTableColumn('exc_assignment_peer', 'upload');
}
?>

<#4314>
<?php

$res = $ilDB->queryF(
    "SELECT COUNT(*) cnt FROM qpl_qst_type WHERE type_tag = %s",
    array('text'),
    array('assKprimChoice')
);

$row = $ilDB->fetchAssoc($res);

if (!$row['cnt']) {
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

if (!$ilDB->tableExists('qpl_qst_kprim')) {
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

if (!$ilDB->tableExists('qpl_a_kprim')) {
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
if (!$ilDB->tableColumnExists('tst_solutions', 'step')) {
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
if (!$ilDB->tableColumnExists('tst_test_result', 'step')) {
    $ilDB->addTableColumn('tst_test_result', 'step', array(
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

if (!$ilDB->tableExists('il_bibl_settings')) {
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
    if (!$ilDB->tableColumnExists('frm_threads', 'thr_author_id')) {
        $ilDB->addTableColumn(
            'frm_threads',
            'thr_author_id',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            )
        );
    }
?>
<#4330>
<?php
    if ($ilDB->tableColumnExists('frm_threads', 'thr_author_id')) {
        $ilDB->manipulate('UPDATE frm_threads SET thr_author_id = thr_usr_id');
    }
?>
<#4331>
<?php
    if (!$ilDB->tableColumnExists('frm_posts', 'pos_author_id')) {
        $ilDB->addTableColumn(
            'frm_posts',
            'pos_author_id',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            )
        );
    }
?>
<#4332>
<?php
    if ($ilDB->tableColumnExists('frm_posts', 'pos_author_id')) {
        $ilDB->manipulate('UPDATE frm_posts SET pos_author_id = pos_usr_id');
    }
?>
<#4333>
<?php
    if (!$ilDB->tableColumnExists('frm_threads', 'thr_display_user_id')) {
        $ilDB->addTableColumn(
            'frm_threads',
            'thr_display_user_id',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            )
        );
    }
?>
<#4334>
<?php
    if ($ilDB->tableColumnExists('frm_threads', 'thr_display_user_id')) {
        $ilDB->manipulate('UPDATE frm_threads SET thr_display_user_id = thr_usr_id');
    }
?>
<#4335>
<?php
    if ($ilDB->tableColumnExists('frm_threads', 'thr_usr_id')) {
        $ilDB->dropTableColumn('frm_threads', 'thr_usr_id');
    }

?>
<#4336>
<?php
    if (!$ilDB->tableColumnExists('frm_posts', 'pos_display_user_id')) {
        $ilDB->addTableColumn(
            'frm_posts',
            'pos_display_user_id',
            array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            )
        );
    }
?>
<#4337>
<?php
    if ($ilDB->tableColumnExists('frm_posts', 'pos_display_user_id')) {
        $ilDB->manipulate('UPDATE frm_posts SET pos_display_user_id = pos_usr_id');
    }
?>
<#4338>
<?php
    if ($ilDB->tableColumnExists('frm_posts', 'pos_usr_id')) {
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
$ini = new ilIniFile(ILIAS_ABSOLUTE_PATH . "/ilias.ini.php");

if ($ini->read()) {
    $ilSetting = new ilSetting();

    $https_header_enable = (bool) $ilSetting->get('ps_auto_https_enabled', false);
    $https_header_name = (string) $ilSetting->get('ps_auto_https_headername', "ILIAS_HTTPS_ENABLED");
    $https_header_value = (string) $ilSetting->get('ps_auto_https_headervalue', "1");

    if (!$ini->groupExists('https')) {
        $ini->addGroup('https');
    }

    $ini->setVariable("https", "auto_https_detect_enabled", (!$https_header_enable) ? 0 : 1);
    $ini->setVariable("https", "auto_https_detect_header_name", $https_header_name);
    $ini->setVariable("https", "auto_https_detect_header_value", $https_header_value);

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
if (!$ilDB->tableColumnExists('tst_active', 'objective_container')) {
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
if (!$ilDB->tableExists('qpl_a_cloze_combi_res')) {
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
if (!$ilDB->tableColumnExists('conditions', 'hidden_status')) {
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
    if ($ilDB->tableColumnExists('frm_posts', 'pos_usr_id')) {
        $ilDB->dropTableColumn('frm_posts', 'pos_usr_id');
    }
?>
<#4350>
<?php
    if ($ilDB->tableColumnExists('frm_threads', 'thr_usr_id')) {
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

if (!$ilDB->tableColumnExists('il_blog', 'abs_shorten')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_shorten_len')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten_len',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_image')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_image',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_img_width')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_width',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

if (!$ilDB->tableColumnExists('il_blog', 'abs_img_height')) {
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_height',
        array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false,
            'default' => 0
        )
    );
}

?>

<#4353>
<?php

if (!$ilDB->tableExists('usr_data_multi')) {
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
if (!$ilDB->tableColumnExists('crs_start', 'pos')) {
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
if (!$ilDB->tableExists('loc_settings')) {
    $ilDB->createTable(
        'loc_settings',
        array(
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
if (!$ilDB->tableColumnExists('loc_settings', 'itest')) {
    $ilDB->addTableColumn('loc_settings', 'itest', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false,
        'default' => null
    ));
}

if (!$ilDB->tableColumnExists('loc_settings', 'qtest')) {
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
if (!$ilDB->tableColumnExists('adm_settings_template', 'auto_generated')) {
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
if (!$ilDB->tableColumnExists('crs_objective_lm', 'position')) {
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

if (!$ilDB->tableExists('loc_rnd_qpl')) {
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

$query = 'INSERT INTO adm_settings_template ' .
        '(id, type, title, description, auto_generated) ' .
        'VALUES( ' .
        $ilDB->quote($ilDB->nextId('adm_settings_template'), 'integer') . ', ' .
        $ilDB->quote('tst', 'text') . ', ' .
        $ilDB->quote('il_astpl_loc_initial', 'text') . ', ' .
        $ilDB->quote('il_astpl_loc_initial_desc', 'text') . ', ' .
        $ilDB->quote(1, 'integer') . ' ' .
        ')';
$ilDB->manipulate($query);
?>
<#4361>
<?php

$query = 'INSERT INTO adm_settings_template ' .
        '(id, type, title, description, auto_generated) ' .
        'VALUES( ' .
        $ilDB->quote($ilDB->nextId('adm_settings_template'), 'integer') . ', ' .
        $ilDB->quote('tst', 'text') . ', ' .
        $ilDB->quote('il_astpl_loc_qualified', 'text') . ', ' .
        $ilDB->quote('il_astpl_loc_qualified_desc', 'text') . ', ' .
        $ilDB->quote(1, 'integer') . ' ' .
        ')';
$ilDB->manipulate($query);
?>

<#4362>
<?php

if (!$ilDB->tableExists('loc_user_results')) {
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
if (!$ilDB->tableColumnExists('loc_settings', 'qt_vis_all')) {
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
if (!$ilDB->tableColumnExists('loc_settings', 'qt_vis_obj')) {
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
if (!$ilDB->tableColumnExists('crs_objectives', 'active')) {
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
if (!$ilDB->tableColumnExists('crs_objectives', 'passes')) {
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
if (!$ilDB->tableExists('loc_tst_run')) {
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
if (!$ilDB->tableColumnExists('loc_settings', 'reset_results')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'reset_results',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#4369>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4370>
<?php
if (!$ilDB->tableColumnExists('il_bibl_settings', 'show_in_list')) {
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
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_obj_id[] = $data['targetobjectiveid'];
    }
    //make arrays
    for ($i = 0;$i < count($a_obj_id);$i++) {
        $a_scope_id[$a_obj_id[$i]] = array();
        $a_scope_id_one[$a_obj_id[$i]] = array();
    }
    //only global_to_system=0 -> should be updated
    $res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id
		FROM cp_package, cp_mapinfo, cp_node
		WHERE cp_package.global_to_system = 0 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_scope_id[$data['targetobjectiveid']][] = $data['obj_id'];
    }
    //only global_to_system=1 -> should maintain
    $res = $ilDB->query('SELECT cp_mapinfo.targetobjectiveid, cp_package.obj_id
		FROM cp_package, cp_mapinfo, cp_node
		WHERE cp_package.global_to_system = 1 AND cp_package.obj_id = cp_node.slm_id AND cp_node.cp_node_id = cp_mapinfo.cp_node_id');
    while ($data = $ilDB->fetchAssoc($res)) {
        $a_scope_id_one[$data['targetobjectiveid']][] = $data['obj_id'];
    }

    //for all targetobjectiveid
    for ($i = 0;$i < count($a_obj_id);$i++) {
        $a_toupdate = array();
        //get old data without correct scope_id
        $res = $ilDB->queryF(
            "SELECT * FROM cmi_gobjective WHERE scope_id = %s AND objective_id = %s",
            array('integer', 'text'),
            array(0, $a_obj_id[$i])
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $a_toupdate[] = $data;
        }
        //check specific possible scope_ids with global_to_system=0 -> a_o
        $a_o = $a_scope_id[$a_obj_id[$i]];
        for ($z = 0; $z < count($a_o); $z++) {
            //for all existing entries
            for ($y = 0; $y < count($a_toupdate); $y++) {
                $a_t = $a_toupdate[$y];
                //only users attempted
                $res = $ilDB->queryF(
                    'SELECT user_id FROM sahs_user WHERE obj_id=%s AND user_id=%s',
                    array('integer', 'integer'),
                    array($a_o[$z], $a_t['user_id'])
                );
                if ($ilDB->numRows($res)) {
                    //check existing entry
                    $res = $ilDB->queryF(
                        'SELECT user_id FROM cmi_gobjective WHERE scope_id=%s AND user_id=%s AND objective_id=%s',
                        array('integer', 'integer','text'),
                        array($a_o[$z], $a_t['user_id'],$a_t['objective_id'])
                    );
                    if (!$ilDB->numRows($res)) {
                        $ilDB->manipulate("INSERT INTO cmi_gobjective (user_id, satisfied, measure, scope_id, status, objective_id, score_raw, score_min, score_max, progress_measure, completion_status) VALUES"
                        . " (" . $ilDB->quote($a_t['user_id'], "integer")
                        . ", " . $ilDB->quote($a_t['satisfied'], "text")
                        . ", " . $ilDB->quote($a_t['measure'], "text")
                        . ", " . $ilDB->quote($a_o[$z], "integer")
                        . ", " . $ilDB->quote($a_t['status'], "text")
                        . ", " . $ilDB->quote($a_t['objective_id'], "text")
                        . ", " . $ilDB->quote($a_t['score_raw'], "text")
                        . ", " . $ilDB->quote($a_t['score_min'], "text")
                        . ", " . $ilDB->quote($a_t['score_max'], "text")
                        . ", " . $ilDB->quote($a_t['progress_measure'], "text")
                        . ", " . $ilDB->quote($a_t['completion_status'], "text")
                        . ")");
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
    if ($ilDB->getDBType() == 'innodb') {
        $query = "show index from cmi_gobjective where Key_name = 'PRIMARY'";
        $res = $ilDB->query($query);
        if (!$ilDB->numRows($res)) {
            $ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
        }
    }
?>
<#4373>
<?php
    if ($ilDB->getDBType() == 'innodb') {
        $query = "show index from cp_suspend where Key_name = 'PRIMARY'";
        $res = $ilDB->query($query);
        if (!$ilDB->numRows($res)) {
            $ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
        }
    }
?>
<#4374>
<?php
    if (!$ilDB->tableColumnExists('frm_posts', 'is_author_moderator')) {
        $ilDB->addTableColumn(
            'frm_posts',
            'is_author_moderator',
            array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => null)
        );
    }
?>
<#4375>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'token')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'token',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 1
        )
    );
}
?>
<#4376>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4377>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'export_types')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'export_types',
        array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false,
        )
    );
}
?>
<#4378>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'import_types')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'import_types',
        array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false,
        )
    );
}
?>
<#4379>
<?php

    $query = 'UPDATE ecs_part_settings SET export_types = ' . $ilDB->quote(serialize(array('cat','crs','file','glo','grp','wiki','lm')), 'text');
    $ilDB->manipulate($query);

?>

<#4380>
<?php

    $query = 'UPDATE ecs_part_settings SET import_types = ' . $ilDB->quote(serialize(array('cat','crs','file','glo','grp','wiki','lm')), 'text');
    $ilDB->manipulate($query);

?>
<#4381>
<?php
if (!$ilDB->tableColumnExists('reg_registration_codes', 'reg_enabled')) {
    $ilDB->addTableColumn(
        'reg_registration_codes',
        'reg_enabled',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        )
    );
}
?>

<#4382>
<?php
if (!$ilDB->tableColumnExists('reg_registration_codes', 'ext_enabled')) {
    $ilDB->addTableColumn(
        'reg_registration_codes',
        'ext_enabled',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#4383>
<?php

if ($ilDB->tableColumnExists('reg_registration_codes', 'generated')) {
    $ilDB->renameTableColumn('reg_registration_codes', "generated", 'generated_on');
}


$query = 'SELECT * FROM usr_account_codes ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $until = $row->valid_until;
    if ($until === '0') {
        $alimit = 'unlimited';
        $a_limitdt = null;
    } elseif (is_numeric($until)) {
        $alimit = 'relative';
        $a_limitdt = array(
            'd' => (string) $until,
            'm' => '',
            'y' => ''
        );
        $a_limitdt = serialize($a_limitdt);
    } else {
        $alimit = 'absolute';
        $a_limitdt = $until;
    }

    $next_id = $ilDB->nextId('reg_registration_codes');
    $query = 'INSERT INTO reg_registration_codes ' .
            '(code_id, code, role, generated_on, used, role_local, alimit, alimitdt, reg_enabled, ext_enabled ) ' .
            'VALUES ( ' .
            $ilDB->quote($next_id, 'integer') . ', ' .
            $ilDB->quote($row->code, 'text') . ', ' .
            $ilDB->quote(0, 'integer') . ', ' .
            $ilDB->quote($row->generated_on, 'integer') . ', ' .
            $ilDB->quote($row->used, 'integer') . ', ' .
            $ilDB->quote('', 'text') . ', ' .
            $ilDB->quote($alimit, 'text') . ', ' .
            $ilDB->quote($a_limitdt, 'text') . ', ' .
            $ilDB->quote(0, 'integer') . ', ' .
            $ilDB->quote(1, 'integer') . ' ' .
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
    $ilDB->update(
    'tst_tests',
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
$ilDB->addTableColumn(
    "tst_test_defaults",
    "marks_tmp",
    array(
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
$ilDB->addTableColumn(
    "tst_test_defaults",
    "defaults_tmp",
    array(
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

if (!$ilDB->tableExists('tst_seq_qst_tracking')) {
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

while ($row = $ilDB->fetchAssoc($res)) {
    $tracking = unserialize($row['sequence']);

    if (is_array($tracking)) {
        foreach ($tracking as $index => $question) {
            $ilDB->replace(
                'tst_seq_qst_tracking',
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

        $ilDB->update(
            'tst_sequence',
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

if (!$ilDB->tableExists('tst_seq_qst_postponed')) {
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

while ($row = $ilDB->fetchAssoc($res)) {
    $postponed = unserialize($row['postponed']);

    if (is_array($postponed)) {
        foreach ($postponed as $questionId => $postponeCount) {
            $ilDB->replace(
                'tst_seq_qst_postponed',
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

        $ilDB->update(
            'tst_sequence',
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

if (!$ilDB->tableExists('tst_seq_qst_answstatus')) {
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

while ($row = $ilDB->fetchAssoc($res)) {
    $answerStatus = unserialize($row['hidden']);

    if (is_array($answerStatus)) {
        foreach ($answerStatus['correct'] as $questionId) {
            $ilDB->replace(
                'tst_seq_qst_answstatus',
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

        foreach ($answerStatus['wrong'] as $questionId) {
            $ilDB->replace(
                'tst_seq_qst_answstatus',
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

        $ilDB->update(
            'tst_sequence',
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

if (($ilDB->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) && $ilDB->db->options['field_case'] == CASE_LOWER) {
    $indexName = strtolower($indexName);
} else {
    $indexName = strtoupper($indexName);
}

$indexDefinition = $ilDB->loadModule('Reverse')->getTableConstraintDefinition('tst_dyn_quest_set_cfg', $indexName);

if ($indexDefinition instanceof MDB2_Error) {
    $res = $ilDB->query("
		SELECT test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		FROM tst_dyn_quest_set_cfg
		GROUP BY test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		HAVING COUNT(*) > 1
	");

    $insertStmt = $ilDB->prepareManip(
        "
		INSERT INTO tst_dyn_quest_set_cfg (
			test_fi, source_qpl_fi, source_qpl_title, answer_filter_enabled, tax_filter_enabled, order_tax
		) VALUES (?, ?, ?, ?, ?, ?)
		",
        array('integer', 'integer', 'text', 'integer', 'integer', 'integer')
    );

    while ($row = $ilDB->fetchAssoc($res)) {
        $expressions = array();

        foreach ($row as $field => $value) {
            if ($value === null) {
                $expressions[] = "$field IS NULL";
            } else {
                if ($field == 'source_qpl_title') {
                    $value = $ilDB->quote($value, 'text');
                } else {
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
if (!$ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled')) {
    $ilDB->addTableColumn(
        'tst_dyn_quest_set_cfg',
        'prev_quest_list_enabled',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
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

$ilDB->manipulate("DELETE FROM settings" .
    " WHERE module = " . $ilDB->quote("common", "text") .
    " AND keyword = " . $ilDB->quote("obj_dis_creation_rcrs", "text"));

?>
<#4403>
<?php

$settings = new ilSetting();
if (!$settings->get('ommit_legacy_ou_dbtable_deletion', 0)) {
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

if ($fixState === '0') {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        $testsWithoutDefinitionsDetected = true;
        break;
    }

    if ($testsWithoutDefinitionsDetected) {
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
    } else {
        $setting->set('dbupdate_randtest_pooldef_migration_fix', '2');
    }
} elseif ($fixState === '1') {
    if ($ilDB->tableExists('tst_test_random')) {
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

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!(int) $row['num_of_q']) {
                $row['num_of_q'] = null;
            }

            if (!strlen($row['pool_title'])) {
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

            if (!is_array($syncTimes[$row['test_fi']])) {
                $syncTimes[$row['test_fi']] = array();
            }

            $syncTimes[$row['test_fi']][] = $row['tstamp'];
        }

        foreach ($syncTimes as $testId => $times) {
            $assumedSyncTS = max($times);

            $ilDB->update(
                'tst_rnd_quest_set_cfg',
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
if ($ilDB->tableExists('tst_test_random')) {
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
if (!$ilDB->tableColumnExists('ecs_part_settings', 'dtoken')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'dtoken',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        )
    );
}
?>
<#4413>
<?php
if ($ilDB->tableColumnExists('crs_objectives', 'description')) {
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

if (!$ilDB->uniqueConstraintExists('tst_active', array('user_fi', 'test_fi', 'anonymous_id'))) {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        if (is_null($row['anonymous_id']) || !strlen($row['anonymous_id'])) {
            $row['anonymous_id'] = '-';
        }

        $ilDB->replace(
            'tmp_active_fix',
            array(
                'test_fi' => array('integer', $row['test_fi']),
                'user_fi' => array('integer', (int) $row['user_fi']),
                'anonymous_id' => array('text', $row['anonymous_id'])
            ),
            array()
        );
    }
}

?>
<#4416>
<?php

if ($ilDB->tableExists('tmp_active_fix')) {
    $selectUser = $ilDB->prepare(
        "
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id IS NULL
		",
        array('integer', 'integer')
    );

    $selectAnonym = $ilDB->prepare(
        "
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi IS NULL AND anonymous_id = ?
		",
        array('integer', 'text')
    );

    $select = $ilDB->prepare(
        "
			SELECT active_id, max_points, reached_points, passed FROM tst_active
			LEFT JOIN tst_result_cache ON active_fi = active_id
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id = ?
		",
        array('integer', 'integer', 'text')
    );

    $update = $ilDB->prepareManip(
        "
			UPDATE tmp_active_fix SET active_id = ?
			WHERE test_fi = ? AND user_fi = ? AND anonymous_id = ?
		",
        array('integer', 'integer', 'integer', 'text')
    );

    $res1 = $ilDB->query("SELECT * FROM tmp_active_fix WHERE active_id IS NULL");

    while ($row1 = $ilDB->fetchAssoc($res1)) {
        if (!$row1['user_fi']) {
            $res2 = $ilDB->execute($selectAnonym, array(
                $row1['test_fi'], $row1['anonymous_id']
            ));
        } elseif ($row1['anonymous_id'] == '-') {
            $res2 = $ilDB->execute($selectUser, array(
                $row1['test_fi'], $row1['user_fi']
            ));
        } else {
            $res2 = $ilDB->execute($select, array(
                $row1['test_fi'], $row1['user_fi'], $row1['anonymous_id']
            ));
        }

        $activeId = null;
        $passed = null;
        $points = null;

        while ($row2 = $ilDB->fetchAssoc($res2)) {
            if ($activeId === null) {
                $activeId = $row2['active_id'];
                $passed = $row2['passed'];
                $points = $row2['reached_points'];
                continue;
            }

            if (!$row2['max_points']) {
                continue;
            }

            if (!$passed && $row2['passed']) {
                $activeId = $row2['active_id'];
                $passed = $row2['passed'];
                $points = $row2['reached_points'];
                continue;
            }

            if ($passed && !$row2['passed']) {
                continue;
            }

            if ($row2['reached_points'] > $points) {
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

if ($ilDB->tableExists('tmp_active_fix')) {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        if (!$row['user_fi']) {
            $ilDB->execute($deleteAnonymActives, array(
                $row['active_id'], $row['test_fi'], $row['anonymous_id']
            ));
        } elseif ($row['anonymous_id'] == '-') {
            $ilDB->execute($deleteUserActives, array(
                $row['active_id'], $row['test_fi'], $row['user_fi']
            ));
        } else {
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

if (!$ilDB->uniqueConstraintExists('tst_active', array('user_fi', 'test_fi', 'anonymous_id'))) {
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

if (!(int) $settings->get('quest_process_lock_mode_autoinit', 0)) {
    if ($settings->get('quest_process_lock_mode', 'none') == 'none') {
        $settings->set('quest_process_lock_mode', 'db');
    }

    $settings->set('quest_process_lock_mode_autoinit_done', 1);
}

?>
<#4423>
<?php

if ($ilDB->tableColumnExists("usr_portfolio", "comments")) {
    // #14661 - centralized public comments setting
    include_once "Services/Notes/classes/class.ilNote.php";

    $data = array();

    $set = $ilDB->query("SELECT prtf.id,prtf.comments,od.type" .
        " FROM usr_portfolio prtf" .
        " JOIN object_data od ON (prtf.id = od.obj_id)");
    while ($row = $ilDB->fetchAssoc($set)) {
        $row["comments"] = (bool) $row["comments"];
        $data[] = $row;
    }

    $set = $ilDB->query("SELECT id,notes comments" .
        " FROM il_blog");
    while ($row = $ilDB->fetchAssoc($set)) {
        $row["type"] = "blog";
        $row["comments"] = (bool) $row["comments"];
        $data[] = $row;
    }

    $set = $ilDB->query("SELECT cobj.id,cobj.pub_notes comments,od.type" .
        " FROM content_object cobj" .
        " JOIN object_data od ON (cobj.id = od.obj_id)");
    while ($row = $ilDB->fetchAssoc($set)) {
        $row["comments"] = ($row["comments"] == "y" ? true : false);
        $data[] = $row;
    }

    $set = $ilDB->query("SELECT id,show_comments comments" .
        " FROM il_poll");
    while ($row = $ilDB->fetchAssoc($set)) {
        $row["type"] = "poll";
        $row["comments"] = (bool) $row["comments"];
        $data[] = $row;
    }

    if (sizeof($data)) {
        foreach ($data as $item) {
            if ($item["id"] && $item["type"]) {
                $ilDB->manipulate("DELETE FROM note_settings" .
                    " WHERE rep_obj_id = " . $ilDB->quote($item["id"], "integer") .
                    " AND obj_id = " . $ilDB->quote(0, "integer") .
                    " AND obj_type = " . $ilDB->quote($item["type"], "text"));

                if ($item["comments"]) {
                    $ilDB->manipulate("INSERT INTO note_settings" .
                        " (rep_obj_id, obj_id, obj_type, activated)" .
                        " VALUES (" . $ilDB->quote($item["id"], "integer") .
                        ", " . $ilDB->quote(0, "integer") .
                        ", " . $ilDB->quote($item["type"], "text") .
                        ", " . $ilDB->quote(1, "integer") . ")");
                }
            }
        }
    }
}

?>
<#4424>
<?php

if ($ilDB->tableColumnExists("usr_portfolio", "comments")) {
    $ilDB->dropTableColumn("usr_portfolio", "comments");
    $ilDB->dropTableColumn("il_blog", "notes");
    $ilDB->dropTableColumn("content_object", "pub_notes");
    $ilDB->dropTableColumn("il_poll", "show_comments");
}

?>

<#4425>
<?php

if ($ilDB->tableColumnExists('ecs_cms_data', 'cms_id')) {
    $ilDB->renameTableColumn('ecs_cms_data', 'cms_id', 'cms_bak');
    $ilDB->addTableColumn(
        'ecs_cms_data',
        'cms_id',
        array(
            "type" => "text",
            "notnull" => false,
            "length" => 512
        )
    );

    $query = 'UPDATE ecs_cms_data SET cms_id = cms_bak ';
    $ilDB->manipulate($query);

    $ilDB->dropTableColumn('ecs_cms_data', 'cms_bak');
}
?>
<#4426>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4427>
<?php

if ($ilDB->tableColumnExists('ecs_import', 'econtent_id')) {
    $ilDB->renameTableColumn('ecs_import', 'econtent_id', 'econtent_id_bak');
    $ilDB->addTableColumn(
        'ecs_import',
        'econtent_id',
        array(
            "type" => "text",
            "notnull" => false,
            "length" => 512
        )
    );

    $query = 'UPDATE ecs_import SET econtent_id = econtent_id_bak ';
    $ilDB->manipulate($query);

    $ilDB->dropTableColumn('ecs_import', 'econtent_id_bak');
}
?>
<#4428>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
if ($tgt_ops_id) {
    $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
    if ($lp_type_id) {
        // add "edit_learning_progress" to session
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);

        // clone settings from "write" to "edit_learning_progress"
        $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
        ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);

        // clone settings from "write" to "read_learning_progress" (4287 did not work for sessions)
        $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
        if ($tgt_ops_id) {
            ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
        }
    }
}

?>

<#4429>
<?php

$query = 'DELETE from cal_recurrence_rules WHERE cal_id IN ( select cal_id from cal_entries where is_milestone =  ' . $ilDB->quote(1, 'integer') . ')';
$ilDB->manipulate($query);

?>

<#4430>
<?php
if (!$ilDB->tableColumnExists('qpl_a_cloze_combi_res', 'row_id')) {
    $query = 'DELETE from qpl_a_cloze_combi_res';
    $ilDB->manipulate($query);
    $ilDB->addTableColumn(
        'qpl_a_cloze_combi_res',
        'row_id',
        array(
                 'type' => 'integer',
                 'length' => 4,
                 'default' => 0
             )
    );
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
if ($ilDB->tableColumnExists('tst_tests', 'examid_in_kiosk')) {
    $ilDB->renameTableColumn('tst_tests', 'examid_in_kiosk', 'examid_in_test_pass');
}
?>
<#4435>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'show_exam_id')) {
    $ilDB->renameTableColumn('tst_tests', 'show_exam_id', 'examid_in_test_res');
}
?>
<#4436>
<?php
if (!$ilDB->tableColumnExists('il_wiki_page', 'hide_adv_md')) {
    $ilDB->addTableColumn(
        'il_wiki_page',
        'hide_adv_md',
        array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0
        )
    );
}
?>
<#4437>
<?php
if (!$ilDB->tableColumnExists('tst_active', 'start_lock')) {
    $ilDB->addTableColumn(
        'tst_active',
        'start_lock',
        array(
            'type' => 'text',
            'length' => 128,
            'notnull' => false,
            'default' => null
        )
    );
}
?>
<#4438>
<?php

$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT count(*) cnt FROM settings WHERE module = %s AND keyword = %s",
    array('text', 'text'),
    array('assessment', 'ass_process_lock_mode')
));

if ($row['cnt']) {
    $ilDB->manipulateF(
        "DELETE FROM settings WHERE module = %s AND keyword = %s",
        array('text', 'text'),
        array('assessment', 'quest_process_lock_mode')
    );
} else {
    $ilDB->update(
        'settings',
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
if (!$ilDB->tableColumnExists('file_based_lm', 'show_lic')) {
    $ilDB->addTableColumn(
        'file_based_lm',
        'show_lic',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => null
        )
    );
}
if (!$ilDB->tableColumnExists('file_based_lm', 'show_bib')) {
    $ilDB->addTableColumn(
        'file_based_lm',
        'show_bib',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => null
        )
    );
}
?>
<#4440>
<?php

$ilDB->manipulate("UPDATE settings " .
    "SET value = " . $ilDB->quote(1370, "text") .
    " WHERE module = " . $ilDB->quote("blga", "text") .
    " AND keyword = " . $ilDB->quote("banner_width", "text") .
    " AND value = " . $ilDB->quote(880, "text"));

$ilDB->manipulate("UPDATE settings " .
    "SET value = " . $ilDB->quote(1370, "text") .
    " WHERE module = " . $ilDB->quote("prfa", "text") .
    " AND keyword = " . $ilDB->quote("banner_width", "text") .
    " AND value = " . $ilDB->quote(880, "text"));

?>
<#4441>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');
if ($tgt_ops_id) {
    $feed_type_id = ilDBUpdateNewObjectType::getObjectTypeId('feed');
    if ($feed_type_id) {
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
    if (!$ilDB->tableColumnExists('skl_user_has_level', 'self_eval')) {
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
    if (!$ilDB->tableColumnExists('skl_user_skill_level', 'self_eval')) {
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
        $ilDB->addPrimaryKey(
            "skl_user_has_level",
            array("level_id", "user_id", "trigger_obj_id", "tref_id", "self_eval")
        );
?>
<#4450>
<?php
        $ilDB->modifyTableColumn(
    "skl_user_has_level",
    "trigger_obj_type",
    array(
                "type" => "text",
                "length" => 4,
                "notnull" => false
            )
);

        $ilDB->modifyTableColumn(
            "skl_user_skill_level",
            "trigger_obj_type",
            array(
                "type" => "text",
                "length" => 4,
                "notnull" => false
            )
        );
?>
<#4451>
<?php
    $ilSetting = new ilSetting();
    if ((int) $ilSetting->get("optes_360_db") <= 0) {
        /*$ilDB->manipulate("DELETE FROM skl_user_has_level WHERE ".
            " self_eval = ".$ilDB->quote(1, "integer")
        );
        $ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE ".
            " self_eval = ".$ilDB->quote(1, "integer")
        );*/

        $set = $ilDB->query("SELECT * FROM skl_self_eval_level ORDER BY last_update ASC");
        $writtenkeys = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!in_array($rec["level_id"] . ":" . $rec["user_id"] . ":" . $rec["tref_id"], $writtenkeys)) {
                $writtenkeys[] = $rec["level_id"] . ":" . $rec["user_id"] . ":" . $rec["tref_id"];
                $q = "INSERT INTO skl_user_has_level " .
                    "(level_id, user_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_title, tref_id, trigger_obj_type, self_eval) VALUES (" .
                    $ilDB->quote($rec["level_id"], "integer") . "," .
                    $ilDB->quote($rec["user_id"], "integer") . "," .
                    $ilDB->quote($rec["last_update"], "timestamp") . "," .
                    $ilDB->quote($rec["skill_id"], "integer") . "," .
                    $ilDB->quote(0, "integer") . "," .
                    $ilDB->quote(0, "integer") . "," .
                    $ilDB->quote("", "text") . "," .
                    $ilDB->quote($rec["tref_id"], "integer") . "," .
                    $ilDB->quote("", "text") . "," .
                    $ilDB->quote(1, "integer") .
                    ")";
                $ilDB->manipulate($q);
            } else {
                $ilDB->manipulate(
                    "UPDATE skl_user_has_level SET " .
                    " status_date = " . $ilDB->quote($rec["last_update"], "timestamp") . "," .
                    " skill_id = " . $ilDB->quote($rec["skill_id"], "integer") .
                    " WHERE level_id = " . $ilDB->quote($rec["level_id"], "integer") .
                    " AND user_id = " . $ilDB->quote($rec["user_id"], "integer") .
                    " AND trigger_obj_id = " . $ilDB->quote(0, "integer") .
                    " AND tref_id = " . $ilDB->quote($rec["tref_id"], "integer") .
                    " AND self_eval = " . $ilDB->quote(1, "integer")
                );
            }
            $q = "INSERT INTO skl_user_skill_level " .
                "(level_id, user_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_title, tref_id, trigger_obj_type, self_eval, status, valid) VALUES (" .
                $ilDB->quote($rec["level_id"], "integer") . "," .
                $ilDB->quote($rec["user_id"], "integer") . "," .
                $ilDB->quote($rec["last_update"], "timestamp") . "," .
                $ilDB->quote($rec["skill_id"], "integer") . "," .
                $ilDB->quote(0, "integer") . "," .
                $ilDB->quote(0, "integer") . "," .
                $ilDB->quote("", "text") . "," .
                $ilDB->quote($rec["tref_id"], "integer") . "," .
                $ilDB->quote("", "text") . "," .
                $ilDB->quote(1, "integer") . "," .
                $ilDB->quote(1, "integer") . "," .
                $ilDB->quote(1, "integer") .
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
    if (!$ilDB->sequenceExists('booking_reservation_group')) {
        $ilDB->createSequence('booking_reservation_group');
    }
?>
<#4456>
<?php

    if (!$ilDB->tableColumnExists('crs_objective_tst', 'tst_limit_p')) {
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
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $questions[$row->objective_id . '_' . $row->ref_id][] = $row->question_id;
}

$GLOBALS['ilLog']->write(__METHOD__ . ': ' . print_r($questions, true));

foreach ($questions as $objective_ref_id => $qst_ids) {
    $parts = explode('_', $objective_ref_id);
    $objective_id = $parts[0];
    $tst_ref_id = $parts[1];

    $sum = 0;
    foreach ((array) $qst_ids as $qst_id) {
        $query = 'SELECT points FROM qpl_questions WHERE question_id = ' . $ilDB->quote($qst_id, 'integer');
        $res_qst = $ilDB->query($query);
        while ($row = $res_qst->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sum += $row->points;
        }
        if ($sum > 0) {
            // read limit
            $query = 'SELECT tst_limit FROM crs_objective_tst ' .
                    'WHERE objective_id = ' . $ilDB->quote($objective_id, 'integer');
            $res_limit = $ilDB->query($query);

            $limit_points = 0;
            while ($row = $res_limit->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $limit_points = $row->tst_limit;
            }
            // calculate percentage
            $limit_p = $limit_points / $sum * 100;
            $limit_p = intval($limit_p);
            $limit_p = ($limit_p >= 100 ? 100 : $limit_p);

            // update
            $query = 'UPDATE crs_objective_tst ' .
                    'SET tst_limit_p = ' . $ilDB->quote($limit_p, 'integer') . ' ' .
                    'WHERE objective_id = ' . $ilDB->quote($objective_id, 'integer') . ' ' .
                    'AND ref_id = ' . $ilDB->quote($tst_ref_id, 'integer');
            $ilDB->manipulate($query);
        }
    }
}
?>
<#4458>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'intro_enabled')) {
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
if (!$ilDB->tableColumnExists('tst_tests', 'starting_time_enabled')) {
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
if (!$ilDB->tableColumnExists('tst_tests', 'ending_time_enabled')) {
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
if ($ilDB->tableColumnExists('tst_tests', 'intro_enabled')) {
    $ilDB->dropTableColumn('tst_tests', 'intro_enabled');
}
?>
<#4462>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'starting_time_enabled')) {
    $ilDB->dropTableColumn('tst_tests', 'starting_time_enabled');
}
?>
<#4463>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'ending_time_enabled')) {
    $ilDB->dropTableColumn('tst_tests', 'ending_time_enabled');
}
?>
<#4464>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'intro_enabled')) {
    $ilDB->addTableColumn('tst_tests', 'intro_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->queryF(
        "UPDATE tst_tests SET intro_enabled = %s WHERE LENGTH(introduction) > %s",
        array('integer', 'integer'),
        array(1, 0)
    );

    $ilDB->queryF(
        "UPDATE tst_tests SET intro_enabled = %s WHERE LENGTH(introduction) = %s OR LENGTH(introduction) IS NULL",
        array('integer', 'integer'),
        array(0, 0)
    );
}
?>
<#4465>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'starting_time_enabled')) {
    $ilDB->addTableColumn('tst_tests', 'starting_time_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->queryF(
        "UPDATE tst_tests SET starting_time_enabled = %s WHERE LENGTH(starting_time) > %s",
        array('integer', 'integer'),
        array(1, 0)
    );

    $ilDB->queryF(
        "UPDATE tst_tests SET starting_time_enabled = %s WHERE LENGTH(starting_time) = %s OR LENGTH(starting_time) IS NULL",
        array('integer', 'integer'),
        array(0, 0)
    );
}
?>
<#4466>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'ending_time_enabled')) {
    $ilDB->addTableColumn('tst_tests', 'ending_time_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->queryF(
        "UPDATE tst_tests SET ending_time_enabled = %s WHERE LENGTH(ending_time) > %s",
        array('integer', 'integer'),
        array(1, 0)
    );

    $ilDB->queryF(
        "UPDATE tst_tests SET ending_time_enabled = %s WHERE LENGTH(ending_time) = %s OR LENGTH(ending_time) IS NULL",
        array('integer', 'integer'),
        array(0, 0)
    );
}
?>
<#4467>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'password_enabled')) {
    $ilDB->addTableColumn('tst_tests', 'password_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->queryF(
        "UPDATE tst_tests SET password_enabled = %s WHERE LENGTH(password) > %s",
        array('integer', 'integer'),
        array(1, 0)
    );

    $ilDB->queryF(
        "UPDATE tst_tests SET password_enabled = %s WHERE LENGTH(password) = %s OR LENGTH(password) IS NULL",
        array('integer', 'integer'),
        array(0, 0)
    );
}
?>
<#4468>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'limit_users_enabled')) {
    $ilDB->addTableColumn('tst_tests', 'limit_users_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->queryF(
        "UPDATE tst_tests SET limit_users_enabled = %s WHERE allowedusers IS NOT NULL AND allowedusers > %s",
        array('integer', 'integer'),
        array(1, 0)
    );

    $ilDB->queryF(
        "UPDATE tst_tests SET limit_users_enabled = %s WHERE allowedusers IS NULL OR allowedusers <= %s",
        array('integer', 'integer'),
        array(0, 0)
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
    if (!$ilDB->indexExistsByFields('page_question', array('page_parent_type','page_id', 'page_lang'))) {
        $ilDB->addIndex('page_question', array('page_parent_type','page_id', 'page_lang'), 'i1');
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
if ($lp_type_id) {
    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');

    // clone settings from "write" to "edit_learning_progress"
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
    if ($tgt_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);
        ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
    }

    // clone settings from "write" to "read_learning_progress"
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
    if ($tgt_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);
        ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
    }
}

?>
<#4475>
<?php

if ($ilDB->tableColumnExists('obj_stat', 'tstamp')) {
    $ilDB->dropTableColumn('obj_stat', 'tstamp');
}

?>
<#4476>
<?php
if (!$ilDB->uniqueConstraintExists('usr_data', array('login'))) {
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
    if ($data['cnt'] > 0) {
        echo "<pre>
				Dear Administrator,

				PLEASE READ THE FOLLOWING INSTRUCTIONS

				The update process has been stopped due to data inconsistency reasons.
				We found multiple ILIAS user accounts with the same login. You have to fix this issue manually.

				Database table: usr_data
				Field: login

				You can determine these accounts by executing the following SQL statement:

				SELECT ud.*  FROM usr_data ud
				INNER JOIN (
					SELECT login FROM usr_data GROUP BY login HAVING COUNT(*) > 1
				) tmp ON tmp.login = ud.login

				Please manipulate the affected records by choosing different login names or use the following statement
				to change the duplicate login name to unique name like [usr_id]_[login]_duplicate. The further changes on
				user data (e.g. deletion of duplicates) could then be easily done in ILIAS administration.

				UPDATE usr_data ud
				INNER JOIN (
					SELECT udinner.login, udinner.usr_id
					FROM usr_data udinner
					GROUP BY udinner.login
					HAVING COUNT(udinner.login) > 1
				) dup ON ud.login = dup.login
				SET ud.login = CONCAT(CONCAT(CONCAT(ud.usr_id, '_'), CONCAT(ud.login, '_')), 'duplicate')

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

if (!$ilDB->tableColumnExists('tst_tests', 'broken')) {
    $ilDB->addTableColumn(
        'tst_tests',
        'broken',
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
$ilDB->manipulate(
    "UPDATE style_data SET " .
    " uptodate = " . $ilDB->quote(0, "integer")
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
if (!$ilDB->tableColumnExists('qpl_questionpool', 'skill_service')) {
    $ilDB->addTableColumn('qpl_questionpool', 'skill_service', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->manipulateF(
        'UPDATE qpl_questionpool SET skill_service = %s',
        array('integer'),
        array(0)
    );
}
?>
<#4485>
<?php
if (!$ilDB->tableExists('qpl_qst_skl_assigns')) {
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

    if ($ilDB->tableExists('tst_skl_qst_assigns')) {
        $res = $ilDB->query("
			SELECT tst_skl_qst_assigns.*, tst_tests.obj_fi
			FROM tst_skl_qst_assigns
			INNER JOIN tst_tests ON test_id = test_fi
		");

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace(
                'qpl_qst_skl_assigns',
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

if (!$setting->get('dbup_tst_skl_thres_mig_done', 0)) {
    if (!$ilDB->tableExists('tst_threshold_tmp')) {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->replace(
            'tst_threshold_tmp',
            array('test_id' => array('integer', $row['test_id'])),
            array('obj_id' => array('integer', $row['obj_fi']))
        );
    }

    if (!$ilDB->tableColumnExists('tst_skl_thresholds', 'tmp')) {
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
if ($ilDB->tableExists('tst_threshold_tmp')) {
    $stmtSelectSklPointSum = $ilDB->prepare(
        "SELECT skill_base_fi, skill_tref_fi, SUM(skill_points) points_sum FROM qpl_qst_skl_assigns
			WHERE obj_fi = ? GROUP BY skill_base_fi, skill_tref_fi",
        array('integer')
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

    while ($row1 = $ilDB->fetchAssoc($res1)) {
        $res2 = $ilDB->execute($stmtSelectSklPointSum, array($row1['obj_id']));

        while ($row2 = $ilDB->fetchAssoc($res2)) {
            $ilDB->execute($stmtUpdatePercentThresholds, array(
                $row2['points_sum'], $row1['test_id'], $row2['skill_base_fi'], $row2['skill_tref_fi']
            ));
        }
    }
}
?>
<#4488>
<?php
if ($ilDB->tableExists('tst_threshold_tmp')) {
    $ilDB->dropTable('tst_threshold_tmp');
}
?>
<#4489>
<?php
if ($ilDB->tableColumnExists('tst_skl_thresholds', 'tmp')) {
    $ilDB->manipulate("UPDATE tst_skl_thresholds SET threshold = tmp");
    $ilDB->dropTableColumn('tst_skl_thresholds', 'tmp');
}
?>
<#4490>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_skl_assigns', 'eval_mode')) {
    $ilDB->addTableColumn('qpl_qst_skl_assigns', 'eval_mode', array(
        'type' => 'text',
        'length' => 16,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->manipulateF(
        "UPDATE qpl_qst_skl_assigns SET eval_mode = %s",
        array('text'),
        array('result')
    );
}
?>
<#4491>
<?php
if (!$ilDB->tableExists('qpl_qst_skl_sol_expr')) {
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

while ($row = $ilDB->fetchAssoc($res)) {
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
    array('text'),
    array('sktr')
));

if ($row['cnt']) {
    $res = $ilDB->queryF(
        'SELECT obj_fi, question_fi, skill_base_fi, skill_tref_fi FROM qpl_qst_skl_assigns LEFT JOIN skl_tree_node ON skill_base_fi = obj_id WHERE type = %s',
        array('text'),
        array('sktr')
    );

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->update(
            'qpl_qst_skl_assigns',
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
    "UPDATE qpl_qst_skl_assigns SET eval_mode = %s WHERE eval_mode IS NULL",
    array('text'),
    array('result')
);
?>
<#4495>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4496>
<?php
if (!$ilDB->tableExists('mail_cron_orphaned')) {
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
if ($ilDB->tableExists('chat_blocked')) {
    $ilDB->dropTable('chat_blocked');
}
?>
<#4498>
<?php
// Don't remove this comment
?>
<#4499>
<?php
if ($ilDB->tableExists('chat_invitations')) {
    $ilDB->dropTable('chat_invitations');
}
?>
<#4500>
<?php
if ($ilDB->tableExists('chat_records')) {
    $ilDB->dropTable('chat_records');
}
?>
<#4501>
<?php
if ($ilDB->sequenceExists('chat_records')) {
    $ilDB->dropSequence('chat_records');
}
?>
<#4502>
<?php
if ($ilDB->sequenceExists('chat_rooms')) {
    $ilDB->dropSequence('chat_rooms');
}
?>
<#4503>
<?php
if ($ilDB->tableExists('chat_rooms')) {
    $ilDB->dropTable('chat_rooms');
}
?>
<#4504>
<?php
if ($ilDB->tableExists('chat_room_messages')) {
    $ilDB->dropTable('chat_room_messages');
}
?>
<#4505>
<?php
if ($ilDB->sequenceExists('chat_room_messages')) {
    $ilDB->dropSequence('chat_room_messages');
}
?>
<#4506>
<?php
if ($ilDB->sequenceExists('chat_smilies')) {
    $ilDB->dropSequence('chat_smilies');
}
?>
<#4507>
<?php
if ($ilDB->tableExists('chat_smilies')) {
    $ilDB->dropTable('chat_smilies');
}
?>
<#4508>
<?php
if ($ilDB->tableExists('chat_user')) {
    $ilDB->dropTable('chat_user');
}
?>
<#4509>
<?php
if ($ilDB->tableExists('chat_record_data')) {
    $ilDB->dropTable('chat_record_data');
}
?>
<#4510>
<?php
if ($ilDB->sequenceExists('chat_record_data')) {
    $ilDB->dropSequence('chat_record_data');
}
?>
<#4511>
<?php
if ($ilDB->tableExists('ilinc_data')) {
    $ilDB->dropTable('ilinc_data');
}
?>
<#4512>
<?php
if ($ilDB->tableExists('ilinc_registration')) {
    $ilDB->dropTable('ilinc_registration');
}
?>
<#4513>
<?php
if ($ilDB->tableColumnExists('usr_data', 'ilinc_id')) {
    $ilDB->dropTableColumn('usr_data', 'ilinc_id');
}

if ($ilDB->tableColumnExists('usr_data', 'ilinc_login')) {
    $ilDB->dropTableColumn('usr_data', 'ilinc_login');
}

if ($ilDB->tableColumnExists('usr_data', 'ilinc_passwd')) {
    $ilDB->dropTableColumn('usr_data', 'ilinc_passwd');
}
?>
<#4514>
<?php
if ($ilDB->uniqueConstraintExists('tst_sequence', array('active_fi', 'pass'))) {
    $ilDB->dropUniqueConstraintByFields('tst_sequence', array('active_fi', 'pass'));
    $ilDB->addPrimaryKey('tst_sequence', array('active_fi', 'pass'));
}
?>
<#4515>
<?php
if ($ilDB->uniqueConstraintExists('tst_pass_result', array('active_fi', 'pass'))) {
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
$res = $ilDB->query($crpra_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    $mopt_dup_query = "
	SELECT proom_id, user_id
	FROM chatroom_proomaccess
	GROUP BY proom_id, user_id
	HAVING COUNT(*) > 1
	";
    $res = $ilDB->query($mopt_dup_query);

    $stmt_del = $ilDB->prepareManip("DELETE FROM chatroom_proomaccess WHERE proom_id = ? AND user_id = ?", array('integer', 'integer'));
    $stmt_in = $ilDB->prepareManip("INSERT INTO chatroom_proomaccess (proom_id, user_id) VALUES(?, ?)", array('integer', 'integer'));

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($stmt_del, array($row['proom_id'], $row['user_id']));
        $ilDB->execute($stmt_in, array($row['proom_id'], $row['user_id']));
    }
}

$res = $ilDB->query($crpra_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
$res = $ilDB->query($mopt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    $mopt_dup_query = "
	SELECT user_id
	FROM mail_options
	GROUP BY user_id
	HAVING COUNT(*) > 1
	";
    $res = $ilDB->query($mopt_dup_query);

    $stmt_sel = $ilDB->prepare("SELECT * FROM mail_options WHERE user_id = ?", array('integer'));
    $stmt_del = $ilDB->prepareManip("DELETE FROM mail_options WHERE user_id = ?", array('integer'));
    $stmt_in = $ilDB->prepareManip("INSERT INTO mail_options (user_id, linebreak, signature, incoming_type, cronjob_notification) VALUES(?, ?, ?, ?, ?)", array('integer', 'integer', 'text', 'integer', 'integer'));

    while ($row = $ilDB->fetchAssoc($res)) {
        $opt_res = $ilDB->execute($stmt_sel, array($row['user_id']));
        $opt_row = $ilDB->fetchAssoc($opt_res);
        if ($opt_row) {
            $ilDB->execute($stmt_del, array($opt_row['user_id']));
            $ilDB->execute($stmt_in, array($opt_row['user_id'], $opt_row['linebreak'], $opt_row['signature'], $opt_row['incoming_type'], $opt_row['cronjob_notification']));
        }
    }
}

$res = $ilDB->query($mopt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
$res = $ilDB->query($psc_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    $psc_dup_query = "
	SELECT psc_ps_fk, psc_pc_fk, psc_pcc_fk
	FROM payment_statistic_coup
	GROUP BY psc_ps_fk, psc_pc_fk, psc_pcc_fk
	HAVING COUNT(*) > 1
	";
    $res = $ilDB->query($psc_dup_query);

    $stmt_del = $ilDB->prepareManip("DELETE FROM payment_statistic_coup WHERE psc_ps_fk = ? AND psc_pc_fk = ? AND psc_pcc_fk = ?", array('integer', 'integer', 'integer'));
    $stmt_in = $ilDB->prepareManip("INSERT INTO payment_statistic_coup (psc_ps_fk, psc_pc_fk, psc_pcc_fk) VALUES(?, ?, ?)", array('integer', 'integer', 'integer'));

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($stmt_del, array($row['psc_ps_fk'], $row['psc_pc_fk'], $row['psc_pcc_fk']));
        $ilDB->execute($stmt_in, array($row['psc_ps_fk'], $row['psc_pc_fk'], $row['psc_pcc_fk']));
    }
}

$res = $ilDB->query($psc_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
$res = $ilDB->query($msave_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    $msave_dup_query = "
	SELECT user_id
	FROM mail_saved
	GROUP BY user_id
	HAVING COUNT(*) > 1
	";
    $res = $ilDB->query($msave_dup_query);

    $stmt_sel = $ilDB->prepare("SELECT * FROM mail_saved WHERE user_id = ?", array('integer'));
    $stmt_del = $ilDB->prepareManip("DELETE FROM mail_saved WHERE user_id = ?", array('integer'));

    while ($row = $ilDB->fetchAssoc($res)) {
        $opt_res = $ilDB->execute($stmt_sel, array($row['user_id']));
        $opt_row = $ilDB->fetchAssoc($opt_res);
        if ($opt_row) {
            $ilDB->execute($stmt_del, array($opt_row['user_id']));
            $ilDB->insert(
                'mail_saved',
                array(
                    'user_id' => array('integer', $opt_row['user_id']),
                    'm_type' => array('text', $opt_row['m_type']),
                    'm_email' => array('integer', $opt_row['m_email']),
                    'm_subject' => array('text', $opt_row['m_subject']),
                    'use_placeholders' => array('integer', $opt_row['use_placeholders']),
                    'm_message' => array('clob', $opt_row['m_message']),
                    'rcp_to' => array('clob', $opt_row['rcp_to']),
                    'rcp_cc' => array('clob', $opt_row['rcp_cc']),
                    'rcp_bcc' => array('clob', $opt_row['rcp_bcc']),
                    'attachments' => array('clob', $opt_row['attachments'])
                )
            );
        }
    }
}

$res = $ilDB->query($msave_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
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
$res = $ilDB->query($chrban_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
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
    $stmt_in = $ilDB->prepareManip("INSERT INTO chatroom_bans (room_id, user_id, timestamp, remark) VALUES(?, ?, ?, ?)", array('integer', 'integer',  'integer',  'text'));

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($stmt_del, array($row['room_id'], $row['user_id']));
        $ilDB->execute($stmt_in, array($row['room_id'], $row['user_id'], $row['timestamp'], $row['remark']));
    }
}

$res = $ilDB->query($chrban_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    die("There are still duplicate entries in table 'chatroom_bans'. Please execute this database update step again.");
}

$ilDB->addPrimaryKey('chatroom_bans', array('room_id', 'user_id'));
?>
<#4521>
<?php
if (!$ilDB->sequenceExists('chatroom_psessionstmp')) {
    $ilDB->createSequence('chatroom_psessionstmp');
}
?>
<#4522>
<?php
if (!$ilDB->tableExists('chatroom_psessionstmp')) {
    $fields = array(
        'psess_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
        'proom_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'user_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'connected' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
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

while ($row = $ilDB->fetchAssoc($res)) {
    $psess_id = $ilDB->nextId('chatroom_psessionstmp');
    $ilDB->execute($stmt_in, array($psess_id, (int) $row['proom_id'], (int) $row['user_id'], (int) $row['connected'], (int) $row['disconnected']));
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
if (!$ilDB->sequenceExists('chatroom_psessions')) {
    $query = "SELECT MAX(psess_id) mpsess_id FROM chatroom_psessions";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('chatroom_psessions', (int) $row['mpsess_id'] + 1);
}
?>
<#4527>
<?php
if ($ilDB->sequenceExists('chatroom_psessionstmp')) {
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
if (!$ilDB->sequenceExists('chatroom_sessionstmp')) {
    $ilDB->createSequence('chatroom_sessionstmp');
}
?>
<#4531>
<?php
if (!$ilDB->tableExists('chatroom_sessionstmp')) {
    $fields = array(
        'sess_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
        'room_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'user_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'userdata' => array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null),
        'connected' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'disconnected' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
    );
    $ilDB->createTable('chatroom_sessionstmp', $fields);
    $ilDB->addPrimaryKey('chatroom_sessionstmp', array('sess_id'));
}
?>
<#4532>
<?php
if ($ilDB->getDBType() == 'innodb' || $ilDB->getDBType() == 'mysql') {
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
} else {
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

while ($row = $ilDB->fetchAssoc($res)) {
    $sess_id = $ilDB->nextId('chatroom_sessionstmp');
    $ilDB->execute($stmt_in, array($sess_id, (int) $row['room_id'], (int) $row['user_id'], (int) $row['connected'], (int) $row['disconnected'], (string) $row['userdata']));
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
if (!$ilDB->sequenceExists('chatroom_sessions')) {
    $query = "SELECT MAX(sess_id) msess_id FROM chatroom_sessions";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('chatroom_sessions', (int) $row['msess_id'] + 1);
}
?>
<#4536>
<?php
if ($ilDB->sequenceExists('chatroom_sessionstmp')) {
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

if ($dupsCountRow['dups_cnt'] > 0) {
    if (!$ilDB->tableExists('dups_clozecombis_qst')) {
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

    if (!$ilDB->tableExists('dups_clozecombis_rows')) {
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

if ($ilDB->tableExists('dups_clozecombis_qst')) {
    $res = $ilDB->query("
			SELECT combination_id, question_fi, gap_fi, row_id, COUNT(*)
			FROM qpl_a_cloze_combi_res
			LEFT JOIN dups_clozecombis_qst ON qst = question_fi
			WHERE qst IS NULL
			GROUP BY combination_id, question_fi, gap_fi, row_id
			HAVING COUNT(*) > 1
		");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->replace(
            'dups_clozecombis_qst',
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

if ($ilDB->tableExists('dups_clozecombis_qst')) {
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

    while ($qstRow = $ilDB->fetchAssoc($qstRes)) {
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

if ($ilDB->tableExists('dups_clozecombis_qst')) {
    $deleteRowsStmt = $ilDB->prepareManip(
        "DELETE FROM dups_clozecombis_rows WHERE question_fi = ?",
        array('integer')
    );

    $selectRowsStmt = $ilDB->prepare(
        "SELECT * FROM qpl_a_cloze_combi_res WHERE question_fi = ? ORDER BY combination_id, row_id, gap_fi",
        array('integer')
    );

    $insertRowStmt = $ilDB->prepareManip(
        "INSERT INTO dups_clozecombis_rows (combination_id, question_fi, gap_fi, answer, points, best_solution, row_id)
			VALUES (?, ?, ?, ?, ?, ?, ?)",
        array('integer', 'integer', 'integer', 'text', 'float', 'integer', 'integer')
    );

    $qstRes = $ilDB->query("
			SELECT qst, num
			FROM dups_clozecombis_qst
			LEFT JOIN dups_clozecombis_rows
			ON question_fi = qst
			GROUP BY qst, num, question_fi
			HAVING COUNT(question_fi) < num
		");

    while ($qstRow = $ilDB->fetchAssoc($qstRes)) {
        $ilDB->execute($deleteRowsStmt, array($qstRow['qst']));

        $selectRowsRes = $ilDB->execute($selectRowsStmt, array($qstRow['qst']));

        $existingRows = array();
        while ($selectRowsRow = $ilDB->fetchAssoc($selectRowsRes)) {
            $combinationId = $selectRowsRow['combination_id'];
            $rowId = $selectRowsRow['row_id'];
            $gapFi = $selectRowsRow['gap_fi'];

            if (!isset($existingRows[$combinationId])) {
                $existingRows[$combinationId] = array();
            }

            if (!isset($existingRows[$combinationId][$rowId])) {
                $existingRows[$combinationId][$rowId] = array();
            }

            if (!isset($existingRows[$combinationId][$rowId][$gapFi])) {
                $existingRows[$combinationId][$rowId][$gapFi] = array();
            }

            $existingRows[$combinationId][$rowId][$gapFi][] = array(
                'answer' => $selectRowsRow['answer'],
                'points' => $selectRowsRow['points']
            );
        }

        $newRows = array();
        foreach ($existingRows as $combinationId => $combination) {
            if (!isset($newRows[$combinationId])) {
                $newRows[$combinationId] = array();
            }

            $maxPointsForCombination = null;
            $maxPointsRowIdForCombination = null;
            foreach ($combination as $rowId => $row) {
                if (!isset($newRows[$combinationId][$rowId])) {
                    $newRows[$combinationId][$rowId] = array();
                }

                $maxPointsForRow = null;
                foreach ($row as $gapFi => $gap) {
                    foreach ($gap as $dups) {
                        if (!isset($newRows[$combinationId][$rowId][$gapFi])) {
                            $newRows[$combinationId][$rowId][$gapFi] = array(
                                'answer' => $dups['answer']
                            );

                            if ($maxPointsForRow === null || $maxPointsForRow < $dups['points']) {
                                $maxPointsForRow = $dups['points'];
                            }
                        }
                    }
                }

                foreach ($newRows[$combinationId][$rowId] as $gapFi => $gap) {
                    $newRows[$combinationId][$rowId][$gapFi]['points'] = $maxPointsForRow;
                }

                if ($maxPointsForCombination === null || $maxPointsForCombination < $maxPointsForRow) {
                    $maxPointsForCombination = $maxPointsForRow;
                    $maxPointsRowIdForCombination = $rowId;
                }
            }

            foreach ($combination as $rowId => $row) {
                foreach ($newRows[$combinationId][$rowId] as $gapFi => $gap) {
                    $newRows[$combinationId][$rowId][$gapFi]['best_solution'] = ($rowId == $maxPointsRowIdForCombination ? 1 : 0);
                }
            }
        }

        foreach ($newRows as $combinationId => $combination) {
            foreach ($combination as $rowId => $row) {
                foreach ($row as $gapFi => $gap) {
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

if ($ilDB->tableExists('dups_clozecombis_rows')) {
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

if ($ilDB->tableExists('dups_clozecombis_rows')) {
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

if ($ilDB->tableExists('dups_clozecombis_qst')) {
    $ilDB->dropTable('dups_clozecombis_qst');
}

if ($ilDB->tableExists('dups_clozecombis_rows')) {
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
if (!$ilDB->sequenceExists('chatroom_historytmp')) {
    $ilDB->createSequence('chatroom_historytmp');
}
?>
<#4549>
<?php
if (!$ilDB->tableExists('chatroom_historytmp')) {
    $fields = array(
        'hist_id' => array('type' => 'integer', 'length' => 8, 'notnull' => true, 'default' => 0),
        'room_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'message' => array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null),
        'timestamp' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
        'sub_room' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
    );
    $ilDB->createTable('chatroom_historytmp', $fields);
    $ilDB->addPrimaryKey('chatroom_historytmp', array('hist_id'));
}
?>
<#4550>
<?php
require_once 'Services/Migration/DBUpdate_4550/classes/class.ilDBUpdate4550.php';
ilDBUpdate4550::cleanupOrphanedChatRoomData();
if ($ilDB->getDBType() == 'innodb' || $ilDB->getDBType() == 'mysql' || $ilDB->getDBType() == '') {
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
} else {
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

while ($row = $ilDB->fetchAssoc($res)) {
    $hist_id = $ilDB->nextId('chatroom_historytmp');
    $ilDB->execute($stmt_in, array($hist_id, (int) $row['room_id'], (int) $row['timestamp'], (int) $row['sub_room'], (string) $row['message']));
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
if (!$ilDB->sequenceExists('chatroom_history')) {
    $query = "SELECT MAX(hist_id) mhist_id FROM chatroom_history";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('chatroom_history', (int) $row['mhist_id'] + 1);
}
?>
<#4554>
<?php
if ($ilDB->sequenceExists('chatroom_historytmp')) {
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
if ($ilDB->getDBType() == 'postgres') {
    $ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER COLUMN parent_id SET DEFAULT 0");
    $ilDB->manipulate("ALTER TABLE chatroom_prooms ALTER parent_id TYPE INTEGER USING (parent_id::INTEGER)");
} else {
    $ilDB->modifyTableColumn('chatroom_prooms', 'parent_id', array(
        'type' => 'integer',
        'length' => 4,
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
if ($ilDB->sequenceExists('chatroom_smilies')) {
    $ilDB->dropSequence('chatroom_smilies');
}
?>
<#4561>
<?php
$query = "SELECT MAX(smiley_id) msmiley_id FROM chatroom_smilies";
$row = $ilDB->fetchAssoc($ilDB->query($query));
$ilDB->createSequence('chatroom_smilies', (int) $row['msmiley_id'] + 1);
?>
<#4562>
<?php
if (!$ilDB->tableColumnExists('frm_settings', 'file_upload_allowed')) {
    $ilDB->addTableColumn(
        'frm_settings',
        'file_upload_allowed',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        )
    );
}
?>
<#4563>
<?php

if ($ilDB->tableExists('sysc_groups')) {
    $ilDB->dropTable('sysc_groups');
}
if ($ilDB->tableExists('sysc_groups_seq')) {
    $ilDB->dropTable('sysc_groups_seq');
}

if (!$ilDB->tableExists('sysc_groups')) {
    $fields = array(
    'id' => array(
            'type' => 'integer',
            'length' => 4,
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

if (!$ilDB->tableExists('sysc_tasks')) {
    $fields = array(
    'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
    'grp_id' => array(
            "type" => "integer",
            "notnull" => true,
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
            "notnull" => false,
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

if ($cntRow['cnt'] > 0) {
    $ilDB->createTable('tst_addtime_tmp', array(
        'active_fi' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'additionaltime' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => false,
            'default' => null,
        ),
        'tstamp' => array(
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

if ($ilDB->tableExists('tst_addtime_tmp')) {
    $res = $ilDB->query("
		SELECT orig.active_fi FROM tst_addtime orig
		LEFT JOIN tst_addtime_tmp tmp ON tmp.active_fi = orig.active_fi
		WHERE tmp.active_fi IS NULL
		GROUP BY orig.active_fi HAVING COUNT(orig.active_fi) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->replace(
            'tst_addtime_tmp',
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

if ($ilDB->tableExists('tst_addtime_tmp')) {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        if ($active_fi === null) {
            // first loop
            $active_fi = $row['active_fi'];
        } elseif ($row['active_fi'] != $active_fi) {
            // update last active
            $ilDB->update(
                'tst_addtime_tmp',
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

        if ($addtime === null || $row['additionaltime'] >= $addtime) {
            $addtime = $row['additionaltime'];
            $tstamp = $row['tstamp'];
        }
    }

    $ilDB->update(
        'tst_addtime_tmp',
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

if ($ilDB->tableExists('tst_addtime_tmp')) {
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

if ($ilDB->tableExists('tst_addtime_tmp')) {
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

if ($ilDB->tableExists('tst_addtime_tmp')) {
    $ilDB->dropTable('tst_addtime_tmp');
}
?>
<#4571>
<?php
// primary key for tst_addtime - step 7/8

if ($ilDB->indexExistsByFields('tst_addtime', array('active_fi'))) {
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

if ($ilDB->indexExistsByFields('ctrl_calls', array('parent'))) {
    $ilDB->dropIndexByFields('ctrl_calls', array('parent'));
}
$ilDB->addPrimaryKey('ctrl_calls', array('parent','child'));
?>
<#4574>
<?php
global $ilDB;
if (!$ilDB->tableColumnExists('il_dcl_table', 'delete_by_owner')) {
    $ilDB->addTableColumn(
        'il_dcl_table',
        'delete_by_owner',
        array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
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

if ($row['cnt'] > 0) {
    $ilDB->createTable('tst_result_cache_tmp', array(
        'active_fi' => array(
            'type' => 'integer',
            'length' => 8,
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

if ($ilDB->tableExists('tst_result_cache_tmp')) {
    $res = $ilDB->query("
		SELECT active_fi FROM tst_result_cache
		GROUP BY active_fi HAVING COUNT(active_fi) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->replace('tst_result_cache_tmp', array(), array(
            'active_fi' => array('integer', $row['active_fi'])
        ));
    }
}
?>
<#4577>
<?php
// primary key for tst_result_cache - step 3/7

if ($ilDB->tableExists('tst_result_cache_tmp')) {
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

if ($ilDB->indexExistsByFields('tst_result_cache', array('active_fi'))) {
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

if ($ilDB->tableExists('tst_result_cache_tmp')) {
    include_once 'Services/Migration/DBUpdate_4209/classes/class.DBUpdateTestResultCalculator.php';

    $res = $ilDB->query("
		SELECT tmp.active_fi, pass_scoring FROM tst_result_cache_tmp tmp
		INNER JOIN tst_active ON active_id = tmp.active_fi
		INNER JOIN tst_tests ON test_id = test_fi
		LEFT JOIN tst_result_cache orig ON orig.active_fi = tmp.active_fi
		WHERE orig.active_fi IS NULL
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        DBUpdateTestResultCalculator::_updateTestResultCache(
            $row['active_fi'],
            $row['pass_scoring']
        );
    }
}
?>
<#4581>
<?php
// primary key for tst_result_cache - step 7/7

if ($ilDB->tableExists('tst_result_cache_tmp')) {
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

$res = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);

$ilSetting = new ilSetting();
$setting = $ilSetting->get('mail_mod_dupl_warn_51x_shown', 0);
if ($data['cnt'] > 0 && !(int) $setting) {
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


if ($data['cnt'] > 0) {
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
    while ($row = $ilDB->fetchAssoc($res)) {
        $old_folder_id = $row['obj_id'];
        $user_id = $row['user_id'];
        $title = $row['title'];
        $type = $row['m_type'];

        // Delete old folder entry
        $ilDB->execute($ps_delete_mf_by_obj_and_usr, array($old_folder_id, $user_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Deleted folder %s of user %s .",
            $db_step,
            $old_folder_id,
            $user_id
        ));

        $new_folder_id = $ilDB->nextId('mail_obj_data');
        // create new folder entry
        $ilDB->execute($ps_create_mf_by_obj_and_usr, array($new_folder_id, $user_id, $title, $type));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Created new folder %s for user %s .",
            $db_step,
            $new_folder_id,
            $user_id
        ));

        // Move mails to new folder
        $ilDB->execute($ps_update_mail_by_usr_and_folder, array($new_folder_id, $old_folder_id, $user_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Moved mails from %s to %s for user %s .",
            $db_step,
            $old_folder_id,
            $new_folder_id,
            $user_id
        ));

        // Change existing tree entry
        $ilDB->execute($ps_update_tree_entry_by_child_and_usr, array($new_folder_id, $old_folder_id, $user_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Changed child in table 'mail_tree' from %s to %s for tree %s .",
            $db_step,
            $old_folder_id,
            $new_folder_id,
            $user_id
        ));
        // Change existing tree parent entry
        $ilDB->execute($ps_update_tree_par_entry_by_child_and_usr, array($new_folder_id, $old_folder_id, $user_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Changed parent in table 'mail_tree' from %s to %s for tree %s .",
            $db_step,
            $old_folder_id,
            $new_folder_id,
            $user_id
        ));
    }
}

$res = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
$res = $ilDB->query($mod_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
if (!$ilDB->tableExists('il_gc_memcache_server')) {
    $ilDB->createTable('il_gc_memcache_server', $fields);
    $ilDB->addPrimaryKey('il_gc_memcache_server', array( 'id' ));
    $ilDB->createSequence('il_gc_memcache_server');
}
?>
<#4587>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass(
    "Sup",
    "sup",
    "sup",
    array()
);
ilDBUpdate3136::addStyleClass(
    "Sub",
    "sub",
    "sub",
    array()
);
?>
<#4588>
<?php
if (!$ilDB->tableColumnExists("il_wiki_data", "link_md_values")) {
    $ilDB->addTableColumn("il_wiki_data", "link_md_values", array(
        "type" => "integer",
        "length" => 1,
        "notnull" => false,
        "default" => 0,
    ));
}
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
$res = $ilDB->query($mt_dup_query_num);
$data = $ilDB->fetchAssoc($res);

$ilSetting = new ilSetting();
$setting = $ilSetting->get('mail_mt_dupl_warn_51x_shown', 0);
if ($data['cnt'] > 0 && !(int) $setting) {
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

if ($data['cnt'] > 0) {
    if (!$ilDB->tableExists('mail_tree_migr')) {
        $ilDB->createTable('mail_tree_migr', array(
            'usr_id' => array(
                'type' => 'integer',
                'length' => 4,
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
if ($ilDB->tableExists('mail_tree_migr')) {
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
    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($ps_create_mtmig_rec, array($row['tree']));

        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Detected duplicate entries (field: child) in table 'mail_tree' for user (field: tree) %s .",
            $db_step,
            $row['tree']
        ));
    }
}
?>
<#4591>
<?php
if ($ilDB->tableExists('mail_tree_migr')) {
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
        'a_root' => 'root',
        'b_inbox' => 'inbox',
        'c_trash' => 'trash',
        'd_drafts' => 'drafts',
        'e_sent' => 'sent',
        'z_local' => 'local'
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
        $db_step,
        $num
    ));

    $i = 0;
    while ($row = $ilDB->fetchAssoc($res)) {
        ++$i;

        $usr_id = $row['usr_id'];

        $ilDB->execute($ps_del_tree_entries, array($usr_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Started 'mail_tree' migration for user %s. Deleted all records referring this user (field: tree)",
            $db_step,
            $usr_id
        ));

        $fold_res = $ilDB->execute($ps_sel_fold_entries, array($usr_id));
        $user_folders = array();
        $user_default_folders = array();
        while ($fold_row = $ilDB->fetchAssoc($fold_res)) {
            $user_folders[$fold_row['obj_id']] = $fold_row;
            if (isset($default_folder_type_to_title_map[strtolower($fold_row['m_type'])])) {
                $user_default_folders[$fold_row['m_type']] = $fold_row['title'];
            }
        }

        // Create missing default folders
        $folders_to_create = array_diff_key($default_folder_type_to_title_map, $user_default_folders);
        foreach ($folders_to_create as $type => $title) {
            $folder_id = $ilDB->nextId('mail_obj_data');
            $ilDB->execute($ps_in_fold_entry, array($folder_id, $usr_id, $title, $type));

            $user_folders[$folder_id] = array(
                'obj_id' => $folder_id,
                'user_id' => $usr_id,
                'title' => $title,
                'm_type' => $type
            );
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created 'mail_obj_data' record (missing folder type): %s, %s, %s, %s .",
                $db_step,
                $i,
                $folder_id,
                $usr_id,
                $title,
                $type
            ));
        }

        // Create a new root folder node
        $root_id = null;
        foreach ($user_folders as $folder_id => $data) {
            if ('root' != $data['m_type']) {
                continue;
            }

            $root_id = $folder_id;
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $root_id, 0, 1, 2, 1));

            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created root node with id %s for user %s in 'mail_tree'.",
                $db_step,
                $i,
                $root_id,
                $usr_id
            ));
            break;
        }

        if (!$root_id) {
            // Did not find root folder, skip user and move to the next one
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: No root folder found for user %s . Skipped user.",
                $db_step,
                $i,
                $usr_id
            ));
            continue;
        }

        $custom_folder_root_id = null;
        // Create all default folders below 'root'
        foreach ($user_folders as $folder_id => $data) {
            if ('root' == $data['m_type'] || !isset($default_folder_type_to_title_map[strtolower($data['m_type'])])) {
                continue;
            }

            if (null === $custom_folder_root_id && 'local' == $data['m_type']) {
                $custom_folder_root_id = $folder_id;
            }

            $res_parent = $ilDB->execute($ps_sel_tree_entry, array($root_id, $usr_id));
            $parent_row = $ilDB->fetchAssoc($res_parent);

            $right = $parent_row['rgt'];
            $lft = $right;
            $rgt = $right + 1;

            $ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $root_id, $lft, $rgt, 2));
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created node with id %s (lft: %s | rgt: %s) for user %s in 'mail_tree'.",
                $db_step,
                $i,
                $folder_id,
                $lft,
                $rgt,
                $usr_id
            ));
        }

        if (!$custom_folder_root_id) {
            // Did not find custom folder root, skip user and move to the next one
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: No custom folder root found for user %s . Skipped user.",
                $db_step,
                $i,
                $usr_id
            ));
            continue;
        }

        // Create all custom folders below 'local'
        foreach ($user_folders as $folder_id => $data) {
            if (isset($default_folder_type_to_title_map[strtolower($data['m_type'])])) {
                continue;
            }

            $res_parent = $ilDB->execute($ps_sel_tree_entry, array($custom_folder_root_id, $usr_id));
            $parent_row = $ilDB->fetchAssoc($res_parent);

            $right = $parent_row['rgt'];
            $lft = $right;
            $rgt = $right + 1;

            $ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $custom_folder_root_id, $lft, $rgt, 3));
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created custom folder node with id %s (lft: %s | rgt: %s) for user % in 'mail_tree'.",
                $db_step,
                $i,
                $folder_id,
                $lft,
                $rgt,
                $usr_id
            ));
        }

        // Tree completely created, remove migration record
        $ilDB->execute($ps_del_mtmig_rec, array($usr_id));

        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s, iteration %s: Finished 'mail_tree' migration for user %s .",
            $db_step,
            $i,
            $usr_id
        ));
    }

    $res = $ilDB->query("SELECT usr_id FROM mail_tree_migr");
    $num = $ilDB->numRows($res);
    if ($num > 0) {
        die("There are still duplicate entries in table 'mail_tree'. Please execute this database update step again.");
    }
}
?>
<#4592>
<?php
if ($ilDB->tableExists('mail_tree_migr')) {
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
$res = $ilDB->query($mt_dup_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
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
    if (!$ilDB->tableColumnExists("booking_schedule", "av_from")) {
        $ilDB->addTableColumn("booking_schedule", "av_from", array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
    if (!$ilDB->tableColumnExists("booking_schedule", "av_to")) {
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
ilDBUpdate3136::addStyleClass(
    "CarouselCntr",
    "ca_cntr",
    "div",
    array()
);
?>
<#4596>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass(
    "CarouselICntr",
    "ca_icntr",
    "div",
    array()
);
?>
<#4597>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass(
    "CarouselIHead",
    "ca_ihead",
    "div",
    array()
);
?>
<#4598>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass(
    "CarouselICont",
    "ca_icont",
    "div",
    array()
);
?>
<#4599>
<?php

if (!$ilDB->tableExists('member_noti')) {
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

if (!$ilDB->tableExists('member_noti_user')) {
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
if (!$ilDB->tableColumnExists('frm_posts', 'pos_cens_date')) {
    $ilDB->addTableColumn(
        'frm_posts',
        'pos_cens_date',
        array(
            'type' => 'timestamp',
            'notnull' => false)
    );
}
?>
<#4602>
<?php
if (!$ilDB->tableExists('frm_posts_deleted')) {
    $ilDB->createTable(
        'frm_posts_deleted',
        array(
            'deleted_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'deleted_date' => array(
                'type' => 'timestamp',
                'notnull' => true
            ),
            'deleted_by' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'forum_title' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'thread_title' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'post_title' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'post_message' => array(
                'type' => 'clob',
                'notnull' => true
            ),
            'post_date' => array(
                'type' => 'timestamp',
                'notnull' => true
            ),
            'obj_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'ref_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'thread_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'forum_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'pos_display_user_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ),
            'pos_usr_alias' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            )
        )
    );

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
if (!$ilDB->tableColumnExists('frm_posts_deleted', 'is_thread_deleted')) {
    $ilDB->addTableColumn(
        'frm_posts_deleted',
        'is_thread_deleted',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0)
    );
}
?>
<#4605>
<?php

$res = $ilDB->query("SELECT a.id, a.tpl_id, od.obj_id , od.title FROM " .
    "(didactic_tpl_a a JOIN " .
    "(didactic_tpl_alr alr JOIN " .
    "object_data od " .
    "ON (alr.role_template_id = od.obj_id)) " .
    "ON ( a.id = alr.action_id)) " .
    "WHERE a.type_id = " . $ilDB->quote(2, 'integer'));

$names = array();
$templates = array();

while ($row = $ilDB->fetchAssoc($res)) {
    $names[$row["tpl_id"]][$row["id"]] = array(
        "action_id" => $row["id"],
        "role_template_id" => $row["obj_id"],
        "role_title" => $row["title"]);

    $templates[$row["tpl_id"]] = $row["tpl_id"];
}

$res = $ilDB->query("SELECT * FROM didactic_tpl_objs");

while ($row = $ilDB->fetchAssoc($res)) {
    if (in_array($row["tpl_id"], $templates)) {
        $roles = array();
        $rol_res = $ilDB->query("SELECT rol_id FROM rbac_fa " .
            "WHERE parent = " . $ilDB->quote($row["ref_id"], 'integer') . " AND assign = " . $ilDB->quote('y', 'text'));

        while ($rol_row = $ilDB->fetchObject($rol_res)) {
            $roles[] = $rol_row->rol_id;
        }

        foreach ($names[$row["tpl_id"]] as $name) {
            $concat = $ilDB->concat(array(
                array("title", "text"),
                array($ilDB->quote("_" . $row["ref_id"], "text"), "text")
            ), false);

            $ilDB->manipulate("UPDATE object_data" .
                " SET title = " . $concat .
                " WHERE " . $ilDB->in("obj_id", $roles, "", "integer") .
                " AND title = " . $ilDB->quote($name['role_title']));
        }
    }
}
?>
<#4606>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_char')) {
    $ilDB->addTableColumn('exc_assignment', 'peer_char', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => false
    ));
}
?>
<#4607>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_unlock')) {
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
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_valid')) {
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
if (!$ilDB->tableColumnExists('exc_assignment', 'team_tutor')) {
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
if (!$ilDB->tableColumnExists('exc_assignment', 'max_file')) {
    $ilDB->addTableColumn('exc_assignment', 'max_file', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false
    ));
}
?>
<#4611>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'deadline2')) {
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
if (!$ilDB->tableColumnExists('exc_returned', 'late')) {
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

if (!$ilDB->tableExists('exc_crit_cat')) {
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
    $ilDB->addPrimaryKey('exc_crit_cat', array('id'));
    $ilDB->createSequence('exc_crit_cat');
}

?>
<#4615>
<?php

if (!$ilDB->tableExists('exc_crit')) {
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
    $ilDB->addPrimaryKey('exc_crit', array('id'));
    $ilDB->createSequence('exc_crit');
}

?>
<#4616>
<?php

if (!$ilDB->tableColumnExists('exc_crit', 'required')) {
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

if (!$ilDB->tableColumnExists('exc_crit', 'def')) {
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

if (!$ilDB->tableColumnExists('exc_assignment', 'peer_text')) {
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

if (!$ilDB->tableColumnExists('exc_assignment', 'peer_rating')) {
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

if (!$ilDB->tableColumnExists('exc_assignment', 'peer_crit_cat')) {
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
if ($blog_type_id) {
    // not sure if we want to clone "write" or "contribute"?
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('redact', 'Redact', 'object', 6100);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($blog_type_id, $new_ops_id);
    }
}

?>
<#4623>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$redact_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('redact');
if ($redact_ops_id) {
    ilDBUpdateNewObjectType::addRBACTemplate(
        'blog',
        'il_blog_editor',
        'Editor template for blogs',
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

if (!$ilDB->tableColumnExists('adv_md_record_objs', 'optional')) {
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

if (!$ilDB->tableColumnExists('adv_md_record', 'parent_obj')) {
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
    if (!$ilDB->tableExists("copg_section_timings")) {
        $fields = array(
            'pm_id' => array('type' => 'integer', 'length' => 4,'notnull' => true, 'default' => 0),
            'pm_title' => array('type' => 'text', 'notnull' => true, 'length' => 60, 'fixed' => false),
            'pm_enabled' => array('type' => 'integer', 'length' => 1,"notnull" => true,"default" => 0),
            'save_usr_adr' => array('type' => 'integer', 'length' => 1,"notnull" => true,"default" => 0)
        );


        $fields = array(
            "page_id" => array(
                "type" => "integer",
                "length" => 4,
                "notnull" => true
            ),
            "parent_type" => array(
                "type" => "text",
                "length" => 10,
                "notnull" => true
            ),
            "utc_ts" => array(
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
    $ilDB->addTableColumn(
        'copg_section_timings',
        'unix_ts',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        )
    );
?>
<#4629>
<?php
if (!$ilDB->tableColumnExists('skl_user_skill_level', 'unique_identifier')) {
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
    if (!$ilDB->tableColumnExists('crs_settings', 'crs_start')) {
        $ilDB->addTableColumn('crs_settings', 'crs_start', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
    if (!$ilDB->tableColumnExists('crs_settings', 'crs_end')) {
        $ilDB->addTableColumn('crs_settings', 'crs_end', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
?>
<#4632>
<?php
    if (!$ilDB->tableColumnExists('crs_settings', 'leave_end')) {
        $ilDB->addTableColumn('crs_settings', 'leave_end', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
?>
<#4633>
<?php
    if (!$ilDB->tableColumnExists('crs_settings', 'auto_wait')) {
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
    if (!$ilDB->tableColumnExists('crs_settings', 'min_members')) {
        $ilDB->addTableColumn('crs_settings', 'min_members', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 2
        ));
    }
?>
<#4635>
<?php
    if (!$ilDB->tableColumnExists('grp_settings', 'registration_min_members')) {
        $ilDB->addTableColumn('grp_settings', 'registration_min_members', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 2
        ));
    }
?>
<#4636>
<?php
    if (!$ilDB->tableColumnExists('grp_settings', 'leave_end')) {
        $ilDB->addTableColumn('grp_settings', 'leave_end', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
?>
<#4637>
<?php
    if (!$ilDB->tableColumnExists('grp_settings', 'auto_wait')) {
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
    if (!$ilDB->tableColumnExists('event', 'reg_min_users')) {
        $ilDB->addTableColumn('event', 'reg_min_users', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 2
        ));
    }
?>
<#4639>
<?php
    if (!$ilDB->tableColumnExists('event', 'reg_auto_wait')) {
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
if (!$ilDB->tableExists('mail_man_tpl')) {
    $ilDB->createTable('mail_man_tpl', array(
        'tpl_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'context' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true
        ),
        'lang' => array(
            'type' => 'text',
            'length' => 2,
            'notnull' => true
        ),
        'm_subject' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
            'default' => null
        ),
        'm_message' => array(
            'type' => 'clob',
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
if (!$ilDB->tableExists('mail_tpl_ctx')) {
    $ilDB->createTable('mail_tpl_ctx', array(
        'id' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true
        ),
        'component' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true
        ),
        'class' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true
        ),
        'path' => array(
            'type' => 'text',
            'length' => 4000,
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
if (!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_id')) {
    $ilDB->addTableColumn(
        'mail_saved',
        'tpl_ctx_id',
        array(
            'type' => 'text',
            'length' => '100',
            'notnull' => false,
            'default' => null
        )
    );
}

if (!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_params')) {
    $ilDB->addTableColumn(
        'mail_saved',
        'tpl_ctx_params',
        array(
            'type' => 'blob',
            'notnull' => false,
            'default' => null
        )
    );
}
?>
<#4644>
<?php
if (!$ilDB->tableColumnExists('mail', 'tpl_ctx_id')) {
    $ilDB->addTableColumn(
        'mail',
        'tpl_ctx_id',
        array(
            'type' => 'text',
            'length' => '100',
            'notnull' => false,
            'default' => null
        )
    );
}

if (!$ilDB->tableColumnExists('mail', 'tpl_ctx_params')) {
    $ilDB->addTableColumn(
        'mail',
        'tpl_ctx_params',
        array(
            'type' => 'blob',
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
if (!$ilDB->tableExists('itgr_data')) {
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

    $ilDB->addPrimaryKey('itgr_data', array('id'));
}
?>
<#4647>
<?php
$set = $ilDB->query(
    "SELECT * FROM object_data " .
    " WHERE type = " . $ilDB->quote("itgr", "text")
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $ilDB->manipulate("INSERT INTO itgr_data " .
        "(id, hide_title) VALUES (" .
        $ilDB->quote($rec["obj_id"], "integer") . "," .
        $ilDB->quote(0, "integer") .
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
if (!$ilDB->tableColumnExists("content_object", "for_translation")) {
    $ilDB->addTableColumn("content_object", "for_translation", array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0));
}
?>
<#4650>
<?php
$set = $ilDB->query(
    "SELECT * FROM mep_item JOIN mep_tree ON (mep_item.obj_id = mep_tree.child) " .
    " WHERE mep_item.type = " . $ilDB->quote("pg", "text")
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $q = "UPDATE page_object SET " .
        " parent_id = " . $ilDB->quote($rec["mep_id"], "integer") .
        " WHERE parent_type = " . $ilDB->quote("mep", "text") .
        " AND page_id = " . $ilDB->quote($rec["obj_id"], "integer");
    //echo "<br>".$q;
    $ilDB->manipulate($q);
}
?>
<#4651>
<?php
if (!$ilDB->tableColumnExists("mep_data", "for_translation")) {
    $ilDB->addTableColumn("mep_data", "for_translation", array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0));
}
?>
<#4652>
<?php
if (!$ilDB->tableColumnExists("mep_item", "import_id")) {
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
if ($wiki_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('edit_wiki_navigation', 'Edit Wiki Navigation', 'object', 3220);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
    }
}
?>
<#4655>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if ($wiki_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('delete_wiki_pages', 'Delete Wiki Pages', 'object', 3300);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
    }
}

?>
<#4656>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if ($wiki_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('activate_wiki_protection', 'Set Read-Only', 'object', 3240);
    if ($new_ops_id) {
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
    if (!$ilDB->tableExists('wiki_user_html_export')) {
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
if ($wiki_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('wiki_html_export', 'Wiki HTML Export', 'object', 3242);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
    }
}

?>

<#4660>
<?php

if (!$ilDB->tableColumnExists('loc_settings', 'it_type')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'it_type',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 5
        )
    );
}
?>
<#4661>
<?php

if (!$ilDB->tableColumnExists('loc_settings', 'qt_type')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_type',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 1
        )
    );
}

?>

<#4662>
<?php

if (!$ilDB->tableColumnExists('loc_settings', 'it_start')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'it_start',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 1
        )
    );
}

?>

<#4663>
<?php

if (!$ilDB->tableColumnExists('loc_settings', 'qt_start')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'qt_start',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 1
        )
    );
}
?>

<#4664>
<?php


$query = 'UPDATE loc_settings SET it_type = ' . $ilDB->quote(1, 'integer') . ' WHERE type = ' . $ilDB->quote(1, 'integer');
$res = $ilDB->manipulate($query);

?>

<#4665>
<?php


$query = 'UPDATE loc_settings SET qt_start = ' . $ilDB->quote(0, 'integer') . ' WHERE type = ' . $ilDB->quote(4, 'integer');
$res = $ilDB->manipulate($query);

?>

<#4666>
<?php

if (!$ilDB->tableExists('loc_tst_assignments')) {
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

if (!$ilDB->tableColumnExists('loc_settings', 'passed_obj_mode')) {
    $ilDB->addTableColumn(
        'loc_settings',
        'passed_obj_mode',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 1
        )
    );
}
?>

<#4668>
<?php
if (!$ilDB->tableExists('tst_seq_qst_optional')) {
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
if (!$ilDB->tableColumnExists('tst_sequence', 'ans_opt_confirmed')) {
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
if (!$ilDB->tableExists('il_wac_secure_path')) {
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

    if ($ilDB->tableExists('desktop_item')) {
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

        if ($ilDB->numRows($res)) {
            if (!$ilDB->tableExists('desktop_item_tmp')) {
                $ilDB->createTable('desktop_item_tmp', array(
                    'item_id' => array(
                        'type' => 'integer',
                        'length' => 8,
                        'notnull' => true,
                        'default' => 0
                    ),
                    'user_id' => array(
                        'type' => 'integer',
                        'length' => 8,
                        'notnull' => true,
                        'default' => 0
                    )
                ));
                $ilDB->addPrimaryKey('desktop_item_tmp', array('item_id','user_id'));
            }

            while ($row = $ilDB->fetchAssoc($res)) {
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

    if ($ilDB->tableExists('desktop_item_tmp')) {
        $res = $ilDB->query("
		SELECT item_id, user_id
		FROM desktop_item_tmp
	");

        while ($row = $ilDB->fetchAssoc($res)) {
            $res_data = $ilDB->query(
                "
			SELECT *
			FROM desktop_item
			WHERE
			item_id = " . $ilDB->quote($row['item_id'], 'integer') . " AND
			user_id = " . $ilDB->quote($row['user_id'], 'integer')
            );
            $data = $ilDB->fetchAssoc($res_data);

            $ilDB->manipulate(
                "DELETE FROM desktop_item WHERE" .
                " item_id = " . $ilDB->quote($row['item_id'], 'integer') .
                " AND user_id = " . $ilDB->quote($row['user_id'], 'integer')
            );

            $ilDB->manipulate("INSERT INTO desktop_item (item_id,user_id,type,parameters) " .
                "VALUES ( " .
                $ilDB->quote($data['item_id'], 'integer') . ', ' .
                $ilDB->quote($data['user_id'], 'integer') . ', ' .
                $ilDB->quote($data['type'], 'text') . ', ' .
                $ilDB->quote($data['parameters'], 'text') .
                ")");
        }
    }
    ?>
<#4673>
	<?php
    //step 3/5 drop desktop_item_tmp

    if ($ilDB->tableExists('desktop_item_tmp')) {
        $ilDB->dropTable('desktop_item_tmp');
    }
    ?>
<#4674>
	<?php
    //step 4/5 drops not used indexes

    if ($ilDB->indexExistsByFields('desktop_item', array('item_id'))) {
        $ilDB->dropIndexByFields('desktop_item', array('item_id'));
    }
    if ($ilDB->indexExistsByFields('desktop_item', array('user_id'))) {
        $ilDB->dropIndexByFields('desktop_item', array('user_id'));
    }
    ?>
<#4675>
<?php
//step 5/5 adding primary keys and useful indexes

if ($ilDB->tableExists('desktop_item')) {
    $ilDB->addPrimaryKey('desktop_item', array('user_id', 'item_id'));
}
?>
<#4676>
<?php
if (!$ilDB->tableExists('buddylist')) {
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
if (!$ilDB->tableExists('buddylist_requests')) {
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
if ($ilDB->tableColumnExists('addressbook_mlist_ass', 'addr_id')) {
    $ilDB->renameTableColumn('addressbook_mlist_ass', 'addr_id', 'usr_id');
}
?>
<#4679>
<?php
if ($ilDB->tableExists('addressbook')) {
    $query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		INNER JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE ud1.usr_id != ud2.usr_id
	";
    $res = $ilDB->query($query);
    while ($row = $ilDB->fetchAssoc($res)) {
        $this->db->replace(
            'buddylist',
            array(
                'usr_id' => array('integer', $row['u1']),
                'buddy_usr_id' => array('integer', $row['u2'])
            ),
            array(
                'ts' => array('integer', time())
            )
        );

        $this->db->replace(
            'buddylist',
            array(
                'usr_id' => array('integer', $row['u2']),
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
    while ($row = $ilDB->fetchAssoc($res)) {
        $this->db->replace(
            'buddylist_requests',
            array(
                'usr_id' => array('integer', $row['u1']),
                'buddy_usr_id' => array('integer', $row['u2'])
            ),
            array(
                'ts' => array('integer', time()),
                'ignored' => array('integer', 0)
            )
        );
    }

    $ilDB->dropTable('addressbook');
}
?>
<#4680>
<?php
if ($ilDB->sequenceExists('addressbook')) {
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
if (!$ilDB->numRows($res)) {
    $ilDB->insert(
        'notification_usercfg',
        array(
            'usr_id' => array('integer', -1),
            'module' => array('text', 'buddysystem_request'),
            'channel' => array('text', 'mail')
        )
    );
}

$res = $ilDB->queryF(
    'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
    array('integer', 'text', 'text'),
    array(-1,  'buddysystem_request', 'osd')
);
if (!$ilDB->numRows($res)) {
    $ilDB->insert(
        'notification_usercfg',
        array(
            'usr_id' => array('integer', -1),
            'module' => array('text', 'buddysystem_request'),
            'channel' => array('text', 'osd')
        )
    );
}
?>
<#4683>
<?php
if (!$ilDB->tableColumnExists('obj_members', 'contact')) {
    $ilDB->addTableColumn(
        'obj_members',
        'contact',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#4684>
<?php
    // register new object type 'awra' for awareness tool administration
    $id = $ilDB->nextId("object_data");
    $ilDB->manipulateF(
        "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
        array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
        array($id, "typ", "awra", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now())
    );
    $typ_id = $id;

    // create object data entry
    $id = $ilDB->nextId("object_data");
    $ilDB->manipulateF(
        "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
        array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
        array($id, "awra", "__AwarenessToolAdministration", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now())
    );

    // create object reference entry
    $ref_id = $ilDB->nextId('object_reference');
    $res = $ilDB->manipulateF(
        "INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
        array("integer", "integer"),
        array($ref_id, $id)
    );

    // put in tree
    $tree = new ilTree(ROOT_FOLDER_ID);
    $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

    // add rbac operations
    // 1: edit_permissions, 2: visible, 3: read, 4:write
    $ilDB->manipulateF(
        "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
        array("integer", "integer"),
        array($typ_id, 1)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
        array("integer", "integer"),
        array($typ_id, 2)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
        array("integer", "integer"),
        array($typ_id, 3)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
        array("integer", "integer"),
        array($typ_id, 4)
    );
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

if ($ilDB->tableExists('rbac_log') && !$ilDB->tableExists('rbac_log_old')) {
    $ilDB->renameTable("rbac_log", "rbac_log_old");
}
?>
<#4691>
<?php
//step 2/4 rbac_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('rbac_log')) {
    $ilDB->createTable('rbac_log', array(
        'log_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'created' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'action' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'data' => array(
            'type' => 'clob',
            'notnull' => false,
            'default' => null
        )
    ));
    $ilDB->addPrimaryKey('rbac_log', array('log_id'));
    $ilDB->addIndex('rbac_log', array('ref_id'), 'i1');
    $ilDB->createSequence('rbac_log');
}
?>
<#4692>
<?php
//step 3/4 rbac_log moves all data to new table

if ($ilDB->tableExists('rbac_log') && $ilDB->tableExists('rbac_log_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM rbac_log_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('rbac_log');

        $ilDB->manipulate(
            "INSERT INTO rbac_log (log_id, user_id, created, ref_id, action, data)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['user_id'], "integer") .
            "," . $ilDB->quote($row['created'], "integer") .
            "," . $ilDB->quote($row['ref_id'], "integer") .
            "," . $ilDB->quote($row['action'], "integer") .
            "," . $ilDB->quote($row['data'], "text") .
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

if ($ilDB->tableExists('rbac_log_old')) {
    $ilDB->dropTable('rbac_log_old');
}
?>
<#4694>
<?php
//step 1/3 rbac_templates removes all dublicates
if ($ilDB->tableExists('rbac_templates')) {
    $res = $ilDB->query(
        'select * from rbac_templates GROUP BY rol_id, type, ops_id, parent ' .
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

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulateF(
            "DELETE FROM rbac_templates WHERE rol_id = %s AND type = %s AND ops_id = %s AND parent = %s",
            array('integer', 'text', 'integer', 'integer'),
            array($row['rol_id'], $row['type'], $row['ops_id'], $row['parent'])
        );

        $ilDB->manipulate(
            "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
            " VALUES (" .
            $ilDB->quote($row['rol_id'], "integer") .
            "," . $ilDB->quote($row['type'], "text") .
            "," . $ilDB->quote($row['ops_id'], "integer") .
            "," . $ilDB->quote($row['parent'], "integer") .
            ")"
        );
    }
}
?>
<#4695>
<?php
//step 2/3 rbac_templates remove indexes
if ($ilDB->indexExistsByFields('rbac_templates', array('rol_id'))) {
    $ilDB->dropIndexByFields('rbac_templates', array('rol_id'));
}
if ($ilDB->indexExistsByFields('rbac_templates', array('type'))) {
    $ilDB->dropIndexByFields('rbac_templates', array('type'));
}
if ($ilDB->indexExistsByFields('rbac_templates', array('ops_id'))) {
    $ilDB->dropIndexByFields('rbac_templates', array('ops_id'));
}
if ($ilDB->indexExistsByFields('rbac_templates', array('parent'))) {
    $ilDB->dropIndexByFields('rbac_templates', array('parent'));
}
if ($ilDB->indexExistsByFields('rbac_templates', array('rol_id','parent'))) {
    $ilDB->dropIndexByFields('rbac_templates', array('rol_id','parent'));
}
?>
<#4696>
<?php
//step 3/3 rbac_templates add primary
if ($ilDB->tableExists('rbac_templates')) {
    $ilDB->addPrimaryKey('rbac_templates', array('rol_id','parent', 'type', 'ops_id'));
}
?>
<#4697>
<?php
//remove unused table search_tree
if ($ilDB->tableExists('search_tree')) {
    $ilDB->dropTable('search_tree');
}
?>
<#4698>
<?php
    if (!$ilDB->tableColumnExists('sahs_lm', 'mastery_score')) {
        $ilDB->addTableColumn(
            'sahs_lm',
            'mastery_score',
            array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            )
        );
    }
?>
<#4699>
<?php
//step 1/2 adm_set_templ_hide_tab removes all dublicates
if ($ilDB->tableExists('adm_set_templ_hide_tab')) {
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

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulateF(
            "DELETE FROM adm_set_templ_hide_tab WHERE template_id = %s AND tab_id = %s",
            array('integer', 'text'),
            array($row['template_id'], $row['tab_id'])
        );

        $ilDB->manipulate(
            "INSERT INTO adm_set_templ_hide_tab (template_id, tab_id)" .
            " VALUES (" .
            $ilDB->quote($row['template_id'], "integer") .
            ", " . $ilDB->quote($row['tab_id'], "text") .
            ")"
        );
    }
}
?>
<#4700>
<?php
//step 2/2 adm_set_templ_hide_tab add primary
if ($ilDB->tableExists('adm_set_templ_hide_tab')) {
    $ilDB->addPrimaryKey('adm_set_templ_hide_tab', array('template_id','tab_id'));
}
?>
<#4701>
<?php
//step 1/4 adm_set_templ_value search for dublicates and store it in adm_set_tpl_val_tmp

if ($ilDB->tableExists('adm_set_templ_value')) {
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

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('adm_set_tpl_val_tmp')) {
            $ilDB->createTable('adm_set_tpl_val_tmp', array(
                'template_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'setting' => array(
                    'type' => 'text',
                    'length' => 40,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('adm_set_tpl_val_tmp', array('template_id','setting'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
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

if ($ilDB->tableExists('adm_set_tpl_val_tmp')) {
    $res = $ilDB->query("
		SELECT template_id, setting
		FROM adm_set_tpl_val_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM adm_set_templ_value
			WHERE
			template_id = " . $ilDB->quote($row['template_id'], 'integer') . " AND
			setting = " . $ilDB->quote($row['setting'], 'text')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM adm_set_templ_value WHERE" .
            " template_id = " . $ilDB->quote($row['template_id'], 'integer') .
            " AND setting = " . $ilDB->quote($row['setting'], 'text')
        );

        $ilDB->manipulate("INSERT INTO adm_set_templ_value (template_id,setting,value,hide) " .
            "VALUES ( " .
            $ilDB->quote($data['template_id'], 'integer') . ', ' .
            $ilDB->quote($data['setting'], 'text') . ', ' .
            $ilDB->quote($data['value'], 'text') . ', ' .
            $ilDB->quote($data['hide'], 'integer') .
        ")");

        $ilDB->manipulate(
            "DELETE FROM adm_set_tpl_val_tmp WHERE" .
            " template_id = " . $ilDB->quote($row['template_id'], 'integer') .
            " AND setting = " . $ilDB->quote($row['setting'], 'text')
        );
    }
}
?>
<#4703>
<?php
//step 3/4 adm_set_templ_value drop adm_set_tpl_val_tmp

if ($ilDB->tableExists('adm_set_tpl_val_tmp')) {
    $ilDB->dropTable('adm_set_tpl_val_tmp');
}
?>
<#4704>
<?php
//step 4/4 adm_set_templ_value adding primary keys

if ($ilDB->tableExists('adm_set_templ_value')) {
    $ilDB->addPrimaryKey('adm_set_templ_value', array('template_id', 'setting'));
}
?>
<#4705>
<?php
//step 1/4 svy_times renames old table

if ($ilDB->tableExists('svy_times') && !$ilDB->tableExists('svy_times_old')) {
    $ilDB->renameTable("svy_times", "svy_times_old");
}
?>
<#4706>
<?php
//step 2/4 svy_times creates new table with unique id, sequenz and index

if (!$ilDB->tableExists('svy_times')) {
    $ilDB->createTable('svy_times', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'finished_fi' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'entered_page' => array(
            'type' => 'integer',
            'length' => 4,
        ),
        'left_page' => array(
            'type' => 'integer',
            'length' => 4,
        ),
        'first_question' => array(
            'type' => 'integer',
            'length' => 4,
        )
    ));
    $ilDB->addPrimaryKey('svy_times', array('id'));
    $ilDB->addIndex('svy_times', array('finished_fi'), 'i1');
    $ilDB->createSequence('svy_times');
}
?>
<#4707>
<?php
//step 3/4 svy_times moves all data to new table

if ($ilDB->tableExists('svy_times') && $ilDB->tableExists('svy_times_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM svy_times_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('svy_times');

        $ilDB->manipulate(
            "INSERT INTO svy_times (id, finished_fi, entered_page, left_page, first_question)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['finished_fi'], "integer") .
            "," . $ilDB->quote($row['entered_page'], "integer") .
            "," . $ilDB->quote($row['left_page'], "integer") .
            "," . $ilDB->quote($row['first_question'], "integer") .
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

if ($ilDB->tableExists('svy_times_old')) {
    $ilDB->dropTable('svy_times_old');
}
?>

<#4709>
<?php

if (!$ilDB->tableColumnExists("ldap_server_settings", "username_filter")) {
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
$set = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

if (!$set->id) {
    $set->id = 1;
}

$query = "UPDATE ldap_role_assignments " .
        "SET server_id = " . $set->id .
        " WHERE server_id = 0";
$ilDB->manipulate($query);

?>
<#4711>
<?php
if (!$ilDB->tableColumnExists('usr_search', 'creation_filter')) {
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
        $ilDB->manipulateF(
            "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
            array($id, "typ", "logs", "Logging Administration", -1, ilUtil::now(), ilUtil::now())
        );
        $typ_id = $id;

        // create object data entry
        $id = $ilDB->nextId("object_data");
        $ilDB->manipulateF(
            "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
            array($id, "logs", "__LoggingSettings", "Logging Administration", -1, ilUtil::now(), ilUtil::now())
        );

        // create object reference entry
        $ref_id = $ilDB->nextId('object_reference');
        $res = $ilDB->manipulateF(
            "INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
            array("integer", "integer"),
            array($ref_id, $id)
        );

        // put in tree
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

        // add rbac operations
        // 1: edit_permissions, 2: visible, 3: read, 4:write
        $ilDB->manipulateF(
            "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
            array("integer", "integer"),
            array($typ_id, 1)
        );
        $ilDB->manipulateF(
            "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
            array("integer", "integer"),
            array($typ_id, 2)
        );
        $ilDB->manipulateF(
            "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
            array("integer", "integer"),
            array($typ_id, 3)
        );
        $ilDB->manipulateF(
            "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
            array("integer", "integer"),
            array($typ_id, 4)
        );


?>
<#4714>
<?php
        $ilCtrlStructureReader->getStructure();
?>

<#4715>
<?php

        if (!$ilDB->tableExists('log_components')) {
            $ilDB->createTable('log_components', array(
                        'component_id' => array(
                                'type' => 'text',
                                'length' => 20,
                                'notnull' => false
                        ),
                        'log_level' => array(
                                'type' => 'integer',
                                'length' => 4,
                                'notnull' => false,
                                'default' => null
                        )
                ));

            $ilDB->addPrimaryKey('log_components', array('component_id'));
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
    "SELECT COUNT(*) cnt FROM qpl_qst_type WHERE type_tag = %s",
    array('text'),
    array('assLongMenu')
);

$row = $ilDB->fetchAssoc($res);

if (!$row['cnt']) {
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
if (!$ilDB->tableExists('qpl_qst_lome')) {
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
        'long_menu_text' => array(
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
if (!$ilDB->tableExists('qpl_a_lome')) {
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

    $found_dup = false;
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $found_dup = true;
    }

    if (!$found_dup) {
        $ilDB->addPrimaryKey('tree', array('child'));
    } else {
        $ilSetting = new ilSetting();
        $is_read = $ilSetting->get('tree_dups', 0);

        if (!$is_read) {
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

if ($ilDB->tableExists('usr_data_multi') && !$ilDB->tableExists('usr_data_multi_old')) {
    $ilDB->renameTable("usr_data_multi", "usr_data_multi_old");
}
?>
<#4725>
<?php
//step 2/4 usr_data_multi creates new table with unique id, sequenz and index

if (!$ilDB->tableExists('usr_data_multi')) {
    $ilDB->createTable('usr_data_multi', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'field_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 1000,
            'default' => ''
        )
    ));
    $ilDB->addPrimaryKey('usr_data_multi', array('id'));
    $ilDB->addIndex('usr_data_multi', array('usr_id'), 'i1');
    $ilDB->createSequence('usr_data_multi');
}
?>
<#4726>
<?php
//step 3/4 usr_data_multi moves all data to new table

if ($ilDB->tableExists('usr_data_multi') && $ilDB->tableExists('usr_data_multi_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM usr_data_multi_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('usr_data_multi');

        $ilDB->manipulate(
            "INSERT INTO usr_data_multi (id, usr_id, field_id, value)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['usr_id'], "integer") .
            "," . $ilDB->quote($row['field_id'], "text") .
            "," . $ilDB->quote($row['value'], "text") .
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

if ($ilDB->tableExists('usr_data_multi_old')) {
    $ilDB->dropTable('usr_data_multi_old');
}
?>
<#4728>
<?php
//step 1/4 xmlnestedset renames old table

if ($ilDB->tableExists('xmlnestedset') && !$ilDB->tableExists('xmlnestedset_old')) {
    $ilDB->renameTable("xmlnestedset", "xmlnestedset_old");
}
?>
<#4729>
<?php
//step 2/4 xmlnestedset creates new table with unique id and sequenz

if (!$ilDB->tableExists('xmlnestedset')) {
    $ilDB->createTable(
        "xmlnestedset",
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

if ($ilDB->tableExists('xmlnestedset') && $ilDB->tableExists('xmlnestedset_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM xmlnestedset_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('xmlnestedset');

        $ilDB->manipulate(
            "INSERT INTO xmlnestedset (ns_id, ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['ns_book_fk'], "integer") .
            "," . $ilDB->quote($row['ns_type'], "text") .
            "," . $ilDB->quote($row['ns_tag_fk'], "integer") .
            "," . $ilDB->quote($row['ns_l'], "integer") .
            "," . $ilDB->quote($row['ns_r'], "integer") .
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

if ($ilDB->tableExists('xmlnestedset_old')) {
    $ilDB->dropTable('xmlnestedset_old');
}
?>
<#4732>
<?php
//step 1/4 xmlnestedsettmp renames old table

if ($ilDB->tableExists('xmlnestedsettmp') && !$ilDB->tableExists('xmlnestedsettmp_old')) {
    $ilDB->renameTable("xmlnestedsettmp", "xmlnestedsettmp_old");
}
?>
<#4733>
<?php
//step 2/4 xmlnestedsettmp creates new table with unique id and sequenz

if (!$ilDB->tableExists('xmlnestedsettmp')) {
    $ilDB->createTable(
        "xmlnestedsettmp",
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

if ($ilDB->tableExists('xmlnestedsettmp') && $ilDB->tableExists('xmlnestedsettmp_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM xmlnestedsettmp_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('xmlnestedsettmp');

        $ilDB->manipulate(
            "INSERT INTO xmlnestedsettmp (ns_id, ns_unique_id, ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['ns_unique_id'], "text") .
            "," . $ilDB->quote($row['ns_book_fk'], "integer") .
            "," . $ilDB->quote($row['ns_type'], "text") .
            "," . $ilDB->quote($row['ns_tag_fk'], "integer") .
            "," . $ilDB->quote($row['ns_l'], "integer") .
            "," . $ilDB->quote($row['ns_r'], "integer") .
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

if ($ilDB->tableExists('xmlnestedsettmp_old')) {
    $ilDB->dropTable('xmlnestedsettmp_old');
}
?>
<#4736>
<?php
//step 1/5 xmlparam search for dublicates and store it in xmlparam_tmp

if ($ilDB->tableExists('xmlparam')) {
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

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('xmlparam_tmp')) {
            $ilDB->createTable('xmlparam_tmp', array(
                'tag_fk' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ),
                'param_name' => array(
                    'type' => 'text',
                    'length' => 50,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('xmlparam_tmp', array('tag_fk','param_name'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
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

if ($ilDB->tableExists('xmlparam_tmp')) {
    $res = $ilDB->query("
		SELECT tag_fk, param_name
		FROM xmlparam_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM xmlparam
			WHERE
			tag_fk = " . $ilDB->quote($row['tag_fk'], 'integer') . " AND
			param_name = " . $ilDB->quote($row['param_name'], 'text')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM xmlparam WHERE" .
            " tag_fk = " . $ilDB->quote($row['tag_fk'], 'integer') .
            " AND param_name = " . $ilDB->quote($row['param_name'], 'text')
        );

        $ilDB->manipulate("INSERT INTO xmlparam (tag_fk,param_name,param_value) " .
            "VALUES ( " .
            $ilDB->quote($data['tag_fk'], 'integer') . ', ' .
            $ilDB->quote($data['param_name'], 'text') . ', ' .
            $ilDB->quote($data['param_value'], 'text') .
            ")");

        $ilDB->manipulate(
            "DELETE FROM xmlparam_tmp WHERE" .
            " tag_fk = " . $ilDB->quote($row['tag_fk'], 'integer') .
            " AND param_name = " . $ilDB->quote($row['param_name'], 'text')
        );
    }
}
?>
<#4738>
<?php
//step 3/5 xmlparam drop xmlparam_tmp

if ($ilDB->tableExists('xmlparam_tmp')) {
    $ilDB->dropTable('xmlparam_tmp');
}
?>
<#4739>
<?php
//step 4/5 xmlparam drops not used indexes

if ($ilDB->indexExistsByFields('xmlparam', array('tag_fk'))) {
    $ilDB->dropIndexByFields('xmlparam', array('tag_fk'));
}
?>
<#4740>
<?php
//step 5/5 xmlparam adding primary keys

if ($ilDB->tableExists('xmlparam')) {
    $ilDB->addPrimaryKey('xmlparam', array('tag_fk', 'param_name'));
}
?>
<#4741>
<?php
//step 1/1 tree_workspace adding primary key

if ($ilDB->tableExists('tree_workspace')) {
    if ($ilDB->indexExistsByFields('tree_workspace', array('child'))) {
        $ilDB->dropIndexByFields('tree_workspace', array('child'));
    }

    $ilDB->addPrimaryKey('tree_workspace', array('child'));
}
?>
<#4742>
<?php
if (!$ilDB->tableColumnExists('tst_active', 'last_pmode')) {
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
if (!$ilDB->tableColumnExists('tst_solutions', 'authorized')) {
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
if ($ilDB->tableColumnExists('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled')) {
    $ilDB->dropTableColumn('tst_dyn_quest_set_cfg', 'prev_quest_list_enabled');
}
?>
<#4745>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'force_inst_fb')) {
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
if (!$ilDB->tableExists('prg_settings')) {
    $ilDB->createTable('prg_settings', $fields);
    $ilDB->addPrimaryKey('prg_settings', array( 'obj_id' ));
    if (!$ilDB->sequenceExists('prg_settings')) {
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
if (!$ilDB->tableExists('prg_usr_assignments')) {
    $ilDB->createTable('prg_usr_assignments', $fields);
    $ilDB->addPrimaryKey('prg_usr_assignments', array( 'id' ));

    if (!$ilDB->sequenceExists('prg_usr_assignments')) {
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
if (!$ilDB->tableExists('prg_usr_progress')) {
    $ilDB->createTable('prg_usr_progress', $fields);
    $ilDB->addPrimaryKey('prg_usr_progress', array( 'id' ));

    if (!$ilDB->sequenceExists('prg_usr_progress')) {
        $ilDB->createSequence('prg_usr_progress');
    }
}

// Active Record does not support tuples as primary keys, so we have to
// set those on our own.
$ilDB->addUniqueConstraint(
    ilStudyProgrammeProgress::returnDbTableName(),
    array("assignment_id", "prg_id", "usr_id")
);

// ActiveRecord seems to not interpret con_is_null correctly, so we have to set
// it manually.
$ilDB->modifyTableColumn(
    ilStudyProgrammeProgress::returnDbTableName(),
    "completion_by",
    array( "notnull" => false
                               , "default" => null
                               )
);
$ilDB->modifyTableColumn(
    ilStudyProgrammeProgress::returnDbTableName(),
    "last_change_by",
    array( "notnull" => false
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
if (!$ilDB->tableExists('prg_type_adv_md_rec')) {
    $ilDB->createTable('prg_type_adv_md_rec', $fields);
    $ilDB->addPrimaryKey('prg_type_adv_md_rec', array( 'id' ));

    if (!$ilDB->sequenceExists('prg_type_adv_md_rec')) {
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
if (!$ilDB->tableExists('prg_type')) {
    $ilDB->createTable('prg_type', $fields);
    $ilDB->addPrimaryKey('prg_type', array( 'id' ));

    if (!$ilDB->sequenceExists('prg_type')) {
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
if (!$ilDB->tableExists('prg_translations')) {
    $ilDB->createTable('prg_translations', $fields);
    $ilDB->addPrimaryKey('prg_translations', array( 'id' ));

    if (!$ilDB->sequenceExists('prg_translations')) {
        $ilDB->createSequence('prg_translations');
    }
}



?>
<#4747>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

// workaround to avoid error when using addAdminNode. Bug?
class EventHandler
{
    public function raise($a_component, $a_event, $a_parameter = "")
    {
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
if (!$ilDB->tableColumnExists("obj_members", "admin")) {
    $ilDB->addTableColumn(
        "obj_members",
        "admin",
        array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => false,
                    'default' => 0
        )
    );
}
if (!$ilDB->tableColumnExists("obj_members", "tutor")) {
    $ilDB->addTableColumn(
        "obj_members",
        "tutor",
        array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => false,
                    'default' => 0
        )
    );
}
if (!$ilDB->tableColumnExists("obj_members", "member")) {
    $ilDB->addTableColumn(
        "obj_members",
        "member",
        array(
                    'type' => 'integer',
                    'length' => 2,
                    'notnull' => false,
                    'default' => 0
        )
    );
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
if (!$ilDB->sequenceExists('prg_settings')) {
    $ilDB->createSequence('prg_settings');
}
if (!$ilDB->sequenceExists('prg_usr_assignments')) {
    $ilDB->createSequence('prg_usr_assignments');
}
if (!$ilDB->sequenceExists('prg_usr_progress')) {
    $ilDB->createSequence('prg_usr_progress');
}
if (!$ilDB->sequenceExists('prg_type_adv_md_rec')) {
    $ilDB->createSequence('prg_type_adv_md_rec');
}
if (!$ilDB->sequenceExists('prg_type')) {
    $ilDB->createSequence('prg_type');
}
if (!$ilDB->sequenceExists('prg_translations')) {
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
    'length' => 64,
));
?>
<#4756>
<?php
$obj_type = 'icla';
$set = $ilDB->queryF(
    "SELECT obj_id FROM object_data WHERE type = %s",
    array('text'),
    array($obj_type)
);
while ($row = $ilDB->fetchAssoc($set)) {
    $obj_id = $row['obj_id'];

    $refset = $ilDB->queryF(
        "SELECT ref_id FROM object_reference WHERE obj_id = %s",
        array('integer'),
        array($obj_id)
    );
    while ($refrow = $ilDB->fetchAssoc($refset)) {
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
            $nr,
            $obj_type,
            $ref_id
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
        $nr,
        $obj_type,
        $obj_id
    ));
}
?>
<#4757>
<?php
$obj_type = 'icrs';
$set = $ilDB->queryF(
    "SELECT obj_id FROM object_data WHERE type = %s",
    array('text'),
    array($obj_type)
);
while ($row = $ilDB->fetchAssoc($set)) {
    $obj_id = $row['obj_id'];

    $refset = $ilDB->queryF(
        "SELECT ref_id FROM object_reference WHERE obj_id = %s",
        array('integer'),
        array($obj_id)
    );
    while ($refrow = $ilDB->fetchAssoc($refset)) {
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
            $nr,
            $obj_type,
            $ref_id
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
        $nr,
        $obj_type,
        $obj_id
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
$row = $ilDB->fetchAssoc($set);
$type_id = $row['obj_id'];
if ($type_id) {
    // RBAC

    // basic operations
    $ilDB->manipulate("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($type_id, "integer"));

    // creation operation
    $set = $ilDB->query("SELECT ops_id" .
        " FROM rbac_operations " .
        " WHERE class = " . $ilDB->quote("create", "text") .
        " AND operation = " . $ilDB->quote("create_" . $a_type, "text"));
    $row = $ilDB->fetchAssoc($set);
    $create_ops_id = $row["ops_id"];
    if ($create_ops_id) {
        $ilDB->manipulate("DELETE FROM rbac_templates WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Deleted rbac_templates create operation with ops_id %s for object type %s with obj_id %s.",
            $nr,
            $create_ops_id,
            $a_type,
            $type_id
        ));

        // container create
        foreach (array("icrs") as $parent_type) {
            $pset = $ilDB->queryF(
                "SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
                array('text', 'text'),
                array('typ', $parent_type)
            );
            $prow = $ilDB->fetchAssoc($pset);
            $parent_type_id = $prow['obj_id'];
            if ($parent_type_id) {
                $ilDB->manipulate("DELETE FROM rbac_ta" .
                    " WHERE typ_id = " . $ilDB->quote($parent_type_id, "integer") .
                    " AND ops_id = " . $ilDB->quote($create_ops_id, "integer"));
            }
        }

        $ilDB->manipulate("DELETE FROM rbac_operations WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Deleted create operation with ops_id %s for object type %s with obj_id %s.",
            $nr,
            $create_ops_id,
            $a_type,
            $type_id
        ));
    }

    // Type
    $ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($type_id, "integer"));
    $GLOBALS['ilLog']->write(sprintf(
        "DB Step %s: Deleted object type %s with obj_id %s.",
        $nr,
        $a_type,
        $type_id
    ));
}

$set = new ilSetting();
$set->delete("obj_dis_creation_" . $a_type);
$set->delete("obj_add_new_pos_" . $a_type);
$set->delete("obj_add_new_pos_grp_" . $a_type);
?>
<#4759>
<?php
$a_type = 'icrs';
$set = $ilDB->queryF(
    "SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
    array('text', 'text'),
    array('typ', $a_type)
);
$row = $ilDB->fetchAssoc($set);
$type_id = $row['obj_id'];
if ($type_id) {
    // RBAC

    // basic operations
    $ilDB->manipulate("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($type_id, "integer"));

    // creation operation
    $set = $ilDB->query("SELECT ops_id" .
        " FROM rbac_operations " .
        " WHERE class = " . $ilDB->quote("create", "text") .
        " AND operation = " . $ilDB->quote("create_" . $a_type, "text"));
    $row = $ilDB->fetchAssoc($set);
    $create_ops_id = $row["ops_id"];
    if ($create_ops_id) {
        $ilDB->manipulate("DELETE FROM rbac_templates WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Deleted rbac_templates create operation with ops_id %s for object type %s with obj_id %s.",
            $nr,
            $create_ops_id,
            $a_type,
            $type_id
        ));

        // container create
        foreach (array("root", "cat", "crs", "grp", "fold") as $parent_type) {
            $pset = $ilDB->queryF(
                "SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
                array('text', 'text'),
                array('typ', $parent_type)
            );
            $prow = $ilDB->fetchAssoc($pset);
            $parent_type_id = $prow['obj_id'];
            if ($parent_type_id) {
                $ilDB->manipulate("DELETE FROM rbac_ta" .
                    " WHERE typ_id = " . $ilDB->quote($parent_type_id, "integer") .
                    " AND ops_id = " . $ilDB->quote($create_ops_id, "integer"));
            }
        }

        $ilDB->manipulate("DELETE FROM rbac_operations WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Deleted create operation with ops_id %s for object type %s with obj_id %s.",
            $nr,
            $create_ops_id,
            $a_type,
            $type_id
        ));
    }

    // Type
    $ilDB->manipulate("DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($type_id, "integer"));
    $GLOBALS['ilLog']->write(sprintf(
        "DB Step %s: Deleted object type %s with obj_id %s.",
        $nr,
        $a_type,
        $type_id
    ));
}

$set = new ilSetting();
$set->delete("obj_dis_creation_" . $a_type);
$set->delete("obj_add_new_pos_" . $a_type);
$set->delete("obj_add_new_pos_grp_" . $a_type);
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
$res = $ilDB->query($mt_mod_incon_query_num);
$data = $ilDB->fetchAssoc($res);

if ($data['cnt'] > 0) {
    if (!$ilDB->tableExists('mail_tree_mod_migr')) {
        $ilDB->createTable('mail_tree_mod_migr', array(
            'usr_id' => array(
                'type' => 'integer',
                'length' => 4,
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
if ($ilDB->tableExists('mail_tree_mod_migr')) {
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
    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($ps_create_mtmig_rec, array($row['tree']));

        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Detected wrong child in table 'mail_tree' for user (field: tree) %s .",
            $db_step,
            $row['tree']
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
    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($ps_create_mtmig_rec, array($row['user_id']));

        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Detected missing child in table 'mail_tree' for user (field: tree) %s .",
            $db_step,
            $row['user_id']
        ));
    }
}
?>
<#4763>
<?php
if ($ilDB->tableExists('mail_tree_mod_migr')) {
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
        'a_root' => 'root',
        'b_inbox' => 'inbox',
        'c_trash' => 'trash',
        'd_drafts' => 'drafts',
        'e_sent' => 'sent',
        'z_local' => 'local'
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
        $db_step,
        $num
    ));

    // We need a first loop to delete all affected mail trees
    $i = 0;
    while ($row = $ilDB->fetchAssoc($res)) {
        ++$i;

        $usr_id = $row['usr_id'];

        $ilDB->execute($ps_del_tree_entries, array($usr_id));
        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s: Started 'mail_tree' migration for user %s. Deleted all records referring this user (field: tree)",
            $db_step,
            $usr_id
        ));
    }

    $res = $ilDB->query("SELECT usr_id FROM mail_tree_mod_migr");

    $i = 0;
    while ($row = $ilDB->fetchAssoc($res)) {
        ++$i;

        $usr_id = $row['usr_id'];

        $fold_res = $ilDB->execute($ps_sel_fold_entries, array($usr_id));
        $user_folders = array();
        $user_default_folders = array();
        while ($fold_row = $ilDB->fetchAssoc($fold_res)) {
            $user_folders[$fold_row['obj_id']] = $fold_row;
            if (isset($default_folder_type_to_title_map[strtolower($fold_row['m_type'])])) {
                $user_default_folders[$fold_row['m_type']] = $fold_row['title'];
            }
        }

        // Create missing default folders
        $folders_to_create = array_diff_key($default_folder_type_to_title_map, $user_default_folders);
        foreach ($folders_to_create as $type => $title) {
            $folder_id = $ilDB->nextId('mail_obj_data');
            $ilDB->execute($ps_in_fold_entry, array($folder_id, $usr_id, $title, $type));

            $user_folders[$folder_id] = array(
                'obj_id' => $folder_id,
                'user_id' => $usr_id,
                'title' => $title,
                'm_type' => $type
            );
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created 'mail_obj_data' record (missing folder type): %s, %s, %s, %s .",
                $db_step,
                $i,
                $folder_id,
                $usr_id,
                $title,
                $type
            ));
        }

        // Create a new root folder node
        $root_id = null;
        foreach ($user_folders as $folder_id => $data) {
            if ('root' != $data['m_type']) {
                continue;
            }

            $root_id = $folder_id;
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $root_id, 0, 1, 2, 1));

            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created root node with id %s for user %s in 'mail_tree'.",
                $db_step,
                $i,
                $root_id,
                $usr_id
            ));
            break;
        }

        if (!$root_id) {
            // Did not find root folder, skip user and move to the next one
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: No root folder found for user %s . Skipped user.",
                $db_step,
                $i,
                $usr_id
            ));
            continue;
        }

        $custom_folder_root_id = null;
        // Create all default folders below 'root'
        foreach ($user_folders as $folder_id => $data) {
            if ('root' == $data['m_type'] || !isset($default_folder_type_to_title_map[strtolower($data['m_type'])])) {
                continue;
            }

            if (null === $custom_folder_root_id && 'local' == $data['m_type']) {
                $custom_folder_root_id = $folder_id;
            }

            $res_parent = $ilDB->execute($ps_sel_tree_entry, array($root_id, $usr_id));
            $parent_row = $ilDB->fetchAssoc($res_parent);

            $right = $parent_row['rgt'];
            $lft = $right;
            $rgt = $right + 1;

            $ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $root_id, $lft, $rgt, 2));
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created node with id %s (lft: %s | rgt: %s) for user %s in 'mail_tree'.",
                $db_step,
                $i,
                $folder_id,
                $lft,
                $rgt,
                $usr_id
            ));
        }

        if (!$custom_folder_root_id) {
            // Did not find custom folder root, skip user and move to the next one
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: No custom folder root found for user %s . Skipped user.",
                $db_step,
                $i,
                $usr_id
            ));
            continue;
        }

        // Create all custom folders below 'local'
        foreach ($user_folders as $folder_id => $data) {
            if (isset($default_folder_type_to_title_map[strtolower($data['m_type'])])) {
                continue;
            }

            $res_parent = $ilDB->execute($ps_sel_tree_entry, array($custom_folder_root_id, $usr_id));
            $parent_row = $ilDB->fetchAssoc($res_parent);

            $right = $parent_row['rgt'];
            $lft = $right;
            $rgt = $right + 1;

            $ilDB->execute($ps_up_tree_entry, array($right, $right, $usr_id));
            $ilDB->execute($ps_in_tree_entry, array($usr_id, $folder_id, $custom_folder_root_id, $lft, $rgt, 3));
            $GLOBALS['ilLog']->write(sprintf(
                "DB Step %s, iteration %s: Created custom folder node with id %s (lft: %s | rgt: %s) for user % in 'mail_tree'.",
                $db_step,
                $i,
                $folder_id,
                $lft,
                $rgt,
                $usr_id
            ));
        }

        // Tree completely created, remove migration record
        $ilDB->execute($ps_del_mtmig_rec, array($usr_id));

        $GLOBALS['ilLog']->write(sprintf(
            "DB Step %s, iteration %s: Finished 'mail_tree' migration for user %s .",
            $db_step,
            $i,
            $usr_id
        ));
    }

    $res = $ilDB->query("SELECT usr_id FROM mail_tree_mod_migr");
    $num = $ilDB->numRows($res);
    if ($num > 0) {
        die("There are still wrong child entries in table 'mail_tree'. Please execute this database update step again.");
    }
}

if ($ilDB->tableExists('mail_tree_mod_migr')) {
    $ilDB->dropTable('mail_tree_mod_migr');
}

$mt_mod_incon_query_num = "
	SELECT COUNT(*) cnt
	FROM mail_obj_data
	INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id
	WHERE mail_tree.tree != mail_obj_data.user_id
";
$res = $ilDB->query($mt_mod_incon_query_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
    die("There are still wrong child entries in table 'mail_tree'. Please execute database update step 4761 again. Execute the following SQL string manually: UPDATE settings SET value = 4760 WHERE keyword = 'db_version'; ");
}
?>
<#4764>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4765>
<?php
if (!$ilDB->indexExistsByFields('frm_posts_tree', array('thr_fk'))) {
    $ilDB->addIndex('frm_posts_tree', array('thr_fk'), 'i1');
}
?>
<#4766>
<?php
if (!$ilDB->indexExistsByFields('frm_posts_tree', array('pos_fk'))) {
    $ilDB->addIndex('frm_posts_tree', array('pos_fk'), 'i2');
}
?>
<#4767>
<?php

    if (!$ilDB->indexExistsByFields('role_data', array('auth_mode'))) {
        $ilDB->addIndex('role_data', array('auth_mode'), 'i1');
    }
?>
<#4768>
<?php
$ilDB->modifyTableColumn('cmi_gobjective', 'objective_id', array(
    'length' => 253,
));
?>
<#4769>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4770>
<?php
    $query = 'INSERT INTO log_components (component_id) VALUES (' . $ilDB->quote('log_root', 'text') . ')';
    $ilDB->manipulate($query);
?>

<#4771>
<?php

// remove role entries in obj_members
$query = 'update obj_members set admin = ' . $ilDB->quote(0, 'integer') . ', ' .
        'tutor = ' . $ilDB->quote(0, 'integer') . ', member = ' . $ilDB->quote(0, 'integer');
$ilDB->manipulate($query);

// iterate through all courses
$offset = 0;
$limit = 100;
do {
    $query = 'SELECT obr.ref_id, obr.obj_id FROM object_reference obr ' .
            'join object_data obd on obr.obj_id = obd.obj_id where (type = ' . $ilDB->quote('crs', 'text') . ' or type = ' . $ilDB->quote('grp', 'text') . ') ' .
            $ilDB->setLimit($limit, $offset);
    $res = $ilDB->query($query);

    if (!$res->numRows()) {
        break;
    }
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        // find course members roles
        $query = 'select rol_id, title from rbac_fa ' .
                'join object_data on rol_id = obj_id ' .
                'where parent = ' . $ilDB->quote($row->ref_id, 'integer') . ' ' .
                'and assign = ' . $ilDB->quote('y', 'text');
        $rol_res = $ilDB->query($query);
        while ($rol_row = $rol_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // find users which are not assigned to obj_members and create a default entry
            $query = 'select ua.usr_id from rbac_ua ua ' .
                    'left join obj_members om on ua.usr_id = om.usr_id ' .
                    'where om.usr_id IS NULL ' .
                    'and rol_id = ' . $ilDB->quote($rol_row->rol_id, 'integer') . ' ' .
                    'and om.obj_id = ' . $ilDB->quote($row->obj_id, 'integer');
            $ua_res = $ilDB->query($query);
            while ($ua_row = $ua_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $query = 'insert into obj_members (obj_id, usr_id) ' .
                        'values(' .
                        $ilDB->quote($row->obj_id, 'integer') . ', ' .
                        $ilDB->quote($ua_row->usr_id, 'integer') . ' ' .
                        ')';
                $ilDB->manipulate($query);
            }

            // find users which are assigned to obj_members and update their role assignment
            $query = 'select * from rbac_ua ua ' .
                    'left join obj_members om on ua.usr_id = om.usr_id ' .
                    'where om.usr_id IS NOT NULL ' .
                    'and rol_id = ' . $ilDB->quote($rol_row->rol_id, 'integer') . ' ' .
                    'and om.obj_id = ' . $ilDB->quote($row->obj_id, 'integer');
            $ua_res = $ilDB->query($query);
            while ($ua_row = $ua_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $admin = $tutor = $member = 0;
                switch (substr($rol_row->title, 0, 8)) {
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

                $query = 'update obj_members ' .
                        'set admin = admin  + ' . $ilDB->quote($admin, 'integer') . ', ' .
                        'tutor = tutor + ' . $ilDB->quote($tutor, 'integer') . ', ' .
                        'member = member + ' . $ilDB->quote($member, 'integer') . ' ' .
                        'WHERE usr_id = ' . $ilDB->quote($ua_row->usr_id, 'integer') . ' ' .
                        'AND obj_id = ' . $ilDB->quote($row->obj_id, 'integer');
                $ilDB->manipulate($query);
            }
        }
    }
    // increase offset
    $offset += $limit;
} while (true);

?>
<#4772>
<?php

if (!$ilDB->indexExistsByFields('obj_members', array('usr_id'))) {
    $ilDB->addIndex('obj_members', array('usr_id'), 'i1');
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
    if ($ilDB->getDBType() == 'oracle') {
        if (!$ilDB->tableColumnExists('svy_qst_matrixrows', 'title_tmp')) {
            $ilDB->addTableColumn(
                'svy_qst_matrixrows',
                'title_tmp',
                array(
                "type" => "text",
                "length" => 1000,
                "notnull" => false,
                "default" => null)
            );
            $ilDB->manipulate('UPDATE svy_qst_matrixrows SET title_tmp = title');
            $ilDB->dropTableColumn('svy_qst_matrixrows', 'title');
            $ilDB->renameTableColumn('svy_qst_matrixrows', 'title_tmp', 'title');
        }
    } else {
        $ilDB->modifyTableColumn(
            'svy_qst_matrixrows',
            'title',
            array(
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
$res = $ilDB->queryF($query, array('text'), array('bs_allow_to_contact_me'));
while ($row = $ilDB->fetchAssoc($res)) {
    $ilDB->execute($stmt, array($row['usr_id'], 'bs_allow_to_contact_me', 'y'));
}
?>
<#4790>
<?php

    if (!$ilDB->indexExistsByFields('page_question', array('question_id'))) {
        $ilDB->addIndex('page_question', array('question_id'), 'i2');
    }
?>
<#4791>
<?php
    if (!$ilDB->indexExistsByFields('help_tooltip', array('tt_id', 'module_id'))) {
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
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $server_id = $row->server_id;
    }

    if ($server_id) {
        $query = 'UPDATE usr_data SET auth_mode = ' . $ilDB->quote('ldap_' . (int) $server_id, 'text') . ' ' .
                'WHERE auth_mode = ' . $ilDB->quote('ldap', 'text');
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

while ($row = $ilDB->fetchAssoc($res)) {
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

    if (!$ilDB->tableColumnExists('rbac_fa', 'blocked')) {
        $ilDB->addTableColumn(
            'rbac_fa',
            'blocked',
            array(
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

$manager = $ilDB->loadModule('Manager');

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
if (!$ilDB->tableColumnExists('adl_shared_data', 'cp_node_id')) {
    $ilDB->addTableColumn(
        'adl_shared_data',
        'cp_node_id',
        array(
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => "0"
        )
    );

    $dataRes = $ilDB->query(
        "select cp_datamap.cp_node_id, cp_datamap.slm_id, cp_datamap.target_id from cp_datamap, adl_shared_data "
        . "WHERE cp_datamap.slm_id = adl_shared_data.slm_id AND cp_datamap.target_id = adl_shared_data.target_id"
    );
    while ($row = $ilDB->fetchAssoc($dataRes)) {
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
?>
<#4809>
<?php
if (!$ilDB->tableColumnExists('notification_osd', 'visible_for')) {
    $ilDB->addTableColumn(
        'notification_osd',
        'visible_for',
        array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0)
    );
}
?>
<#4810>
<?php
if ($ilDB->tableColumnExists('svy_times', 'first_question')) {
    $ilDB->modifyTableColumn(
        'svy_times',
        'first_question',
        array(
            'type' => 'integer',
            'length' => 4)
    );
}
?>
<#4811>
<?php
//step 1/4 ecs_part_settings search for dublicates and store it in ecs_part_settings_tmp

if ($ilDB->tableExists('ecs_part_settings')) {
    $res = $ilDB->query("
		SELECT sid, mid
		FROM ecs_part_settings
		GROUP BY sid, mid
		HAVING COUNT(sid) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('ecs_part_settings_tmp')) {
            $ilDB->createTable('ecs_part_settings_tmp', array(
                'sid' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'mid' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('ecs_part_settings_tmp', array('sid','mid'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('ecs_part_settings_tmp', array(), array(
                'sid' => array('integer', $row['sid']),
                'mid' => array('integer', $row['mid'])
            ));
        }
    }
}
?>
<#4812>
<?php
//step 2/4 ecs_part_settings deletes dublicates stored in ecs_part_settings_tmp

if ($ilDB->tableExists('ecs_part_settings_tmp')) {
    $res = $ilDB->query("
	SELECT sid, mid
	FROM ecs_part_settings_tmp
");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM ecs_part_settings
			WHERE
			sid = " . $ilDB->quote($row['sid'], 'integer') . " AND
			mid = " . $ilDB->quote($row['mid'], 'integer')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM ecs_part_settings WHERE" .
            " sid = " . $ilDB->quote($row['sid'], 'integer') .
            " AND mid = " . $ilDB->quote($row['mid'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO ecs_part_settings (sid, mid, export, import, import_type, title, cname, token, export_types, import_types, dtoken) " .
            "VALUES ( " .
            $ilDB->quote($data['sid'], 'integer') . ', ' .
            $ilDB->quote($data['mid'], 'integer') . ', ' .
            $ilDB->quote($data['export'], 'integer') . ', ' .
            $ilDB->quote($data['import'], 'integer') . ', ' .
            $ilDB->quote($data['import_type'], 'integer') . ', ' .
            $ilDB->quote($data['title'], 'text') . ', ' .
            $ilDB->quote($data['cname'], 'text') . ', ' .
            $ilDB->quote($data['token'], 'integer') . ', ' .
            $ilDB->quote($data['export_types'], 'text') . ', ' .
            $ilDB->quote($data['import_types'], 'text') . ', ' .
            $ilDB->quote($data['dtoken'], 'integer') .
            ")");

        $ilDB->manipulate(
            "DELETE FROM ecs_part_settings_tmp WHERE" .
            " sid = " . $ilDB->quote($row['sid'], 'integer') .
            " AND mid = " . $ilDB->quote($row['mid'], 'integer')
        );
    }
}
?>
<#4813>
<?php
//step 3/4 ecs_part_settings adding primary key

if ($ilDB->tableExists('ecs_part_settings')) {
    $ilDB->addPrimaryKey('ecs_part_settings', array('sid', 'mid'));
}
?>
<#4814>
<?php
//step 4/4 ecs_part_settings removes temp table

if ($ilDB->tableExists('ecs_part_settings_tmp')) {
    $ilDB->dropTable('ecs_part_settings_tmp');
}
?>
<#4815>
<?php
//step 1/1 feedback_results removes table

if ($ilDB->tableExists('feedback_results')) {
    $ilDB->dropTable('feedback_results');
}
if ($ilDB->tableExists('feedback_items')) {
    $ilDB->dropTable('feedback_items');
}
?>
<#4816>
<?php
//step 1/4 il_exc_team_log renames old table

if ($ilDB->tableExists('il_exc_team_log') && !$ilDB->tableExists('exc_team_log_old')) {
    $ilDB->renameTable("il_exc_team_log", "exc_team_log_old");
}
?>
<#4817>
<?php
//step 2/4 il_exc_team_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('il_exc_team_log')) {
    $ilDB->createTable('il_exc_team_log', array(
        'log_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'team_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'details' => array(
            'type' => 'text',
            'length' => 500,
            'notnull' => false
        ),
        'action' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('il_exc_team_log', array('log_id'));
    $ilDB->addIndex('il_exc_team_log', array('team_id'), 'i1');
    $ilDB->createSequence('il_exc_team_log');
}
?>
<#4818>
<?php
//step 3/4 il_exc_team_log moves all data to new table

if ($ilDB->tableExists('il_exc_team_log') && $ilDB->tableExists('exc_team_log_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM exc_team_log_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('il_exc_team_log');

        $ilDB->manipulate(
            "INSERT INTO il_exc_team_log (log_id, team_id, user_id, details, action, tstamp)" .
            " VALUES (" .
            $ilDB->quote($id, "integer") .
            "," . $ilDB->quote($row['team_id'], "integer") .
            "," . $ilDB->quote($row['user_id'], "integer") .
            "," . $ilDB->quote($row['details'], "text") .
            "," . $ilDB->quote($row['action'], "integer") .
            "," . $ilDB->quote($row['tstamp'], "integer") .
            ")"
        );
    }
}
?>
<#4819>
<?php
//step 4/4 il_exc_team_log removes old table

if ($ilDB->tableExists('exc_team_log_old')) {
    $ilDB->dropTable('exc_team_log_old');
}
?>
<#4820>
<?php
//step 1/1 il_log removes old table

if ($ilDB->tableExists('il_log')) {
    $ilDB->dropTable('il_log');
}
?>
<#4821>
<?php
//step 1/5 il_verification removes dublicates

if ($ilDB->tableExists('il_verification')) {
    $res = $ilDB->query("
		SELECT id, type
		FROM il_verification
		GROUP BY id, type
		HAVING COUNT(id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('il_verification_tmp')) {
            $ilDB->createTable('il_verification_tmp', array(
                    'id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('il_verification_tmp', array('id', 'type'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('il_verification_tmp', array(), array(
                'id' => array('integer', $row['id']),
                'type' => array('text', $row['type'])
            ));
        }
    }
}
?>
<#4822>
<?php
//step 2/5 il_verification deletes dublicates stored in il_verification_tmp

if ($ilDB->tableExists('il_verification_tmp')) {
    $res = $ilDB->query("
		SELECT id, type
		FROM il_verification_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM il_verification
			WHERE
			id = " . $ilDB->quote($row['id'], 'integer') . " AND
			type = " . $ilDB->quote($row['type'], 'text')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM il_verification WHERE" .
            " id = " . $ilDB->quote($row['id'], 'integer') .
            " AND type = " . $ilDB->quote($row['type'], 'text')
        );

        $ilDB->manipulate("INSERT INTO il_verification (id, type, parameters, raw_data) " .
            "VALUES ( " .
            $ilDB->quote($data['id'], 'integer') . ', ' .
            $ilDB->quote($data['type'], 'text') . ', ' .
            $ilDB->quote($data['parameters'], 'text') . ', ' .
            $ilDB->quote($data['raw_data'], 'text') .
            ")");

        $ilDB->manipulate(
            "DELETE FROM il_verification_tmp WHERE" .
            " id = " . $ilDB->quote($row['id'], 'integer') .
            " AND type = " . $ilDB->quote($row['type'], 'text')
        );
    }
}
?>
<#4823>
<?php
//step 3/5 il_verification drops not used indexes

if ($ilDB->indexExistsByFields('il_verification', array('id'))) {
    $ilDB->dropIndexByFields('il_verification', array('id'));
}
?>
<#4824>
<?php
//step 4/5 il_verification adding primary key

if ($ilDB->tableExists('il_verification')) {
    $ilDB->addPrimaryKey('il_verification', array('id', 'type'));
}
?>
<#4825>
<?php
//step 5/5 il_verification removes temp table

if ($ilDB->tableExists('il_verification_tmp')) {
    $ilDB->dropTable('il_verification_tmp');
}
?>
<#4826>
<?php
//step 1/4 il_wiki_imp_pages removes dublicates

if ($ilDB->tableExists('il_wiki_imp_pages')) {
    $res = $ilDB->query("
		SELECT wiki_id, page_id
		FROM il_wiki_imp_pages
		GROUP BY wiki_id, page_id
		HAVING COUNT(wiki_id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('wiki_imp_pages_tmp')) {
            $ilDB->createTable('wiki_imp_pages_tmp', array(
                'wiki_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'page_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('wiki_imp_pages_tmp', array('wiki_id','page_id'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('wiki_imp_pages_tmp', array(), array(
                'wiki_id' => array('integer', $row['wiki_id']),
                'page_id' => array('integer', $row['page_id'])
            ));
        }
    }
}
?>
<#4827>
<?php
//step 2/4 il_wiki_imp_pages deletes dublicates stored in wiki_imp_pages_tmp

if ($ilDB->tableExists('wiki_imp_pages_tmp')) {
    $res = $ilDB->query("
		SELECT wiki_id, page_id
		FROM wiki_imp_pages_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM il_wiki_imp_pages
			WHERE
			wiki_id = " . $ilDB->quote($row['wiki_id'], 'integer') . " AND
			page_id = " . $ilDB->quote($row['page_id'], 'integer')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM il_wiki_imp_pages WHERE" .
            " wiki_id = " . $ilDB->quote($row['wiki_id'], 'integer') .
            " AND page_id = " . $ilDB->quote($row['page_id'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO il_wiki_imp_pages (wiki_id, ord, indent, page_id) " .
            "VALUES ( " .
            $ilDB->quote($data['wiki_id'], 'integer') . ', ' .
            $ilDB->quote($data['ord'], 'integer') . ', ' .
            $ilDB->quote($data['indent'], 'integer') . ', ' .
            $ilDB->quote($data['page_id'], 'integer') .
            ")");

        $ilDB->manipulate(
            "DELETE FROM wiki_imp_pages_tmp WHERE" .
            " wiki_id = " . $ilDB->quote($row['wiki_id'], 'integer') .
            " AND page_id = " . $ilDB->quote($row['page_id'], 'integer')
        );
    }
}
?>
<#4828>
<?php
//step 3/4 il_wiki_imp_pages adding primary key

if ($ilDB->tableExists('il_wiki_imp_pages')) {
    $ilDB->addPrimaryKey('il_wiki_imp_pages', array('wiki_id', 'page_id'));
}
?>
<#4829>
<?php
//step 4/4 il_wiki_imp_pages removes temp table

if ($ilDB->tableExists('wiki_imp_pages_tmp')) {
    $ilDB->dropTable('wiki_imp_pages_tmp');
}
?>
<#4830>
<?php
//step 1/3 il_wiki_missing_page removes dublicates

if ($ilDB->tableExists('il_wiki_missing_page')) {
    $res = $ilDB->query("
		SELECT wiki_id, source_id, target_name
		FROM il_wiki_missing_page
		GROUP BY wiki_id, source_id, target_name
		HAVING COUNT(wiki_id) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulate(
            "DELETE FROM il_wiki_missing_page WHERE" .
            " wiki_id = " . $ilDB->quote($row['wiki_id'], 'integer') .
            " AND source_id = " . $ilDB->quote($row['source_id'], 'integer') .
            " AND target_name = " . $ilDB->quote($row['target_name'], 'text')
        );

        $ilDB->manipulate("INSERT INTO il_wiki_missing_page (wiki_id, source_id, target_name) " .
            "VALUES ( " .
            $ilDB->quote($row['wiki_id'], 'integer') . ', ' .
            $ilDB->quote($row['source_id'], 'integer') . ', ' .
            $ilDB->quote($row['target_name'], 'text') .
            ")");
    }
}
?>
<#4831>
<?php
//step 2/3 il_wiki_missing_page drops not used indexes

if ($ilDB->indexExistsByFields('il_wiki_missing_page', array('wiki_id'))) {
    $ilDB->dropIndexByFields('il_wiki_missing_page', array('wiki_id'));
}
?>
<#4832>
<?php
//step 3/3 il_wiki_missing_page adding primary key and removing index
if (!$ilDB->indexExistsByFields('il_wiki_missing_page', array('wiki_id', 'target_name'))) {
    $ilDB->addIndex('il_wiki_missing_page', array('wiki_id', 'target_name'), 'i1');
}

if ($ilDB->tableExists('il_wiki_missing_page')) {
    $ilDB->addPrimaryKey('il_wiki_missing_page', array('wiki_id', 'source_id', 'target_name'));
}
?>
<#4833>
<?php
//step 1/2 lo_access search for dublicates and remove them
/*
if ($ilDB->tableExists('lo_access'))
{
    $res = $ilDB->query("
        SELECT first.timestamp ts, first.usr_id ui, first.lm_id li, first.obj_id oi, first.lm_title lt
        FROM lo_access first
        WHERE EXISTS (
            SELECT second.usr_id, second.lm_id
            FROM lo_access second
            WHERE first.usr_id = second.usr_id AND first.lm_id = second.lm_id
            GROUP BY second.usr_id, second.lm_id
            HAVING COUNT(second.lm_id) > 1
        )
    ");
    $data = array();

    while($row = $ilDB->fetchAssoc($res))
    {
        $data[$row['ui'] . '_' . $row['li']][] = $row;
    }


    foreach($data as $rows) {
        $newest = null;

        foreach ($rows as $row) {

            if($newest && ($newest['ts'] == $row['ts'] && $newest['oi'] == $row['oi']))
            {
                $ilDB->manipulate("DELETE FROM lo_access WHERE" .
                    " usr_id = " . $ilDB->quote($newest['ui'], 'integer') .
                    " AND lm_id = " . $ilDB->quote($newest['li'], 'integer') .
                    " AND timestamp = " . $ilDB->quote($newest['ts'], 'date') .
                    " AND obj_id = " . $ilDB->quote($newest['oi'], 'integer')
                );

                $ilDB->manipulate("INSERT INTO lo_access (usr_id, lm_id, timestamp, obj_id) ".
                    "VALUES ( ".
                    $ilDB->quote($row['ui'] ,'integer').', '.
                    $ilDB->quote($row['li'] ,'integer').', '.
                    $ilDB->quote($row['ts'] ,'date').', '.
                    $ilDB->quote($row['oi'] ,'integer').
                    ")");
            }

            if (!$newest || new DateTime($row["ts"]) > new DateTime($newest["ts"])) {
                $newest = $row;
            }
        }

        $ilDB->manipulate("DELETE FROM lo_access WHERE" .
            " usr_id = " . $ilDB->quote($newest['ui'], 'integer') .
            " AND lm_id = " . $ilDB->quote($newest['li'], 'integer') .
            " AND (timestamp != " . $ilDB->quote($newest['ts'], 'date') .
            " XOR obj_id != " . $ilDB->quote($newest['oi'], 'integer') . ")"
        );
    }
}
*/
?>
<#4834>
<?php

// fixes step 4833

$set1 = $ilDB->query("SELECT DISTINCT usr_id, lm_id FROM lo_access ORDER BY usr_id");

while ($r1 = $ilDB->fetchAssoc($set1)) {
    $set2 = $ilDB->query("SELECT * FROM lo_access WHERE usr_id = " . $ilDB->quote($r1["usr_id"], "integer") .
        " AND lm_id = " . $ilDB->quote($r1["lm_id"], "integer") . " ORDER BY timestamp ASC");
    $new_recs = array();
    while ($r2 = $ilDB->fetchAssoc($set2)) {
        $new_recs[$r2["usr_id"] . ":" . $r2["lm_id"]] = $r2;
    }
    $ilDB->manipulate("DELETE FROM lo_access WHERE usr_id = " . $ilDB->quote($r1["usr_id"], "integer") .
        " AND lm_id = " . $ilDB->quote($r1["lm_id"], "integer"));
    foreach ($new_recs as $r) {
        $ilDB->manipulate("INSERT INTO lo_access " .
            "(timestamp, usr_id, lm_id, obj_id, lm_title) VALUES (" .
            $ilDB->quote($r["timestamp"], "timestamp") . "," .
            $ilDB->quote($r["usr_id"], "integer") . "," .
            $ilDB->quote($r["lm_id"], "integer") . "," .
            $ilDB->quote($r["obj_id"], "integer") . "," .
            $ilDB->quote($r["lm_title"], "text") .
            ")");
    }
}


//step 2/2 lo_access adding primary key and removing indexes

if ($ilDB->indexExistsByFields('lo_access', array('usr_id'))) {
    $ilDB->dropIndexByFields('lo_access', array('usr_id'));
}

if ($ilDB->tableExists('lo_access')) {
    $ilDB->addPrimaryKey('lo_access', array('usr_id', 'lm_id'));
}
?>
<#4835>
<?php
//step 1/4 obj_stat search for dublicates and store it in obj_stat_tmp

if ($ilDB->tableExists('obj_stat')) {
    $res = $ilDB->query("
		SELECT obj_id, yyyy, mm, dd, hh
		FROM obj_stat
		GROUP BY obj_id, yyyy, mm, dd, hh
		HAVING COUNT(obj_id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('obj_stat_tmpd')) {
            $ilDB->createTable('obj_stat_tmpd', array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'yyyy' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'mm' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'dd' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'hh' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('obj_stat_tmpd', array('obj_id','yyyy','mm','dd','hh'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('obj_stat_tmpd', array(), array(
                'obj_id' => array('integer', $row['obj_id']),
                'yyyy' => array('integer', $row['yyyy']),
                'mm' => array('integer', $row['mm']),
                'dd' => array('integer', $row['dd']),
                'hh' => array('integer', $row['hh'])
            ));
        }
    }
}
?>
<#4836>
<?php
//step 2/4 obj_stat deletes dublicates stored in obj_stat_tmpd

if ($ilDB->tableExists('obj_stat_tmpd')) {
    $res = $ilDB->query("
		SELECT obj_id, yyyy, mm, dd, hh
		FROM obj_stat_tmpd
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM obj_stat
			WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			yyyy = " . $ilDB->quote($row['yyyy'], 'integer') . " AND
			mm = " . $ilDB->quote($row['mm'], 'integer') . " AND
			dd = " . $ilDB->quote($row['dd'], 'integer') . " AND
			hh = " . $ilDB->quote($row['hh'], 'integer')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "
			DELETE FROM obj_stat WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			yyyy = " . $ilDB->quote($row['yyyy'], 'integer') . " AND
			mm = " . $ilDB->quote($row['mm'], 'integer') . " AND
			dd = " . $ilDB->quote($row['dd'], 'integer') . " AND
			hh = " . $ilDB->quote($row['hh'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO obj_stat " .
            "(obj_id, obj_type,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) " .
            "VALUES ( " .
            $ilDB->quote($data['obj_id'], 'integer') . ', ' .
            $ilDB->quote($data['obj_type'], 'text') . ', ' .
            $ilDB->quote($data['yyyy'], 'integer') . ', ' .
            $ilDB->quote($data['mm'], 'integer') . ', ' .
            $ilDB->quote($data['dd'], 'integer') . ', ' .
            $ilDB->quote($data['hh'], 'integer') . ', ' .
            $ilDB->quote($data['read_count'], 'integer') . ', ' .
            $ilDB->quote($data['childs_read_count'], 'integer') . ', ' .
            $ilDB->quote($data['spent_seconds'], 'integer') . ', ' .
            $ilDB->quote($data['childs_spent_seconds'], 'integer') .
            ")");

        $ilDB->manipulate(
            "
			DELETE FROM obj_stat_tmpd WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			yyyy = " . $ilDB->quote($row['yyyy'], 'integer') . " AND
			mm = " . $ilDB->quote($row['mm'], 'integer') . " AND
			dd = " . $ilDB->quote($row['dd'], 'integer') . " AND
			hh = " . $ilDB->quote($row['hh'], 'integer')
        );
    }
}
?>
<#4837>
<?php
//step 3/4 obj_stat adding primary key
if ($ilDB->indexExistsByFields('obj_stat', array('obj_id','yyyy','mm'))) {
    $ilDB->dropIndexByFields('obj_stat', array('obj_id','yyyy','mm'));
}

if ($ilDB->indexExistsByFields('obj_stat', array('obj_id'))) {
    $ilDB->dropIndexByFields('obj_stat', array('obj_id'));
}

if ($ilDB->tableExists('obj_stat')) {
    $ilDB->addPrimaryKey('obj_stat', array('obj_id','yyyy','mm','dd','hh'));
}
?>
<#4838>
<?php
//step 4/4 obj_stat removes temp table

if ($ilDB->tableExists('obj_stat_tmpd')) {
    $ilDB->dropTable('obj_stat_tmpd');
}
?>
<#4839>
<?php
//step 1/4 obj_stat_log renames old table

if ($ilDB->tableExists('obj_stat_log') && !$ilDB->tableExists('obj_stat_log_old')) {
    $ilDB->renameTable("obj_stat_log", "obj_stat_log_old");
}
?>
<#4840>
<?php
//step 2/4 obj_stat_log creates new table with unique id and sequenz

if (!$ilDB->tableExists('obj_stat_log')) {
    $ilDB->createTable('obj_stat_log', array(
        'log_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'obj_type' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true
        ),
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'yyyy' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false
        ),
        'mm' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'dd' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'hh' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'read_count' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'childs_read_count' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'spent_seconds' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'childs_spent_seconds' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
    ));
    $ilDB->addPrimaryKey('obj_stat_log', array('log_id'));
    $ilDB->addIndex('obj_stat_log', array('tstamp'), 'i1');
    $ilDB->createSequence('obj_stat_log');
}
?>
<#4841>
<?php
//step 3/4 obj_stat_log moves all data to new table

if ($ilDB->tableExists('obj_stat_log') && $ilDB->tableExists('obj_stat_log_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM obj_stat_log_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('obj_stat_log');

        $ilDB->manipulate(
            "INSERT INTO obj_stat_log " .
                          "(log_id, obj_id, obj_type, tstamp,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) " .
                          "VALUES ( " .
                          $ilDB->quote($id, 'integer') . ', ' .
                          $ilDB->quote($row['obj_id'], 'integer') . ', ' .
                          $ilDB->quote($row['obj_type'], 'text') . ', ' .
                          $ilDB->quote($row['tstamp'], 'integer') . ', ' .
                          $ilDB->quote($row['yyyy'], 'integer') . ', ' .
                          $ilDB->quote($row['mm'], 'integer') . ', ' .
                          $ilDB->quote($row['dd'], 'integer') . ', ' .
                          $ilDB->quote($row['hh'], 'integer') . ', ' .
                          $ilDB->quote($row['read_count'], 'integer') . ', ' .
                          $ilDB->quote($row['childs_read_count'], 'integer') . ', ' .
                          $ilDB->quote($row['spent_seconds'], 'integer') . ', ' .
                          $ilDB->quote($row['childs_spent_seconds'], 'integer') .
                          ")"
        );

        $ilDB->manipulate(
            "
			DELETE FROM obj_stat_log_old WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			obj_type = " . $ilDB->quote($row['obj_type'], 'integer') . " AND
			tstamp = " . $ilDB->quote($row['tstamp'], 'integer') . " AND
			yyyy = " . $ilDB->quote($row['yyyy'], 'integer') . " AND
			mm = " . $ilDB->quote($row['mm'], 'integer') . " AND
			dd = " . $ilDB->quote($row['dd'], 'integer') . " AND
			hh = " . $ilDB->quote($row['hh'], 'integer') . " AND
			read_count = " . $ilDB->quote($row['read_count'], 'integer') . " AND
			childs_read_count = " . $ilDB->quote($row['childs_read_count'], 'integer') . " AND
			spent_seconds = " . $ilDB->quote($row['spent_seconds'], 'integer') . " AND
			childs_spent_seconds = " . $ilDB->quote($row['childs_spent_seconds'], 'integer')
        );
    }
}
?>
<#4842>
<?php
//step 4/4 obj_stat_log removes old table

if ($ilDB->tableExists('obj_stat_log_old')) {
    $ilDB->dropTable('obj_stat_log_old');
}
?>
<#4843>
<?php
//step 1/4 obj_stat_tmp renames old table

if ($ilDB->tableExists('obj_stat_tmp') && !$ilDB->tableExists('obj_stat_tmp_old')) {
    $ilDB->renameTable("obj_stat_tmp", "obj_stat_tmp_old");
}
?>
<#4844>
<?php
//step 2/4 obj_stat_tmp creates new table with unique id

if (!$ilDB->tableExists('obj_stat_tmp')) {
    $ilDB->createTable('obj_stat_tmp', array(
        'log_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'obj_type' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true
        ),
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'yyyy' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false
        ),
        'mm' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'dd' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'hh' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'read_count' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'childs_read_count' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'spent_seconds' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'childs_spent_seconds' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
    ));
    $ilDB->addPrimaryKey('obj_stat_tmp', array('log_id'));
    $ilDB->addIndex('obj_stat_tmp', array('obj_id', 'obj_type', 'yyyy', 'mm', 'dd', 'hh'), 'i1');
    $ilDB->createSequence('obj_stat_tmp');
}
?>
<#4845>
<?php
//step 3/4 obj_stat_tmp moves all data to new table

if ($ilDB->tableExists('obj_stat_tmp') && $ilDB->tableExists('obj_stat_tmp_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM obj_stat_tmp_old
");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('obj_stat_tmp');

        $ilDB->manipulate(
            "INSERT INTO obj_stat_tmp " .
                          "(log_id, obj_id, obj_type, tstamp,  yyyy, mm, dd, hh, read_count, childs_read_count, spent_seconds, childs_spent_seconds) " .
                          "VALUES ( " .
                          $ilDB->quote($id, 'integer') . ', ' .
                          $ilDB->quote($row['obj_id'], 'integer') . ', ' .
                          $ilDB->quote($row['obj_type'], 'text') . ', ' .
                          $ilDB->quote($row['tstamp'], 'integer') . ', ' .
                          $ilDB->quote($row['yyyy'], 'integer') . ', ' .
                          $ilDB->quote($row['mm'], 'integer') . ', ' .
                          $ilDB->quote($row['dd'], 'integer') . ', ' .
                          $ilDB->quote($row['hh'], 'integer') . ', ' .
                          $ilDB->quote($row['read_count'], 'integer') . ', ' .
                          $ilDB->quote($row['childs_read_count'], 'integer') . ', ' .
                          $ilDB->quote($row['spent_seconds'], 'integer') . ', ' .
                          $ilDB->quote($row['childs_spent_seconds'], 'integer') .
                          ")"
        );

        $ilDB->manipulate(
            "
			DELETE FROM obj_stat_tmp_old WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			yyyy = " . $ilDB->quote($row['yyyy'], 'integer') . " AND
			mm = " . $ilDB->quote($row['mm'], 'integer') . " AND
			dd = " . $ilDB->quote($row['dd'], 'integer') . " AND
			hh = " . $ilDB->quote($row['hh'], 'integer') . " AND
			read_count = " . $ilDB->quote($row['read_count'], 'integer') . " AND
			childs_read_count = " . $ilDB->quote($row['childs_read_count'], 'integer') . " AND
			spent_seconds = " . $ilDB->quote($row['spent_seconds'], 'integer') . " AND
			childs_spent_seconds = " . $ilDB->quote($row['childs_spent_seconds'], 'integer')
        );
    }
}
?>
<#4846>
<?php
//step 4/4 obj_stat_tmp_old removes old table

if ($ilDB->tableExists('obj_stat_tmp_old')) {
    $ilDB->dropTable('obj_stat_tmp_old');
}
?>
<#4847>
<?php
//page_question adding primary key

if ($ilDB->tableExists('page_question')) {
    $ilDB->addPrimaryKey('page_question', array('page_id', 'question_id'));
}
?>
<#4848>
<?php
//step 1/4 page_style_usage renames old table

if ($ilDB->tableExists('page_style_usage') && !$ilDB->tableExists('page_style_usage_old')) {
    $ilDB->renameTable("page_style_usage", "page_style_usage_old");
}
?>
<#4849>
<?php
//step 2/4 page_style_usage creates new table with unique id and sequenz

if (!$ilDB->tableExists('page_style_usage')) {
    $ilDB->createTable('page_style_usage', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'page_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'page_type' => array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => true
        ),
        'page_nr' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'template' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'stype' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => false
        ),
        'sname' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => true,
            'notnull' => false
        ),
        'page_lang' => array(
            'type' => 'text',
            'length' => 2,
            'notnull' => true,
            'default' => "-")
    ));
    $ilDB->addPrimaryKey('page_style_usage', array('id'));
    $ilDB->createSequence('page_style_usage');
}
?>
<#4850>
<?php
//step 3/4 page_style_usage moves all data to new table

if ($ilDB->tableExists('page_style_usage') && $ilDB->tableExists('page_style_usage_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM page_style_usage_old
	");

    $ilDB->manipulate("DELETE FROM page_style_usage");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('page_style_usage');

        $ilDB->manipulate("INSERT INTO page_style_usage " .
                          "(id, page_id, page_type, page_lang, page_nr, template, stype, sname) VALUES (" .
                          $ilDB->quote($id, "integer") . "," .
                          $ilDB->quote($row['page_id'], "integer") . "," .
                          $ilDB->quote($row['page_type'], "text") . "," .
                          $ilDB->quote($row['page_lang'], "text") . "," .
                          $ilDB->quote($row['page_nr'], "integer") . "," .
                          $ilDB->quote($row['template'], "integer") . "," .
                          $ilDB->quote($row['stype'], "text") . "," .
                          $ilDB->quote($row['sname'], "text") .
                          ")");
    }
}
?>
<#4851>
<?php
//step 4/4 page_style_usage removes old table

if ($ilDB->tableExists('page_style_usage_old')) {
    $ilDB->dropTable('page_style_usage_old');
}
?>
<#4852>
<?php
//page_question adding primary key

// fixes duplicate entries
$set1 = $ilDB->query("SELECT DISTINCT user_id FROM personal_pc_clipboard ORDER BY user_id");

while ($r1 = $ilDB->fetchAssoc($set1)) {
    $set2 = $ilDB->query("SELECT * FROM personal_pc_clipboard WHERE user_id = " . $ilDB->quote($r1["user_id"], "integer") .
        " ORDER BY insert_time ASC");
    $new_recs = array();
    while ($r2 = $ilDB->fetchAssoc($set2)) {
        $new_recs[$r2["user_id"] . ":" . $r2["insert_time"] . ":" . $r2["order_nr"]] = $r2;
    }
    $ilDB->manipulate("DELETE FROM personal_pc_clipboard WHERE user_id = " . $ilDB->quote($r1["user_id"], "integer"));
    foreach ($new_recs as $r) {
        $ilDB->insert("personal_pc_clipboard", array(
            "user_id" => array("integer", $r["user_id"]),
            "content" => array("clob", $r["content"]),
            "insert_time" => array("timestamp", $r["insert_time"]),
            "order_nr" => array("integer", $r["order_nr"])
            ));
    }
}

if ($ilDB->indexExistsByFields('personal_pc_clipboard', array('user_id'))) {
    $ilDB->dropIndexByFields('obj_stat', array('user_id'));
}

if ($ilDB->tableExists('personal_pc_clipboard')) {
    $ilDB->addPrimaryKey('personal_pc_clipboard', array('user_id', 'insert_time', 'order_nr'));
}
?>
<#4853>
<?php
//step 1/4 ut_lp_collections search for dublicates and store it in ut_lp_collections_tmp

if ($ilDB->tableExists('ut_lp_collections')) {
    $res = $ilDB->query("
		SELECT obj_id, item_id
		FROM ut_lp_collections
		GROUP BY obj_id, item_id
		HAVING COUNT(obj_id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('ut_lp_collections_tmp')) {
            $ilDB->createTable('ut_lp_collections_tmp', array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'item_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('ut_lp_collections_tmp', array('obj_id','item_id'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('ut_lp_collections_tmp', array(), array(
                'obj_id' => array('integer', $row['obj_id']),
                'item_id' => array('integer', $row['item_id'])
            ));
        }
    }
}
?>
<#4854>
<?php
//step 2/4 ut_lp_collections deletes dublicates stored in ut_lp_collections_tmp

if ($ilDB->tableExists('ut_lp_collections_tmp')) {
    $res = $ilDB->query("
		SELECT obj_id, item_id
		FROM ut_lp_collections_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM ut_lp_collections
			WHERE
			obj_id = " . $ilDB->quote($row['obj_id'], 'integer') . " AND
			item_id = " . $ilDB->quote($row['item_id'], 'integer')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM ut_lp_collections WHERE" .
                          " obj_id = " . $ilDB->quote($row['obj_id'], 'integer') .
                          " AND item_id = " . $ilDB->quote($row['item_id'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO ut_lp_collections (obj_id, item_id, grouping_id, num_obligatory, active, lpmode) " .
                          "VALUES ( " .
                          $ilDB->quote($data['obj_id'], 'integer') . ', ' .
                          $ilDB->quote($data['item_id'], 'integer') . ', ' .
                          $ilDB->quote($data['grouping_id'], 'integer') . ', ' .
                          $ilDB->quote($data['num_obligatory'], 'integer') . ', ' .
                          $ilDB->quote($data['active'], 'integer') . ', ' .
                          $ilDB->quote($data['lpmode'], 'text') .
                          ")");

        $ilDB->manipulate(
            "DELETE FROM ut_lp_collections_tmp WHERE" .
                          " obj_id = " . $ilDB->quote($row['obj_id'], 'integer') .
                          " AND item_id = " . $ilDB->quote($row['item_id'], 'integer')
        );
    }
}
?>
<#4855>
<?php
//step 3/4 ut_lp_collections adding primary key and removing indexes

if ($ilDB->indexExistsByFields('ut_lp_collections', array('obj_id', 'item_id'))) {
    $ilDB->dropIndexByFields('ut_lp_collections', array('obj_id', 'item_id'));
}

if ($ilDB->tableExists('ut_lp_collections')) {
    $ilDB->addPrimaryKey('ut_lp_collections', array('obj_id', 'item_id'));
}
?>
<#4856>
<?php
//step 4/4 ut_lp_collections removes temp table

if ($ilDB->tableExists('ut_lp_collections_tmp')) {
    $ilDB->dropTable('ut_lp_collections_tmp');
}
?>
<#4857>
<?php
//usr_session_stats adding primary key
$usr_session_stats_temp_num = "
SELECT COUNT(*) cnt
FROM (
	SELECT slot_begin
    FROM usr_session_stats
    GROUP BY slot_begin
    HAVING COUNT(*) > 1
) duplicateSessionStats
";
$res = $ilDB->query($usr_session_stats_temp_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt']) {
    $usr_session_stats_dup_query = "
	SELECT *
	FROM usr_session_stats
	GROUP BY slot_begin
	HAVING COUNT(*) > 1
	";
    $res = $ilDB->query($usr_session_stats_dup_query);

    $stmt_del = $ilDB->prepareManip("DELETE FROM usr_session_stats WHERE slot_begin = ? ", array('integer'));
    $stmt_in = $ilDB->prepareManip(
        "INSERT INTO usr_session_stats ("
        . "slot_begin"
        . ",slot_end"
        . ",active_min"
        . ",active_max"
        . ",active_avg"
        . ",active_end"
        . ",opened"
        . ",closed_manual"
        . ",closed_expire"
        . ",closed_idle"
        . ",closed_idle_first"
        . ",closed_limit"
        . ",closed_login"
        . ",max_sessions"
        . ",closed_misc"
        . ") VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        array(
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer'
        )
    );

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($stmt_del, array($row['slot_begin']));
        $ilDB->execute(
            $stmt_in,
            array(
                $row['slot_begin'],
                $row['slot_end'],
                $row['active_min'],
                $row['active_max'],
                $row['active_avg'],
                $row['active_end'],
                $row['opened'],
                $row['closed_manual'],
                $row['closed_expire'],
                $row['closed_idle'],
                $row['closed_idle_first'],
                $row['closed_limit'],
                $row['closed_login'],
                $row['max_sessions'],
                $row['closed_misc']
            )
        );
    }
}

$res = $ilDB->query($usr_session_stats_temp_num);
$data = $ilDB->fetchAssoc($res);
if ($data['cnt'] > 0) {
    die("There are still duplicate entries in table 'usr_session_stats'. Please execute this database update step again.");
}


if ($ilDB->tableExists('usr_session_stats')) {
    $ilDB->addPrimaryKey('usr_session_stats', array('slot_begin'));
}
?>
<#4858>
<?php
//step 1/2 usr_session_log search for dublicates and delete them

if ($ilDB->tableExists('usr_session_log')) {
    $res = $ilDB->query("
		SELECT tstamp, maxval, user_id
		FROM usr_session_log
		GROUP BY tstamp, maxval, user_id
		HAVING COUNT(tstamp) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulate(
            "DELETE FROM usr_session_log WHERE" .
              " tstamp = " . $ilDB->quote($row['tstamp'], 'integer') .
              " AND maxval = " . $ilDB->quote($row['maxval'], 'integer') .
              " AND user_id = " . $ilDB->quote($row['user_id'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO usr_session_log (tstamp, maxval, user_id) " .
              "VALUES ( " .
              $ilDB->quote($row['tstamp'], 'integer') . ', ' .
              $ilDB->quote($row['maxval'], 'integer') . ', ' .
              $ilDB->quote($row['user_id'], 'integer') .
        ")");
    }
}
?>
<#4859>
<?php
//step 2/2 usr_session_log adding primary key

if ($ilDB->tableExists('usr_session_log')) {
    $ilDB->addPrimaryKey('usr_session_log', array('tstamp', 'maxval', 'user_id'));
}
?>
<#4860>
<?php
//step 1/2 style_template_class search for dublicates and delete them

if ($ilDB->tableExists('style_template_class')) {
    $res = $ilDB->query("
		SELECT template_id, class_type
		FROM style_template_class
		GROUP BY template_id, class_type
		HAVING COUNT(template_id) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM style_template_class
			WHERE
			template_id = " . $ilDB->quote($row['template_id'], 'integer') . " AND
			class_type = " . $ilDB->quote($row['class_type'], 'integer')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM style_template_class WHERE" .
                          " template_id = " . $ilDB->quote($row['template_id'], 'integer') .
                          " AND class_type = " . $ilDB->quote($row['class_type'], 'text')
        );

        $ilDB->manipulate("INSERT INTO style_template_class (template_id, class_type, class) " .
                          "VALUES ( " .
                          $ilDB->quote($row['template_id'], 'integer') . ', ' .
                          $ilDB->quote($row['class_type'], 'text') . ', ' .
                          $ilDB->quote($data['class'], 'text') .
                          ")");
    }
}
?>
<#4861>
<?php
//step 2/2 style_template_class adding primary key

if ($ilDB->tableExists('style_template_class')) {
    $ilDB->addPrimaryKey('style_template_class', array('template_id', 'class_type', 'class'));
}
?>
<#4862>
<?php
//step 1/2 style_folder_styles search for dublicates and delete them

if ($ilDB->tableExists('style_folder_styles')) {
    $res = $ilDB->query("
		SELECT folder_id, style_id
		FROM style_folder_styles
		GROUP BY folder_id, style_id
		HAVING COUNT(folder_id) > 1
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulate(
            "DELETE FROM style_folder_styles WHERE" .
                          " folder_id = " . $ilDB->quote($row['folder_id'], 'integer') .
                          " AND style_id = " . $ilDB->quote($row['style_id'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO style_folder_styles (folder_id, style_id) " .
                          "VALUES ( " .
                          $ilDB->quote($row['folder_id'], 'integer') . ', ' .
                          $ilDB->quote($row['style_id'], 'integer') .
                          ")");
    }
}
?>
<#4863>
<?php
//step 2/2 style_folder_styles adding primary key
if ($ilDB->indexExistsByFields('style_folder_styles', array('folder_id'))) {
    $ilDB->dropIndexByFields('style_folder_styles', array('folder_id'));
}

if ($ilDB->tableExists('style_folder_styles')) {
    $ilDB->addPrimaryKey('style_folder_styles', array('folder_id', 'style_id'));
}
?>
<#4864>
<?php
//step 1/4 mob_parameter search for dublicates and store it in mob_parameter_tmp

if ($ilDB->tableExists('mob_parameter')) {
    $res = $ilDB->query("
		SELECT med_item_id, name
		FROM mob_parameter
		GROUP BY med_item_id, name
		HAVING COUNT(med_item_id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('mob_parameter_tmp')) {
            $ilDB->createTable('mob_parameter_tmp', array(
                'med_item_id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                ),
                'name' => array(
                    'type' => 'text',
                    'length' => 50,
                    'notnull' => true,
                )
            ));
            $ilDB->addPrimaryKey('mob_parameter_tmp', array('med_item_id','name'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('mob_parameter_tmp', array(), array(
                'med_item_id' => array('integer', $row['med_item_id']),
                'name' => array('text', $row['name'])
            ));
        }
    }
}
?>
<#4865>
<?php
//step 2/4 mob_parameter deletes dublicates stored in mob_parameter_tmp

if ($ilDB->tableExists('mob_parameter_tmp')) {
    $res = $ilDB->query("
		SELECT med_item_id, name
		FROM mob_parameter_tmp
");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
		SELECT *
		FROM mob_parameter
		WHERE
		med_item_id = " . $ilDB->quote($row['med_item_id'], 'integer') . " AND
		name = " . $ilDB->quote($row['name'], 'text')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM mob_parameter WHERE" .
                      " med_item_id = " . $ilDB->quote($row['med_item_id'], 'integer') .
                      " AND name = " . $ilDB->quote($row['name'], 'integer')
        );

        $ilDB->manipulate("INSERT INTO mob_parameter (med_item_id, name, value) " .
                      "VALUES ( " .
                      $ilDB->quote($data['med_item_id'], 'integer') . ', ' .
                      $ilDB->quote($data['name'], 'text') . ', ' .
                      $ilDB->quote($data['value'], 'text') .
                      ")");

        $ilDB->manipulate(
            "DELETE FROM mob_parameter_tmp WHERE" .
                      " med_item_id = " . $ilDB->quote($row['med_item_id'], 'integer') .
                      " AND name = " . $ilDB->quote($row['name'], 'text')
        );
    }
}
?>
<#4866>
<?php
//step 3/4 mob_parameter adding primary key
if ($ilDB->indexExistsByFields('mob_parameter', array('med_item_id'))) {
    $ilDB->dropIndexByFields('mob_parameter', array('med_item_id'));
}

if ($ilDB->tableExists('mob_parameter')) {
    $ilDB->addPrimaryKey('mob_parameter', array('med_item_id', 'name'));
}
?>
<#4867>
<?php
//step 4/4 mob_parameter removes temp table

if ($ilDB->tableExists('mob_parameter_tmp')) {
    $ilDB->dropTable('mob_parameter_tmp');
}
?>
<#4868>
<?php
//step 1/4 link_check renames old table

if ($ilDB->tableExists('link_check') && !$ilDB->tableExists('link_check_old')) {
    $ilDB->renameTable("link_check", "link_check_old");
}
?>
<#4869>
<?php
//step 2/4 link_check creates new table with unique id and sequenz

if (!$ilDB->tableExists('link_check')) {
    $ilDB->createTable('link_check', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'page_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'url' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
            'default' => null
        ),
        'parent_type' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => false,
            'default' => null
        ),
        'http_status_code' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'last_check' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('link_check', array('id'));
    $ilDB->addIndex('link_check', array('obj_id'), 'i1');
    $ilDB->createSequence('link_check');
}
?>
<#4870>
<?php
//step 3/4 link_check moves all data to new table

if ($ilDB->tableExists('link_check') && $ilDB->tableExists('link_check_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM link_check_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('link_check');

        $ilDB->manipulate(
            "INSERT INTO link_check (id, obj_id, page_id, url, parent_type, http_status_code, last_check)" .
                          " VALUES (" .
                          $ilDB->quote($id, "integer") .
                          "," . $ilDB->quote($row['obj_id'], "integer") .
                          "," . $ilDB->quote($row['page_id'], "integer") .
                          "," . $ilDB->quote($row['url'], "text") .
                          "," . $ilDB->quote($row['parent_type'], "text") .
                          "," . $ilDB->quote($row['http_status_code'], "integer") .
                          "," . $ilDB->quote($row['last_check'], "integer") .
                          ")"
        );

        $ilDB->manipulateF(
            "DELETE FROM link_check_old WHERE obj_id = %s AND page_id = %s AND url = %s AND parent_type = %s AND http_status_code = %s AND last_check = %s",
            array('integer', 'integer', 'text', 'text', 'integer', 'integer'),
            array($row['obj_id'], $row['page_id'], $row['url'], $row['parent_type'], $row['http_status_code'], $row['last_check'])
        );
    }
}
?>
<#4871>
<?php
//step 4/4 link_check removes old table

if ($ilDB->tableExists('link_check_old')) {
    $ilDB->dropTable('link_check_old');
}
?>
<#4872>
<?php
//$num_query = "
//	SELECT COUNT(*) cnt
//	FROM (
//		SELECT tree, child
//		FROM bookmark_tree
//		GROUP BY tree, child
//		HAVING COUNT(*) > 1
//	) duplicateBookmarkTree
//";
//$res  = $ilDB->query($num_query);
//$data = $ilDB->fetchAssoc($res);
//
//if($data['cnt'] > 0)
//{
//	echo "<pre>
//
//		Dear Administrator,
//
//		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS
//
//		The update process has been stopped due to a data consistency issue in table 'bookmark_tree'.
//		The values in field 'tree' and 'child' should be unique together, but there are dublicated values in these fields.
//		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:
//
//		SELECT *
//		FROM bookmark_tree first
//		WHERE EXISTS (
//			SELECT second.tree, second.child
//			FROM bookmark_tree second
//			WHERE first.tree = second.tree AND first.child = second.child
//			GROUP BY second.tree, second.child
//			HAVING COUNT(second.tree) > 1
//		);
//
//		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.
//
//		Please ensure to backup your current database before fixing the database.
//		Furthermore disable your client while fixing the database.
//
//		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.
//
//		Best regards,
//		The Bookmark maintainer
//
//	</pre>";
//
//	exit();
//}
//
//
//if($ilDB->tableExists('bookmark_tree'))
//{
//	$ilDB->addPrimaryKey('bookmark_tree', array('tree', 'child'));
//}

?>
<#4873>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
	SELECT lm_id, child
	FROM lm_tree
	GROUP BY lm_id, child
	HAVING COUNT(*) > 1
	) duplicateLMTree
";
$res = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if ($data['cnt'] > 0) {
    echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'lm_tree'.
		The values in field 'lm_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM lm_tree first
		WHERE EXISTS (
			SELECT second.lm_id, second.child
			FROM lm_tree second
			WHERE first.lm_id = second.lm_id AND first.child = second.child
			GROUP BY second.lm_id, second.child
			HAVING COUNT(second.lm_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Learning Modules maintainer

	</pre>";

    exit();
}


if ($ilDB->tableExists('lm_tree')) {
    $ilDB->addPrimaryKey('lm_tree', array('lm_id', 'child'));
}

?>
<#4874>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
		SELECT mep_id, child
		FROM mep_tree
		GROUP BY mep_id, child
		HAVING COUNT(*) > 1
	) duplicateMEPTree
";
$res = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if ($data['cnt'] > 0) {
    echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'mep_tree'.
		The values in field 'mep_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM mep_tree first
		WHERE EXISTS (
			SELECT second.mep_id, second.child
			FROM mep_tree second
			WHERE first.mep_id = second.mep_id AND first.child = second.child
			GROUP BY second.mep_id, second.child
			HAVING COUNT(second.mep_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Media Pool maintainer

	</pre>";

    exit();
}


if ($ilDB->tableExists('mep_tree')) {
    $ilDB->addPrimaryKey('mep_tree', array('mep_id', 'child'));
}

?>
<#4875>
<?php
$num_query = "
	SELECT COUNT(*) cnt
	FROM (
	SELECT skl_tree_id, child
	FROM skl_tree
	GROUP BY skl_tree_id, child
	HAVING COUNT(*) > 1
	) duplicateSKLTree
";
$res = $ilDB->query($num_query);
$data = $ilDB->fetchAssoc($res);

if ($data['cnt'] > 0) {
    echo "<pre>

		Dear Administrator,

		DO NOT REFRESH THIS PAGE UNLESS YOU HAVE READ THE FOLLOWING INSTRUCTIONS

		The update process has been stopped due to a data consistency issue in table 'skl_tree'.
		The values in field 'skl_tree_id' and 'child' should be unique together, but there are dublicated values in these fields.
		You have to review the data and apply manual fixes on your own risk. The duplicates can be determined with the following SQL string:

		SELECT *
		FROM skl_tree first
		WHERE EXISTS (
			SELECT second.skl_tree_id, second.child
			FROM skl_tree second
			WHERE first.skl_tree_id = second.skl_tree_id AND first.child = second.child
			GROUP BY second.skl_tree_id, second.child
			HAVING COUNT(second.skl_tree_id) > 1
		);

		If you have fixed the Problem and try to rerun the update process, this warning will be skipped.

		Please ensure to backup your current database before fixing the database.
		Furthermore disable your client while fixing the database.

		For further questions use our <a href='http://mantis.ilias.de'>Bugtracker</a> or write a message to the responsible <a href='http://www.ilias.de/docu/goto_docu_pg_9985_42.html'>Maintainer</a>.

		Best regards,
		The Competence Managment maintainer

	</pre>";

    exit();
}


if ($ilDB->tableExists('skl_tree')) {
    $ilDB->addPrimaryKey('skl_tree', array('skl_tree_id', 'child'));
}

?>
<#4876>
<?php
//step 1/4 benchmark renames old table

if ($ilDB->tableExists('benchmark') && !$ilDB->tableExists('benchmark_old')) {
    $ilDB->renameTable("benchmark", "benchmark_old");
}
?>
<#4877>
<?php
//step 2/4 benchmark creates new table with unique id and sequenz

if (!$ilDB->tableExists('benchmark')) {
    $ilDB->createTable('benchmark', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        "cdate" => array(
            "notnull" => false,
            "type" => "timestamp"
        ),
        "module" => array(
            "notnull" => false,
            "length" => 150,
            "fixed" => false,
            "type" => "text"
        ),
        "benchmark" => array(
            "notnull" => false,
            "length" => 150,
            "fixed" => false,
            "type" => "text"
        ),
        "duration" => array(
            "notnull" => false,
            "type" => "float"
        ),
        "sql_stmt" => array(
            "notnull" => false,
            "type" => "clob"
        )
    ));
    $ilDB->addPrimaryKey('benchmark', array('id'));
    $ilDB->addIndex('benchmark', array("module","benchmark"), 'i1');
    $ilDB->createSequence('benchmark');
}
?>
<#4878>
<?php
//step 3/4 benchmark moves all data to new table

if ($ilDB->tableExists('benchmark') && $ilDB->tableExists('benchmark_old')) {
    $res = $ilDB->query("
		SELECT *
		FROM benchmark_old
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $id = $ilDB->nextId('benchmark');

        $ilDB->insert("benchmark", array(
            "id" => array("integer", $id),
            "cdate" => array("timestamp", $row['cdate']),
            "module" => array("text",$row['module']),
            "benchmark" => array("text", $row['benchmark']),
            "duration" => array("float", $row['duration']),
            "sql_stmt" => array("clob", $row['sql_stmt'])
        ));

        $ilDB->manipulateF(
            "DELETE FROM benchmark_old WHERE cdate = %s AND module = %s AND benchmark = %s AND duration = %s ",
            array('timestamp', 'text', 'text', 'float'),
            array($row['cdate'], $row['module'], $row['benchmark'], $row['duration'])
        );
    }
}
?>
<#4879>
<?php
//step 4/4 benchmark removes old table

if ($ilDB->tableExists('benchmark_old')) {
    $ilDB->dropTable('benchmark_old');
}
?>
<#4880>
<?php
//step skl_user_skill_level adding primary key
if ($ilDB->tableExists('skl_user_skill_level')) {
    // get rid of duplicates
    $set = $ilDB->query("SELECT * FROM skl_user_skill_level ORDER BY status_date ASC");
    while ($rec = $ilDB->fetchAssoc($set)) {
        $q = "DELETE FROM skl_user_skill_level WHERE " .
            " skill_id = " . $ilDB->quote($rec["skill_id"], "integer") . " AND " .
            " tref_id = " . $ilDB->quote($rec["tref_id"], "integer") . " AND " .
            " user_id = " . $ilDB->quote($rec["user_id"], "integer") . " AND " .
            " status_date = " . $ilDB->quote($rec["status_date"], "datetime") . " AND " .
            " status = " . $ilDB->quote($rec["status"], "integer") . " AND " .
            " trigger_obj_id = " . $ilDB->quote($rec["trigger_obj_id"], "integer") . " AND " .
            " self_eval = " . $ilDB->quote($rec["self_eval"], "integer");
        //echo "<br>".$q;
        $ilDB->manipulate($q);

        $q = "INSERT INTO skl_user_skill_level " .
            "(skill_id, tref_id, user_id, status_date, status, trigger_obj_id, self_eval, level_id, valid, trigger_ref_id, trigger_title, trigger_obj_type, unique_identifier) VALUES (" .
            $ilDB->quote($rec["skill_id"], "integer") . ", " .
            $ilDB->quote($rec["tref_id"], "integer") . ", " .
            $ilDB->quote($rec["user_id"], "integer") . ", " .
            $ilDB->quote($rec["status_date"], "datetime") . ", " .
            $ilDB->quote($rec["status"], "integer") . ", " .
            $ilDB->quote($rec["trigger_obj_id"], "integer") . ", " .
            $ilDB->quote($rec["self_eval"], "integer") . ", " .
            $ilDB->quote($rec["level_id"], "integer") . ", " .
            $ilDB->quote($rec["valid"], "integer") . ", " .
            $ilDB->quote($rec["trigger_ref_id"], "integer") . ", " .
            $ilDB->quote($rec["trigger_title"], "text") . ", " .
            $ilDB->quote($rec["trigger_obj_type"], "text") . ", " .
            $ilDB->quote($rec["unique_identifier"], "text") . ")";
        //echo "<br>".$q;
        $ilDB->manipulate($q);
    }

    $ilDB->addPrimaryKey('skl_user_skill_level', array('skill_id', 'tref_id', 'user_id', 'status_date', 'status', 'trigger_obj_id', 'self_eval'));
}

?>
<#4881>
<?php


$ilDB->manipulate(
    'update usr_data set passwd = ' .
        $ilDB->quote('', 'text') . ' , auth_mode = ' .
        $ilDB->quote('local', 'text') . ', active = ' .
        $ilDB->quote(0, 'integer') . ' WHERE auth_mode = ' .
        $ilDB->quote('openid', 'text')
);

?>
<#4882>
<?php
if (!$ilDB->indexExistsByFields('il_qpl_qst_fq_unit', array('question_fi'))) {
    $ilDB->addIndex('il_qpl_qst_fq_unit', array('question_fi'), 'i2');
}
?>
<#4883>
<?php

$query = 'SELECT * FROM settings WHERE module = ' . $ilDB->quote('common', 'text') . ' AND keyword = ' . $ilDB->quote('mail_send_html', 'text');
$res = $ilDB->query($query);

$found = false;
while ($row = $ilDB->fetchAssoc($res)) {
    $found = true;
    break;
}

if (!$found) {
    $setting = new ilSetting();
    $setting->set('mail_send_html', 1);
}
?>
<#4884>
<?php
if (!$ilDB->tableExists('lng_log')) {
    $ilDB->createTable(
        'lng_log',
        array(
           'module' => array(
               'type' => 'text',
               'length' => 30,
               'notnull' => true
           ),
           'identifier' => array(
               'type' => 'text',
               'length' => 60,
               'notnull' => true
           )
       )
    );
    $ilDB->addPrimaryKey('lng_log', array('module', 'identifier'));
}
?>
<#4885>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4886>
<?php
$payment_tables = array(
    'payment_coupons', 'payment_coupons_codes', 'payment_coupons_obj', 'payment_coupons_track',
    'payment_currencies', 'payment_erp', 'payment_erps', 'payment_news',
    'payment_objects', 'payment_paymethods', 'payment_prices', 'payment_settings',
    'payment_shopping_cart', 'payment_statistic', 'payment_statistic_coup', 'payment_topics',
    'payment_topic_usr_sort', 'payment_trustees', 'payment_vats', 'payment_vendors'
);

foreach ($payment_tables as $payment_table) {
    if ($ilDB->tableExists($payment_table)) {
        $ilDB->dropTable($payment_table);
    }

    if ($ilDB->sequenceExists($payment_table)) {
        $ilDB->dropSequence($payment_table);
    }
}
?>
<#4887>
<?php
$res = $ilDB->queryF(
    'SELECT obj_id FROM object_data WHERE type = %s',
    array('text'),
    array('pays')
);
$row = $ilDB->fetchAssoc($res);
if (is_array($row) && isset($row['obj_id'])) {
    $obj_id = $row['obj_id'];

    $ref_res = $ilDB->queryF(
        'SELECT ref_id FROM object_reference WHERE obj_id = %s',
        array('integer'),
        array($obj_id)
    );
    while ($ref_row = $ilDB->fetchAssoc($ref_res)) {
        if (is_array($ref_row) && isset($ref_row['ref_id'])) {
            $ref_id = $ref_row['ref_id'];

            $ilDB->manipulateF(
                'DELETE FROM tree WHERE child = %s',
                array('integer'),
                array($ref_id)
            );
        }
    }

    $ilDB->manipulateF(
        'DELETE FROM object_reference WHERE obj_id = %s',
        array('integer'),
        array($obj_id)
    );

    $ilDB->manipulateF(
        'DELETE FROM object_data WHERE obj_id = %s',
        array('integer'),
        array($obj_id)
    );
}
?>
<#4888>
<?php
$res = $ilDB->queryF(
    'SELECT obj_id FROM object_data WHERE type = %s AND title = %s',
    array('text', 'text'),
    array('typ', 'pays')
);
$row = $ilDB->fetchAssoc($res);
if (is_array($row) && isset($row['obj_id'])) {
    $obj_id = $row['obj_id'];

    $ilDB->manipulateF(
        'DELETE FROM rbac_ta WHERE typ_id = %s',
        array('integer'),
        array($obj_id)
    );

    $ilDB->manipulateF(
        'DELETE FROM object_data WHERE obj_id = %s',
        array('integer'),
        array($obj_id)
    );
}
?>
<#4889>
<?php
$ilDB->manipulateF(
    'DELETE FROM cron_job WHERE job_id = %s',
    array('text'),
    array('pay_notification')
);
?>
<#4890>
<?php
$ilDB->manipulateF(
    'DELETE FROM page_style_usage WHERE page_type = %s',
    array('text'),
    array('shop')
);

$ilDB->manipulateF(
    'DELETE FROM page_history WHERE parent_type = %s',
    array('text'),
    array('shop')
);

$ilDB->manipulateF(
    'DELETE FROM page_object WHERE parent_type = %s',
    array('text'),
    array('shop')
);
?>
<#4891>
<?php

if (!$ilDB->tableColumnExists('booking_settings', 'rsv_filter_period')) {
    $ilDB->addTableColumn('booking_settings', 'rsv_filter_period', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => false,
        'default' => null
    ));
}

?>
<#4892>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4893>
<?php
$ilDB->manipulateF(
    'DELETE FROM settings WHERE keyword = %s',
    array('text'),
    array('pear_mail_enable')
);
?>
<#4894>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');
if ($tgt_ops_id) {
    $book_type_id = ilDBUpdateNewObjectType::getObjectTypeId('book');
    if ($book_type_id) {
        // add "copy" to booking tool - returns false if already exists
        if (ilDBUpdateNewObjectType::addRBACOperation($book_type_id, $tgt_ops_id)) {
            // clone settings from "write" to "copy"
            $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
            ilDBUpdateNewObjectType::cloneOperation('book', $src_ops_id, $tgt_ops_id);
        }
    }
}

?>
<#4895>
<?php

if (!$ilDB->tableColumnExists('webr_items', 'internal')) {
    $ilDB->addTableColumn('webr_items', 'internal', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ));
}

?>
<#4896>
<?php
if (!$ilDB->indexExistsByFields('usr_data_multi', array('usr_id'))) {
    $ilDB->addIndex('usr_data_multi', array('usr_id'), 'i1');
}
?>
<#4897>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'starting_time_tmp')) {
    $ilDB->addTableColumn('tst_tests', 'starting_time_tmp', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#4898>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'starting_time_tmp')) {
    $stmp_up = $ilDB->prepareManip("UPDATE tst_tests SET starting_time_tmp = ? WHERE test_id = ?", array('integer', 'integer'));

    $res = $ilDB->query("SELECT test_id, starting_time FROM tst_tests WHERE starting_time_tmp = " . $ilDB->quote(0, 'integer'));
    while ($row = $ilDB->fetchAssoc($res)) {
        $new_starting_time = 0;
        $starting_time = $row['starting_time'];

        if (strlen($starting_time) > 0) {
            if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $starting_time, $matches)) {
                if (is_array($matches)) {
                    if (checkdate($matches[2], $matches[3], $matches[1])) {
                        $new_starting_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
                    }
                }
            }
        }

        $ilDB->execute($stmp_up, array((int) $new_starting_time, $row['test_id']));
    }
}
?>
<#4899>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'starting_time')) {
    $ilDB->dropTableColumn('tst_tests', 'starting_time');
}
?>
<#4900>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'starting_time') && $ilDB->tableColumnExists('tst_tests', 'starting_time_tmp')) {
    $ilDB->renameTableColumn('tst_tests', 'starting_time_tmp', 'starting_time');
}
?>
<#4901>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'ending_time_tmp')) {
    $ilDB->addTableColumn('tst_tests', 'ending_time_tmp', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#4902>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'ending_time_tmp')) {
    $stmp_up = $ilDB->prepareManip("UPDATE tst_tests SET ending_time_tmp = ? WHERE test_id = ?", array('integer', 'integer'));

    $res = $ilDB->query("SELECT test_id, ending_time FROM tst_tests WHERE ending_time_tmp = " . $ilDB->quote(0, 'integer'));
    while ($row = $ilDB->fetchAssoc($res)) {
        $new_ending_time = 0;
        $ending_time = $row['ending_time'];

        if (strlen($ending_time) > 0) {
            if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $ending_time, $matches)) {
                if (is_array($matches)) {
                    if (checkdate($matches[2], $matches[3], $matches[1])) {
                        $new_ending_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
                    }
                }
            }
        }

        $ilDB->execute($stmp_up, array((int) $new_ending_time, $row['test_id']));
    }
}
?>
<#4903>
<?php
if ($ilDB->tableColumnExists('tst_tests', 'ending_time')) {
    $ilDB->dropTableColumn('tst_tests', 'ending_time');
}
?>
<#4904>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'ending_time') && $ilDB->tableColumnExists('tst_tests', 'ending_time_tmp')) {
    $ilDB->renameTableColumn('tst_tests', 'ending_time_tmp', 'ending_time');
}
?>
<#4905>
<?php
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclFieldProperty.php');

if (!$ilDB->tableColumnExists('il_dcl_field_prop', 'name')) {
    $backup_table_name = 'il_dcl_field_prop_b';
    $ilDB->renameTable('il_dcl_field_prop', $backup_table_name);
    $ilDB->renameTable('il_dcl_field_prop_seq', 'il_dcl_field_prop_s_b');

    $ilDB->createTable(ilDclFieldProperty::returnDbTableName(), array(
        'id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'field_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'name' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 4000,
        ),
    ));

    $ilDB->addPrimaryKey(ilDclFieldProperty::returnDbTableName(), array('id'));
    $ilDB->createSequence(ilDclFieldProperty::returnDbTableName());

    if ($ilDB->tableExists('il_dcl_datatype_prop')) {
        $query = "SELECT field_id, inputformat, title, " . $backup_table_name . ".value FROM " . $backup_table_name . " LEFT JOIN il_dcl_datatype_prop ON il_dcl_datatype_prop.id = " . $backup_table_name . ".datatype_prop_id WHERE " . $backup_table_name . ".value IS NOT NULL";
        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result)) {
            $new_entry = new ilDclFieldProperty();
            $new_entry->setFieldId($row['field_id']);
            $new_entry->setInputformat($row['inputformat']);
            $new_entry->setName($row['title']);
            $new_entry->setValue($row['value']);
            $new_entry->store();
        }
    } else {
        throw new Exception("The table 'il_dcl_datatype_prop' is missing for proper migration. Please check if the migration is already completed.");
    }
}

?>

<#4906>
<?php

$result = $ilDB->query("SELECT * FROM il_dcl_datatype WHERE id = 12");
if ($ilDB->numRows($result) == 0) {
    $ilDB->insert('il_dcl_datatype', array(
        'id' => array('integer', 12),
        'title' => array('text', 'plugin'),
        'ildb_type' => array('text', 'text'),
        'storage_location' => array('integer', 0),
        'sort' => array('integer', 100)
    ));
}


$ilDB->update(
    'il_dcl_datatype',
    array(
        'title' => array('text', 'fileupload'),
    ),
    array(
        'id' => array('integer', 6),
    )
);

$ilDB->update(
    'il_dcl_datatype',
    array(
        'title' => array('text', 'ilias_reference'),
    ),
    array(
        'id' => array('integer', 8),
    )
);

$ilDB->update(
    'il_dcl_datatype',
    array(
        'title' => array('text', 'number'),
    ),
    array(
        'id' => array('integer', 1),
    )
);

?>

<#4907>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$dcl_type_id = ilDBUpdateNewObjectType::getObjectTypeId('dcl');

if ($dcl_type_id) {
    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_content');
    if ($src_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($dcl_type_id, $src_ops_id);
    }
}

?>

<#4908>
<?php

global $ilDB;

if (!$ilDB->tableColumnExists('il_dcl_table', 'save_confirmation')) {
    $ilDB->addTableColumn(
        'il_dcl_table',
        'save_confirmation',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        )
    );
}

?>
<#4909>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#4910>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#4911>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('prg');
$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('manage_members', 'Manage Members', 'object', 2400);
if ($type_id && $new_ops_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
}
?>

<#4912>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4913>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#4914>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
    ilDBUpdateNewObjectType::cloneOperation('prg', $src_ops_id, $tgt_ops_id);
?>
<#4915>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');
if ($tgt_ops_id) {
    $mep_type_id = ilDBUpdateNewObjectType::getObjectTypeId('mep');
    if ($mep_type_id) {
        if (!ilDBUpdateNewObjectType::isRBACOperation($mep_type_id, $tgt_ops_id)) {
            // add "copy" to (external) feed
            ilDBUpdateNewObjectType::addRBACOperation($mep_type_id, $tgt_ops_id);

            // clone settings from "write" to "copy"
            $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
            ilDBUpdateNewObjectType::cloneOperation('mep', $src_ops_id, $tgt_ops_id);
        }
    }
}
?>
<#4916>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4917>
<?php
if (!$ilDB->tableColumnExists('il_dcl_table', 'import_enabled')) {
    $ilDB->addTableColumn('il_dcl_table', 'import_enabled', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 1
    ));
}
?>
<#4918>
<?php
//tableview
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'table_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'title' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'roles' => array(
        'type' => 'clob',
    ),
    'description' => array(
        'type' => 'text',
        'length' => '128',

    ),
    'tableview_order' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_dcl_tableview')) {
    $ilDB->createTable('il_dcl_tableview', $fields);
    $ilDB->addPrimaryKey('il_dcl_tableview', array( 'id' ));

    if (!$ilDB->sequenceExists('il_dcl_tableview')) {
        $ilDB->createSequence('il_dcl_tableview');
    }
    if (!$ilDB->indexExistsByFields('il_dcl_tableview', array('table_id'))) {
        $ilDB->addIndex('il_dcl_tableview', array('table_id'), 't1');
    }
}

//tableview_field_setting
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'tableview_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'field' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'visible' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'in_filter' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'filter_value' => array(
        'type' => 'clob',
    ),
    'filter_changeable' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_dcl_tview_set')) {
    $ilDB->createTable('il_dcl_tview_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tview_set', array( 'id' ));

    if (!$ilDB->sequenceExists('il_dcl_tview_set')) {
        $ilDB->createSequence('il_dcl_tview_set');
    }
}

if (!$ilDB->tableExists('il_dcl_tview_set')) {
    $ilDB->createTable('il_dcl_tview_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tview_set', array( 'id' ));

    if (!$ilDB->sequenceExists('il_dcl_tview_set')) {
        $ilDB->createSequence('il_dcl_tview_set');
    }
    if (!$ilDB->indexExistsByFields('il_dcl_tview_set', array('tableview_id'))) {
        $ilDB->addIndex('il_dcl_tview_set', array('tableview_id'), 't1');
    }
}

$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'table_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'field' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'field_order' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'exportable' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_dcl_tfield_set')) {
    $ilDB->createTable('il_dcl_tfield_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tfield_set', array( 'id' ));

    if (!$ilDB->sequenceExists('il_dcl_tfield_set')) {
        $ilDB->createSequence('il_dcl_tfield_set');
    }
    if (!$ilDB->indexExistsByFields('il_dcl_tfield_set', array('table_id', 'field'))) {
        $ilDB->addIndex('il_dcl_tfield_set', array('table_id', 'field'), 't2');
    }
}
?>
<#4919>
<?php
//migration for datacollections:
//ĉreate a standardview for each table, set visibility/filterability for each field
//and delete entries from old view tables
$roles = array();
$query = $ilDB->query('SELECT rol_id FROM rbac_fa WHERE parent = ' . $ilDB->quote(ROLE_FOLDER_ID, 'integer') . " AND assign='y'");
while ($global_role = $ilDB->fetchAssoc($query)) {
    $roles[] = $global_role['rol_id'];
}

//set order of main tables, since main_table_id will be removed
if (!$ilDB->tableColumnExists('il_dcl_table', 'table_order')) {
    $ilDB->addTableColumn('il_dcl_table', 'table_order', array('type' => 'integer', 'length' => 8));
}

if ($ilDB->tableColumnExists('il_dcl_data', 'main_table_id')) {
    $main_table_query = $ilDB->query('SELECT main_table_id FROM il_dcl_data');
    while ($rec = $ilDB->fetchAssoc($main_table_query)) {
        $ilDB->query('UPDATE il_dcl_table SET table_order = 10, is_visible = 1 WHERE id = ' . $ilDB->quote($rec['main_table_id'], 'integer'));
    }
    $ilDB->dropTableColumn('il_dcl_data', 'main_table_id');
}
//
$table_query = $ilDB->query('SELECT id, ref_id FROM il_dcl_table
                          INNER JOIN object_reference ON (object_reference.obj_id = il_dcl_table.obj_id)');

$mapping = array();
while ($rec = $ilDB->fetchAssoc($table_query)) {
    $temp_sql = $ilDB->query('SELECT * FROM il_dcl_tableview WHERE table_id = ' . $ilDB->quote($rec['id']));
    if ($ilDB->numRows($temp_sql)) {
        continue;
    }
    $query = $ilDB->query('SELECT rol_id FROM rbac_fa WHERE parent = ' . $ilDB->quote($rec['ref_id'], 'integer') . " AND assign='y'");
    while ($local_role = $ilDB->fetchAssoc($query)) {
        $roles[] = $local_role['rol_id'];
    }
    //create standardviews for each DCL Table and set id mapping
    $next_id = $ilDB->nextId('il_dcl_tableview');
    $ilDB->query('INSERT INTO il_dcl_tableview (id, table_id, title, roles, description, tableview_order) VALUES ('
        . $ilDB->quote($next_id, 'integer') . ', '
        . $ilDB->quote($rec['id'], 'integer') . ', '
        . $ilDB->quote('Standardview', 'text') . ', '
        . $ilDB->quote(json_encode($roles), 'text') . ', '
        . $ilDB->quote('', 'text') . ', '
        . $ilDB->quote(10, 'integer') . ')');
    $mapping[$rec['id']] = $next_id;
}

if ($ilDB->tableExists('il_dcl_view') && $ilDB->tableExists('il_dcl_viewdefinition')) {

    //fetch information about visibility/filterability
    $view_query = $ilDB->query(
        "SELECT il_dcl_view.table_id, tbl_visible.field, tbl_visible.is_set as visible, f.filterable
        FROM il_dcl_viewdefinition tbl_visible
            INNER JOIN il_dcl_view ON (il_dcl_view.id = tbl_visible.view_id
            AND il_dcl_view.type = 1)
            INNER JOIN
                (SELECT table_id, field, tbl_filterable.is_set as filterable
                    FROM il_dcl_view
                    INNER JOIN il_dcl_viewdefinition tbl_filterable ON (il_dcl_view.id = tbl_filterable.view_id
                    AND il_dcl_view.type = 3)) f ON (f.field = tbl_visible.field AND f.table_id = il_dcl_view.table_id)"
    );

    //set visibility/filterability
    $view_id_cache = array();
    while ($rec = $ilDB->fetchAssoc($view_query)) {
        if (!$mapping[$rec['table_id']]) {
            continue;
        }
        $next_id = $ilDB->nextId('il_dcl_tview_set');
        $ilDB->query(
            'INSERT INTO il_dcl_tview_set (id, tableview_id, field, visible, in_filter, filter_value,
        filter_changeable) VALUES ('
            . $ilDB->quote($next_id, 'integer') . ', '
            . $ilDB->quote($mapping[$rec['table_id']], 'integer') . ', '
            . $ilDB->quote($rec['field'], 'text') . ', '
            . $ilDB->quote($rec['visible'], 'integer') . ', '
            . $ilDB->quote($rec['filterable'], 'integer') . ', '
            . $ilDB->quote('', 'text') . ', '
            . $ilDB->quote(1, 'integer') . ')'
        );
    }

    //fetch information about editability/exportability
    $view_query = $ilDB->query(
        "SELECT il_dcl_view.table_id, tbl_exportable.field, tbl_exportable.is_set as exportable, tbl_exportable.field_order
        FROM il_dcl_viewdefinition tbl_exportable
            INNER JOIN il_dcl_view ON (il_dcl_view.id = tbl_exportable.view_id
            AND il_dcl_view.type = 4)"
    );


    //set editability/exportability
    while ($rec = $ilDB->fetchAssoc($view_query)) {
        $temp_sql = $ilDB->query('SELECT * FROM il_dcl_tfield_set
								WHERE table_id = ' . $ilDB->quote($rec['table_id'], 'integer') . '
								AND field = ' . $ilDB->quote($rec['field'], 'text'));

        if (!$ilDB->numRows($temp_sql)) {
            $next_id = $ilDB->nextId('il_dcl_tfield_set');
            $ilDB->query(
                'INSERT INTO il_dcl_tfield_set (id, table_id, field, field_order, exportable) VALUES ('
                . $ilDB->quote($next_id, 'integer') . ', '
                . $ilDB->quote($rec['table_id'], 'integer') . ', '
                . $ilDB->quote($rec['field'], 'text') . ', '
                . $ilDB->quote($rec['field_order'], 'integer') . ', '
                . $ilDB->quote($rec['exportable'], 'integer') . ')'
            );
        }
    }

    //migrate page object
    $query = $ilDB->query('SELECT *
        FROM il_dcl_view
        INNER JOIN page_object on (il_dcl_view.id = page_object.page_id)
          WHERE il_dcl_view.type = 0
            AND page_object.parent_type = ' . $ilDB->quote('dclf', 'text'));

    while ($rec = $ilDB->fetchAssoc($query)) {
        if (!$mapping[$rec['table_id']]) {
            continue;
        }

        $temp_sql = $ilDB->query('SELECT * FROM page_object
						WHERE page_id = ' . $ilDB->quote($mapping[$rec['table_id']], 'integer') . '
						AND parent_type = ' . $ilDB->quote('dclf', 'text'));

        if ($ilDB->numRows($temp_sql)) {
            $ilDB->query('DELETE FROM page_object
						WHERE page_id = ' . $ilDB->quote($rec['id'], 'integer') . '
						AND parent_type = ' . $ilDB->quote('dclf', 'text'));
        } else {
            $ilDB->query('UPDATE page_object
                  SET page_id = ' . $ilDB->quote($mapping[$rec['table_id']], 'integer') . '
                  WHERE page_id = ' . $ilDB->quote($rec['id'], 'integer') . '
                      AND page_object.parent_type = ' . $ilDB->quote('dclf', 'text'));
        }
    }

    //delete old tables
    $ilDB->dropTable('il_dcl_viewdefinition');
    $ilDB->dropTable('il_dcl_view');
}

?>
<#4920>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4921>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4922>
<?php
require_once 'Services/Migration/DBUpdate_4922/classes/class.ilPasswordUtils.php';

$salt_location = CLIENT_DATA_DIR . '/pwsalt.txt';
if (!is_file($salt_location) || !is_readable($salt_location)) {
    $result = @file_put_contents(
        $salt_location,
        substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(16))), 0, 22)
    );
    if (!$result) {
        die("Could not create the client salt for bcrypt password hashing.");
    }
}

if (!is_file($salt_location) || !is_readable($salt_location)) {
    die("Could not determine the client salt for bcrypt password hashing.");
}
?>
<#4923>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4924>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4925>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('stys');
if ($type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('sty_write_content', 'Edit Content Styles', 'object', 6101);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

        $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
        if ($src_ops_id) {
            ilDBUpdateNewObjectType::cloneOperation('stys', $src_ops_id, $new_ops_id);
        }
    }
}
?>
<#4926>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('stys');
if ($type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('sty_write_system', 'Edit System Styles', 'object', 6100);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

        $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
        if ($src_ops_id) {
            ilDBUpdateNewObjectType::cloneOperation('stys', $src_ops_id, $new_ops_id);
        }
    }
}
?>
<#4927>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('stys');
if ($type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('sty_write_page_layout', 'Edit Page Layouts', 'object', 6102);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

        $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
        if ($src_ops_id) {
            ilDBUpdateNewObjectType::cloneOperation('stys', $src_ops_id, $new_ops_id);
        }
    }
}
?>
<#4928>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
ilDBUpdateNewObjectType::deleteRBACOperation('stys', $ops_id);
?>
<#4929>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4930>
<?php
    if (!$ilDB->tableColumnExists('skl_tree_node', 'creation_date')) {
        $ilDB->addTableColumn('skl_tree_node', 'creation_date', array(
                "type" => "timestamp",
                "notnull" => false,
        ));
    }
?>
<#4931>
<?php
if (!$ilDB->tableColumnExists('skl_tree_node', 'import_id')) {
    $ilDB->addTableColumn('skl_tree_node', 'import_id', array(
            "type" => "text",
            "length" => 50,
            "notnull" => false
    ));
}
?>
<#4932>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'creation_date')) {
    $ilDB->addTableColumn('skl_level', 'creation_date', array(
            "type" => "timestamp",
            "notnull" => false,
    ));
}
?>
<#4933>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'import_id')) {
    $ilDB->addTableColumn('skl_level', 'import_id', array(
            "type" => "text",
            "length" => 50,
            "notnull" => false
    ));
}
?>
<#4934>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_lome', 'min_auto_complete')) {
    $ilDB->addTableColumn(
        'qpl_qst_lome',
        'min_auto_complete',
        array(
            'type' => 'integer',
            'length' => 1,
            'default' => 1)
    );
}
if ($ilDB->tableColumnExists('qpl_qst_lome', 'min_auto_complete')) {
    $ilDB->modifyTableColumn(
        'qpl_qst_lome',
        'min_auto_complete',
        array(
            'default' => 3)
    );
}
?>
<#4935>
<?php

if (!$ilDB->tableColumnExists('svy_svy', 'confirmation_mail')) {
    $ilDB->addTableColumn(
        'svy_svy',
        'confirmation_mail',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => null
        )
    );
}

?>
<#4936>
<?php

$ilDB->manipulate("UPDATE svy_svy" .
    " SET confirmation_mail = " . $ilDB->quote(1, "integer") .
    " WHERE own_results_mail = " . $ilDB->quote(1, "integer") .
    " AND confirmation_mail IS NULL");

?>
<#4937>
<?php

if (!$ilDB->tableColumnExists('svy_svy', 'anon_user_list')) {
    $ilDB->addTableColumn(
        'svy_svy',
        'anon_user_list',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}

?>
<#4938>
<?php

    //Create new object type grpr 'Group Reference'
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $grpr_type_id = ilDBUpdateNewObjectType::addNewType('grpr', 'Group Reference Object');

    $rbac_ops = array(
        ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
        ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
        ilDBUpdateNewObjectType::RBAC_OP_READ,
        ilDBUpdateNewObjectType::RBAC_OP_WRITE,
        ilDBUpdateNewObjectType::RBAC_OP_DELETE,
        ilDBUpdateNewObjectType::RBAC_OP_COPY
    );
    ilDBUpdateNewObjectType::addRBACOperations($grpr_type_id, $rbac_ops);

    $parent_types = array('root', 'cat', 'crs', 'fold', 'grp');
    ilDBUpdateNewObjectType::addRBACCreate('create_grpr', 'Create Group Reference', $parent_types);
?>
<#4939>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4940>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4941>
<?php
//step 1/2 il_request_token deletes old table

if ($ilDB->tableExists('il_request_token')) {
    $ilDB->dropTable('il_request_token');
}

?>
<#4942>
<?php
//step 2/2 il_request_token creates table with primary key

if (!$ilDB->tableExists('il_request_token')) {
    $fields = array(
        "user_id" => array(
            "notnull" => true
        , "length" => 4
        , "unsigned" => false
        , "default" => "0"
        , "type" => "integer"
        )
    , "token" => array(
            "notnull" => false
        , "length" => 64
        , "fixed" => true
        , "type" => "text"
        )
    , "stamp" => array(
            "notnull" => false
        , "type" => "timestamp"
        )
    , "session_id" => array(
            "notnull" => false
        , "length" => 100
        , "fixed" => false
        , "type" => "text"
        )
    );

    $ilDB->createTable("il_request_token", $fields);
    $ilDB->addPrimaryKey("il_request_token", array('token'));
    $ilDB->addIndex("il_request_token", array('user_id', 'session_id'), 'i1');
    $ilDB->addIndex("il_request_token", array('user_id', 'stamp'), 'i2');
}
?>
<#4943>
<?php
//step 1/3 il_event_handling deletes old table
if ($ilDB->tableExists('il_event_handling')) {
    $ilDB->dropTable('il_event_handling');
}

?>
<#4944>
<?php
//step 2/3 il_event_handling creates table with primary key
if (!$ilDB->tableExists('il_event_handling')) {
    $fields = array(
        'component' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true,
            'fixed' => false
        ),
        'type' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true,
            'fixed' => false
        ),
        'id' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true,
            'fixed' => false
        ));
    $ilDB->createTable('il_event_handling', $fields);
    $ilDB->addPrimaryKey("il_event_handling", array('component', 'type', 'id'));
}
?>
<#4945>
<?php
//step 3/3 il_event_handling fill table
$ilCtrlStructureReader->getStructure();
?>
<#4946>
<?php
//step 1/4 copg_section_timings renames old table

if ($ilDB->tableExists('copg_section_timings') && !$ilDB->tableExists('copg_section_t_old')) {
    $ilDB->renameTable("copg_section_timings", "copg_section_t_old");
}
?>
<#4947>
<?php
//step 2/4 copg_section_timings create new table with primary keys
if (!$ilDB->tableExists("copg_section_timings")) {
    $fields = array(
        "page_id" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true
        ),
        "parent_type" => array(
            "type" => "text",
            "length" => 10,
            "notnull" => true
        ),
        "unix_ts" => array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        )
    );

    $ilDB->createTable("copg_section_timings", $fields);
    $ilDB->addPrimaryKey("copg_section_timings", array('page_id', 'parent_type', 'unix_ts'));
}
?>
<#4948>
<?php
//step 3/4 copg_section_timings moves all data to new table

if ($ilDB->tableExists('copg_section_timings') && $ilDB->tableExists('copg_section_t_old')) {
    $res = $ilDB->query("
        SELECT *
        FROM copg_section_t_old
    ");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->replace("copg_section_timings", array(
            "page_id" => array("integer", $row['page_id']),
            "parent_type" => array("text", $row['parent_type']),
            "unix_ts" => array("integer",$row['unix_ts'])
        ), array());

        $ilDB->manipulateF(
            "DELETE FROM copg_section_t_old WHERE page_id = %s AND parent_type = %s AND unix_ts = %s ",
            array('integer', 'text', 'integer'),
            array($row['page_id'], $row['parent_type'], $row['unix_ts'])
        );
    }
}
?>
<#4949>
<?php
//step 4/4 copg_section_timings removes old table

if ($ilDB->tableExists('copg_section_t_old')) {
    $ilDB->dropTable('copg_section_t_old');
}
?>
<#4950>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('bdga', 'Badge Settings');

?>
<#4951>
<?php

if (!$ilDB->tableExists('badge_badge')) {
    $ilDB->createTable('badge_badge', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'parent_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'type_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'descr' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        ),
        'conf' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('badge_badge', array('id'));
    $ilDB->createSequence('badge_badge');
}

?>
<#4952>
<?php

if (!$ilDB->tableExists('badge_image_template')) {
    $ilDB->createTable('badge_image_template', array(
        'id' => array(
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
        'image' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('badge_image_template', array('id'));
    $ilDB->createSequence('badge_image_template');
}

?>
<#4953>
<?php

if (!$ilDB->tableColumnExists('badge_badge', 'image')) {
    $ilDB->addTableColumn(
        'badge_badge',
        'image',
        array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false)
    );
}

?>
<#4954>
<?php

if (!$ilDB->tableExists('badge_image_templ_type')) {
    $ilDB->createTable('badge_image_templ_type', array(
        'tmpl_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'type_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => ""
        )
    ));
    $ilDB->addPrimaryKey('badge_image_templ_type', array('tmpl_id', 'type_id'));
}

?>
<#4955>
<?php

if (!$ilDB->tableExists('badge_user_badge')) {
    $ilDB->createTable('badge_user_badge', array(
        'badge_id' => array(
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
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'awarded_by' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'pos' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('badge_user_badge', array('badge_id', 'user_id'));
}

?>
<#4956>
<?php

if (!$ilDB->tableColumnExists('badge_badge', 'valid')) {
    $ilDB->addTableColumn(
        'badge_badge',
        'valid',
        array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false)
    );
}

?>
<#4957>
<?php

if (!$ilDB->tableExists('object_data_del')) {
    $ilDB->createTable('object_data_del', array(
        'obj_id' => array(
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
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
    ));
    $ilDB->addPrimaryKey('object_data_del', array('obj_id'));
}

?>
<#4958>
<?php

if (!$ilDB->tableColumnExists('object_data_del', 'type')) {
    $ilDB->addTableColumn(
        'object_data_del',
        'type',
        array(
            'type' => 'text',
            'length' => 4,
            'fixed' => true,
            'notnull' => false)
    );
}

?>
<#4959>
<?php

if (!$ilDB->tableColumnExists('badge_badge', 'crit')) {
    $ilDB->addTableColumn(
        'badge_badge',
        'crit',
        array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        )
    );
}

?>
<#4960>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#4961>
<?php

if (!$ilDB->tableExists('ut_lp_defaults')) {
    $ilDB->createTable('ut_lp_defaults', array(
        'type_id' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true,
            'default' => ""
        ),
        'lp_mode' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
    ));
    $ilDB->addPrimaryKey('ut_lp_defaults', array('type_id'));
}

?>
<#4962>
<?php

$dubs_sql = "SELECT * FROM (" .
                    "SELECT tree, child " .
                    "FROM bookmark_tree " .
                    "GROUP BY tree, child " .
                    "HAVING COUNT(*) > 1 ) " .
                "duplicateBookmarkTree";

$res = $ilDB->query($dubs_sql);
$dublicates = array();

while ($row = $ilDB->fetchAssoc($res)) {
    $dublicates[] = $row;
}

if (count($dublicates)) {
    $ilSetting = new ilSetting();
    $ilSetting->set('bookmark_tree_renumber', 1);

    foreach ($dublicates as $key => $row) {
        $res = $ilDB->query("SELECT * FROM bookmark_tree WHERE tree = " . $ilDB->quote($row["tree"], "integer") .
            " AND child = " . $ilDB->quote($row["child"], "integer"));

        $first = $ilDB->fetchAssoc($res);

        $ilDB->manipulate("DELETE FROM bookmark_tree WHERE tree = " . $ilDB->quote($row["tree"], "integer") .
            " AND child = " . $ilDB->quote($row["child"], "integer"));

        $ilDB->query(
            'INSERT INTO bookmark_tree (tree, child, parent, lft, rgt, depth) VALUES ('
                        . $ilDB->quote($first['tree'], 'integer') . ', '
                        . $ilDB->quote($first['child'], 'integer') . ', '
                        . $ilDB->quote($first['parent'], 'integer') . ', '
                        . $ilDB->quote($first['lft'], 'integer') . ', '
                        . $ilDB->quote($first['rgt'], 'integer') . ', '
                        . $ilDB->quote($first['depth'], 'integer') . ')'
        );
    }
}

?>
<#4963>
<?php
$ilSetting = new ilSetting();
if ($ilSetting->get('bookmark_tree_renumber', "0") == "1") {
    include_once('./Services/Migration/DBUpdate_4963/classes/class.ilDBUpdate4963.php');
    ilDBUpdate4963::renumberBookmarkTree();
    $ilSetting->delete('bookmark_tree_renumber');
}

?>
<#4964>
<?php
$manager = $ilDB->loadModule('Manager');

if (!$manager) {
    $manager = $ilDB->loadModule('Manager');
}

$const = $manager->listTableConstraints("bookmark_tree");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('bookmark_tree', array('tree', 'child'));
}

?>
<#4965>
<?php
if (!$ilDB->tableExists('frm_posts_drafts')) {
    $fields = array(
        'draft_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'post_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'thread_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'forum_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ),
        'post_author_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'post_subject' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => true
        ),
        'post_message' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'post_notify' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'post_date' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'post_update' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'update_user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'post_user_alias' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'pos_display_usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'notify' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )

    );

    $ilDB->createTable('frm_posts_drafts', $fields);
    $ilDB->addPrimaryKey('frm_posts_drafts', array('draft_id'));
    $ilDB->createSequence('frm_posts_drafts');
}
?>
<#4966>
<?php
if (!$ilDB->indexExistsByFields('frm_posts_drafts', array('post_id'))) {
    $ilDB->addIndex('frm_posts_drafts', array('post_id'), 'i1');
}
?>
<#4967>
<?php
if (!$ilDB->indexExistsByFields('frm_posts_drafts', array('thread_id'))) {
    $ilDB->addIndex('frm_posts_drafts', array('thread_id'), 'i2');
}
?>
<#4968>
<?php
if (!$ilDB->indexExistsByFields('frm_posts_drafts', array('forum_id'))) {
    $ilDB->addIndex('frm_posts_drafts', array('forum_id'), 'i3');
}
?>
<#4969>
<?php
if (!$ilDB->tableExists('frm_drafts_history')) {
    $fields = array(
        'history_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'draft_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'post_subject' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => true
        ),
        'post_message' => array(
            'type' => 'clob',
            'notnull' => true
        ),
        'draft_date' => array(
            'type' => 'timestamp',
            'notnull' => true
            )
    );

    $ilDB->createTable('frm_drafts_history', $fields);
    $ilDB->addPrimaryKey('frm_drafts_history', array('history_id'));
    $ilDB->createSequence('frm_drafts_history');
}
?>
<#4970>
<?php
 if (!$ilDB->indexExistsByFields('frm_drafts_history', array('draft_id'))) {
     $ilDB->addIndex('frm_drafts_history', array('draft_id'), 'i1');
 }
?>
<#4971>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#4972>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'pass_waiting')) {
    $ilDB->addTableColumn(
        'tst_tests',
        'pass_waiting',
        array(
            'type' => 'text',
            'length' => 15,
            'notnull' => false,
            'default' => null)
    );
}
?>
<#4973>
<?php
if (!$ilDB->tableColumnExists('tst_active', 'last_started_pass')) {
    $ilDB->addTableColumn('tst_active', 'last_started_pass', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false,
        'default' => null
    ));
}
?>
<#4974>
<?php
if ($ilDB->tableExists('bookmark_social_bm')) {
    $ilDB->dropTable('bookmark_social_bm');
}
?>
<#4975>
<?php
if ($ilDB->sequenceExists('bookmark_social_bm')) {
    $ilDB->dropSequence('bookmark_social_bm');
}
?>
<#4976>
<?php
$sbm_path = realpath(CLIENT_WEB_DIR . DIRECTORY_SEPARATOR . 'social_bm_icons');
if (file_exists($sbm_path) && is_dir($sbm_path)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sbm_path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iter as $fileinfo) {
        if ($fileinfo->isDir()) {
            @rmdir($fileinfo->getRealPath());
        } else {
            @unlink($fileinfo->getRealPath());
        }
    }

    @rmdir($sbm_path);
}
?>
<#4977>
<?php
$ilSetting = new ilSetting();
$ilSetting->delete('passwd_auto_generate');
?>
<#4978>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_icq')) {
    $ilDB->dropTableColumn('usr_data', 'im_icq');
}
?>
<#4979>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_yahoo')) {
    $ilDB->dropTableColumn('usr_data', 'im_yahoo');
}
?>
<#4980>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_msn')) {
    $ilDB->dropTableColumn('usr_data', 'im_msn');
}
?>
<#4981>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_aim')) {
    $ilDB->dropTableColumn('usr_data', 'im_aim');
}
?>
<#4982>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_skype')) {
    $ilDB->dropTableColumn('usr_data', 'im_skype');
}
?>
<#4983>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_voip')) {
    $ilDB->dropTableColumn('usr_data', 'im_voip');
}
?>
<#4984>
<?php
if ($ilDB->tableColumnExists('usr_data', 'im_jabber')) {
    $ilDB->dropTableColumn('usr_data', 'im_jabber');
}
?>
<#4985>
<?php
if ($ilDB->tableColumnExists('usr_data', 'delicious')) {
    $ilDB->dropTableColumn('usr_data', 'delicious');
}
?>
<#4986>
<?php
$pd_set = new ilSetting('pd');
$pd_set->delete('osi_host');
?>
<#4987>
<?php
$dset = new ilSetting('delicious');
$dset->deleteAll();
?>
<#4988>
<?php
$fields = array('im_icq', 'im_yahoo', 'im_msn', 'im_aim', 'im_skype', 'im_jabber', 'im_voip', 'delicious');
foreach ($fields as $field) {
    $ilDB->manipulateF(
        'DELETE FROM usr_pref WHERE keyword = %s',
        array('text'),
        array('public_' . $field)
    );
}
?>
<#4989>
<?php
foreach (array('instant_messengers', 'delicous') as $field) {
    foreach (array(
        'usr_settings_hide', 'usr_settings_disable', 'usr_settings_visib_reg', 'usr_settings_changeable_lua',
        'usr_settings_export', 'usr_settings_course_export', 'usr_settings_group_export', 'require'
    ) as $type) {
        $ilDB->manipulateF(
            "DELETE FROM settings WHERE keyword = %s",
            array("text"),
            array($type . "_" . $field)
        );
    }
}
?>
<#4990>
<?php
if (!$ilDB->tableExists('glo_glossaries')) {
    $ilDB->createTable('glo_glossaries', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'glo_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
}
?>
<#4991>
<?php
if (!$ilDB->tableExists('glo_term_reference')) {
    $ilDB->createTable('glo_term_reference', array(
        'glo_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'term_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
}
?>
<#4992>
<?php
    $ilDB->addPrimaryKey('glo_term_reference', array('glo_id', 'term_id'));
?>
<#4993>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4994>
<?php
    if (!$ilDB->tableColumnExists('svy_svy', 'reminder_tmpl')) {
        $ilDB->addTableColumn('svy_svy', 'reminder_tmpl', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
    }
?>
<#4995>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4996>
<?php

if (!$ilDB->tableExists('exc_idl')) {
    $ilDB->createTable('exc_idl', array(
        'ass_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'member_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'is_team' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'tstamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    ));

    $ilDB->addPrimaryKey('exc_idl', array('ass_id', 'member_id', 'is_team'));
}

?>
<#4997>
<?php
    if (!$ilDB->tableColumnExists('exc_data', 'tfeedback')) {
        $ilDB->addTableColumn('exc_data', 'tfeedback', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 7
        ));
    }
?>
<#4998>
<?php
$ilDB->modifyTableColumn(
    "usr_pref",
    "value",
    array(
        "type" => "text",
        "length" => 4000,
        "fixed" => false,
        "notnull" => false,
        "default" => null
    )
);
?>
<#4999>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5000>
<?php
    //
?>
<#5001>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5002>
<?php
if (!$ilDB->tableExists('wfe_workflows')) {
    $fields = array(
        'workflow_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'workflow_type' => array('type' => 'text',	  'length' => 255),
        'workflow_content' => array('type' => 'text',	  'length' => 255),
        'workflow_class' => array('type' => 'text',	  'length' => 255),
        'workflow_location' => array('type' => 'text',	  'length' => 255),
        'subject_type' => array('type' => 'text',	  'length' => 30),
        'subject_id' => array('type' => 'integer', 'length' => 4),
        'context_type' => array('type' => 'text',    'length' => 30),
        'context_id' => array('type' => 'integer', 'length' => 4),
        'workflow_instance' => array('type' => 'clob',	  'notnull' => false, 'default' => null),
        'active' => array('type' => 'integer', 'length' => 4)
    );

    $ilDB->createTable('wfe_workflows', $fields);
    $ilDB->addPrimaryKey('wfe_workflows', array('workflow_id'));
    $ilDB->createSequence('wfe_workflows');
}

if (!$ilDB->tableExists('wfe_det_listening')) {
    $fields = array(
        'detector_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'workflow_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'type' => array('type' => 'text',	  'length' => 255),
        'content' => array('type' => 'text',	  'length' => 255),
        'subject_type' => array('type' => 'text',	  'length' => 30),
        'subject_id' => array('type' => 'integer', 'length' => 4),
        'context_type' => array('type' => 'text',    'length' => 30),
        'context_id' => array('type' => 'integer', 'length' => 4),
        'listening_start' => array('type' => 'integer', 'length' => 4),
        'listening_end' => array('type' => 'integer', 'length' => 4)
    );

    $ilDB->createTable('wfe_det_listening', $fields);
    $ilDB->addPrimaryKey('wfe_det_listening', array('detector_id'));
    $ilDB->createSequence('wfe_det_listening');
}

if (!$ilDB->tableExists('wfe_startup_events')) {
    $fields = array(
        'event_id' => array('type' => 'integer',	'length' => 4, 	'notnull' => true),
        'workflow_id' => array('type' => 'text',		'length' => 60, 'notnull' => true),
        'type' => array('type' => 'text',		'length' => 255),
        'content' => array('type' => 'text',		'length' => 255),
        'subject_type' => array('type' => 'text',		'length' => 30),
        'subject_id' => array('type' => 'integer',	'length' => 4),
        'context_type' => array('type' => 'text',		'length' => 30),
        'context_id' => array('type' => 'integer',	'length' => 4)
    );

    $ilDB->createTable('wfe_startup_events', $fields);
    $ilDB->addPrimaryKey('wfe_startup_events', array('event_id'));
    $ilDB->createSequence('wfe_startup_events');
}

if (!$ilDB->tableExists('wfe_static_inputs')) {
    $fields = array(
        'input_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'event_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
        'name' => array('type' => 'text',	  'length' => 255),
        'value' => array('type' => 'clob')
    );

    $ilDB->createTable('wfe_static_inputs', $fields);
    $ilDB->addPrimaryKey('wfe_static_inputs', array('input_id'));
    $ilDB->createSequence('wfe_static_inputs');
}

require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addAdminNode('wfe', 'WorkflowEngine');

$ilCtrlStructureReader->getStructure();
?>
<#5003>
<?php
//create il translation table to store translations for title and descriptions
if (!$ilDB->tableExists('il_translations')) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
            ),
        'id_type' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
            ),
        'lang_code' => array(
            'type' => 'text',
            'length' => 2,
            'notnull' => true
        ),
        'title' => array(
            'type' => 'text',
            'length' => 256,
            'fixed' => false,
        ),
        'description' => array(
            'type' => 'text',
            'length' => 512,
        ),
        'lang_default' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        )
    );
    $ilDB->createTable('il_translations', $fields);
    $ilDB->addPrimaryKey("il_translations", array("id", "id_type", "lang_code"));
}
?>
<#5004>
<?php
//data migration didactic templates to il_translation
if ($ilDB->tableExists('didactic_tpl_settings') && $ilDB->tableExists('il_translations')) {
    $ini = new ilIniFile(ILIAS_ABSOLUTE_PATH . "/ilias.ini.php");

    $lang_default = $ini->readVariable("language", "default");

    $ilSetting = new ilSetting();

    if ($ilSetting->get("language") != "") {
        $lang_default = $ilSetting->get("language");
    }

    $set = $ilDB->query("SELECT id, title, description" .
        " FROM didactic_tpl_settings");

    while ($row = $ilDB->fetchAssoc($set)) {
        $fields = array("id" => array("integer", $row['id']),
            "id_type" => array("text", "dtpl"),
            "lang_code" => array("text", $lang_default),
            "title" => array("text", $row['title']),
            "description" => array("text", $row['description']),
            "lang_default" => array("integer", 1));

        $ilDB->insert("il_translations", $fields);
    }
}

?>
<#5005>
<?php
//table to store "effective from" nodes for didactic templates
if (!$ilDB->tableExists('didactic_tpl_en')) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
            ),
        'node' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
            )
    );
    $ilDB->createTable('didactic_tpl_en', $fields);
    $ilDB->addPrimaryKey("didactic_tpl_en", array("id", "node"));
}

?>
<#5006>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5007>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'show_members')) {
    $ilDB->addTableColumn('grp_settings', 'show_members', array(
        "notnull" => true
        ,"length" => 1
        ,"unsigned" => false
        ,"default" => "1"
        ,"type" => "integer"
    ));
}
?>
<#5008>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if ($type_id && $tgt_ops_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#5009>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('grp');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if ($type_id && $tgt_ops_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#5010>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);

?>
<#5011>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);

?>
<#5012>
<?php
if (!$ilDB->tableColumnExists('didactic_tpl_settings', 'auto_generated')) {
    $ilDB->addTableColumn('didactic_tpl_settings', 'auto_generated', array(
        "notnull" => true,
        "length" => 1,
        "default" => 0,
        "type" => "integer"
    ));
}
?>
<#5013>
<?php
if (!$ilDB->tableColumnExists('didactic_tpl_settings', 'exclusive_tpl')) {
    $ilDB->addTableColumn('didactic_tpl_settings', 'exclusive_tpl', array(
        "notnull" => true,
        "length" => 1,
        "default" => 0,
        "type" => "integer"
    ));
}
?>

<#5014>
<?php
$id = $ilDB->nextId('didactic_tpl_settings');
$query = 'INSERT INTO didactic_tpl_settings (id,enabled,type,title, description,info,auto_generated,exclusive_tpl) values( ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote('grp_closed', 'text') . ', ' .
    $ilDB->quote('grp_closed_info', 'text') . ', ' .
    $ilDB->quote('', 'text') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(0, 'integer') . ' ' .
    ')';
$ilDB->manipulate($query);

$query = 'INSERT INTO didactic_tpl_sa (id, obj_type) values( ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote('grp', 'text') .
    ')';
$ilDB->manipulate($query);


$aid = $ilDB->nextId('didactic_tpl_a');
$query = 'INSERT INTO didactic_tpl_a (id, tpl_id, type_id) values( ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') .
    ')';
$ilDB->manipulate($query);

$query = 'select obj_id from object_data where type = ' . $ilDB->quote('rolt', 'text') . ' and title = ' . $ilDB->quote('il_grp_status_closed', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $closed_id = $row->obj_id;
}

$query = 'INSERT INTO didactic_tpl_alp (action_id, filter_type, template_type, template_id) values( ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote(3, 'integer') . ', ' .
    $ilDB->quote(2, 'integer') . ', ' .
    $ilDB->quote($closed_id, 'integer') .
    ')';
$ilDB->manipulate($query);


$fid = $ilDB->nextId('didactic_tpl_fp');
$query = 'INSERT INTO didactic_tpl_fp (pattern_id, pattern_type, pattern_sub_type, pattern, parent_id, parent_type ) values( ' .
    $ilDB->quote($fid, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote('.*', 'text') . ', ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote('action', 'text') .
    ')';
$ilDB->manipulate($query);

?>
<#5015>
<?php
$query =
    "SELECT id FROM didactic_tpl_settings " .
    "WHERE title = " . $ilDB->quote('grp_closed', 'text') .
    " AND description = " . $ilDB->quote('grp_closed_info', 'text') .
    " AND auto_generated = 1";

$closed_grp = $ilDB->query($query)->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->id;

$query =
    "SELECT objr.obj_id obj_id, objr.ref_id ref_id " .
    "FROM (grp_settings grps JOIN object_reference objr ON (grps.obj_id = objr.obj_id)) " .
    "LEFT JOIN didactic_tpl_objs dtplo ON (dtplo.obj_id = objr.obj_id) " .
    "WHERE grps.grp_type = 1 " .
    "AND (dtplo.tpl_id IS NULL OR dtplo.tpl_id = 0)";
$res = $ilDB->query($query);

while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'INSERT INTO didactic_tpl_objs (obj_id,tpl_id,ref_id) ' .
        'VALUES( ' .
        $ilDB->quote($row->obj_id, 'integer') . ', ' .
        $ilDB->quote($closed_grp, 'integer') . ', ' .
        $ilDB->quote($row->ref_id, 'integer') .
        ')';
    $ilDB->manipulate($query);
}

?>
<#5016>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('grp');
if ($type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('news_add_news', 'Add News', 'object', 2100);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
    }
}
?>

<#5017>
<?php
if (!$ilDB->tableColumnExists('il_news_item', 'content_html')) {
    $ilDB->addTableColumn(
        'il_news_item',
        'content_html',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        )
    );
}
?>

<#5018>
<?php
if (!$ilDB->tableColumnExists('il_news_item', 'update_user_id')) {
    $ilDB->addTableColumn(
        'il_news_item',
        'update_user_id',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        )
    );
}
?>
<#5019>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
if ($type_id) {
    $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("news_add_news");
    if ($ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $ops_id);
    }
}
?>

<#5020>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$target_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('news_add_news');
ilDBUpdateNewObjectType::cloneOperation("crs", $src_ops_id, $target_ops_id);
ilDBUpdateNewObjectType::cloneOperation("grp", $src_ops_id, $target_ops_id);
?>
<#5021>
<?php

if (!$ilDB->tableExists('background_task')) {
    $ilDB->createTable('background_task', array(
        'id' => array(
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
        'handler' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false
        ),
        'steps' => array(
            'type' => 'integer',
            'length' => 3,
            'notnull' => true,
            'default' => 0
        ),
        'cstep' => array(
            'type' => 'integer',
            'length' => 3,
            'notnull' => false
        ),
        'start_date' => array(
            'type' => 'timestamp'
        ),
        'status' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => false
        ),
        'params' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        )
    ));

    $ilDB->addPrimaryKey('background_task', array('id'));
    $ilDB->createSequence('background_task');
}

?>
<#5022>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5023>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_mc', 'selection_limit')) {
    $ilDB->addTableColumn('qpl_qst_mc', 'selection_limit', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false,
        'default' => null
    ));
}
?>


<#5024>

<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$obj_type_id = ilDBUpdateNewObjectType::addNewType("mass", "Manual Assessment");
$existing_ops = array('visible', 'read', 'write', 'copy', 'delete'
                        , 'edit_permission', 'read_learning_progress', 'edit_learning_progress');
foreach ($existing_ops as $op) {
    $op_id = ilDBUpdateNewObjectType::getCustomRBACOperationId($op);
    ilDBUpdateNewObjectType::addRBACOperation($obj_type_id, $op_id);
}
$parent_types = array('root', 'cat', 'crs');
ilDBUpdateNewObjectType::addRBACCreate('create_mass', 'Create Manuall Assessment', $parent_types);

if (!$ilDB->tableExists("mass_settings")) {
    $fields = array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'content' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => null
        ),
        'record_template' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => null
        )
    );
    $ilDB->createTable('mass_settings', $fields);
}

if (!$ilDB->tableExists('mass_members')) {
    $fields = array(
        'obj_id' => array(
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
        'examiner_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        ),
        'record' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => ''
        ),
        'internal_note' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => ''
        ),
        'notify' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'notification_ts' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => -1
        ),
        'learning_progress' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ),
        'finalized' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->createTable('mass_members', $fields);
}

$mass_type_id = ilDBUpdateNewObjectType::getObjectTypeId('mass');
if ($mass_type_id) {
    $custom_ops = array('edit_members' => 'Manage members');
    $counter = 1;
    foreach ($custom_ops as $ops_id => $ops_description) {
        $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
            $ops_id,
            $ops_description,
            'object',
            8000 + $counter * 100
        );
        $counter++;
        if ($new_ops_id) {
            ilDBUpdateNewObjectType::addRBACOperation($mass_type_id, $new_ops_id);
        }
    }
    $rolt_title = 'il_mass_member';
    $rec = $ilDB->fetchAssoc(
        $ilDB->query("SELECT obj_id FROM object_data "
                        . "	WHERE type = 'rolt' AND title = " . $ilDB->quote($rolt_title, 'text'))
    );
    if ($rec) {
        $mass_member_tpl_id = $rec['obj_id'];
    } else {
        $mass_member_tpl_id = $ilDB->nextId('object_data');
        $ilDB->manipulateF(
            "
			INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
            "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
            array($mass_member_tpl_id, "rolt", $rolt_title, "Member of a manual assessment object", -1, ilUtil::now(), ilUtil::now())
        );
    }
    $ops = array();
    $rec = $ilDB->fetchAssoc(
        $ilDB->query("SELECT ops_id FROM rbac_operations WHERE operation = 'visible'")
    );
    $ops[] = $rec['ops_id'];
    $rec = $ilDB->fetchAssoc(
        $ilDB->query("SELECT ops_id FROM rbac_operations WHERE operation = 'read'")
    );
    $ops[] = $rec['ops_id'];
    foreach ($ops as $op_id) {
        if (!$ilDB->fetchAssoc(
            $ilDB->query("SELECT * FROM rbac_templates "
                            . "	WHERE ops_id = " . $ilDB->quote($op_id, 'integer')
                            . " 		AND rol_id = " . $ilDB->quote($mass_member_tpl_id, 'integer'))
        )) {
            $query = "INSERT INTO rbac_templates
				VALUES (" . $ilDB->quote($mass_member_tpl_id) . ", 'mass', " . $ilDB->quote($op_id) . ", 8)";
            $ilDB->manipulate($query);
        }
    }
    $query = "INSERT INTO rbac_fa VALUES (" . $ilDB->quote($mass_member_tpl_id) . ", 8, 'n', 'n', 0)";
    $ilDB->manipulate($query);
}
?>

<#5025>
<?php
if (!$ilDB->tableExists("mass_info_settings")) {
    $fields = array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'contact' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => false,
            'default' => null
        ),
        'responsibility' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => false,
            'default' => null
        ),
        'phone' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => false,
            'default' => null
        ),
        'mails' => array(
            'type' => 'text',
            'length' => 300,
            'notnull' => false,
            'default' => null
        ),
        'consultation_hours' => array(
            'type' => 'text',
            'length' => 500,
            'notnull' => false,
            'default' => null
        ),
    );
    $ilDB->createTable('mass_info_settings', $fields);
}
?>
<#5026>
<?php
if (!$ilDB->indexExistsByFields('mass_settings', array('obj_id'))) {
    $ilDB->addPrimaryKey('mass_settings', array('obj_id'));
}
if (!$ilDB->indexExistsByFields('mass_info_settings', array('obj_id'))) {
    $ilDB->addPrimaryKey('mass_info_settings', array('obj_id'));
}
if (!$ilDB->indexExistsByFields('mass_members', array('obj_id','usr_id'))) {
    $ilDB->addPrimaryKey('mass_members', array('obj_id','usr_id'));
}
?>
<#5027>
<?php
    if (!$ilDB->indexExistsByFields('lng_data', array('local_change'))) {
        $ilDB->addIndex('lng_data', array('local_change'), 'i3');
    }
?>
<#5028>
<?php
if (!$ilDB->tableExists('osc_activity')) {
    $ilDB->createTable(
        'osc_activity',
        array(
            'conversation_id' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'user_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ),
            'timestamp' => array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            )
        )
    );
    $ilDB->addPrimaryKey('osc_activity', array('conversation_id', 'user_id'));
}
?>
<#5029>
<?php
if (!$ilDB->tableExists('osc_messages')) {
    $ilDB->createTable(
        'osc_messages',
        array(
            'id' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'conversation_id' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'user_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ),
            'message' => array(
                'type' => 'clob',
                'notnull' => false,
                'default' => null
            ),
            'timestamp' => array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            )
        )
    );
    $ilDB->addPrimaryKey('osc_messages', array('id'));
}
?>
<#5030>
<?php
if (!$ilDB->tableExists('osc_conversation')) {
    $ilDB->createTable(
        'osc_conversation',
        array(
            'id' => array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ),
            'is_group' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ),
            'participants' => array(
                'type' => 'text',
                'length' => 4000,
                'notnull' => false,
                'default' => null
            )
        )
    );
    $ilDB->addPrimaryKey('osc_conversation', array('id'));
}
?>
<#5031>
<?php
if (!$ilDB->tableColumnExists('osc_activity', 'is_closed')) {
    $ilDB->addTableColumn('osc_activity', 'is_closed', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#5032>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5033>
<?php
if (!$ilDB->tableExists('user_action_activation')) {
    $ilDB->createTable('user_action_activation', array(
        'context_comp' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ),
        'context_id' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ),
        'action_comp' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ),
        'action_type' => array(
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    ));

    $ilDB->addPrimaryKey('user_action_activation', array('context_comp', 'context_id', 'action_comp', 'action_type'));
}
?>
<#5034>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5035>
<?php
$fields = array(
    'ref_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'obj_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'path' => array(
        'type' => 'clob',

    ),

);
if (!$ilDB->tableExists('orgu_path_storage')) {
    $ilDB->createTable('orgu_path_storage', $fields);
    $ilDB->addPrimaryKey('orgu_path_storage', array( 'ref_id' ));
}
?>
<#5036>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#5037>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::deleteRBACOperation('grpr', ilDBUpdateNewObjectType::RBAC_OP_READ);

?>
<#5038>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5039>
<?php

// get badge administration ref_id
$set = $ilDB->query("SELECT oref.ref_id FROM object_reference oref" .
    " JOIN object_data od ON (od.obj_id = oref.obj_id)" .
    " WHERE od.type = " . $ilDB->quote("bdga"));
$bdga_ref_id = $ilDB->fetchAssoc($set);
$bdga_ref_id = (int) $bdga_ref_id["ref_id"];
if ($bdga_ref_id) {
    // #18931 - check if ref_id can be found as child of admin node
    $set = $ilDB->query("SELECT parent FROM tree" .
        " WHERE child = " . $ilDB->quote($bdga_ref_id, "int") .
        " AND tree.tree = " . $ilDB->quote(1, "int"));
    $bdga_tree = $ilDB->fetchAssoc($set);
    $bdga_tree = (int) $bdga_tree["parent"];
    if ($bdga_tree != SYSTEM_FOLDER_ID) {
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($bdga_ref_id, SYSTEM_FOLDER_ID);
    }
}

?>
<#5040>
<?php
//step 1/5 il_verification removes dublicates
if ($ilDB->tableExists('il_verification')) {
    $res = $ilDB->query("
		SELECT id, type
		FROM il_verification
		GROUP BY id, type
		HAVING COUNT(id) > 1
	");

    if ($ilDB->numRows($res)) {
        if (!$ilDB->tableExists('il_verification_tmp')) {
            $ilDB->createTable('il_verification_tmp', array(
                    'id' => array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true,
                    'default' => 0
                )
            ));
            $ilDB->addPrimaryKey('il_verification_tmp', array('id', 'type'));
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->replace('il_verification_tmp', array(), array(
                'id' => array('integer', $row['id']),
                'type' => array('text', $row['type'])
            ));
        }
    }
}
?>
<#5041>
<?php
//step 2/5 il_verification deletes dublicates stored in il_verification_tmp
if ($ilDB->tableExists('il_verification_tmp')) {
    $res = $ilDB->query("
		SELECT id, type
		FROM il_verification_tmp
	");

    while ($row = $ilDB->fetchAssoc($res)) {
        $res_data = $ilDB->query(
            "
			SELECT *
			FROM il_verification
			WHERE
			id = " . $ilDB->quote($row['id'], 'integer') . " AND
			type = " . $ilDB->quote($row['type'], 'text')
        );
        $data = $ilDB->fetchAssoc($res_data);

        $ilDB->manipulate(
            "DELETE FROM il_verification WHERE" .
            " id = " . $ilDB->quote($row['id'], 'integer') .
            " AND type = " . $ilDB->quote($row['type'], 'text')
        );

        $ilDB->manipulate("INSERT INTO il_verification (id, type, parameters, raw_data) " .
            "VALUES ( " .
            $ilDB->quote($data['id'], 'integer') . ', ' .
            $ilDB->quote($data['type'], 'text') . ', ' .
            $ilDB->quote($data['parameters'], 'text') . ', ' .
            $ilDB->quote($data['raw_data'], 'text') .
            ")");

        $ilDB->manipulate(
            "DELETE FROM il_verification_tmp WHERE" .
            " id = " . $ilDB->quote($row['id'], 'integer') .
            " AND type = " . $ilDB->quote($row['type'], 'text')
        );
    }
}
?>
<#5042>
<?php
//step 3/5 il_verification drops not used indexes
if ($ilDB->indexExistsByFields('il_verification', array('id'))) {
    $ilDB->dropIndexByFields('il_verification', array('id'));
}
?>
<#5043>
<?php
//step 4/5 il_verification adding primary key
if ($ilDB->tableExists('il_verification')) {
    $ilDB->dropPrimaryKey('il_verification');
    $ilDB->addPrimaryKey('il_verification', array('id', 'type'));
}
?>
<#5044>
<?php
//step 5/5 il_verification removes temp table
if ($ilDB->tableExists('il_verification_tmp')) {
    $ilDB->dropTable('il_verification_tmp');
}
?>
<#5045>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5046>
<?php
    $ilDB->addPrimaryKey('glo_glossaries', array('id', 'glo_id'));
?>
<#5047>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5048>
<?php
if ($ilDB->sequenceExists('mail_obj_data')) {
    $ilDB->dropSequence('mail_obj_data');
}

if ($ilDB->sequenceExists('mail_obj_data')) {
    die("Sequence could not be dropped!");
} else {
    $res1 = $ilDB->query("SELECT MAX(child) max_id FROM mail_tree");
    $row1 = $ilDB->fetchAssoc($res1);

    $res2 = $ilDB->query("SELECT MAX(obj_id) max_id FROM mail_obj_data");
    $row2 = $ilDB->fetchAssoc($res2);

    $start = max($row1['max_id'], $row2['max_id']) + 2; // add + 2 to be save

    $ilDB->createSequence('mail_obj_data', $start);
}
?>
<#5049>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5050>
<?php
    require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

    ilDBUpdateNewObjectType::updateOperationOrder("edit_members", 2400);
?>
<#5051>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5052>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5053>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5054>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5055>
<?php
// 1. Select all the questions of surveys
$q = "SELECT svy_question.question_id, svy_svy_qst.survey_fi FROM svy_question, svy_svy_qst WHERE svy_question.question_id = svy_svy_qst.question_fi";
$res = $ilDB->query($q);

while ($svy_data = $res->fetchAssoc()) {
    $question_id = $svy_data['question_id'];
    $svy_id = $svy_data['survey_fi'];

    $q = "SELECT obj_fi FROM svy_svy WHERE survey_id = " . $ilDB->quote($svy_id, "integer");
    $res2 = $ilDB->query($q);
    $row = $res2->fetchAssoc();
    $obj_id = $row['obj_fi'];

    $u = "UPDATE svy_question SET obj_fi = " . $ilDB->quote($obj_id, "integer") . " WHERE question_id = " . $ilDB->quote($question_id, "integer");
    $ilDB->query($u);
}
?>
<#5056>
<?php
$ilDB->update(
    'il_dcl_datatype',
    array(
        "ildb_type" => array("text", "text"),
        "storage_location" => array("integer", 1)
    ),
    array(
        "title" => array("text", "reference")
    )
);
?>
<#5057>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_type', 'plugin_name')) {
    $ilDB->addTableColumn('qpl_qst_type', 'plugin_name', array(
        'type' => 'text',
        'length' => 40,
        'notnull' => false,
        'default' => null
    ));
}
?>
<#5058>
<?php
if (!$ilDB->tableColumnExists('qpl_a_ordering', 'order_position')) {
    $ilDB->addTableColumn('qpl_a_ordering', 'order_position', array(
        'type' => 'integer',
        'length' => 3,
        'notnull' => false,
        'default' => null
    ));

    $ilDB->manipulate("UPDATE qpl_a_ordering SET order_position = solution_order");
    $ilDB->renameTableColumn('qpl_a_ordering', 'solution_order', 'solution_keyvalue');
}
?>
<#5059>
<?php
if ($ilDB->tableColumnExists('qpl_a_ordering', 'solution_keyvalue')) {
    $ilDB->renameTableColumn('qpl_a_ordering', 'solution_keyvalue', 'solution_key');
}
?>
<#5060>
<?php
if ($ilDB->tableColumnExists('qpl_a_ordering', 'order_position')) {
    $ilDB->renameTableColumn('qpl_a_ordering', 'order_position', 'position');
}
?>
<#5061>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#5062>
<?php
    //rename tables
    if ($ilDB->tableExists('mass_info_settings') && !$ilDB->tableExists('iass_info_settings')) {
        $ilDB->renameTable('mass_info_settings', 'iass_info_settings');
    }

    if ($ilDB->tableExists('mass_settings') && !$ilDB->tableExists('iass_settings')) {
        $ilDB->renameTable('mass_settings', 'iass_settings');
    }

    if ($ilDB->tableExists('mass_members') && !$ilDB->tableExists('iass_members')) {
        $ilDB->renameTable('mass_members', 'iass_members');
    }

    //change obj type
    $ilDB->manipulate('UPDATE object_data SET type = ' . $ilDB->quote('iass', 'text')
                        . '	WHERE type = ' . $ilDB->quote('mass', 'text'));

    //change name of role template for iass member
    $ilDB->manipulate('UPDATE object_data SET title = ' . $ilDB->quote('il_iass_member', 'text')
                        . '	WHERE type = ' . $ilDB->quote('rolt', 'text')
                        . '		AND title =' . $ilDB->quote('il_mass_member', 'text'));

    //change names of existing iass member roles
    $ilDB->manipulate('UPDATE object_data SET title = REPLACE(title,' . $ilDB->quote('_mass_', 'text') . ',' . $ilDB->quote('_iass_', 'text') . ')'
                        . '	WHERE type = ' . $ilDB->quote('role', 'text')
                        . '		AND title LIKE ' . $ilDB->quote('il_mass_member_%', 'text'));

    //change typ name
    $ilDB->manipulate('UPDATE object_data SET title = ' . $ilDB->quote('iass', 'text')
                        . '		,description = ' . $ilDB->quote('Individual Assessment', 'text')
                        . '	WHERE type = ' . $ilDB->quote('typ', 'text')
                        . '		AND title = ' . $ilDB->quote('mass', 'text'));

    //adapt object declaration in rbac
    $ilDB->manipulate('UPDATE rbac_templates SET type = ' . $ilDB->quote('iass', 'text')
                        . '	WHERE type = ' . $ilDB->quote('mass', 'text'));

    //change op names
    $ilDB->manipulate('UPDATE rbac_operations SET operation = ' . $ilDB->quote('create_iass', 'text')
                        . '		,description = ' . $ilDB->quote('Create Individual Assessment', 'text')
                        . '	WHERE operation = ' . $ilDB->quote('create_mass', 'text'));

    $ilCtrlStructureReader->getStructure();
?>
<#5063>
<?php
if ($ilDB->tableExists('svy_qst_oblig')) {
    $ilDB->manipulate("UPDATE svy_question" .
        " INNER JOIN svy_qst_oblig" .
        " ON svy_question.question_id = svy_qst_oblig.question_fi" .
        " SET svy_question.obligatory = svy_qst_oblig.obligatory");
}
?>
<#5064>
<?php
$ilDB->modifyTableColumn(
    'mail_attachment',
    'path',
    array(
        "type" => "text",
        "length" => 500,
        "notnull" => false,
        'default' => null
    )
);
?>
<#5065>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5066>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5067>
<?php

    if (!$ilDB->tableColumnExists('qpl_a_mterm', 'ident')) {
        $ilDB->addTableColumn('qpl_a_mterm', 'ident', array(
            'type' => 'integer', 'length' => 4,
            'notnull' => false, 'default' => null
        ));

        $ilDB->manipulate("UPDATE qpl_a_mterm SET ident = term_id WHERE ident IS NULL");
    }

    if (!$ilDB->tableColumnExists('qpl_a_mdef', 'ident')) {
        require_once 'Services/Database/classes/class.ilDBAnalyzer.php';
        $ilDB->renameTableColumn('qpl_a_mdef', 'morder', 'ident');
    }

?>
<#5068>
<?php
$ilDB->modifyTableColumn(
    'exc_returned',
    'mimetype',
    array(
                                        'type' => 'text',
                                        'length' => 150,
                                        'notnull' => false)
);
?>
<#5069>
<?php
include_once('./Services/Migration/DBUpdate_5069/classes/class.ilDBUpdate5069.php');
ilDBUpdate5069::fix19795();
?>

<#5070>
<?php

// remove role entries in obj_members
$query = 'update obj_members set admin = ' . $ilDB->quote(0, 'integer') . ', ' .
        'tutor = ' . $ilDB->quote(0, 'integer') . ', member = ' . $ilDB->quote(0, 'integer');
$ilDB->manipulate($query);

// iterate through all courses
$offset = 0;
$limit = 100;
do {
    $ilDB->setLimit($limit, $offset);
    $query = 'SELECT obr.ref_id, obr.obj_id FROM object_reference obr ' .
            'join object_data obd on obr.obj_id = obd.obj_id where (type = ' . $ilDB->quote('crs', 'text') . ' or type = ' . $ilDB->quote('grp', 'text') . ') ';
    $res = $ilDB->query($query);

    if (!$res->numRows()) {
        break;
    }
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        // find course members roles
        $query = 'select rol_id, title from rbac_fa ' .
                'join object_data on rol_id = obj_id ' .
                'where parent = ' . $ilDB->quote($row->ref_id, 'integer') . ' ' .
                'and assign = ' . $ilDB->quote('y', 'text');
        $rol_res = $ilDB->query($query);
        while ($rol_row = $rol_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // find users which are not assigned to obj_members and create a default entry
            $query = 'select ua.usr_id from rbac_ua ua ' .
                    'left join obj_members om on (ua.usr_id = om.usr_id and om.obj_id = ' . $ilDB->quote($row->obj_id, 'integer') . ') ' .
                    'where om.usr_id IS NULL ' .
                    'and rol_id = ' . $ilDB->quote($rol_row->rol_id, 'integer');
            $ua_res = $ilDB->query($query);
            while ($ua_row = $ua_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $query = 'insert into obj_members (obj_id, usr_id) ' .
                        'values(' .
                        $ilDB->quote($row->obj_id, 'integer') . ', ' .
                        $ilDB->quote($ua_row->usr_id, 'integer') . ' ' .
                        ')';
                $ilDB->manipulate($query);
            }

            // find users which are assigned to obj_members and update their role assignment
            $query = 'select usr_id from rbac_ua ' .
                'where rol_id = ' . $ilDB->quote($rol_row->rol_id, 'integer');

            $ua_res = $ilDB->query($query);
            while ($ua_row = $ua_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $admin = $tutor = $member = 0;
                switch (substr($rol_row->title, 0, 8)) {
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

                $query = 'update obj_members ' .
                        'set admin = admin  + ' . $ilDB->quote($admin, 'integer') . ', ' .
                        'tutor = tutor + ' . $ilDB->quote($tutor, 'integer') . ', ' .
                        'member = member + ' . $ilDB->quote($member, 'integer') . ' ' .
                        'WHERE usr_id = ' . $ilDB->quote($ua_row->usr_id, 'integer') . ' ' .
                        'AND obj_id = ' . $ilDB->quote($row->obj_id, 'integer');
                $ilDB->manipulate($query);
            }
        }
    }
    // increase offset
    $offset += $limit;
} while (true);
?>

<#5071>
<?php

$ilDB->manipulate(
    'delete from obj_members where admin = ' .
    $ilDB->quote(0, 'integer') . ' and tutor = ' .
    $ilDB->quote(0, 'integer') . ' and member = ' .
    $ilDB->quote(0, 'integer')
);
?>
<#5072>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5073>
<?php
$ilDB->modifyTableColumn(
    'wiki_stat_page',
    'num_ratings',
    array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    )
);
?>
<#5074>
<?php
$ilDB->modifyTableColumn(
    'wiki_stat_page',
    'avg_rating',
    array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    )
);
?>
<#5075>
<?php
$query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
$res = $ilDB->queryF($query, array('text', 'text'), array("mobs", "black_list_file_types"));
if (!$ilDB->fetchAssoc($res)) {
    $mset = new ilSetting("mobs");
    $mset->set("black_list_file_types", "html");
}
?>
<#5076>
<?php
// #0020342
$query = $ilDB->query('SELECT
    stloc.*
FROM
    il_dcl_stloc2_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
WHERE
    f.datatype_id = 3
ORDER BY stloc.id ASC');
while ($row = $query->fetchAssoc()) {
    $query2 = $ilDB->query('SELECT * FROM il_dcl_stloc1_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
    if ($ilDB->numRows($query2)) {
        $rec = $ilDB->fetchAssoc($query2);
        if ($rec['value'] != null) {
            continue;
        }
    }
    $id = $ilDB->nextId('il_dcl_stloc1_value');
    $ilDB->insert('il_dcl_stloc1_value', array(
        'id' => array('integer', $id),
        'record_field_id' => array('integer', $row['record_field_id']),
        'value' => array('text', $row['value']),
    ));
    $ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE id = ' . $ilDB->quote($row['id'], 'integer'));
}
?>
<#5077>
<?php

$ilDB->manipulate(
    'update grp_settings set registration_start = ' . $ilDB->quote(null, 'integer') . ', ' .
    'registration_end = ' . $ilDB->quote(null, 'integer') . ' ' .
    'where registration_unlimited = ' . $ilDB->quote(1, 'integer')
);
?>

<#5078>
<?php
$ilDB->manipulate(
    'update crs_settings set '
    . 'sub_start = ' . $ilDB->quote(null, 'integer') . ', '
    . 'sub_end = ' . $ilDB->quote(null, 'integer') . ' '
    . 'WHERE sub_limitation_type != ' . $ilDB->quote(2, 'integer')
);

?>
<#5079>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'grp_start')) {
    $ilDB->addTableColumn(
        'grp_settings',
        'grp_start',
        array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
    )
    );
}
if (!$ilDB->tableColumnExists('grp_settings', 'grp_end')) {
    $ilDB->addTableColumn(
        'grp_settings',
        'grp_end',
        array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
    )
    );
}
?>
<#5080>
<?php
if (!$ilDB->tableColumnExists('frm_posts', 'pos_activation_date')) {
    $ilDB->addTableColumn(
        'frm_posts',
        'pos_activation_date',
        array('type' => 'timestamp', 'notnull' => false)
    );
}
?>
<#5081>
<?php
if ($ilDB->tableColumnExists('frm_posts', 'pos_activation_date')) {
    $ilDB->manipulate(
        '
		UPDATE frm_posts SET pos_activation_date = pos_date
		WHERE pos_status = ' . $ilDB->quote(1, 'integer')
        . ' AND pos_activation_date is NULL'
    );
}
?>
<#5082>
<?php
if ($ilDB->tableExists('svy_answer')) {
    if ($ilDB->tableColumnExists('svy_answer', 'textanswer')) {
        $ilDB->modifyTableColumn('svy_answer', 'textanswer', array(
            'type' => 'clob',
            'notnull' => false
        ));
    }
}
?>

<#5083>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$rp_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
$ep_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$w_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if ($rp_ops_id && $ep_ops_id && $w_ops_id) {
    // see ilObjectLP
    $lp_types = array('file');

    foreach ($lp_types as $lp_type) {
        $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
        if ($lp_type_id) {
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $rp_ops_id);
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ep_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $rp_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $ep_ops_id);
        }
    }
}
?>

<#5084>
<?php
// #0020342
$query = $ilDB->query('SELECT
    stloc.*,
	fp.value as fp_value,
	fp.name as fp_name
FROM
    il_dcl_stloc1_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
		INNER JOIN
	il_dcl_field_prop fp ON rf.field_id = fp.field_id
WHERE
    f.datatype_id = 3
	AND fp.name = ' . $ilDB->quote("multiple_selection", 'text') . '
	AND fp.value = ' . $ilDB->quote("1", 'text') . '
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
    if (!is_numeric($row['value'])) {
        continue;
    }

    $value_array = array($row['value']);

    $query2 = $ilDB->query('SELECT * FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
    while ($row2 = $ilDB->fetchAssoc($query2)) {
        $value_array[] = $row2['value'];
    }

    $ilDB->update('il_dcl_stloc1_value', array(
        'id' => array('integer', $row['id']),
        'record_field_id' => array('integer', $row['record_field_id']),
        'value' => array('text', json_encode($value_array)),
    ), array('id' => array('integer', $row['id'])));
    $ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
}
?>
<#5085>
<?php
$set = $ilDB->query(
    "SELECT * FROM mep_item JOIN mep_tree ON (mep_item.obj_id = mep_tree.child) " .
    " WHERE mep_item.type = " . $ilDB->quote("pg", "text")
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $q = "UPDATE page_object SET " .
        " parent_id = " . $ilDB->quote($rec["mep_id"], "integer") .
        " WHERE parent_type = " . $ilDB->quote("mep", "text") .
        " AND page_id = " . $ilDB->quote($rec["obj_id"], "integer");
    //echo "<br>".$q;
    $ilDB->manipulate($q);
}
?>
<#5086>
<?php
    // fix 20706 (and 22921)
    require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
    $analyzer = new ilDBAnalyzer();
    $cons = $analyzer->getPrimaryKeyInformation('page_question');
    if (is_array($cons["fields"]) && count($cons["fields"]) > 0) {
        $ilDB->dropPrimaryKey('page_question');
    }
    $ilDB->addPrimaryKey('page_question', array('page_parent_type', 'page_id', 'question_id', 'page_lang'));
?>
<#5087>
<?php
    // fix 20409 and 20638
    $old = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    $new = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML';

    $ilDB->manipulateF(
        "UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'),
        array($new, $old)
    );
?>
<#5088>
<?php
    require_once('./Services/Component/classes/class.ilPluginAdmin.php');
    require_once('./Services/Component/classes/class.ilPlugin.php');
    require_once('./Services/UICore/classes/class.ilCtrl.php');

    // Mantis #17842
    /** @var $ilCtrl ilCtrl */
    global $ilCtrl, $ilPluginAdmin, $DIC;
    if (is_null($ilPluginAdmin)) {
        $GLOBALS['ilPluginAdmin'] = new ilPluginAdmin();
        $DIC["ilPluginAdmin"] = function ($c) {
            return $GLOBALS['ilPluginAdmin'];
        };
    }
    if (is_null($ilCtrl)) {
        $GLOBALS['ilCtrl'] = new ilCtrl();
        $DIC["ilCtrl"] = function ($c) {
            return $GLOBALS['ilCtrl'];
        };
    }
    global $ilCtrl;

    function writeCtrlClassEntry(ilPluginSlot $slot, array $plugin_data)
    {
        global $ilCtrl;
        $prefix = $slot->getPrefix() . '_' . $plugin_data['id'];
        $ilCtrl->insertCtrlCalls("ilobjcomponentsettingsgui", ilPlugin::getConfigureClassName($plugin_data), $prefix);
    }

    include_once("./Services/Component/classes/class.ilModule.php");
    $modules = ilModule::getAvailableCoreModules();
    foreach ($modules as $m) {
        $plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE, $m["subdir"]);
        foreach ($plugin_slots as $ps) {
            include_once("./Services/Component/classes/class.ilPluginSlot.php");
            $slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
            foreach ($slot->getPluginsInformation() as $p) {
                $plugin_db_data = ilPlugin::getPluginRecord($p["component_type"], $p["component_name"], $p["slot_id"], $p["name"]);
                if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p, $plugin_db_data) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p))) {
                    writeCtrlClassEntry($slot, $p);
                }
            }
        }
    }
    include_once("./Services/Component/classes/class.ilService.php");
    $services = ilService::getAvailableCoreServices();
    foreach ($services as $s) {
        $plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $s["subdir"]);
        foreach ($plugin_slots as $ps) {
            $slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
            foreach ($slot->getPluginsInformation() as $p) {
                $plugin_db_data = ilPlugin::getPluginRecord($p["component_type"], $p["component_name"], $p["slot_id"], $p["name"]);
                if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p, $plugin_db_data) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p))) {
                    writeCtrlClassEntry($slot, $p);
                }
            }
        }
    }
?>
<#5089>
<?php
$signature = "\n\n* * * * *\n";
$signature .= "[CLIENT_NAME]\n";
$signature .= "[CLIENT_DESC]\n";
$signature .= "[CLIENT_URL]\n";

$ilSetting = new ilSetting();

$prevent_smtp_globally = $ilSetting->get('prevent_smtp_globally', 0);
$mail_system_sender_name = $ilSetting->get('mail_system_sender_name', '');
$mail_external_sender_noreply = $ilSetting->get('mail_external_sender_noreply', '');
$mail_system_return_path = $ilSetting->get('mail_system_return_path', '');

$ilSetting->set('mail_allow_external', !(int) $prevent_smtp_globally);

$ilSetting->set('mail_system_usr_from_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_usr_from_name', $mail_system_sender_name);
$ilSetting->set('mail_system_usr_env_from_addr', $mail_system_return_path);

$ilSetting->set('mail_system_sys_from_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_sys_from_name', $mail_system_sender_name);
$ilSetting->set('mail_system_sys_reply_to_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_sys_env_from_addr', $mail_system_return_path);

$ilSetting->set('mail_system_sys_signature', $signature);

$ilSetting->delete('prevent_smtp_globally');
$ilSetting->delete('mail_system_return_path');
$ilSetting->delete('mail_system_sender_name');
$ilSetting->delete('mail_external_sender_noreply');
?>
<#5090>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'user_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'root_task_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'current_task_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'state' => array(
        'type' => 'integer',
        'length' => '2',

    ),
    'total_number_of_tasks' => array(
        'type' => 'integer',
        'length' => '4',

    ),
    'percentage' => array(
        'type' => 'integer',
        'length' => '2',

    ),
    'title' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'description' => array(
        'type' => 'text',
        'length' => '255',

    ),

);
if (!$ilDB->tableExists('il_bt_bucket')) {
    $ilDB->createTable('il_bt_bucket', $fields);
    $ilDB->addPrimaryKey('il_bt_bucket', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bt_bucket')) {
        $ilDB->createSequence('il_bt_bucket');
    }
}

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'type' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'class_path' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'class_name' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'bucket_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_bt_task')) {
    $ilDB->createTable('il_bt_task', $fields);
    $ilDB->addPrimaryKey('il_bt_task', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bt_task')) {
        $ilDB->createSequence('il_bt_task');
    }
}

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'has_parent_task' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'parent_task_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'hash' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'type' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'class_path' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'class_name' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'serialized' => array(
        'type' => 'clob',

    ),
    'bucket_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_bt_value')) {
    $ilDB->createTable('il_bt_value', $fields);
    $ilDB->addPrimaryKey('il_bt_value', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bt_value')) {
        $ilDB->createSequence('il_bt_value');
    }
}

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'task_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'value_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'bucket_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_bt_value_to_task')) {
    $ilDB->createTable('il_bt_value_to_task', $fields);
    $ilDB->addPrimaryKey('il_bt_value_to_task', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bt_value_to_task')) {
        $ilDB->createSequence('il_bt_value_to_task');
    }
}
?>
<#5091>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5092>
<?php
if (!$ilDB->tableColumnExists('chatroom_settings', 'online_status')) {
    $ilDB->addTableColumn('chatroom_settings', 'online_status', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    ));
}

$ilDB->manipulateF("UPDATE chatroom_settings SET online_status = %s", array('integer'), array(1));
?>
<#5093>
<?php
if (!$ilDB->tableColumnExists('chatroom_bans', 'actor_id')) {
    $ilDB->addTableColumn(
        'chatroom_bans',
        'actor_id',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => null
        )
    );
}
?>
<#5094>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5095>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'second_email')) {
    $ilDB->addTableColumn(
        'usr_data',
        'second_email',
        array('type' => 'text',
              'length' => 80,
              'notnull' => false
        )
    );
}
?>
<#5096>
<?php
if (!$ilDB->tableColumnExists('mail_options', 'mail_address_option')) {
    $ilDB->addTableColumn(
        'mail_options',
        'mail_address_option',
        array('type' => 'integer',
              'length' => 1,
              'notnull' => true,
              'default' => 3
        )
    );
}
?>
<#5097>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5098>
<?php
include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addRBACTemplate(
    'sess',
    'il_sess_participant',
    'Session participant template',
    [
        ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'),
        ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
    ]
);
?>
<#5099>
<?php

// add new role entry for each session
$query = 'SELECT obd.obj_id,ref_id,owner  FROM object_data obd ' .
    'join object_reference obr on obd.obj_id = obr.obj_id' . ' ' .
    'where type = ' . $ilDB->quote('sess', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    // add role entry
    $id = $ilDB->nextId("object_data");
    $q = "INSERT INTO object_data " .
        "(obj_id,type,title,description,owner,create_date,last_update) " .
        "VALUES " .
        "(" .
         $ilDB->quote($id, "integer") . "," .
         $ilDB->quote('role', "text") . "," .
         $ilDB->quote('il_sess_participant_' . $row->ref_id, "text") . "," .
         $ilDB->quote('Participant of session obj_no.' . $row->obj_id, "text") . "," .
         $ilDB->quote($row->owner, "integer") . "," .
         $ilDB->now() . "," .
         $ilDB->now() . ")";

    $ilDB->manipulate($q);

    // add role data
    $rd = 'INSERT INTO role_data (role_id) VALUES (' . $id . ')';
    $ilDB->manipulate($rd);

    // assign to session
    $fa = 'INSERT INTO rbac_fa (rol_id,parent,assign,protected,blocked ) VALUES(' .
        $ilDB->quote($id, 'integer') . ', ' .
        $ilDB->quote($row->ref_id, 'integer') . ', ' .
        $ilDB->quote('y', 'text') . ', ' .
        $ilDB->quote('n', 'text') . ', ' .
        $ilDB->quote(0, 'integer') . ' ' .
        ')';

    $ilDB->manipulate($fa);

    // assign template permissions
    $temp = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES(' .
        $ilDB->quote($id, 'integer') . ', ' .
        $ilDB->quote('sess', 'text') . ', ' .
        $ilDB->quote(2, 'integer') . ', ' .
        $ilDB->quote($row->ref_id, 'integer') . ') ';
    $ilDB->manipulate($temp);

    // assign template permissions
    $temp = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES(' .
        $ilDB->quote($id, 'integer') . ', ' .
        $ilDB->quote('sess', 'text') . ', ' .
        $ilDB->quote(3, 'integer') . ', ' .
        $ilDB->quote($row->ref_id, 'integer') . ') ';
    $ilDB->manipulate($temp);

    // assign permission
    $pa = 'INSERT INTO rbac_pa (rol_id,ops_id,ref_id) VALUES(' .
        $ilDB->quote($id, 'integer') . ', ' .
        $ilDB->quote(serialize([2,3]), 'text') . ', ' .
        $ilDB->quote($row->ref_id, 'integer') . ')';
    $ilDB->manipulate($pa);

    // assign users
    $users = 'SELECT usr_id from event_participants WHERE event_id = ' . $ilDB->quote($row->obj_id, 'integer');
    $user_res = $ilDB->query($users);
    while ($user_row = $user_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $ua = 'INSERT INTO rbac_ua (usr_id,rol_id) VALUES(' .
            $ilDB->quote($user_row->usr_id, 'integer') . ', ' .
            $ilDB->quote($id, 'integer') . ')';
        $ilDB->manipulate($ua);
    }
}
?>
<#5100>
<?php
$id = $ilDB->nextId("object_data");
$q = "INSERT INTO object_data " .
    "(obj_id,type,title,description,owner,create_date,last_update) " .
    "VALUES " .
    "(" .
     $ilDB->quote($id, "integer") . "," .
     $ilDB->quote('rolt', "text") . "," .
     $ilDB->quote('il_sess_status_closed', "text") . "," .
     $ilDB->quote('Closed session template', 'text') . ', ' .
     $ilDB->quote(0, "integer") . "," .
     $ilDB->now() . "," .
     $ilDB->now() . ")";

$ilDB->manipulate($q);

$query = "INSERT INTO rbac_fa VALUES (" . $ilDB->quote($id) . ", 8, 'n', 'n', 0)";
$ilDB->manipulate($query);

?>

<#5101>
<?php
$id = $ilDB->nextId('didactic_tpl_settings');
$query = 'INSERT INTO didactic_tpl_settings (id,enabled,type,title, description,info,auto_generated,exclusive_tpl) values( ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote('sess_closed', 'text') . ', ' .
    $ilDB->quote('sess_closed_info', 'text') . ', ' .
    $ilDB->quote('', 'text') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(0, 'integer') . ' ' .
    ')';
$ilDB->manipulate($query);

$query = 'INSERT INTO didactic_tpl_sa (id, obj_type) values( ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote('sess', 'text') .
    ')';
$ilDB->manipulate($query);


$aid = $ilDB->nextId('didactic_tpl_a');
$query = 'INSERT INTO didactic_tpl_a (id, tpl_id, type_id) values( ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote($id, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') .
    ')';
$ilDB->manipulate($query);

$query = 'select obj_id from object_data where type = ' . $ilDB->quote('rolt', 'text') . ' and title = ' . $ilDB->quote('il_sess_status_closed', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $closed_id = $row->obj_id;
}

$query = 'INSERT INTO didactic_tpl_alp (action_id, filter_type, template_type, template_id) values( ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote(3, 'integer') . ', ' .
    $ilDB->quote(2, 'integer') . ', ' .
    $ilDB->quote($closed_id, 'integer') .
    ')';
$ilDB->manipulate($query);


$fid = $ilDB->nextId('didactic_tpl_fp');
$query = 'INSERT INTO didactic_tpl_fp (pattern_id, pattern_type, pattern_sub_type, pattern, parent_id, parent_type ) values( ' .
    $ilDB->quote($fid, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote(1, 'integer') . ', ' .
    $ilDB->quote('.*', 'text') . ', ' .
    $ilDB->quote($aid, 'integer') . ', ' .
    $ilDB->quote('action', 'text') .
    ')';
$ilDB->manipulate($query);
?>
<#5102>
<?php

$sessions = [];

$query = 'select obd.obj_id, title, od.description from object_data obd left join object_description od on od.obj_id = obd.obj_id  where type = ' . $ilDB->quote('sess', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $tmp['obj_id'] = $row->obj_id;
    $tmp['title'] = $row->title;
    $tmp['description'] = $row->description;

    $sessions[] = $tmp;
}

foreach ($sessions as $idx => $sess_info) {
    $meta_id = $ilDB->nextId('il_meta_general');
    $insert = 'INSERT INTO il_meta_general (meta_general_id, rbac_id, obj_id, obj_type, general_structure, title, title_language, coverage, coverage_language) ' .
        'VALUES( ' .
        $ilDB->quote($meta_id, 'integer') . ', ' .
        $ilDB->quote($sess_info['obj_id'], 'integer') . ', ' .
        $ilDB->quote($sess_info['obj_id'], 'integer') . ', ' .
        $ilDB->quote('sess', 'text') . ', ' .
        $ilDB->quote('Hierarchical', 'text') . ', ' .
        $ilDB->quote($sess_info['title'], 'text') . ', ' .
        $ilDB->quote('en', 'text') . ', ' .
        $ilDB->quote('', 'text') . ', ' .
        $ilDB->quote('en', 'text') . ' ' .
        ')';

    $ilDB->manipulate($insert);

    $meta_des_id = $ilDB->nextId('il_meta_description');
    $insert = 'INSERT INTO il_meta_description (meta_description_id, rbac_id, obj_id, obj_type, parent_type, parent_id, description, description_language) ' .
        'VALUES( ' .
        $ilDB->quote($meta_des_id, 'integer') . ', ' .
        $ilDB->quote($sess_info['obj_id'], 'integer') . ', ' .
        $ilDB->quote($sess_info['obj_id'], 'integer') . ', ' .
        $ilDB->quote('sess', 'text') . ', ' .
        $ilDB->quote('meta_general', 'text') . ', ' .
        $ilDB->quote($meta_id, 'integer') . ', ' .
        $ilDB->quote($sess_info['description'], 'text') . ', ' .
        $ilDB->quote('en', 'text') . ' ' .
        ')';
    $ilDB->manipulate($insert);
}
?>
<#5103>
<?php

if (!$ilDB->tableExists('adv_md_record_scope')) {
    $ilDB->createTable('adv_md_record_scope', array(
        'scope_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'record_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        )
    ));
    $ilDB->addPrimaryKey('adv_md_record_scope', ['scope_id']);
    $ilDB->createSequence('adv_md_record_scope');
}
?>
<#5104>
<?php

if (!$ilDB->tableExists('adv_md_values_extlink')) {
    $ilDB->createTable('adv_md_values_extlink', array(
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
            'type' => 'text',
            'length' => 500,
            'notnull' => false
        ),
        'title' => array(
            'type' => 'text',
            'length' => 500,
            'notnull' => false
        ),
        'disabled' => [
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ]

    ));

    $ilDB->addPrimaryKey('adv_md_values_extlink', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}
?>
<#5105>
<?php

if (!$ilDB->tableExists('adv_md_values_intlink')) {
    $ilDB->createTable('adv_md_values_intlink', array(
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
            'notnull' => true
        ),
        'disabled' => [
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        ]

    ));

    $ilDB->addPrimaryKey('adv_md_values_intlink', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
}
?>
<#5106>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5107>
<?php
if (!$ilDB->tableColumnExists('iass_settings', 'event_time_place_required')) {
    $ilDB->addTableColumn('iass_settings', 'event_time_place_required', array(
    "type" => "integer",
    "length" => 1,
    "notnull" => true,
    "default" => 0
    ));
}
?>
<#5108>
<?php
if (!$ilDB->tableColumnExists('iass_members', 'place')) {
    $ilDB->addTableColumn('iass_members', 'place', array(
    "type" => "text",
    "length" => 255
    ));
}
?>
<#5109>
<?php
if (!$ilDB->tableColumnExists('iass_members', 'event_time')) {
    $ilDB->addTableColumn('iass_members', 'event_time', array(
    "type" => "integer",
    "length" => 8
    ));
}
?>
<#5110>
<?php

if (!$ilDB->tableColumnExists("il_object_def", "orgunit_permissions")) {
    $def = array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        );
    $ilDB->addTableColumn("il_object_def", "orgunit_permissions", $def);
}

$ilCtrlStructureReader->getStructure();
?>
<#5111>
<?php
if (!$ilDB->tableExists('orgu_obj_type_settings')) {
    $ilDB->createTable(
        'orgu_obj_type_settings',
        array(
        'obj_type' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ),
        'activation_default' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ),
        'changeable' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
        )
    );
    $ilDB->addPrimaryKey('orgu_obj_type_settings', array('obj_type'));
}
?>
<#5112>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5113>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'grp_start')) {
    $ilDB->addTableColumn('grp_settings', 'grp_start', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
}
if (!$ilDB->tableColumnExists('grp_settings', 'grp_end')) {
    $ilDB->addTableColumn('grp_settings', 'grp_end', array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        ));
}
?>
<#5114>
<?php
if (!$ilDB->tableExists("usr_starting_point")) {
    $ilDB->createTable("usr_starting_point", array(
        "id" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
            "default" => 0
        ),
        "position" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => false,
            "default" => 0
        ),
        "starting_point" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => false,
            "default" => 0
        ),
        "starting_object" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => false,
            "default" => 0
        ),
        "rule_type" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => false,
            "default" => 0
        ),
        "rule_options" => array(
            "type" => "text",
            "length" => 4000,
            "notnull" => false,
        )
    ));

    $ilDB->addPrimaryKey('usr_starting_point', array('id'));
    $ilDB->createSequence('usr_starting_point');
}
?>
<#5115>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5116>
<?php
if ($ilDB->tableExists("exc_assignment")) {
    if (!$ilDB->tableColumnExists('exc_assignment', 'portfolio_template')) {
        $ilDB->addTableColumn("exc_assignment", "portfolio_template", array("type" => "integer", "length" => 4));
    }
    if (!$ilDB->tableColumnExists('exc_assignment', 'min_char_limit')) {
        $ilDB->addTableColumn("exc_assignment", "min_char_limit", array("type" => "integer", "length" => 4));
    }
    if (!$ilDB->tableColumnExists('exc_assignment', 'max_char_limit')) {
        $ilDB->addTableColumn("exc_assignment", "max_char_limit", array("type" => "integer", "length" => 4));
    }
}
?>
<#5117>
<?php
if (!$ilDB->tableExists("exc_ass_file_order")) {
    $fields = array(
        "id" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
            "default" => 0
        ),
        "assignment_id" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
            "default" => 0
        ),
        "filename" => array(
            "type" => "text",
            "length" => 150,
            "notnull" => true,
        ),
        "order_nr" => array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true,
            "default" => 0
        ),
    );

    $ilDB->createTable("exc_ass_file_order", $fields);
    $ilDB->addPrimaryKey('exc_ass_file_order', array('id'));

    $ilDB->createSequence("exc_ass_file_order");
}
?>
<#5118>
<?php
    //
?>
<#5119>
<?php
    if (!$ilDB->tableExists("obj_noti_settings")) {
        $fields = array(
            "obj_id" => array(
                "type" => "integer",
                "length" => 4,
                "notnull" => true,
                "default" => 0
            ),
            "noti_mode" => array(
                "type" => "integer",
                "length" => 1,
                "notnull" => true,
                "default" => 0
            )
        );

        $ilDB->createTable("obj_noti_settings", $fields);
        $ilDB->addPrimaryKey('obj_noti_settings', array('obj_id'));
    }
?>
<#5120>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5121>
<?php

if (!$ilDB->tableColumnExists('notification', 'activated')) {
    $ilDB->addTableColumn(
        'notification',
        'activated',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );

    $ilDB->manipulate("UPDATE notification SET " .
        " activated = " . $this->db->quote(1, "integer"));
}
?>
<#5122>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5123>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5124>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5125>
<?php
if (!$ilDB->tableColumnExists('itgr_data', 'behaviour')) {
    $ilDB->addTableColumn(
        'itgr_data',
        'behaviour',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5126>
<?php
    $ilSetting = new ilSetting();
    $ilSetting->set('letter_avatars', 1);
?>
<#5127>
<?php

    if (!$ilDB->tableExists('pdfgen_conf')) {
        $fields = array(
            'conf_id' => array('type' => 'integer', 	'length' => 4,		'notnull' => true),
            'renderer' => array('type' => 'text', 		'length' => 255,	'notnull' => true),
            'service' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
            'purpose' => array('type' => 'text',		'length' => 255,	'notnull' => true),
            'config' => array('type' => 'clob')
        );

        $ilDB->createTable('pdfgen_conf', $fields);
        $ilDB->addPrimaryKey('pdfgen_conf', array('conf_id'));
        $ilDB->createSequence('pdfgen_conf');
    }

    if (!$ilDB->tableExists('pdfgen_map')) {
        $fields = array(
            'map_id' => array('type' => 'integer', 	'length' => 4,		'notnull' => true),
            'service' => array('type' => 'text', 		'length' => 255,	'notnull' => true),
            'purpose' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
            'preferred' => array('type' => 'text',		'length' => 255,	'notnull' => true),
            'selected' => array('type' => 'text',		'length' => 255,	'notnull' => true)
    );

        $ilDB->createTable('pdfgen_map', $fields);
        $ilDB->addPrimaryKey('pdfgen_map', array('map_id'));
        $ilDB->createSequence('pdfgen_map');
    }
?>
<#5128>
	<?php
        if (!$ilDB->tableExists('pdfgen_purposes')) {
            $fields = array(
                'purpose_id' => array('type' => 'integer', 	'length' => 4,		'notnull' => true),
                'service' => array('type' => 'text', 		'length' => 255,	'notnull' => true),
                'purpose' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
            );

            $ilDB->createTable('pdfgen_purposes', $fields);
            $ilDB->addPrimaryKey('pdfgen_purposes', array('purpose_id'));
            $ilDB->createSequence('pdfgen_purposes');
        }
    ?>
<#5129>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
    ilDBUpdateNewObjectType::addAdminNode('pdfg', 'PDFGeneration');
?>
<#5130>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5131>
<?php
    if (!$ilDB->tableExists('pdfgen_renderer')) {
        $fields = array(
        'renderer_id' => array('type' => 'integer', 	'length' => 4,		'notnull' => true),
        'renderer' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
        'path' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
        );

        $ilDB->createTable('pdfgen_renderer', $fields);
        $ilDB->addPrimaryKey('pdfgen_renderer', array('renderer_id'));
        $ilDB->createSequence('pdfgen_renderer');
    }

    if (!$ilDB->tableExists('pdfgen_renderer_avail')) {
        $fields = array(
        'availability_id' => array('type' => 'integer', 	'length' => 4,		'notnull' => true),
        'service' => array('type' => 'text', 		'length' => 255,	'notnull' => true),
        'purpose' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
        'renderer' => array('type' => 'text',	  	'length' => 255,	'notnull' => true),
    );

        $ilDB->createTable('pdfgen_renderer_avail', $fields);
        $ilDB->addPrimaryKey('pdfgen_renderer_avail', array('availability_id'));
        $ilDB->createSequence('pdfgen_renderer_avail');
    }
?>
<#5132>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5133>
<?php
    $ilDB->insert(
    'pdfgen_renderer',
    array(
        'renderer_id' => array('integer', $ilDB->nextId('pdfgen_renderer')),
        'renderer' => array('text', 'TCPDF'),
        'path' => array('text', 'Services/PDFGeneration/classes/renderer/tcpdf/class.ilTCPDFRenderer.php')
        )
);
?>
<#5134>
<?php
    $ilDB->insert(
    'pdfgen_renderer',
    array(
        'renderer_id' => array('integer',$ilDB->nextId('pdfgen_renderer')),
        'renderer' => array('text','PhantomJS'),
        'path' => array('text','Services/PDFGeneration/classes/renderer/phantomjs/class.ilPhantomJSRenderer.php')
        )
);
?>
<#5135>
<?php
    $ilDB->insert(
    'pdfgen_renderer_avail',
    array(
        'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
        'service' => array('text', 'Test'),
        'purpose' => array('text', 'PrintViewOfQuestions'),
        'renderer' => array('text', 'PhantomJS')
        )
);
?>
<#5136>
<?php
    $ilDB->insert(
    'pdfgen_renderer_avail',
    array(
            'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
            'service' => array('text', 'Test'),
            'purpose' => array('text', 'UserResult'),
            'renderer' => array('text', 'PhantomJS')
        )
);
?>
<#5137>
<?php
    $ilDB->insert(
    'pdfgen_renderer_avail',
    array(
            'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
            'service' => array('text', 'Test'),
            'purpose' => array('text', 'PrintViewOfQuestions'),
            'renderer' => array('text', 'TCPDF')
        )
);
?>
<#5138>
<?php
$ilDB->insert(
    'pdfgen_renderer_avail',
    array(
        'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
        'service' => array('text', 'Test'),
        'purpose' => array('text', 'UserResult'),
        'renderer' => array('text', 'TCPDF')
    )
);
?>
<#5139>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5140>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5141>
<?php
if (!$ilDB->tableColumnExists('lm_data', 'short_title')) {
    $ilDB->addTableColumn(
        'lm_data',
        'short_title',
        array(
            'type' => 'text',
            'length' => 200,
            'default' => ''
        )
    );
}
?>
<#5142>
<?php
if (!$ilDB->tableColumnExists('lm_data_transl', 'short_title')) {
    $ilDB->addTableColumn(
        'lm_data_transl',
        'short_title',
        array(
            'type' => 'text',
            'length' => 200,
            'default' => ''
        )
    );
}
?>
<#5143>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$iass_type_id = ilDBUpdateNewObjectType::getObjectTypeId('iass');
if ($iass_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'amend_grading',
        'Amend grading',
        'object',
        8200
    );
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($iass_type_id, $new_ops_id);
    }
}
?>
<#5144>
<?php
if (!$ilDB->tableExists('cont_skills')) {
    $ilDB->createTable('cont_skills', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'skill_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'tref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));

    $ilDB->addPrimaryKey('cont_skills', array('id','skill_id','tref_id'));
}
?>
<#5145>
<?php
if (!$ilDB->tableExists('cont_member_skills')) {
    $ilDB->createTable('cont_member_skills', array(
        'obj_id' => array(
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
        'tref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'skill_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'level_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'published' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    ));

    $ilDB->addPrimaryKey('cont_member_skills', array('obj_id','user_id','skill_id', 'tref_id'));
}
?>
<#5146>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('grade', 'Grade', 'object', 2410);
    $type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
    if ($type_id && $new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
    }
    $type_id2 = ilDBUpdateNewObjectType::getObjectTypeId('grp');
    if ($type_id2 && $new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id2, $new_ops_id);
    }
?>
<#5147>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('grade');
    ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);
    ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);
?>

<#5148>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('grade');
    ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);
    ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);
?>

<#5149>
<?php
if (!$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'origin_tax_filter')) {
    $ilDB->addTableColumn(
        'tst_rnd_quest_set_qpls',
        'origin_tax_filter',
        array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null)
    );
}
?>

<#5150>
<?php
if (!$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'mapped_tax_filter')) {
    $ilDB->addTableColumn(
        'tst_rnd_quest_set_qpls',
        'mapped_tax_filter',
        array('type' => 'text', 'length' => 4000, 'notnull' => false, 'default' => null)
    );
}
?>

<#5151>
<?php
$query = "SELECT * FROM tst_rnd_quest_set_qpls WHERE origin_tax_fi IS NOT NULL OR mapped_tax_fi IS NOT NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchObject($result)) {
    if (!empty($row->origin_tax_fi)) {
        $origin_tax_filter = serialize(array((int) $row->origin_tax_fi => array((int) $row->origin_node_fi)));
    } else {
        $origin_tax_filter = null;
    }

    if (!empty($row->mapped_tax_fi)) {
        $mapped_tax_filter = serialize(array((int) $row->mapped_tax_fi => array((int) $row->mapped_node_fi)));
    } else {
        $mapped_tax_filter = null;
    }

    $update = "UPDATE tst_rnd_quest_set_qpls SET "
        . " origin_tax_fi = NULL, origin_node_fi = NULL, mapped_tax_fi = NULL, mapped_node_fi = NULL, "
        . " origin_tax_filter = " . $ilDB->quote($origin_tax_filter, 'text') . ", "
        . " mapped_tax_filter = " . $ilDB->quote($mapped_tax_filter, 'text')
        . " WHERE def_id = " . $ilDB->quote($row->def_id);

    $ilDB->manipulate($update);
}
?>
<#5152>
<?php
if (!$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'type_filter')) {
    $ilDB->addTableColumn(
        'tst_rnd_quest_set_qpls',
        'type_filter',
        array('type' => 'text', 'length' => 250, 'notnull' => false, 'default' => null)
    );
}
?>
<#5153>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('edit_page_meta', 'Edit Page Metadata', 'object', 3050);
    $type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
    if ($type_id && $new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
    }
?>
<#5154>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_page_meta');
    ilDBUpdateNewObjectType::cloneOperation('wiki', $src_ops_id, $tgt_ops_id);
?>
<#5155>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5156>
<?php
if (!$ilDB->tableExists('saml_attribute_mapping')) {
    $ilDB->createTable(
        'saml_attribute_mapping',
        array(
            'idp_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'attribute' => array(
                'type' => 'text',
                'length' => '75',
                'notnull' => true
            ),
            'idp_attribute' => array(
                'type' => 'text',
                'length' => '1000',
                'notnull' => false,
                'default' => null
            ),
        )
    );
}
?>

<#5157>
<?php
$ilDB->addPrimaryKey('saml_attribute_mapping', array('idp_id', 'attribute'));
?>
<#5158>
<?php
if (!$ilDB->tableColumnExists('saml_attribute_mapping', 'idp_attribute')) {
    $ilDB->modifyTableColumn('saml_attribute_mapping', 'idp_attribute', array(
        'type' => 'text',
        'length' => '1000',
        'notnull' => false,
        'default' => null
    ));
}
?>
<#5159>
<?php
if (!$ilDB->tableColumnExists('saml_attribute_mapping', 'update_automatically')) {
    $ilDB->addTableColumn('saml_attribute_mapping', 'update_automatically', array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#5160>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5161>
<?php
if (!$ilDB->tableExists('saml_idp_settings')) {
    $ilDB->createTable(
        'saml_idp_settings',
        array(
            'idp_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'is_active' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
            )
        )
    );
}
?>
<#5162>
<?php
$ilDB->addPrimaryKey('saml_idp_settings', array('idp_id'));
?>
<#5163>
<?php
if (!$ilDB->tableColumnExists('saml_idp_settings', 'allow_local_auth')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'allow_local_auth',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
if (!$ilDB->tableColumnExists('saml_idp_settings', 'default_role_id')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'default_role_id',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    );
}
if (!$ilDB->tableColumnExists('saml_idp_settings', 'uid_claim')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'uid_claim',
        array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => null
        )
    );
}
if (!$ilDB->tableColumnExists('saml_idp_settings', 'login_claim')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'login_claim',
        array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => null
        )
    );
}
if (!$ilDB->tableColumnExists('saml_idp_settings', 'sync_status')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'sync_status',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
if (!$ilDB->tableColumnExists('saml_idp_settings', 'account_migr_status')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'account_migr_status',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5164>
<?php
if (!$ilDB->tableExists('auth_ext_attr_mapping') && $ilDB->tableExists('saml_attribute_mapping')) {
    $ilDB->renameTable('saml_attribute_mapping', 'auth_ext_attr_mapping');
}
?>
<#5165>
<?php
if (!$ilDB->tableColumnExists('auth_ext_attr_mapping', 'auth_src_id') && $ilDB->tableColumnExists('auth_ext_attr_mapping', 'idp_id')) {
    $ilDB->renameTableColumn('auth_ext_attr_mapping', 'idp_id', 'auth_src_id');
}
?>
<#5166>
<?php
if (!$ilDB->tableColumnExists('auth_ext_attr_mapping', 'auth_mode')) {
    $ilDB->addTableColumn('auth_ext_attr_mapping', 'auth_mode', array(
        'type' => 'text',
        'notnull' => false,
        'length' => 50
    ));
}
?>
<#5167>
<?php
// This migrates existing records
$ilDB->manipulate('UPDATE auth_ext_attr_mapping SET auth_mode = ' . $ilDB->quote('saml', 'text'));
?>
<#5168>
<?php
$ilDB->dropPrimaryKey('auth_ext_attr_mapping');
?>
<#5169>
<?php
$ilDB->addPrimaryKey('auth_ext_attr_mapping', array('auth_mode', 'auth_src_id', 'attribute'));
?>
<#5170>
<?php
if (!$ilDB->tableColumnExists('auth_ext_attr_mapping', 'ext_attribute') && $ilDB->tableColumnExists('auth_ext_attr_mapping', 'idp_attribute')) {
    $ilDB->renameTableColumn('auth_ext_attr_mapping', 'idp_attribute', 'ext_attribute');
}
?>
<#5171>
<?php
if (!$ilDB->sequenceExists('saml_idp_settings')) {
    $ilDB->createSequence('saml_idp_settings');
}
?>
<#5172>
<?php
if (!$ilDB->tableColumnExists('saml_idp_settings', 'entity_id')) {
    $ilDB->addTableColumn(
        'saml_idp_settings',
        'entity_id',
        array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false,
            'default' => null
        )
    );
}
?>

<#5173>
<?php
if ($ilDB->tableExists('cal_categories_hidden')) {
    $ilDB->renameTable('cal_categories_hidden', 'cal_cat_visibility');
    $ilDB->addTableColumn('cal_cat_visibility', 'obj_id', array(
        "type" => "integer",
        "length" => 4,
        "notnull" => true,
        "default" => 0
    ));
    $ilDB->addTableColumn('cal_cat_visibility', 'visible', array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
}
?>
<#5174>
<?php
if ($ilDB->tableExists('cal_cat_visibility')) {
    $ilDB->dropPrimaryKey('cal_cat_visibility');
    $ilDB->addPrimaryKey('cal_cat_visibility', array('user_id','cat_id','obj_id'));
}
?>
<#5175>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5176>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'title' => array(
        'type' => 'text',
        'length' => '512',

    ),
    'description' => array(
        'type' => 'text',
        'length' => '4000',

    ),
    'core_position' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'core_identifier' => array(
            'type' => 'integer',
            'length' => '1',
        ),

);
if (!$ilDB->tableExists('il_orgu_positions')) {
    $ilDB->createTable('il_orgu_positions', $fields);
    $ilDB->addPrimaryKey('il_orgu_positions', array( 'id' ));

    if (!$ilDB->sequenceExists('il_orgu_positions')) {
        $ilDB->createSequence('il_orgu_positions');
    }
}
?>
<#5177>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'over' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'scope' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'position_id' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_orgu_authority')) {
    $ilDB->createTable('il_orgu_authority', $fields);
    $ilDB->addPrimaryKey('il_orgu_authority', array( 'id' ));

    if (!$ilDB->sequenceExists('il_orgu_authority')) {
        $ilDB->createSequence('il_orgu_authority');
    }
}
?>
<#5178>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'user_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'position_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'orgu_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_orgu_ua')) {
    $ilDB->createTable('il_orgu_ua', $fields);
    $ilDB->addPrimaryKey('il_orgu_ua', array( 'id' ));

    if (!$ilDB->sequenceExists('il_orgu_ua')) {
        $ilDB->createSequence('il_orgu_ua');
    }
}
?>
<#5179>
<?php
$fields = array(
    'operation_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'operation_string' => array(
        'type' => 'text',
        'length' => '16',

    ),
    'description' => array(
        'type' => 'text',
        'length' => '512',

    ),
    'list_order' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'context_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_orgu_operations')) {
    $ilDB->createTable('il_orgu_operations', $fields);
    $ilDB->addPrimaryKey('il_orgu_operations', array( 'operation_id' ));

    if (!$ilDB->sequenceExists('il_orgu_operations')) {
        $ilDB->createSequence('il_orgu_operations');
    }
}
?>
<#5180>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'context' => array(
        'type' => 'text',
        'length' => '16',

    ),
    'parent_context_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_orgu_op_contexts')) {
    $ilDB->createTable('il_orgu_op_contexts', $fields);
    $ilDB->addPrimaryKey('il_orgu_op_contexts', array( 'id' ));

    if (!$ilDB->sequenceExists('il_orgu_op_contexts')) {
        $ilDB->createSequence('il_orgu_op_contexts');
    }
}
?>
<#5181>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'context_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'operations' => array(
        'type' => 'text',
        'length' => '2048',

    ),
    'parent_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'position_id' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_orgu_permissions')) {
    $ilDB->createTable('il_orgu_permissions', $fields);
    $ilDB->addPrimaryKey('il_orgu_permissions', array( 'id' ));

    if (!$ilDB->sequenceExists('il_orgu_permissions')) {
        $ilDB->createSequence('il_orgu_permissions');
    }
}
?>
<#5182>
<?php
$ilOrgUnitPositionEmployee = new ilOrgUnitPosition();
$ilOrgUnitPositionEmployee->setTitle("Employees");
$ilOrgUnitPositionEmployee->setDescription("Employees of a OrgUnit");
$ilOrgUnitPositionEmployee->setCorePosition(true);
$ilOrgUnitPositionEmployee->create();
$employee_position_id = $ilOrgUnitPositionEmployee->getId();

$ilOrgUnitPositionSuperior = new ilOrgUnitPosition();
$ilOrgUnitPositionSuperior->setTitle("Superiors");
$ilOrgUnitPositionSuperior->setDescription("Superiors of a OrgUnit");
$ilOrgUnitPositionSuperior->setCorePosition(true);

// Authority
$Sup = new ilOrgUnitAuthority();
$Sup->setScope(ilOrgUnitAuthority::SCOPE_SAME_ORGU);
$Sup->setOver($ilOrgUnitPositionEmployee->getId());
$ilOrgUnitPositionSuperior->setAuthorities([ $Sup ]);
$ilOrgUnitPositionSuperior->create();
$superiors_position_id = $ilOrgUnitPositionSuperior->getId();

?>
<#5183>
<?php

try {
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_IASS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_CRS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_GRP, ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_TST, ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_EXC, ilOrgUnitOperationContext::CONTEXT_OBJECT);
    ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_SVY, ilOrgUnitOperationContext::CONTEXT_OBJECT);

    // These actions will be registred in step 5186
// ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS, 'Read the learning Progress of a User', array(
// 		ilOrgUnitOperationContext::CONTEXT_CRS,
// 		ilOrgUnitOperationContext::CONTEXT_GRP,
// 		ilOrgUnitOperationContext::CONTEXT_IASS,
// 		ilOrgUnitOperationContext::CONTEXT_EXC,
// 		ilOrgUnitOperationContext::CONTEXT_SVY,
// 	));
//
// 	ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a course', ilOrgUnitOperationContext::CONTEXT_CRS);
// 	ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a group', ilOrgUnitOperationContext::CONTEXT_GRP);
// 	ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_EDIT_SUBMISSION_GRADES, '', ilOrgUnitOperationContext::CONTEXT_EXC);
// 	ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_ACCESS_RESULTS, '', ilOrgUnitOperationContext::CONTEXT_SVY);
} catch (ilException $e) {
}


?>

<#5184>
<?php
if (!$ilDB->tableColumnExists('prg_usr_progress', 'deadline')) {
    $ilDB->addTableColumn(
        'prg_usr_progress',
        'deadline',
        array('type' => 'text',
            'length' => 15,
            'notnull' => false
        )
    );
}

?>
<#5185>
<?php
    if (!$ilDB->tableColumnExists('sahs_lm', 'id_setting')) {
        $ilDB->addTableColumn(
            'sahs_lm',
            'id_setting',
            array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            )
        );
        $ilDB->query("UPDATE sahs_lm SET id_setting = 0");
    }
?>
<#5186>
<?php

$ilDB->modifyTableColumn(
    'il_orgu_operations',
    'operation_string',
    array(
            "length" => 127
        )
);
    ilOrgUnitOperation::resetDB();
    ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS, 'Read the learning Progress of a User', array(
        ilOrgUnitOperationContext::CONTEXT_CRS,
        ilOrgUnitOperationContext::CONTEXT_GRP,
        ilOrgUnitOperationContext::CONTEXT_IASS,
        ilOrgUnitOperationContext::CONTEXT_EXC,
        ilOrgUnitOperationContext::CONTEXT_SVY,
    ));

    ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a course', ilOrgUnitOperationContext::CONTEXT_CRS);
    ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a group', ilOrgUnitOperationContext::CONTEXT_GRP);
    ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_EDIT_SUBMISSION_GRADES, '', ilOrgUnitOperationContext::CONTEXT_EXC);
    ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_ACCESS_RESULTS, '', ilOrgUnitOperationContext::CONTEXT_SVY);
?>
<#5187>
<?php
    if (!$ilDB->tableColumnExists('sahs_lm', 'name_setting')) {
        $ilDB->addTableColumn(
            'sahs_lm',
            'name_setting',
            array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            )
        );
        $ilDB->query("UPDATE sahs_lm SET name_setting = 0");
    }
?>
<#5188>
<?php
if (!$ilDB->tableExists('orgu_obj_type_settings')) {
    $ilDB->createTable(
        'orgu_obj_type_settings',
        array(
        'obj_type' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ),
        'activation_default' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ),
        'changeable' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
        )
    );
    $ilDB->addPrimaryKey('orgu_obj_type_settings', array('obj_type'));
}
?>
<#5189>
<?php
if (!$ilDB->tableExists('orgu_obj_pos_settings')) {
    $ilDB->createTable(
        'orgu_obj_pos_settings',
        array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
        )
    );
    $ilDB->addPrimaryKey('orgu_obj_pos_settings', array('obj_id'));
}

?>
<#5190>
<?php

ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_WRITE_LEARNING_PROGRESS, 'Write the learning Progress of a User', ilOrgUnitOperationContext::CONTEXT_IASS);

?>
<#5191>
<?php
// "make place" for two new datatypes, text_selection comes after text, date_selection comes after datetime
$ilDB->manipulate("UPDATE il_dcl_datatype SET sort = (sort + 10) WHERE title in ('number', 'boolean', 'datetime')");
$ilDB->manipulate("UPDATE il_dcl_datatype SET sort = (sort + 20) WHERE title not in ('text', 'number', 'boolean', 'datetime')");
?>
<#5192>
<?php
// Datacollection: Add text_selection fieldtype
$ilDB->insert('il_dcl_datatype', array(
        'id' => array('integer', ilDclDatatype::INPUTFORMAT_TEXT_SELECTION),
        'title' => array('text', 'text_selection'),
        'ildb_type' => array('text', 'text'),
        'storage_location' => array('integer', 1),
        'sort' => array('integer', 10),
    ));
// Datacollection: Add date_selection fieldtype
$ilDB->insert('il_dcl_datatype', array(
    'id' => array('integer', ilDclDatatype::INPUTFORMAT_DATE_SELECTION),
    'title' => array('text', 'date_selection'),
    'ildb_type' => array('text', 'text'),
    'storage_location' => array('integer', 1),
    'sort' => array('integer', 50),
));
?>
<#5193>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'field_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'opt_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'sorting' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'value' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),

);
if (!$ilDB->tableExists('il_dcl_sel_opts')) {
    $ilDB->createTable('il_dcl_sel_opts', $fields);
    $ilDB->addPrimaryKey('il_dcl_sel_opts', array( 'id' ));

    if (!$ilDB->sequenceExists('il_dcl_sel_opts')) {
        $ilDB->createSequence('il_dcl_sel_opts');
    }
}
?>
<#5194>
<?php

if (!$ilDB->tableColumnExists('il_orgu_positions', 'core_identifier')) {
    $ilDB->addTableColumn(
        'il_orgu_positions',
        'core_identifier',
        array(
            'type' => 'integer',
            'length' => 4,
            'default' => 0
        )
    );
    $ilDB->query("UPDATE il_orgu_positions SET core_identifier = 0");
}
$employee = ilOrgUnitPosition::where(['title' => "Employees", 'core_position' => true])->first();
$employee->setCoreIdentifier(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
$employee->update();

$superior = ilOrgUnitPosition::where(['title' => "Superiors", 'core_position' => true])->first();
$superior->setCoreIdentifier(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
$superior->update();

?>


<#5195>
<?php
$ilDB->insert(
    'pdfgen_renderer_avail',
    array(
        'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
        'service' => array('text', 'Wiki'),
        'purpose' => array('text', 'ContentExport'),
        'renderer' => array('text', 'PhantomJS')
    )
);
?>
<#5196>
<?php
$ilDB->insert(
    'pdfgen_renderer_avail',
    array(
        'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
        'service' => array('text', 'Portfolio'),
        'purpose' => array('text', 'ContentExport'),
        'renderer' => array('text', 'PhantomJS')
    )
);
?>
<#5197>
<?php
    ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_ACCESS_ENROLMENTS, 'Access Enrolments in a course', ilOrgUnitOperationContext::CONTEXT_CRS);
?>
<#5198>
<?php
if (!$ilDB->tableColumnExists('crs_settings', 'show_members_export')) {
    $ilDB->addTableColumn('crs_settings', 'show_members_export', array(
                        "type" => "integer",
                        "notnull" => false,
                        "length" => 4
                ));
}
?>
<#5199>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#5200>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('ltis', 'LTI Settings');

if (!$ilDB->tableExists('lti_ext_consumer')) {
    $ilDB->createTable('lti_ext_consumer', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'description' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'prefix' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'consumer_key' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'consumer_secret' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'user_language' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'role' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('lti_ext_consumer', array('id'));
    $ilDB->createSequence('lti_ext_consumer');
}

if (!$ilDB->tableExists('lti_ext_consumer_otype')) {
    $ilDB->createTable('lti_ext_consumer_otype', array(
        'consumer_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'object_type' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
    ));
    $ilDB->addPrimaryKey('lti_ext_consumer_otype', array('consumer_id', 'object_type'));
}
?>
<#5201>
<?php
if (!$ilDB->tableExists('lti2_consumer')) {
    $ilDB->createTable('lti2_consumer', array(
        'consumer_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ),
        'consumer_key256' => array(
            'type' => 'text',
            'length' => 256,
            'notnull' => true
        ),
        'consumer_key' => array(
            'type' => 'blob',
            'default' => null
        ),
        'secret' => array(
            'type' => 'text',
            'length' => 1024,
            'notnull' => true
        ),
        'lti_version' => array(
            'type' => 'text',
            'length' => 10,
            'default' => null
        ),
        'consumer_name' => array(
            'type' => 'text',
            'length' => 255,
            'default' => null
        ),
        'consumer_version' => array(
            'type' => 'text',
            'length' => 255,
            'default' => null
        ),
        'consumer_guid' => array(
            'type' => 'text',
            'length' => 1024,
            'default' => null
        ),
        'profile' => array(
            'type' => 'blob',
            'default' => null
        ),
        'tool_proxy' => array(
            'type' => 'blob',
            'default' => null
        ),
        'settings' => array(
            'type' => 'blob',
            'default' => null
        ),
        'protected' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        'enabled' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        'enable_from' => array(
            'type' => 'timestamp',
            'default' => null
        ),
        'enable_until' => array(
            'type' => 'timestamp',
            'default' => null
        ),
        'last_access' => array(
            'type' => 'timestamp',
            'default' => null
        ),
        'created' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'updated' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_consumer', array('consumer_pk'));
    $ilDB->createSequence('lti2_consumer');
}
?>
<#5202>
<?php
if (!$ilDB->tableExists('lti2_tool_proxy')) {
    $ilDB->createTable('lti2_tool_proxy', array(
        'tool_proxy_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'tool_proxy_id' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'consumer_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'tool_proxy' => array(
            'type' => 'blob',
            'notnull' => true
        ),
        'created' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'updated' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_tool_proxy', array('tool_proxy_pk'));
    $ilDB->addIndex('lti2_tool_proxy', array('consumer_pk'), 'i1');
    $ilDB->addUniqueConstraint('lti2_tool_proxy', array('tool_proxy_id'), 'u1');
    $ilDB->createSequence('lti2_tool_proxy');
}
?>
<#5203>
<?php
if (!$ilDB->tableExists('lti2_nonce')) {
    $ilDB->createTable('lti2_nonce', array(
        'consumer_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'expires' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_nonce', array('consumer_pk','value'));
}
?>
<#5204>
<?php
if (!$ilDB->tableExists('lti2_context')) {
    $ilDB->createTable('lti2_context', array(
        'context_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'consumer_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'lti_context_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'settings' => array(
            'type' => 'blob',
            'default' => null
        ),
        'created' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'updated' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_context', array('context_pk'));
    $ilDB->addIndex('lti2_context', array('consumer_pk'), 'i1');
    $ilDB->createSequence('lti2_context');
}
?>
<#5205>
<?php
if (!$ilDB->tableExists('lti2_resource_link')) {
    $ilDB->createTable('lti2_resource_link', array(
        'resource_link_pk' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'context_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'default' => null
        ),
        'consumer_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'default' => null
        ),
        'lti_resource_link_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'settings' => array(
            'type' => 'blob'
        ),
        'primary_resource_link_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'default' => null
        ),
        'share_approved' => array(
            'type' => 'integer',
            'length' => 1,
            'default' => null
        ),
        'created' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'updated' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_resource_link', array('resource_link_pk'));
    $ilDB->addIndex('lti2_resource_link', array('consumer_pk'), 'i1');
    $ilDB->addIndex('lti2_resource_link', array('context_pk'), 'i2');
    $ilDB->createSequence('lti2_resource_link');
}
?>
<#5206>
<?php
if (!$ilDB->tableExists('lti2_user_result')) {
    $ilDB->createTable('lti2_user_result', array(
        'user_pk' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'resource_link_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'lti_user_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'lti_result_sourcedid' => array(
            'type' => 'text',
            'length' => 1024,
            'notnull' => true
        ),
        'created' => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        'updated' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_user_result', array('user_pk'));
    $ilDB->addIndex('lti2_user_result', array('resource_link_pk'), 'i1');
    $ilDB->createSequence('lti2_user_result');
}
?>
<#5207>
<?php
if (!$ilDB->tableExists('lti2_share_key')) {
    $ilDB->createTable('lti2_share_key', array(
        'share_key_id' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true
        ),
        'resource_link_pk' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'auto_approve' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        'expires' => array(
            'type' => 'timestamp',
            'notnull' => true
        )
    ));
    $ilDB->addPrimaryKey('lti2_share_key', array('share_key_id'));
    $ilDB->addIndex('lti2_share_key', array('resource_link_pk'), 'i1');
}
?>
<#5208>
<?php
if (!$ilDB->tableColumnExists('lti_ext_consumer', 'local_role_always_member')) {
    $ilDB->addTableColumn('lti_ext_consumer', 'local_role_always_member', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
}
?>
<#5209>
<?php
if (!$ilDB->tableColumnExists('lti_ext_consumer', 'default_skin')) {
    $ilDB->addTableColumn('lti_ext_consumer', 'default_skin', array(
            'type' => 'text',
            'length' => 50,
            'default' => null
        ));
}
?>
<#5210>
<?php
if ($ilDB->tableColumnExists('lti_ext_consumer', 'consumer_key')) {
    $ilDB->dropTableColumn('lti_ext_consumer', 'consumer_key');
}
if ($ilDB->tableColumnExists('lti_ext_consumer', 'consumer_secret')) {
    $ilDB->dropTableColumn('lti_ext_consumer', 'consumer_secret');
}
if ($ilDB->tableColumnExists('lti_ext_consumer', 'active')) {
    $ilDB->dropTableColumn('lti_ext_consumer', 'active');
}
?>
<#5211>
<?php
if (!$ilDB->tableExists('lti_int_provider_obj')) {
    $ilDB->createTable('lti_int_provider_obj', array(
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'consumer_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),

        'enabled' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'admin' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'tutor' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        ),
        'member' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('lti_int_provider_obj', array('ref_id','consumer_id'));
}
?>
<#5212>
<?php
if ($ilDB->tableExists('lti_int_provider_obj')) {
    $ilDB->dropTable('lti_int_provider_obj');
}
?>
<#5213>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('ltis');

$ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('release_objects', 'Release objects', 'object', 500);
if ($ops_id && $type_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $ops_id);
}
?>
<#5214>
<?php
if (!$ilDB->tableColumnExists("il_object_def", "lti_provider")) {
    $def = array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        );
    $ilDB->addTableColumn("il_object_def", "lti_provider", $def);
}
?>
<#5215>
<?php
if (!$ilDB->tableColumnExists('lti2_consumer', 'ext_consumer_id')) {
    $ilDB->addTableColumn(
        'lti2_consumer',
        'ext_consumer_id',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4
        )
    );
}
?>

<#5216>
<?php
if (!$ilDB->tableColumnExists('lti2_consumer', 'ref_id')) {
    $ilDB->addTableColumn(
        'lti2_consumer',
        'ref_id',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4
        )
    );
}
?>
<#5217>
<?php
if (!$ilDB->tableColumnExists('lti_ext_consumer', 'active')) {
    $ilDB->addTableColumn(
        'lti_ext_consumer',
        'active',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#5218>
<?php
if (!$ilDB->tableExists('lti_int_provider_obj')) {
    $ilDB->createTable('lti_int_provider_obj', array(
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'ext_consumer_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'admin' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'tutor' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'member' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('lti_int_provider_obj', array('ref_id','ext_consumer_id'));
}
?>
<#5219>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5220>
<?php
if (!$ilDB->tableColumnExists('file_data', 'page_count')) {
    $ilDB->addTableColumn(
        'file_data',
        'page_count',
        array(
            'type' => 'integer',
            'length' => 8,
        )
    );
}
?>
<#5221>
<?php
if (!$ilDB->tableColumnExists('il_blog', 'nav_list_mon_with_post')) {
    $ilDB->addTableColumn(
        'il_blog',
        'nav_list_mon_with_post',
        array(
            'type' => 'integer',
            'length' => 4,
            'default' => 3
        )
    );
}
?>

<#5222>
<?php
    if (!$ilDB->tableColumnExists('iass_settings', 'file_required')) {
        $ilDB->addTableColumn('iass_settings', 'file_required', array(
                                                                      "type" => "integer",
                                                                      "length" => 1,
                                                                      "notnull" => true,
                                                                      "default" => 0
                                                                      ));
    }
?>

<#5223>
<?php
    if (!$ilDB->tableColumnExists('iass_members', 'file_name')) {
        $ilDB->addTableColumn('iass_members', 'file_name', array(
                                                                 "type" => "text",
                                                                 "length" => 255
                                                                 ));
    }
    if (!$ilDB->tableColumnExists('iass_members', 'user_view_file')) {
        $ilDB->addTableColumn('iass_members', 'user_view_file', array(
                                                                      "type" => "integer",
                                                                      "length" => 1
                                                                      ));
    }
?>
<#5224>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5225>
<?php
if ($ilDB->tableColumnExists('reg_registration_codes', 'generated')) {
    $ilDB->renameTableColumn('reg_registration_codes', "generated", 'generated_on');
}
?>
<#5226>
<?php
if ($ilDB->tableColumnExists('il_orgu_operations', 'operation_string')) {
    $ilDB->modifyTableColumn(
        'il_orgu_operations',
        'operation_string',
        array(
            "length" => 127
        )
    );
}
?>
<#5227>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5228>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5229>
<?php
        if (!$ilDB->tableColumnExists('il_bt_bucket', 'last_heartbeat')) {
            $ilDB->addTableColumn('il_bt_bucket', 'last_heartbeat', array(
                                                                      "type" => "integer",
                                                                      "length" => 4
                                                                      ));
        }
?>
<#5230>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5231>
<?php
if (!$ilDB->indexExistsByFields('style_parameter', array('style_id'))) {
    $ilDB->addIndex('style_parameter', array('style_id'), 'i1');
}
?>
<#5232>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass(
    "OrderListHorizontal",
    "qordul",
    "ul",
    array("margin" => "0px",
                        "padding" => "0px",
                        "list-style" => "none",
                        "list-style-position" => "outside"
                        )
);
ilDBUpdate3136::addStyleClass(
    "OrderListItemHorizontal",
    "qordli",
    "li",
    array(
                        "float" => "left",
                        "margin-top" => "5px",
                        "margin-bottom" => "5px",
                        "margin-right" => "10px",
                        "border-width" => "1px",
                        "border-style" => "solid",
                        "border-color" => "#D0D0FF",
                        "padding" => "10px",
                        "cursor" => "move"
                        )
);
?>
<#5233>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5234>
<?php
if ($ilDB->tableColumnExists('wiki_stat', 'del_pages')) {
    $ilDB->modifyTableColumn('wiki_stat', 'del_pages', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#5235>
<?php
if ($ilDB->tableColumnExists('wiki_stat', 'avg_rating')) {
    $ilDB->modifyTableColumn('wiki_stat', 'avg_rating', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#5236>
<?php

    $ilDB->dropPrimaryKey('loc_rnd_qpl');
?>

<#5237>
<?php

    $ilDB->addPrimaryKey('loc_rnd_qpl', ['container_id', 'objective_id', 'tst_type', 'tst_id', 'qp_seq']);

?>
<#5238>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5239>
<?php
$ilDB->modifyTableColumn(
    'adv_md_record',
    'record_id',
    array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true
        )
);
?>
<#5240>
<?php
$ilDB->modifyTableColumn(
    'adv_md_record_objs',
    'record_id',
    array(
            "type" => "integer",
            "length" => 4,
            "notnull" => true
        )
);
?>
<#5241>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5242>
<?php

/**
 * This will move all the exercise instruction files from outside document root to inside.
 */

$result = $ilDB->query("SELECT id,exc_id FROM exc_assignment");

while ($row = $ilDB->fetchAssoc($result)) {
    include_once("./Services/Migration/DBUpdate_5242/classes/class.ilFSStorageExc5242.php");
    $storage = new ilFSStorageExc5242($row['exc_id'], $row['id']);

    $files = $storage->getFiles();
    if (!empty($files)) {
        foreach ($files as $file) {
            $file_name = $file['name'];
            $file_full_path = $file['fullpath'];
            $file_relative_path = str_replace(ILIAS_DATA_DIR, "", $file_full_path);
            $directory_relative_path = str_replace($file_name, "", $file_relative_path);

            if (!is_dir(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . $directory_relative_path)) {
                //echo "<br> makeDirParents: ".ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$directory_relative_path;
                ilUtil::makeDirParents(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . $directory_relative_path);
            }
            if (!file_exists(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . $file_relative_path) &&
                file_exists($file_full_path)) {
                //echo "<br> rename: $file_full_path TO ".ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$file_relative_path;
                rename($file_full_path, ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . $file_relative_path);
            }
        }
    }
}
?>
<#5243>
<?php
if (!$ilDB->tableColumnExists('usr_session', 'context')) {
    $ilDB->addTableColumn(
        'usr_session',
        'context',
        array(
            'type' => 'text',
            'length' => '80',
            'notnull' => false)
    );
}
?>
<#5244>
<?php
    //add table column
    if (!$ilDB->tableColumnExists('iass_members', 'changer_id')) {
        $ilDB->addTableColumn("iass_members", "changer_id", array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
            ));
    }
?>
<#5245>
<?php
    //add table column
    if (!$ilDB->tableColumnExists('iass_members', 'change_time')) {
        $ilDB->addTableColumn("iass_members", "change_time", array(
            'type' => 'text',
            'length' => 20,
            'notnull' => false
            ));
    }
?>

<#5246>
<?php
    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('edit_submissions_grades', 'Edit Submissions Grades', 'object', 3800);
    $type_id = ilDBUpdateNewObjectType::getObjectTypeId('exc');
    if ($type_id && $new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
    }
?>
<#5247>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
    $tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_submissions_grades');
    ilDBUpdateNewObjectType::cloneOperation('exc', $src_ops_id, $tgt_ops_id);
?>
<#5248>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5249>
<?php

$ilSetting = new ilSetting();

if (!$ilSetting->get('dbupwarn_tstfixqstseq', 0)) {
    $res = $ilDB->query("
		SELECT COUNT(DISTINCT test_fi) num_tst, COUNT(test_question_id) num_qst
		FROM tst_test_question WHERE test_fi IN(
			SELECT test_fi FROM tst_test_question
			GROUP BY test_fi HAVING COUNT(test_fi) < MAX(sequence)
		)
	");

    $row = $ilDB->fetchAssoc($res);

    if ($row) {
        $numTests = $row['num_tst'];
        $numQuestions = $row['num_qst'];
        echo "<pre>

		DEAR ADMINISTRATOR !!

		Please read the following instructions CAREFULLY!

		-> Due to a bug in almost all earlier versions of ILIAS question orderings
		from the assessment component are broken but repairable.

		-> The following dbupdate step can exhaust any php enviroment settings like
		max_execution_time or memory_limit for example.

		-> In the case of any php fatal error during the following dbupdate step
		that is about exhausting any ressource or time restriction you just need
		to refresh the page by using F5 for example.

		=> To proceed the update process you now need to refresh the page as well (F5)

		Mantis Bug Report: https://ilias.de/mantis/view.php?id=20382

		In your database there were > {$numTests} tests < detected having > {$numQuestions} questions < overall,
		that are stored with gaps in the ordering index.

		</pre>";

        $ilSetting->set('dbupwarn_tstfixqstseq', 1);
        exit;
    }

    $ilSetting->set('dbupwarn_tstfixqstseq', 1);
}

?>
<#5250>
<?php

$res = $ilDB->query("
	SELECT test_fi, test_question_id
	FROM tst_test_question WHERE test_fi IN(
		SELECT test_fi FROM tst_test_question
		GROUP BY test_fi HAVING COUNT(test_fi) < MAX(sequence)
	) ORDER BY test_fi ASC, sequence ASC
");

$tests = array();

while ($row = $ilDB->fetchAssoc($res)) {
    if (!isset($tests[ $row['test_fi'] ])) {
        $tests[ $row['test_fi'] ] = array();
    }

    $tests[ $row['test_fi'] ][] = $row['test_question_id'];
}

foreach ($tests as $testFi => $testQuestions) {
    for ($i = 0, $m = count($testQuestions); $i <= $m; $i++) {
        $testQuestionId = $testQuestions[$i];

        $position = $i + 1;

        $ilDB->update(
            'tst_test_question',
            array( 'sequence' => array('integer', $position) ),
            array( 'test_question_id' => array('integer', $testQuestionId) )
        );
    }
}

?>
<#5251>
<?php
$set = $ilDB->query("
  SELECT obj_id, title, description, role_id, usr_id FROM object_data
  INNER JOIN role_data role ON role.role_id = object_data.obj_id
  INNER JOIN rbac_ua on role.role_id = rol_id
  WHERE title LIKE '%il_orgu_superior%' OR title LIKE '%il_orgu_employee%'
");
$assigns = [];
$superior_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
$employee_position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

while ($res = $ilDB->fetchAssoc($set)) {
    $user_id = $res['usr_id'];

    $tmp = explode("_", $res['title']);
    $orgu_ref_id = (int) $tmp[3];
    if ($orgu_ref_id == 0) {
        //$ilLog->write("User $user_id could not be assigned to position. Role description does not contain object id of orgu. Skipping.");
        continue;
    }

    $tmp = explode("_", $res['title']); //il_orgu_[superior|employee]_[$ref_id]
  $role_type = $tmp[2]; // [superior|employee]

  if ($role_type == 'superior') {
      $position_id = $superior_position_id;
  } elseif ($role_type == 'employee') {
      $position_id = $employee_position_id;
  } else {
      //$ilLog->write("User $user_id could not be assigned to position. Role type seems to be neither superior nor employee. Skipping.");
      continue;
  }
    if (!ilOrgUnitUserAssignment::findOrCreateAssignment(
        $user_id,
        $position_id,
        $orgu_ref_id
    )) {
        //$ilLog->write("User $user_id could not be assigned to position $position_id, in orgunit $orgu_ref_id . One of the ids might not actually exist in the db. Skipping.");
    }
}
?>
<#5252>
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
<#5253>
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

while ($row = $ilDB->fetchAssoc($res)) {
    if ($row['answ_points'] > $row['qpl_points']) {
        $ilDB->update(
            'qpl_questions',
            array('points' => array('float', $row['answ_points'])),
            array('question_id' => array('integer', $row['qid']))
        );
    }

    $ilDB->manipulateF(
        "DELETE FROM qpl_a_essay WHERE question_fi = %s",
        array('integer'),
        array($row['qid'])
    );

    $ilDB->update(
        'qpl_qst_essay',
        array('keyword_relation' => array('text', 'non')),
        array('question_fi' => array('integer', $row['qid']))
    );
}

?>
<#5254>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5255>
<?php
if (!$ilDB->tableColumnExists(ilOrgUnitPermission::TABLE_NAME, 'protected')) {
    $ilDB->addTableColumn(ilOrgUnitPermission::TABLE_NAME, 'protected', [
        "type" => "integer",
        "length" => 1,
        "default" => 0,
    ]);
}
$ilDB->manipulate("UPDATE il_orgu_permissions SET protected = 1 WHERE parent_id = -1");
?>
<#5256>
<?php
if ($ilDB->indexExistsByFields('cmi_objective', array('id'))) {
    $ilDB->dropIndexByFields('cmi_objective', array('id'));
}
?>
<#5257>
<?php
if (!$ilDB->indexExistsByFields('page_style_usage', array('page_id', 'page_type', 'page_lang', 'page_nr'))) {
    $ilDB->addIndex('page_style_usage', array('page_id', 'page_type', 'page_lang', 'page_nr'), 'i1');
}
?>
<#5258>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$rp_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
$ep_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$w_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if ($rp_ops_id && $ep_ops_id && $w_ops_id) {
    // see ilObjectLP
    $lp_types = array('mcst');

    foreach ($lp_types as $lp_type) {
        $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);
        if ($lp_type_id) {
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $rp_ops_id);
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ep_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $rp_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $ep_ops_id);
        }
    }
}
?>
<#5259>
<?php
    $ilDB->manipulate('UPDATE exc_mem_ass_status SET status=' . $ilDB->quote('notgraded', 'text') . ' WHERE status = ' . $ilDB->quote('', 'text'));
?>
<#5260>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5261>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5262>
<?php

$query = 'select id from adm_settings_template  ' .
    'where title = ' . $ilDB->quote('il_astpl_loc_initial', 'text') .
    'or title = ' . $ilDB->quote('il_astpl_loc_qualified', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ilDB->replace(
        'adm_set_templ_value',
        [
               'template_id' => ['integer', $row->id],
             'setting' => ['text', 'pass_scoring']
        ],
        [
            'value' => ['integer',0],
            'hide' => ['integer',1]
        ]
    );
}
?>
<#5263>
<?php
$ilDB->modifyTableColumn('il_dcl_tableview', 'roles', array('type' => 'clob'));
?>
<#5264>
<?php
// get tst type id
$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT obj_id tst_type_id FROM object_data WHERE type = %s AND title = %s",
    array('text', 'text'),
    array('typ', 'tst')
));
$tstTypeId = $row['tst_type_id'];

// get 'write' operation id
$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
    array('text', 'text'),
    array('write', 'general')
));
$writeOperationId = $row['ops_id'];

// register new 'object' rbac operation for tst
$resultsOperationId = $ilDB->nextId('rbac_operations');
$ilDB->insert('rbac_operations', array(
    'ops_id' => array('integer', $resultsOperationId),
    'operation' => array('text', 'tst_results'),
    'description' => array('text', 'view the results of test participants'),
    'class' => array('text', 'object'),
    'op_order' => array('integer', 7050)
));
$ilDB->insert('rbac_ta', array(
    'typ_id' => array('integer', $tstTypeId),
    'ops_id' => array('integer', $resultsOperationId)
));

// update existing role templates and grant new operation for all templates having 'write' granted
$res = $ilDB->queryF(
    "SELECT rol_id, parent FROM rbac_templates WHERE type = %s AND ops_id = %s",
    array('text', 'integer'),
    array('tst', $writeOperationId)
);
$stmt = $ilDB->prepareManip(
    "
	INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (?, ?, ?, ?)
	",
    array('integer', 'text', 'integer', 'integer')
);
while ($row = $ilDB->fetchAssoc($res)) {
    $ilDB->execute($stmt, array($row['rol_id'], 'tst', $resultsOperationId, $row['parent']));
}
?>
<#5265>
<?php
// get 'write' operation id
$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
    array('text', 'text'),
    array('tst_results', 'object')
));
$resultsOperationId = $row['ops_id'];

// get 'write' operation id
$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
    array('text', 'text'),
    array('write', 'general')
));
$writeOperationId = $row['ops_id'];

// get roles (not rolts) having 'tst_results' registered in rbac_template
$res = $ilDB->queryF(
    "
	SELECT rol_id FROM rbac_templates INNER JOIN object_data
	ON obj_id = rol_id AND object_data.type = %s WHERE rbac_templates.type = %s AND ops_id = %s
	",
    array('text', 'text', 'integer'),
    array('role', 'tst', $resultsOperationId)
);
$roleIds = array();
while ($row = $ilDB->fetchAssoc($res)) {
    $roleIds[] = $row['rol_id'];
}

// get existing test object references
$res = $ilDB->queryF(
    "
	SELECT oref.ref_id FROM object_data odat INNER JOIN object_reference oref
	ON oref.obj_id = odat.obj_id WHERE odat.type = %s
	",
    array('text'),
    array('tst')
);
$tstRefs = array();
while ($row = $ilDB->fetchAssoc($res)) {
    $tstRefs[] = $row['ref_id'];
}

// complete 'tst_results' permission for all existing role/reference combination that have 'write' permission
$stmt = $ilDB->prepareManip(
    "
	UPDATE rbac_pa SET ops_id = ? WHERE rol_id = ? AND ref_id = ?
	",
    array('text', 'integer', 'integer')
);
$IN_roles = $ilDB->in('rol_id', $roleIds, false, 'integer');
$IN_tstrefs = $ilDB->in('ref_id', $tstRefs, false, 'integer');
$res = $ilDB->query("SELECT * FROM rbac_pa WHERE {$IN_roles} AND {$IN_tstrefs}");
while ($row = $ilDB->fetchAssoc($res)) {
    $perms = unserialize($row['ops_id']);

    if (in_array($writeOperationId, $perms) && !in_array($resultsOperationId, $perms)) {
        $perms[] = $resultsOperationId;
        $ilDB->execute($stmt, array(serialize($perms), $row['rol_id'], $row['ref_id']));
    }
}
?>
<#5266>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5267>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5268>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
    'identifier' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '50',

    ),
    'data_type' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '1',

    ),
    'position' => array(
        'type' => 'integer',
        'length' => '3',

    ),
    'is_standard_field' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '1',

    ),
    'object_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
);
global $ilDB;
if (!$ilDB->tableExists('il_bibl_field')) {
    $ilDB->createTable('il_bibl_field', $fields);
    $ilDB->addPrimaryKey('il_bibl_field', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bibl_field')) {
        $ilDB->createSequence('il_bibl_field');
    }
}
?>
<#5269>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
    'field_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
    'object_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
    'filter_type' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_bibl_filter')) {
    $ilDB->createTable('il_bibl_filter', $fields);
    $ilDB->addPrimaryKey('il_bibl_filter', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bibl_filter')) {
        $ilDB->createSequence('il_bibl_filter');
    }
}
?>
<#5270>
<?php
if (!$ilDB->tableColumnExists("il_bibl_data", "file_type")) {
    $ilDB->addTableColumn("il_bibl_data", "file_type", [
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 1
    ]);
}

$type = function ($filename) {
    if (strtolower(substr($filename, -6)) == "bibtex"
        || strtolower(substr($filename, -3)) == "bib") {
        return 2;
    }
    return 1;
};

$res = $ilDB->query("SELECT * FROM il_bibl_data");
while ($d = $ilDB->fetchObject($res)) {
    $type_id = (int) $type($d->filname);
    $ilDB->update("il_bibl_data", [
        "file_type" => [ "integer", $type_id ]
    ], [ "id" => $d->id ]);
}
?>
<#5271>
<?php
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '4',

    ),
    'field_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'language_key' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '2',

    ),
    'translation' => array(
        'type' => 'text',
        'length' => '256',

    ),
    'description' => array(
        'type' => 'clob',

    ),

);
if (!$ilDB->tableExists('il_bibl_translation')) {
    $ilDB->createTable('il_bibl_translation', $fields);
    $ilDB->addPrimaryKey('il_bibl_translation', array( 'id' ));

    if (!$ilDB->sequenceExists('il_bibl_translation')) {
        $ilDB->createSequence('il_bibl_translation');
    }
}
?>
<#5272>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5273>
<?php
// TODO fill filetype_id with the correct values
if ($ilDB->tableExists('il_bibl_overview_model')) {
    if ($ilDB->tableColumnExists('il_bibl_overview_model', 'filetype')) {
        $type = function ($filetype_string) {
            if (strtolower($filetype_string) == "bib"
                || strtolower($filetype_string) == "bibtex"
            ) {
                return 2; // see ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
            }

            return 1; // ilBiblTypeFactoryInterface::DATA_TYPE_RIS
        };

        if (!$ilDB->tableColumnExists('il_bibl_overview_model', 'file_type_id')) {
            $ilDB->addTableColumn('il_bibl_overview_model', 'file_type_id', array("type" => "integer", 'length' => 4));
        }

        $res = $ilDB->query("SELECT * FROM il_bibl_overview_model");
        while ($d = $ilDB->fetchObject($res)) {
            $type_id = (int) $type($d->filetype);
            $ilDB->update(
                "il_bibl_overview_model",
                [
                "file_type_id" => ["integer", $type_id],
            ],
                ["ovm_id" => ["integer", $d->ovm_id]]
            );
        }

        $ilDB->dropTableColumn('il_bibl_overview_model', 'filetype');
    }
}
?>
<#5274>
<?php
/*
* This hotfix removes org unit assignments of user who don't exist anymore
* select all user_ids from usr_data and remove all il_orgu_ua entries which have an user_id from an user who doesn't exist anymore
*/
global $ilDB;
$q = "DELETE FROM il_orgu_ua WHERE user_id NOT IN (SELECT usr_id FROM usr_data)";
$ilDB->manipulate($q);
?>
<#5275>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5276>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_lome', 'identical_scoring')) {
    $ilDB->addTableColumn('qpl_qst_lome', 'identical_scoring', array(
        'type' => 'integer',
        'length' => 1,
        'default' => 1
    ));
}
?>
<#5277>
<?php
$ilSetting = new ilSetting();

if ($ilSetting->get('show_mail_settings', false) === false) {
    $ilSetting->set('show_mail_settings', 1);
}
?>
<#5278>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$type_id = ilDBUpdateNewObjectType::addNewType('copa', 'Content Page Object');

ilDBUpdateNewObjectType::addRBACOperations($type_id, [
    ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
    ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
    ilDBUpdateNewObjectType::RBAC_OP_READ,
    ilDBUpdateNewObjectType::RBAC_OP_WRITE,
    ilDBUpdateNewObjectType::RBAC_OP_DELETE,
    ilDBUpdateNewObjectType::RBAC_OP_COPY
]);

ilDBUpdateNewObjectType::addRBACCreate('create_copa', 'Create Content Page Object', [
    'root',
    'cat',
    'crs',
    'fold',
    'grp'
]);
?>
<#5279>
<?php
require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$rp_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
$ep_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$w_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if ($rp_ops_id && $ep_ops_id && $w_ops_id) {
    $lp_types = array('copa');

    foreach ($lp_types as $lp_type) {
        $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId($lp_type);

        if ($lp_type_id) {
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $rp_ops_id);
            ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ep_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $rp_ops_id);
            ilDBUpdateNewObjectType::cloneOperation($lp_type, $w_ops_id, $ep_ops_id);
        }
    }
}
?>
<#5280>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5281>
<?php
require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::applyInitialPermissionGuideline('copa', true);
?>
<#5282>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5283>
<?php
if (!$ilDB->tableExists('content_page_data')) {
    $fields = array(
        'content_page_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'stylesheet' => array(
            'type' => 'integer',
            'notnull' => true,
            'length' => 4,
            'default' => 0
        )
    );

    $ilDB->createTable('content_page_data', $fields);
    $ilDB->addPrimaryKey('content_page_data', array('content_page_id'));
}
?>
<#5284>
<?php
$res = $ilDB->queryF(
    'SELECT * FROM object_data WHERE type = %s',
    ['text'],
    ['copa']
);

while ($data = $ilDB->fetchAssoc($res)) {
    $ilDB->replace(
        'content_page_data',
        [
            'content_page_id' => ['integer', (int) $data['obj_id']]
        ],
        []
    );
}
?>
<#5285>
<?php
if (!$ilDB->tableColumnExists('qpl_fb_specific', 'question')) {
    // add new table column for indexing different question gaps in assClozeTest
    $ilDB->addTableColumn('qpl_fb_specific', 'question', array(
        'type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => null
    ));

    // give all other qtypes having a single subquestion the question index 0
    $ilDB->manipulateF(
        "UPDATE qpl_fb_specific SET question = %s WHERE question_fi NOT IN(
			SELECT question_id FROM qpl_questions
			INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
		  	WHERE type_tag = %s
		)",
        array('integer', 'text'),
        array(0, 'assClozeTest')
    );

    // for all assClozeTest entries - migrate the gap feedback indexes from answer field to questin field
    $ilDB->manipulateF(
        "UPDATE qpl_fb_specific SET question = answer WHERE question_fi IN(
			SELECT question_id FROM qpl_questions
			INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
		  	WHERE type_tag = %s
		)",
        array('text'),
        array('assClozeTest')
    );

    // for all assClozeTest entries - initialize the answer field with 0 for the formaly stored gap feedback
    $ilDB->manipulateF(
        "UPDATE qpl_fb_specific SET answer = %s WHERE question_fi IN(
			SELECT question_id FROM qpl_questions
			INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
		  	WHERE type_tag = %s
		)",
        array('integer', 'text'),
        array(0, 'assClozeTest')
    );

    // finaly set the question index field to notnull = true (not nullable) as it is now initialized
    $ilDB->modifyTableColumn('qpl_fb_specific', 'question', array(
        'notnull' => true, 'default' => 0
    ));

    // add unique constraint on qid and the two specific feedback indentification index fields
    $ilDB->addUniqueConstraint('qpl_fb_specific', array(
        'question_fi', 'question', 'answer'
    ));
}

if (!$ilDB->tableColumnExists('qpl_qst_cloze', 'feedback_mode')) {
    $ilDB->addTableColumn('qpl_qst_cloze', 'feedback_mode', array(
        'type' => 'text', 'length' => 16, 'notnull' => false, 'default' => null
    ));

    $ilDB->manipulateF(
        "UPDATE qpl_qst_cloze SET feedback_mode = %s",
        array('text'),
        array('gapQuestion')
    );

    $ilDB->modifyTableColumn('qpl_qst_cloze', 'feedback_mode', array(
        'notnull' => true, 'default' => 'gapQuestion'
    ));
}
?>
<#5286>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'follow_qst_answer_fixation')) {
    $ilDB->addTableColumn('tst_tests', 'follow_qst_answer_fixation', array(
        'type' => 'integer', 'notnull' => false, 'length' => 1, 'default' => 0
    ));

    $ilDB->manipulateF(
        'UPDATE tst_tests SET follow_qst_answer_fixation = %s',
        array('integer'),
        array(0)
    );
}

if (!$ilDB->tableExists('tst_seq_qst_presented')) {
    $ilDB->createTable('tst_seq_qst_presented', array(
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

    $ilDB->addPrimaryKey('tst_seq_qst_presented', array(
        'active_fi','pass', 'question_fi'
    ));
}
?>
<#5287>
<?php
if ($ilDB->tableColumnExists('qpl_fb_specific', 'answer')) {
    $ilDB->manipulateF(
        "
		UPDATE qpl_fb_specific SET answer = %s WHERE question_fi IN(
			SELECT question_fi FROM qpl_qst_cloze WHERE feedback_mode = %s
		)
		",
        array('integer', 'text'),
        array(-10, 'gapQuestion')
    );
}
?>
<#5288>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx1', 0);
if (!$ilrqtix) {
    $ilDB->addIndex('osc_messages', array('user_id'), 'i1');
    $setting->set('iloscmsgidx1', 1);
}
?>
<#5289>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx2', 0);
if (!$ilrqtix) {
    $ilDB->addIndex('osc_messages', array('conversation_id'), 'i2');
    $setting->set('iloscmsgidx2', 1);
}
?>
<#5290>
<?php
$setting = new ilSetting();
$ilrqtix = $setting->get('iloscmsgidx3', 0);
if (!$ilrqtix) {
    $ilDB->addIndex('osc_messages', array('conversation_id', 'user_id', 'timestamp'), 'i3');
    $setting->set('iloscmsgidx3', 1);
}
?>
<#5291>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5292>
<?php
try {
    require_once 'Modules/OrgUnit/classes/Positions/Operation/class.ilOrgUnitOperationQueries.php';

    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
        'Read Test Participants Learning Progress',
        ilOrgUnitOperationContext::CONTEXT_TST
    );

    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_ACCESS_RESULTS,
        'Access Test Participants Results',
        ilOrgUnitOperationContext::CONTEXT_TST
    );

    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS,
        'Manage Test Participants',
        ilOrgUnitOperationContext::CONTEXT_TST
    );

    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_SCORE_PARTICIPANTS,
        'Score Test Participants',
        ilOrgUnitOperationContext::CONTEXT_TST
    );
} catch (ilException $e) {
}
?>
<#5293>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5294>
<?php
$setting = new ilSetting();

if (!$setting->get('tst_score_rep_consts_cleaned', 0)) {
    $ilDB->queryF(
        "UPDATE tst_tests SET score_reporting = %s WHERE score_reporting = %s",
        array('integer', 'integer'),
        array(0, 4)
    );

    $setting->set('tst_score_rep_consts_cleaned', 1);
}
?>
<#5295>
<?php
if (!$ilDB->tableColumnExists('tst_result_cache', 'passed_once')) {
    $ilDB->addTableColumn('tst_result_cache', 'passed_once', array(
        'type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0
    ));
}
?>
<#5296>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'fb_date_custom')) {
    $ilDB->addTableColumn('exc_assignment', 'fb_date_custom', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_submit_status')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_submit_status', [
        "type" => "integer",
        "length" => 1,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_submit_start')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_submit_start', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_submit_end')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_submit_end', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_submit_freq')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_submit_freq', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_grade_status')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_grade_status', [
        "type" => "integer",
        "length" => 1,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_grade_start')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_grade_start', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_grade_end')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_grade_end', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'rmd_grade_freq')) {
    $ilDB->addTableColumn('exc_assignment', 'rmd_grade_freq', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_rmd_status')) {
    $ilDB->addTableColumn('exc_assignment', 'peer_rmd_status', [
        "type" => "integer",
        "length" => 1,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_rmd_start')) {
    $ilDB->addTableColumn('exc_assignment', 'peer_rmd_start', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_rmd_end')) {
    $ilDB->addTableColumn('exc_assignment', 'peer_rmd_end', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableColumnExists('exc_assignment', 'peer_rmd_freq')) {
    $ilDB->addTableColumn('exc_assignment', 'peer_rmd_freq', [
        "type" => "integer",
        "length" => 4,
        "default" => null,
    ]);
}
if (!$ilDB->tableExists('exc_ass_reminders')) {
    $ilDB->createTable('exc_ass_reminders', array(
        'type' => array(
            'type' => 'text',
            'length' => 32,
        ),
        'ass_id' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'exc_id' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'status' => array(
            "type" => "integer",
            "length" => 1,
            "default" => null
        ),
        'start' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'end' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'freq' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'last_send' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'template_id' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        )
    ));
    $ilDB->addPrimaryKey("exc_ass_reminders", array("ass_id", "exc_id", "type"));
}
?>
<#5297>
<?php
if ($ilDB->tableColumnExists('svy_svy', 'mode_360')) {
    $ilDB->renameTableColumn('svy_svy', 'mode_360', 'mode');
}
?>
<#5298>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'mode_self_eval_results')) {
    $ilDB->addTableColumn(
        'svy_svy',
        'mode_self_eval_results',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5299>
<?php
if ($ilDB->tableColumnExists('svy_svy', 'mode_360_skill_service')) {
    $ilDB->renameTableColumn('svy_svy', 'mode_360_skill_service', 'mode_skill_service');
}
?>
<#5300>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5301>
<?php
if (!$ilDB->tableColumnExists('file_data', 'max_version')) {
    $ilDB->addTableColumn('file_data', 'max_version', array(
        'type' => 'integer',
        'length' => 4
    ));
}
?>
<#5302>
<?php
include_once './Services/Migration/DBUpdate_5295/classes/class.ilMDCreator.php';
include_once './Services/Migration/DBUpdate_5295/classes/class.ilMD.php';

ilMD::_deleteAllByType('grp');

$group_ids = [];
$query = 'SELECT obd.obj_id, title, od.description FROM object_data obd ' .
    'JOIN object_description od on obd.obj_id = od.obj_id ' .
    'WHERE type = ' . $ilDB->quote('grp', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $md_creator = new ilMDCreator($row->obj_id, $row->obj_id, 'grp');
    $md_creator->setTitle($row->title);
    $md_creator->setTitleLanguage('en');
    $md_creator->setDescription($row->description);
    $md_creator->setDescriptionLanguage('en');
    $md_creator->setKeywordLanguage('en');
    $md_creator->setLanguage('en');

    $md_creator->create();
}
?>
<#5303>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5304>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5305>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5306>
<?php
if (!$ilDB->tableColumnExists('mail_man_tpl', 'is_default')) {
    $ilDB->addTableColumn(
        'mail_man_tpl',
        'is_default',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0,
        ]
    );
}
?>
<#5307>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5308>
<?php
if ($ilDB->tableExists('object_data_del')) {
    if (!$ilDB->tableColumnExists('object_data_del', 'description')) {
        $ilDB->addTableColumn(
            'object_data_del',
            'description',
            [
                'type' => 'clob',
                'notnull' => false,
                'default' => null,
            ]
        );
    }
}
?>
<#5309>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5310>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5311>
<?php
if (!$ilDB->tableExists("exc_ass_wiki_team")) {
    $fields = array(
        "id" => array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        ),
        "container_ref_id" => array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        ),
        "template_ref_id" => array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        )
    );
    $ilDB->createTable("exc_ass_wiki_team", $fields);
    $ilDB->addPrimaryKey("exc_ass_wiki_team", array("id"));
}
?>
<#5312>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5313>
<?php

    if (!$ilDB->tableColumnExists('exc_returned', 'team_id')) {
        $ilDB->addTableColumn('exc_returned', 'team_id', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        ));
    }

?>
<#5314>
<?php
if ($ilDB->tableExists('object_data_del')) {
    if (!$ilDB->tableColumnExists('object_data_del', 'description')) {
        $ilDB->addTableColumn(
            'object_data_del',
            'description',
            [
                'type' => 'clob',
                'notnull' => false,
                'default' => null,
            ]
        );
    }
}
?>
<#5315>
<?php
if (!$ilDB->tableExists('tos_documents')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'title' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
            'default' => null
        ],
        'creation_ts' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'modification_ts' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'sorting' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'owner_usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'last_modified_usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ]
    ];

    $ilDB->createTable('tos_documents', $fields);
    $ilDB->addPrimaryKey('tos_documents', ['id']);
    $ilDB->createSequence('tos_documents');
}
?>
<#5316>
<?php
if (!$ilDB->tableColumnExists('tos_documents', 'text')) {
    $ilDB->addTableColumn('tos_documents', 'text', [
        'type' => 'clob',
        'notnull' => false,
        'default' => null
    ]);
}
?>
<#5317>
<?php
if (!$ilDB->tableExists('tos_criterion_to_doc')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'doc_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'criterion_id' => [
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ],
        'criterion_value' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ],
        'assigned_ts' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'modification_ts' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'owner_usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'last_modified_usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ]
    ];

    $ilDB->createTable('tos_criterion_to_doc', $fields);
    $ilDB->addPrimaryKey('tos_criterion_to_doc', ['id']);
    $ilDB->createSequence('tos_criterion_to_doc');
}
?>
<#5318>
<?php
if (!$ilDB->tableColumnExists('tos_versions', 'doc_id')) {
    $ilDB->addTableColumn('tos_versions', 'doc_id', [
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ]);
}

if (!$ilDB->tableColumnExists('tos_versions', 'title')) {
    $ilDB->addTableColumn('tos_versions', 'title', [
        'type' => 'text',
        'notnull' => false,
        'default' => null
    ]);
}

if (!$ilDB->tableColumnExists('tos_acceptance_track', 'criteria')) {
    $ilDB->addTableColumn('tos_acceptance_track', 'criteria', [
        'type' => 'clob',
        'notnull' => false,
        'default' => null
    ]);
}
?>
<#5319>
<?php
if ($ilDB->indexExistsByFields('tos_versions', ['hash', 'lng'])) {
    $ilDB->dropIndexByFields('tos_versions', ['hash', 'lng']);
}
?>
<#5320>
<?php
if (!$ilDB->indexExistsByFields('tos_versions', ['hash', 'doc_id'])) {
    $ilDB->addIndex('tos_versions', ['hash', 'doc_id'], 'i1');
}
?>
<#5321>
<?php
$dbStep = $nr;
$globalAgreementPath = './Customizing/global/agreement';
$clientAgreementPath = './Customizing/clients/' . basename(CLIENT_DATA_DIR) . '/agreement';

$ilSetting = new \ilSetting();

$documentDirectoriesExist = false;
if (
    (file_exists($globalAgreementPath) && is_dir($globalAgreementPath) && is_readable($globalAgreementPath)) ||
    (file_exists($clientAgreementPath) && is_dir($clientAgreementPath) && is_readable($clientAgreementPath))
) {
    $documentDirectoriesExist = true;
}

if ($documentDirectoriesExist && !$ilSetting->get('dbupwarn_tos_migr_54x', 0)) {
    echo "<pre>

		DEAR ADMINISTRATOR !!

		Because of the ILIAS 5.4.x feature 'User: Criteria-based »User Agreement« documents'
		(see: https://www.ilias.de/docu/goto_docu_wiki_wpage_5225_1357.html) the file system
		based user agreements in '{$globalAgreementPath}' and '{$clientAgreementPath}' will
		be migrated according to https://www.ilias.de/docu/goto_docu_wiki_wpage_5225_1357.html#ilPageTocA27 .

		The client-independent user agreements will be abandoned at all and migrated to
		client-related documents.

		With ILIAS 5.4.x user agreement documents can be managed in the global ILIAS administration.
		The contents of a document can be uploaded as text or HTML file and will be stored (after purification) in the database.

		If you reload this page (e.g. by pressing the F5 key), the migration process will be started. The agreement files will NOT be deleted.
		</pre>";

    $ilSetting->set('dbupwarn_tos_migr_54x', 1);
    exit;
} elseif (!$documentDirectoriesExist) {
    $ilSetting->set('dbupwarn_tos_migr_54x', 1);
}

if (!$ilDB->tableExists('agreement_migr')) {
    $fields = [
        'agr_type' => [
            'type' => 'text',
            'length' => 20,
            'notnull' => true
        ],
        'agr_lng' => [
            'type' => 'text',
            'length' => 2,
            'notnull' => true
        ]
    ];

    $ilDB->createTable('agreement_migr', $fields);
    $ilDB->addPrimaryKey('agreement_migr', ['agr_type', 'agr_lng']);
    $GLOBALS['ilLog']->info(sprintf(
        'Created agreement migration table: agreement_migr'
    ));
}

// Determine system language
$ilIliasIniFile = new \ilIniFile(ILIAS_ABSOLUTE_PATH . '/ilias.ini.php');
$ilIliasIniFile->read();

$language = $ilIliasIniFile->readVariable('language', 'default');
$ilSetting = new \ilSetting();
if ($ilSetting->get('language') != '') {
    $language = $ilSetting->get('language');
}

$docTitlePrefix = 'Document';
if ('de' === strtolower($language)) {
    $docTitlePrefix = 'Dokument';
}

$res = $ilDB->query("SELECT * FROM agreement_migr");
$i = (int) $ilDB->numRows($res);

if ($documentDirectoriesExist) {
    foreach ([
                 'client-independent' => $globalAgreementPath,
                 'client-related' => $clientAgreementPath,
             ] as $type => $path) {
        if (!file_exists($path) || !is_dir($path)) {
            $GLOBALS['ilLog']->info(sprintf(
                "DB Step %s: Skipped 'Terms of Service' migration, path '%s' not found or not a directory",
                $dbStep,
                $path
            ));
            continue;
        }

        if (!is_readable($path)) {
            $GLOBALS['ilLog']->error(sprintf(
                "DB Step %s: Skipped 'Terms of Service' migration, path '%s' is not readable",
                $dbStep,
                $path
            ));
            continue;
        }

        try {
            foreach (new \RegexIterator(
                new \DirectoryIterator($path),
                '/agreement_[a-zA-Z]{2,2}\.(html)$/i'
            ) as $file) {
                $GLOBALS['ilLog']->info(sprintf(
                    "DB Step %s: Started migration of %s user agreement file '%s'",
                    $dbStep,
                    $type,
                    $file->getPathname()
                ));

                $matches = null;
                if (!preg_match('/agreement_([a-zA-Z]{2,2})\.html/', $file->getBasename(), $matches)) {
                    $GLOBALS['ilLog']->info(sprintf(
                        "DB Step %s: Ignored migration of %s user agreement file '%s' because the basename is not valid",
                        $dbStep,
                        $type,
                        $file->getPathname()
                    ));
                    continue;
                }
                $languageValue = $matches[1];

                $res = $ilDB->queryF(
                    "SELECT * FROM agreement_migr WHERE agr_type = %s AND agr_lng = %s",
                    ['text', 'text'],
                    [$type, $languageValue]
                );
                if ($ilDB->numRows($res) > 0) {
                    $GLOBALS['ilLog']->info(sprintf(
                        "DB Step %s: Ignored migration of %s user agreement file '%s' because it has been already migrated",
                        $dbStep,
                        $type,
                        $file->getPathname()
                    ));
                    continue;
                }

                $i++;

                $sorting = $i;
                $docTitle = $docTitlePrefix . ' ' . $i;

                $text = file_get_contents($file->getPathname());
                if (strip_tags($text) === $text) {
                    $text = nl2br($text);
                }

                $docId = $ilDB->nextId('tos_documents');
                $ilDB->insert(
                    'tos_documents',
                    [
                        'id' => ['integer', $docId],
                        'sorting' => ['integer', $sorting],
                        'title' => ['text', $docTitle],
                        'owner_usr_id' => ['integer', -1],
                        'creation_ts' => ['integer', $file->getMTime() > 0 ? $file->getMTime() : 0],
                        'text' => ['clob', $text],
                    ]
                );
                $GLOBALS['ilLog']->info(sprintf(
                    "DB Step %s: Created new document with id %s and title '%s' for file '%s'",
                    $dbStep,
                    $docId,
                    $docTitle,
                    $file->getPathname()
                ));

                $assignmentId = $ilDB->nextId('tos_criterion_to_doc');
                $ilDB->insert(
                    'tos_criterion_to_doc',
                    [
                        'id' => ['integer', $assignmentId],
                        'doc_id' => ['integer', $docId],
                        'criterion_id' => ['text', 'usr_language'],
                        'criterion_value' => ['text', json_encode(['lng' => $languageValue])],
                        'owner_usr_id' => ['integer', -1],
                        'assigned_ts' => ['integer', $file->getMTime() > 0 ? $file->getMTime() : 0]
                    ]
                );
                $GLOBALS['ilLog']->info(sprintf(
                    "DB Step %s: Created new language criterion assignment with id %s and value '%s' to document with id %s for file '%s'",
                    $dbStep,
                    $assignmentId,
                    $languageValue,
                    $docId,
                    $file->getPathname()
                ));

                // Determine all accepted version with lng = $criterion and hash = hash and src = file
                $docTypeIn = ' AND ' . $ilDB->like('src', 'text', '%%/client/%%', false);
                if ($type === 'client-independent') {
                    $docTypeIn = ' AND ' . $ilDB->like('src', 'text', '%%/global/%%', false);
                }

                $ilDB->manipulateF(
                    'UPDATE tos_versions SET doc_id = %s, title = %s WHERE lng = %s AND hash = %s' . $docTypeIn,
                    ['integer', 'text', 'text', 'text'],
                    [$docId, $docTitle, $languageValue, md5($text)]
                );
                $GLOBALS['ilLog']->info(sprintf(
                    "DB Step %s: Migrated %s user agreement file '%s'",
                    $dbStep,
                    $type,
                    $file->getPathname()
                ));

                $ilDB->replace(
                    'agreement_migr',
                    [
                        'agr_type' => ['text', $type],
                        'agr_lng' => ['text', $languageValue],
                    ],
                    []
                );
            }
        } catch (\Exception $e) {
            $GLOBALS['ilLog']->error(sprintf(
                "DB Step %s: %s",
                $dbStep,
                $e->getMessage()
            ));
        }
    }
}

// Migrate title for all tos_version entries without a doc_id
$numDocumentsData = $ilDB->fetchAssoc(
    $ilDB->query('SELECT COUNT(id) num_docs FROM tos_documents')
);

$numDocs = 0;
if (is_array($numDocumentsData) && $numDocumentsData['num_docs']) {
    $numDocs = $numDocumentsData['num_docs'];
}

$res = $ilDB->query('SELECT lng, src FROM tos_versions WHERE title IS NULL GROUP BY lng, src');
$i = 0;
while ($row = $ilDB->fetchAssoc($res)) {
    $docTitle = $docTitlePrefix . ' ' . ($numDocs + (++$i));
    $ilDB->manipulateF(
        'UPDATE tos_versions SET title = %s WHERE lng = %s AND src = %s AND title IS NULL',
        ['text', 'text', 'text'],
        [$docTitle, $row['lng'], $row['src']]
    );
}
?>
<#5322>
<?php
/** @var $ilDB ilDBInterface */
if (in_array($ilDB->getDBType(), [ilDBConstants::TYPE_PDO_POSTGRE, ilDBConstants::TYPE_POSTGRES])) {
    // Migrate accepted criteria for missing documents (file did not exists during migration)
    $res = $ilDB->query(
        "
        SELECT tos_acceptance_track.*
        FROM tos_acceptance_track
        INNER JOIN tos_versions ON tos_versions.id = tos_acceptance_track.tosv_id
        WHERE tos_versions.doc_id = 0 AND tos_acceptance_track.criteria IS NULL AND tos_versions.lng IS NOT NULL
        "
    );
    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulateF(
            "
                UPDATE tos_acceptance_track
                SET tos_acceptance_track.criteria = CONCAT(%s, CONCAT(tos_versions.lng, %s))
                WHERE tos_acceptance_track.tosv_id = %s AND tos_acceptance_track.usr_id = %s AND tos_acceptance_track.ts = %s
            ",
            ['text', 'text', 'integer', 'integer', 'integer'],
            ['[{"id":"usr_language","value":{"lng":"', '"}}]', $row['tosv_id'], $row['usr_id'], $row['ts']]
        );
    }

    // Migrate accepted criteria for already migrated documents
    $res = $ilDB->queryF(
        "
        SELECT tos_acceptance_track.*
        FROM tos_acceptance_track
        INNER JOIN tos_versions
            ON tos_versions.id = tos_acceptance_track.tosv_id
        INNER JOIN tos_documents
            ON tos_documents.id = tos_versions.doc_id
        INNER JOIN tos_criterion_to_doc
            ON  tos_criterion_to_doc.doc_id = tos_documents.id AND criterion_id = %s
        WHERE tos_versions.lng IS NOT NULL AND tos_acceptance_track.criteria IS NULL
        ",
        ['text'],
        ['usr_language']
    );
    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->manipulateF(
            "
                UPDATE tos_acceptance_track
                SET tos_acceptance_track.criteria = CONCAT(%s, CONCAT(tos_versions.lng, %s))
                WHERE tos_acceptance_track.tosv_id = %s AND tos_acceptance_track.usr_id = %s AND tos_acceptance_track.ts = %s
            ",
            ['text', 'text', 'integer', 'integer', 'integer'],
            ['[{"id":"usr_language","value":', '}]', $row['tosv_id'], $row['usr_id'], $row['ts']]
        );
    }
} else {
    // Migrate accepted criteria for missing documents (file did not exists during migration)
    $ilDB->manipulateF(
        "
        UPDATE tos_acceptance_track
        INNER JOIN tos_versions
            ON tos_versions.id = tos_acceptance_track.tosv_id
        SET tos_acceptance_track.criteria = CONCAT(%s, CONCAT(tos_versions.lng, %s))
        WHERE tos_versions.doc_id = 0 AND tos_acceptance_track.criteria IS NULL AND tos_versions.lng IS NOT NULL
        ",
        ['text', 'text'],
        ['[{"id":"usr_language","value":{"lng":"', '"}}]']
    );

    // Migrate accepted criteria for already migrated documents
    $ilDB->manipulateF(
        "
        UPDATE tos_acceptance_track
        INNER JOIN tos_versions
            ON tos_versions.id = tos_acceptance_track.tosv_id
        INNER JOIN tos_documents
            ON tos_documents.id = tos_versions.doc_id
        INNER JOIN tos_criterion_to_doc
            ON  tos_criterion_to_doc.doc_id = tos_documents.id AND criterion_id = %s
        SET tos_acceptance_track.criteria = CONCAT(%s, CONCAT(tos_criterion_to_doc.criterion_value, %s))
        WHERE tos_versions.lng IS NOT NULL AND tos_acceptance_track.criteria IS NULL
        ",
        ['text', 'text', 'text'],
        ['usr_language', '[{"id":"usr_language","value":', '}]']
    );
}
?>
<#5323>
<?php
if ($ilDB->tableColumnExists('tos_versions', 'lng')) {
    $ilDB->dropTableColumn('tos_versions', 'lng');
}

if ($ilDB->tableColumnExists('tos_versions', 'src_type')) {
    $ilDB->dropTableColumn('tos_versions', 'src_type');
}

if ($ilDB->tableColumnExists('tos_versions', 'src')) {
    $ilDB->dropTableColumn('tos_versions', 'src');
}
?>
<#5324>
<?php
if ($ilDB->tableExists('agreement_migr')) {
    $ilDB->dropTable('agreement_migr');
    $GLOBALS['ilLog']->info(sprintf(
        'Dropped agreement migration table: agreement_migr'
    ));
}
?>
<#5325>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5326>
<?php
if (!$ilDB->tableExists('like_data')) {
    $ilDB->createTable('like_data', array(
        'user_id' => array(
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
        ),
        'obj_type' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true
        ),
        'sub_obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'sub_obj_type' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true
        ),
        'news_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'like_type' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));

    $ilDB->addPrimaryKey('like_data', array('user_id','obj_id','obj_type','sub_obj_id','sub_obj_type','news_id','like_type'));

    $ilDB->addIndex('like_data', array('obj_id'), 'i1');
}
?>
<#5327>
<?php
if (!$ilDB->tableColumnExists('like_data', 'exp_ts')) {
    $ilDB->addTableColumn('like_data', 'exp_ts', array(
        'type' => 'timestamp',
        'notnull' => true
    ));
}
?>
<#5328>
<?php
if (!$ilDB->tableColumnExists('note', 'news_id')) {
    $ilDB->addTableColumn('note', 'news_id', array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#5329>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5330>
<?php

if (!$ilDB->tableColumnExists('media_item', 'upload_hash')) {
    $ilDB->addTableColumn('media_item', 'upload_hash', array(
        "type" => "text",
        "length" => 100
    ));
}

?>
<#5331>
<?php
    if (!$ilDB->tableColumnExists('booking_settings', 'reminder_status')) {
        $ilDB->addTableColumn('booking_settings', 'reminder_status', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        ));
    }
?>
<#5332>
<?php
    if (!$ilDB->tableColumnExists('booking_settings', 'reminder_day')) {
        $ilDB->addTableColumn('booking_settings', 'reminder_day', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        ));
    }
?>
<#5333>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5334>
<?php
    if (!$ilDB->tableColumnExists('booking_settings', 'last_remind_ts')) {
        $ilDB->addTableColumn('booking_settings', 'last_remind_ts', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        ));
    }
?>
<#5335>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5336>
<?php

if ($ilDB->indexExistsByFields('read_event', array('usr_id'))) {
    $ilDB->dropIndexByFields('read_event', array('usr_id'));
}
$ilDB->addIndex('read_event', array('usr_id'), 'i1');

if (!$ilDB->tableColumnExists('usr_data', 'first_login')) {
    $ilDB->addTableColumn('usr_data', 'first_login', array(
        "type" => "timestamp",
        "notnull" => false
    ));

    // since we do not have this date for existing users we take the minimum of last login
    // and first access to any repo object
    $set = $ilDB->queryF(
        "SELECT u.usr_id, u.last_login, min(r.first_access) first_access FROM usr_data u LEFT JOIN read_event r ON (u.usr_id = r.usr_id) GROUP BY u.usr_id, u.last_login",
        array(),
        array()
    );
    while ($rec = $ilDB->fetchAssoc($set)) {
        $first_login = $rec["last_login"];
        if ($rec["first_access"] != "" && ($rec["first_access"] < $rec["last_login"])) {
            $first_login = $rec["first_access"];
        }

        if ($first_login != "") {
            $ilDB->update("usr_data", array(
                "first_login" => array("timestamp", $first_login)
            ), array(    // where
                "usr_id" => array("integer", $rec["usr_id"])
            ));
        }
    }
}
?>
<#5337>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'last_profile_prompt')) {
    $ilDB->addTableColumn('usr_data', 'last_profile_prompt', array(
        "type" => "timestamp",
        "notnull" => false
    ));
}
?>
<#5338>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5339>
<?php
if (!$ilDB->tableExists('certificate_template')) {
    $ilDB->createTable('certificate_template', array(
        'id' => array(
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
        ),
        'obj_type' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => ''
        ),
        'certificate_content' => array(
            'type' => 'clob',
            'notnull' => true,
        ),
        'certificate_hash' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'template_values' => array(
            'type' => 'clob',
            'notnull' => true,
        ),
        'background_image_path' => array(
            'type' => 'text',
            'notnull' => false,
            'length' => 255
        ),
        'version' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 'v1'
        ),
        'ilias_version' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 'v5.4.0'
        ),
        'created_timestamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'currently_active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
    ));

    $ilDB->addPrimaryKey('certificate_template', array('id'));
    $ilDB->createSequence('certificate_template');
    $ilDB->addIndex('certificate_template', array('obj_id'), 'i1');
}

if (!$ilDB->tableExists('user_certificates')) {
    $ilDB->createTable('user_certificates', array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'pattern_certificate_id' => array(
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
        ),
        'obj_type' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 0
        ),
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'user_name' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 0
        ),
        'acquired_timestamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'certificate_content' => array(
            'type' => 'clob',
            'notnull' => true,
        ),
        'template_values' => array(
            'type' => 'clob',
            'notnull' => true,
        ),
        'valid_until' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => null
        ),
        'background_image_path' => array(
            'type' => 'text',
            'notnull' => false,
            'length' => 255
        ),
        'version' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => '1'
        ),
        'ilias_version' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 'v5.4.0'
        ),
        'currently_active' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
    ));

    $ilDB->addPrimaryKey('user_certificates', array('id'));
    $ilDB->createSequence('user_certificates');
    $ilDB->addIndex('user_certificates', array('obj_id', 'pattern_certificate_id'), 'i1');
}

if (!$ilDB->tableExists('certificate_cron_queue')) {
    $ilDB->createTable('certificate_cron_queue', array(
        'id' => array(
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
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'adapter_class' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true,
        ),
        'state' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true
        ),
        'started_timestamp' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
    ));

    $ilDB->addPrimaryKey('certificate_cron_queue', array('id'));
    $ilDB->createSequence('certificate_cron_queue');
    $ilDB->addIndex('certificate_cron_queue', array('obj_id', 'usr_id'), 'i1');
}
?>
<#5340>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5341>
<?php
if ($ilDB->tableExists('certificate_template')) {
    $web_path = CLIENT_WEB_DIR;

    $directories = array(
        'exc' => '/exercise/certificates/',
        'crs' => '/course/certificates/',
        'tst' => '/assessment/certificates/',
        'sahs' => '/certificates/scorm/',
        'lti' => '/lti_data/certficates/',
        'cmix' => '/cmix_data/certficates/',
    );

    $GLOBALS['ilLog']->info(sprintf(
        "Started certificate template XML file migration"
    ));

    $migratedObjectIds = [];
    $has_errors = false;
    $stmtSelectObjCertWithTemplate = $ilDB->prepare(
        "
			SELECT od.obj_id, COUNT(certificate_template.obj_id) as num_migrated_cer_templates
			FROM object_data od
			LEFT JOIN certificate_template ON certificate_template.obj_id = od.obj_id
			WHERE od.obj_id = ?
			GROUP BY od.obj_id
		",
        ['integer']
    );

    foreach ($directories as $type => $relativePath) {
        try {
            $directory = $web_path . $relativePath;

            $GLOBALS['ilLog']->info(sprintf(
                "Started migration for object type directory: %s",
                $directory
            ));

            $iter = new \RegExIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $directory,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ),
                '/certificate\.xml$/'
            );

            foreach ($iter as $certificateFile) {
                /** @var $certificateFile \SplFileInfo */
                $pathToFile = $certificateFile->getPathname();

                $GLOBALS['ilLog']->info(sprintf(
                    "Found certificate template XML file (type: %s): %s",
                    $type,
                    $pathToFile
                ));

                $objectId = basename($certificateFile->getPathInfo());
                if (!is_numeric($objectId) || !($objectId > 0)) {
                    $GLOBALS['ilLog']->warning(sprintf(
                        "Could not extract valid obj_id, cannot migrate certificate XML template file: %s",
                        $pathToFile
                    ));
                    continue;
                }

                $GLOBALS['ilLog']->info(sprintf(
                    "Extracted obj_id %s from certificate file: %s",
                    $objectId,
                    $pathToFile
                ));

                if (isset($migratedObjectIds[$objectId])) {
                    $GLOBALS['ilLog']->warning(sprintf(
                        "Already created a database based certificate template for obj_id %s, cannot migrate file: %s",
                        $objectId,
                        $pathToFile
                    ));
                    continue;
                }

                $res = $ilDB->execute($stmtSelectObjCertWithTemplate, [$objectId]);
                if (0 === (int) $ilDB->numRows($res)) {
                    $GLOBALS['ilLog']->warning(sprintf(
                        "Could not find an existing ILIAS object for obj_id %s, cannot migrate file: %s",
                        $objectId,
                        $pathToFile
                    ));
                    continue;
                }

                $row = $ilDB->fetchAssoc($res);
                if ((int) $row['num_migrated_cer_templates'] > 0) {
                    $GLOBALS['ilLog']->warning(sprintf(
                        "Already created a database based certificate template for obj_id %s, cannot migrate file: %s",
                        $objectId,
                        $pathToFile
                    ));
                    continue;
                }

                $content = file_get_contents($pathToFile);
                $timestamp = $certificateFile->getMTime();

                if (false !== $content) {
                    $backgroundImagePath = '';

                    if (file_exists($web_path . $relativePath . $objectId . '/background.jpg')) {
                        $backgroundImagePath = $relativePath . $objectId . '/background.jpg';
                    }

                    if ('' === $backgroundImagePath && file_exists($web_path . '/certificates/default/background.jpg')) {
                        $backgroundImagePath = '/certificates/default/background.jpg';
                    }

                    $id = $ilDB->nextId('certificate_template');
                    $columns = [
                        'id' => ['integer', $id],
                        'obj_id' => ['integer', $objectId],
                        'obj_type' => ['text', $type],
                        'certificate_content' => ['text', $content],
                        'certificate_hash' => ['text', md5($content)],
                        'template_values' => ['text', ''],
                        'version' => ['text', '1'],
                        'ilias_version' => ['text', ILIAS_VERSION_NUMERIC],
                        'created_timestamp' => ['integer', $timestamp],
                        'currently_active' => ['integer', 1],
                        'background_image_path' => ['text', $backgroundImagePath],
                    ];

                    $ilDB->insert('certificate_template', $columns);
                    $migratedObjectIds[$objectId] = true;

                    $GLOBALS['ilLog']->info(sprintf(
                        "Successfully migrated certificate template XML file for obj_id: %s/type: %s/id: %s",
                        $objectId,
                        $type,
                        $id
                    ));
                } else {
                    $GLOBALS['ilLog']->warning(sprintf(
                        "Empty content, cannot migrate certificate XML template file: %s",
                        $pathToFile
                    ));
                }
            }

            $GLOBALS['ilLog']->info(sprintf(
                "Finished migration for directory: %s",
                $directory
            ));
        } catch (\Exception $e) {
            $has_errors = true;
            $GLOBALS['ilLog']->error(sprintf(
                "Cannot migrate directory, exception raised: %s",
                $e->getMessage()
            ));
        } catch (\Throwable $e) {
            $has_errors = true;
            $GLOBALS['ilLog']->error(sprintf(
                "Cannot migrate directory, exception raised: %s",
                $e->getMessage()
            ));
        }
    }

    $GLOBALS['ilLog']->info(sprintf(
        "Finished certificate template (%s templates created) XML file migration%s",
        count($migratedObjectIds),
        ($has_errors ? ' with errors' : '')
    ));
}
?>
<#5342>
<?php
if (!$ilDB->tableExists('bgtask_cert_migration')) {
    $ilDB->createTable('bgtask_cert_migration', array(
        'id' => array(
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
        'lock' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'found_items' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'processed_items' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'migrated_items' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'progress' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'state' => array(
            'type' => 'text',
            'length' => '255',
            'notnull' => true
        ),
        'started_ts' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        ),
        'finished_ts' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ),
    ));
    $ilDB->addPrimaryKey('bgtask_cert_migration', array('id'));
    $ilDB->createSequence('bgtask_cert_migration');
    $ilDB->addUniqueConstraint('bgtask_cert_migration', array('id', 'usr_id'));
}
$ilCtrlStructureReader->getStructure();
?>
<#5343>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5344>
<?php
if (!$ilDB->tableColumnExists('certificate_template', 'deleted')) {
    $ilDB->addTableColumn(
        'certificate_template',
        'deleted',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5345>
<?php
if (!$ilDB->tableColumnExists('certificate_cron_queue', 'template_id')) {
    $ilDB->addTableColumn(
        'certificate_cron_queue',
        'template_id',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5346>
<?php
/** @var \ilDBInterface $ilDB */
if ($ilDB->tableExists('certificate_cron_queue') && !$ilDB->tableExists('il_cert_cron_queue')) {
    $ilDB->renameTable('certificate_cron_queue', 'il_cert_cron_queue');
}
if ($ilDB->sequenceExists('certificate_cron_queue')) {
    $ilDB->dropSequence('certificate_cron_queue');
}
if (!$ilDB->sequenceExists('il_cert_cron_queue')) {
    $query = "SELECT MAX(id) AS max_id FROM il_cert_cron_queue";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('il_cert_cron_queue', (int) $row['max_id'] + 1);
}
?>
<#5347>
<?php
if ($ilDB->tableExists('certificate_template') && !$ilDB->tableExists('il_cert_template')) {
    $ilDB->renameTable('certificate_template', 'il_cert_template');
}
if ($ilDB->sequenceExists('certificate_template')) {
    $ilDB->dropSequence('certificate_template');
}
if (!$ilDB->sequenceExists('il_cert_template')) {
    $query = "SELECT MAX(id) AS max_id FROM il_cert_template";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('il_cert_template', (int) $row['max_id'] + 1);
}
?>
<#5348>
<?php
if ($ilDB->tableExists('user_certificates') && !$ilDB->tableExists('il_cert_user_cert')) {
    $ilDB->renameTable('user_certificates', 'il_cert_user_cert');
}
if ($ilDB->sequenceExists('user_certificates')) {
    $ilDB->dropSequence('user_certificates');
}
if (!$ilDB->sequenceExists('il_cert_user_cert')) {
    $query = "SELECT MAX(id) AS max_id FROM il_cert_user_cert";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('il_cert_user_cert', (int) $row['max_id'] + 1);
}
?>
<#5349>
<?php
if ($ilDB->tableExists('bgtask_cert_migration') && !$ilDB->tableExists('il_cert_bgtask_migr')) {
    $ilDB->renameTable('bgtask_cert_migration', 'il_cert_bgtask_migr');
}
if ($ilDB->sequenceExists('bgtask_cert_migration')) {
    $ilDB->dropSequence('bgtask_cert_migration');
}
if (!$ilDB->sequenceExists('il_cert_bgtask_migr')) {
    $query = "SELECT MAX(id) AS max_id FROM il_cert_bgtask_migr";
    $row = $ilDB->fetchAssoc($ilDB->query($query));
    $ilDB->createSequence('il_cert_bgtask_migr', (int) $row['max_id'] + 1);
}
?>
<#5350>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5351>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5352>
<?php
if (!$ilDB->tableColumnExists('il_cert_template', 'thumbnail_image_path')) {
    $ilDB->addTableColumn(
        'il_cert_template',
        'thumbnail_image_path',
        array(
            'type' => 'text',
            'notnull' => false,
            'length' => 255
        )
    );
}

if (!$ilDB->tableColumnExists('il_cert_user_cert', 'thumbnail_image_path')) {
    $ilDB->addTableColumn(
        'il_cert_user_cert',
        'thumbnail_image_path',
        array(
            'type' => 'text',
            'notnull' => false,
            'length' => 255
        )
    );
}
?>
<#5353>
<?php
if ($ilDB->tableColumnExists('svy_svy', 'mode_360')) {
    $ilDB->renameTableColumn('svy_svy', 'mode_360', 'mode');
}
?>
<#5354>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'mode_self_eval_results')) {
    $ilDB->addTableColumn(
        'svy_svy',
        'mode_self_eval_results',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5355>
<?php
if ($ilDB->tableColumnExists('svy_svy', 'mode_360_skill_service')) {
    $ilDB->renameTableColumn('svy_svy', 'mode_360_skill_service', 'mode_skill_service');
}
?>
<#5356>
<?php
if (!$ilDB->indexExistsByFields('il_cert_template', ['obj_id', 'deleted'])) {
    $ilDB->addIndex('il_cert_template', ['obj_id', 'deleted'], 'i2');
}
?>
<#5357>
<?php
if (!$ilDB->indexExistsByFields('il_cert_template', ['obj_id', 'currently_active', 'deleted'])) {
    $ilDB->addIndex('il_cert_template', ['obj_id', 'currently_active', 'deleted'], 'i3');
}
?>
<#5358>
<?php
if (!$ilDB->indexExistsByFields('il_cert_template', ['obj_type'])) {
    $ilDB->addIndex('il_cert_template', ['obj_type'], 'i4');
}
?>
<#5359>
<?php
if (!$ilDB->indexExistsByFields('il_cert_user_cert', ['user_id', 'currently_active'])) {
    $ilDB->addIndex('il_cert_user_cert', ['user_id', 'currently_active'], 'i2');
}
?>
<#5360>
<?php
if (!$ilDB->indexExistsByFields('il_cert_user_cert', ['user_id', 'currently_active', 'acquired_timestamp'])) {
    $ilDB->addIndex('il_cert_user_cert', ['user_id', 'currently_active', 'acquired_timestamp'], 'i3');
}
?>
<#5361>
<?php
if (!$ilDB->indexExistsByFields('il_cert_user_cert', ['user_id', 'obj_type', 'currently_active'])) {
    $ilDB->addIndex('il_cert_user_cert', ['user_id', 'obj_type', 'currently_active'], 'i4');
}
?>
<#5362>
<?php
if (!$ilDB->indexExistsByFields('il_cert_user_cert', ['obj_id', 'currently_active'])) {
    $ilDB->addIndex('il_cert_user_cert', ['obj_id', 'currently_active'], 'i5');
}
?>
<#5363>
<?php
if (!$ilDB->indexExistsByFields('il_cert_user_cert', ['user_id', 'obj_id', 'currently_active'])) {
    $ilDB->addIndex('il_cert_user_cert', ['user_id', 'obj_id', 'currently_active'], 'i6');
}
?>
<#5364>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'deadline_mode')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'deadline_mode',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5365>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'relative_deadline')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'relative_deadline',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5366>
<?php
if (!$ilDB->tableColumnExists('exc_idl', 'starting_ts')) {
    $ilDB->addTableColumn(
        'exc_idl',
        'starting_ts',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5367>
<?php
// BEGIN MME
$fields = array(
    'identification' => array(
        'type' => 'text',
        'length' => '64',

    ),
    'active' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'position' => array(
        'type' => 'integer',
        'length' => '4',

    ),
    'parent_identification' => array(
        'type' => 'text',
        'length' => '255',

    )
);
if (!$ilDB->tableExists('il_mm_items')) {
    $ilDB->createTable('il_mm_items', $fields);
    $ilDB->addPrimaryKey('il_mm_items', array( 'identification' ));
}
?>
<#5368>
<?php
$fields = array(
    'id' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'identification' => array(
        'type' => 'text',
        'length' => '255',
    ),
    'translation' => array(
        'type' => 'text',
        'length' => '4000',

    ),
    'language_key' => array(
        'type' => 'text',
        'length' => '8',

    ),
);
if (!$ilDB->tableExists('il_mm_translation')) {
    $ilDB->createTable('il_mm_translation', $fields);
    $ilDB->addPrimaryKey('il_mm_translation', array( 'id' ));
}
?>
<#5369>
<?php
$fields = array(
    'provider_class' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'purpose' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'dynamic' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_gs_providers')) {
    $ilDB->createTable('il_gs_providers', $fields);
    $ilDB->addPrimaryKey('il_gs_providers', array('provider_class'));
}
?>
<#5370>
<?php
$fields = array(
    'identification' => array(
        'type' => 'text',
        'length' => '64',

    ),
    'provider_class' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'active' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (!$ilDB->tableExists('il_gs_identifications')) {
    $ilDB->createTable('il_gs_identifications', $fields);
    $ilDB->addPrimaryKey('il_gs_identifications', array('identification'));
}
?>
<#5371>
<?php
$fields = array(
    'identifier' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'type' => array(
        'type' => 'text',
        'length' => '128',

    ),
    'action' => array(
        'type' => 'text',
        'length' => '4000',

    ),
    'top_item' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'default_title' => array(
        'type' => 'text',
        'length' => '4000',

    ),

);
if (!$ilDB->tableExists('il_mm_custom_items')) {
    $ilDB->createTable('il_mm_custom_items', $fields);
    $ilDB->addPrimaryKey('il_mm_custom_items', array( 'identifier' ));
}
?>
<#5372>
<?php
$fields = array(
    'identification' => array(
        'type' => 'text',
        'length' => '255',

    ),
    'action' => array(
        'type' => 'text',
        'length' => '4000',

    ),
    'external' => array(
        'type' => 'integer',
        'length' => '1',

    )
);
if (!$ilDB->tableExists('il_mm_actions')) {
    $ilDB->createTable('il_mm_actions', $fields);
    $ilDB->addPrimaryKey('il_mm_actions', array( 'identification' ));
}
?>
<#5373>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addAdminNode('mme', 'Main Menu');

$ilCtrlStructureReader->getStructure();
// END MME
?>
<#5374>
<?php
if (!$ilDB->tableColumnExists("il_object_def", "offline_handling")) {
    $def = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
    );
    $ilDB->addTableColumn("il_object_def", "offline_handling", $def);
}
?>
<#5375>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5376>
<?php
if (!$ilDB->tableColumnExists('object_data', 'offline')) {
    $def = [
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => null
    ];
    $ilDB->addTableColumn('object_data', 'offline', $def);
}
?>

<#5377>
<?php

// migration of course offline status
$query = 'update object_data od set offline = ' .
    '(select if( activation_type = 0,1,0) from crs_settings ' .
    'where obj_id = od.obj_id) where type = ' . $ilDB->quote('crs', 'text');
$ilDB->manipulate($query);
?>

<#5378>
<?php

// migration of lm offline status
$query = 'update object_data od set offline = ' .
    '(select if( is_online = ' . $ilDB->quote('n', 'text') . ',1,0) from content_object ' .
    'where id = od.obj_id) where type = ' . $ilDB->quote('lm', 'text');
$ilDB->manipulate($query);

?>
<#5379>
<?php

// migration of lm offline status
$query = 'update object_data od set offline = ' .
    '(select if( is_online = ' . $ilDB->quote('n', 'text') . ',1,0) from file_based_lm ' .
    'where id = od.obj_id) where type = ' . $ilDB->quote('htlm', 'text');
$ilDB->manipulate($query);

?>
<#5380>
<?php

// migration of svy offline status
$query = 'update object_data od set offline = ' .
    '(select if( status = 0,1,0) from svy_svy ' .
    'where obj_fi = od.obj_id) where type = ' . $ilDB->quote('svy', 'text');
$ilDB->manipulate($query);
?>
<#5381>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#5382>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if ($type_id && $tgt_ops_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#5383>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);

?>

<#5384>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addCustomRBACOperation(
    'manage_materials',
    'Manage Materials',
    'object',
    6500
);
?>
<#5385>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');

if ($tgt_ops_id && $type_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}

?>
<#5386>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
?>


<#5387>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addCustomRBACOperation(
    'edit_metadata',
    'Edit Metadata',
    'object',
    5800
);
?>


<#5388>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_metadata');

if ($tgt_ops_id && $type_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}

?>
<#5389>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_metadata');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
?>

<#5390>
<?php
if (!$ilDB->tableColumnExists('adv_md_record', 'gpos')) {
    $ilDB->addTableColumn(
        'adv_md_record',
        'gpos',
        array(
            "type" => "integer",
            "notnull" => false,
            "length" => 4
        )
    );
}
?>
<#5391>
<?php
if (!$ilDB->tableExists('adv_md_record_obj_ord')) {
    $ilDB->createTable(
        'adv_md_record_obj_ord',
        [
            'record_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ],
            'obj_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ],
            'position' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ]
        ]
    );
    $ilDB->addPrimaryKey(
        'adv_md_record_obj_ord',
        [
            'record_id',
            'obj_id'
        ]
    );
}
?>

<#5392>
<?php
if (!$ilDB->tableColumnExists('event', 'show_members')) {
    $ilDB->addTableColumn(
        'event',
        'show_members',
        [
            "notnull" => true,
            "length" => 1,
            "type" => "integer",
            'default' => 0
        ]
    );
}
?>

<#5393>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#5394>
<?php
if (!$ilDB->tableColumnExists('event', 'mail_members')) {
    $ilDB->addTableColumn(
        'event',
        'mail_members',
        [
            "notnull" => true,
            "length" => 1,
            "type" => "integer",
            'default' => 0
        ]
    );
}
?>

<#5395>
<?php
if (!$ilDB->tableColumnExists('event_participants', 'contact')) {
    $ilDB->addTableColumn(
        'event_participants',
        'contact',
        [
            "notnull" => true,
            "length" => 1,
            "type" => "integer",
            'default' => 0
        ]
    );
}
?>
<#5396>
<?php
if (!$ilDB->tableExists('post_conditions')) {
    $ilDB->createTable('post_conditions', array(
        'ref_id' => array(
            "type" => "integer",
            "length" => 4,
            'notnull' => true
        ),
        'condition_type' => array(
            "type" => "integer",
            "length" => 4,
            'notnull' => true
        ),
        'value' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        )
    ));
    $ilDB->addPrimaryKey("post_conditions", array("ref_id", "condition_type", "value"));
}
?>

<#5397>
<?php
$ilSetting = new ilSetting('certificate');
$setting = $ilSetting->set('persisting_cers_introduced_ts', time());
?>

<#5398>
<?php
// migration of svy offline status
$query = 'update object_data od set offline = ' .
    '(select if( online_status = 0,1,0) from tst_tests ' .
    'where obj_fi = od.obj_id) where type = ' . $ilDB->quote('tst', 'text');
$ilDB->manipulate($query);
?>

<#5399>
<?php
if (!$ilDB->tableExists('lso_states')) {
    $ilDB->createTable('lso_states', array(
        'lso_ref_id' => array(
            "type" => "integer",
            "length" => 4,
            'notnull' => true
        ),
        'usr_id' => array(
            "type" => "integer",
            "length" => 4,
            'notnull' => true
        ),
        'current_item' => array(
            "type" => "integer",
            "length" => 4,
            "default" => null
        ),
        'states' => array(
            "type" => "clob"
        )
    ));
    $ilDB->addPrimaryKey("lso_states", array("lso_ref_id", "usr_id"));
}
?>

<#5400>
<?php
global $ilDB;

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lso_type_id = ilDBUpdateNewObjectType::addNewType('lso', 'Learning Sequence');

$rbac_ops = array(
    ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
    ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
    ilDBUpdateNewObjectType::RBAC_OP_READ,
    ilDBUpdateNewObjectType::RBAC_OP_WRITE,
    ilDBUpdateNewObjectType::RBAC_OP_DELETE,
    ilDBUpdateNewObjectType::RBAC_OP_COPY
);
ilDBUpdateNewObjectType::addRBACOperations($lso_type_id, $rbac_ops);

$parent_types = array('root', 'cat', 'crs', 'fold', 'grp');
ilDBUpdateNewObjectType::addRBACCreate('create_lso', 'Create Learning Sequence', $parent_types);
ilDBUpdateNewObjectType::applyInitialPermissionGuideline('lso', true);

if ($lso_type_id) {
    ilDBUpdateNewObjectType::addRBACTemplate(
        'lso',
        'il_lso_admin',
        'Admin template for learning sequences',
        array(
            ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
            ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
            ilDBUpdateNewObjectType::RBAC_OP_READ,
            ilDBUpdateNewObjectType::RBAC_OP_WRITE,
            ilDBUpdateNewObjectType::RBAC_OP_DELETE,
            ilDBUpdateNewObjectType::RBAC_OP_COPY,
            $lso_type_id
        )
    );
    ilDBUpdateNewObjectType::addRBACTemplate(
        'lso',
        'il_lso_member',
        'Member template for learning sequences',
        array(
            ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
            ilDBUpdateNewObjectType::RBAC_OP_READ,
            $lso_type_id
        )
    );
}
?>

<#5401>
<?php
if (!$ilDB->tableExists('lso_settings')) {
    $ilDB->createTable('lso_settings', array(
        'obj_id' => array(
            "type" => "integer",
            "length" => 4,
            'notnull' => true
        ),
        'abstract' => array(
            "type" => "clob"
        ),
        'extro' => array(
            "type" => "clob"
        ),
        'abstract_image' => array(
            'type' => 'text',
            'length' => 128,
            'default' => null,
        ),
        'extro_image' => array(
            'type' => 'text',
            'length' => 128,
            'default' => null,
        )
    ));
    $ilDB->addPrimaryKey("lso_settings", array("obj_id"));
}
?>

<#5402>
<?php
if (!$ilDB->tableColumnExists('lso_settings', 'online')) {
    $ilDB->addTableColumn('lso_settings', 'online', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}
?>

<#5403>
<?php
if (!$ilDB->tableColumnExists('lso_settings', 'gallery')) {
    $ilDB->addTableColumn('lso_settings', 'gallery', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}
?>

<#5404>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId("lso");
if ($lp_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'participate',
        'Participate to Learning Sequence',
        'object',
        9950
    );
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $new_ops_id);
    }
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'unparticipate',
        'Unparticipate from Learning Sequence',
        'object',
        9960
    );
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $new_ops_id);
    }

    $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("manage_members");
    if ($ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ops_id);
    }

    $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("edit_learning_progress");
    if ($ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ops_id);
    }
}
?>

<#5405>
<?php
if (!$ilDB->tableColumnExists('lso_states', 'first_access')) {
    $ilDB->addTableColumn('lso_states', 'first_access', array(
        "type" => "text",
        "notnull" => false,
        "length" => 32,
    ));
}
?>

<#5406>
<?php
if (!$ilDB->tableColumnExists('lso_states', 'last_access')) {
    $ilDB->addTableColumn('lso_states', 'last_access', array(
        "type" => "text",
        "notnull" => false,
        "length" => 32,
    ));
}
?>

<#5407>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$lso_type_id = ilDBUpdateNewObjectType::getObjectTypeId("lso");

$op_ids = [
    ilDBUpdateNewObjectType::getCustomRBACOperationId("manage_members"),
    ilDBUpdateNewObjectType::getCustomRBACOperationId("edit_learning_progress"),
    ilDBUpdateNewObjectType::getCustomRBACOperationId("unparticipate"),
    ilDBUpdateNewObjectType::getCustomRBACOperationId("participate")
];

foreach ($op_ids as $op_id) {
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" . PHP_EOL
        . "VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($lso_type_id, "lso", $op_id, 8)
    )
    ;
}

$ilCtrlStructureReader->getStructure();
?>
<#5408>
<?php
$ilCtrlStructureReader->getStructure();
// migration of scorm offline status
$query = 'update object_data od set offline = ' .
    '(select if( c_online = ' . $ilDB->quote('n', 'text') . ',1,0) from sahs_lm ' .
    'where id = od.obj_id) where type = ' . $ilDB->quote('sahs', 'text');
$ilDB->manipulate($query);

?>

<#5409>
<?php

if (!$ilDB->tableExists('il_meta_oer_stat')) {
    $ilDB->createTable('il_meta_oer_stat', array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'href_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'blocked' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    ));
}
?>
<#5410>
<?php

if ($ilDB->tableExists('il_md_cpr_selections')) {
    if (!$ilDB->tableColumnExists('il_md_cpr_selections', 'is_default')) {
        $ilDB->addTableColumn('il_md_cpr_selections', 'is_default', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }

    $id = $ilDB->nextId('il_md_cpr_selections');
    $ilDB->insert(
        "il_md_cpr_selections",
        array(
            'entry_id' => array('integer',$id),
            'title' => array('text', 'All rights reserved'),
            'description' => array('clob', ''),
            'copyright' => array('clob', 'This work has all rights reserved by the owner.'),
            'language' => array('text', 'en'),
            'costs' => array('integer', '0'),
            'cpr_restrictions' => array('integer', '1'),
            'is_default' => array('integer', '1')
        )
    );
}
?>
<#5411>
<?php
if ($ilDB->tableExists('il_md_cpr_selections')) {
    if (!$ilDB->tableColumnExists('il_md_cpr_selections', 'outdated')) {
        $ilDB->addTableColumn('il_md_cpr_selections', 'outdated', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
}
?>
<#5412>
<?php
if ($ilDB->tableExists('il_md_cpr_selections')) {
    if (!$ilDB->tableColumnExists('il_md_cpr_selections', 'position')) {
        $ilDB->addTableColumn('il_md_cpr_selections', 'position', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
}
?>
<#5413>
<?php
if (!$ilDB->tableColumnExists('crs_settings', 'timing_mode')) {
    $ilDB->addTableColumn(
        'crs_settings',
        'timing_mode',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5414>
<?php
if (!$ilDB->tableColumnExists('crs_items', 'suggestion_start_rel')) {
    $ilDB->addTableColumn(
        'crs_items',
        'suggestion_start_rel',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5415>
<?php

if (!$ilDB->tableColumnExists('crs_items', 'suggestion_end_rel')) {
    $ilDB->addTableColumn(
        'crs_items',
        'suggestion_end_rel',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5416>
<?php

if (!$ilDB->tableColumnExists('crs_items', 'earliest_start_rel')) {
    $ilDB->addTableColumn(
        'crs_items',
        'earliest_start_rel',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5417>
<?php

if (!$ilDB->tableColumnExists('crs_items', 'latest_end_rel')) {
    $ilDB->addTableColumn(
        'crs_items',
        'latest_end_rel',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>

<#5418>
<?php

if ($ilDB->tableColumnExists('crs_items', 'earliest_start')) {
    $ilDB->dropTableColumn('crs_items', 'earliest_start');
}
if ($ilDB->tableColumnExists('crs_items', 'latest_end')) {
    $ilDB->dropTableColumn('crs_items', 'latest_end');
}
if ($ilDB->tableColumnExists('crs_items', 'earliest_start_rel')) {
    $ilDB->dropTableColumn('crs_items', 'earliest_start_rel');
}
if ($ilDB->tableColumnExists('crs_items', 'latest_end_rel')) {
    $ilDB->dropTableColumn('crs_items', 'latest_end_rel');
}
?>
<#5419>
<?php
if (!$ilDB->tableExists('crs_timings_user')) {
    $ilDB->createTable('crs_timings_user', array(
        'ref_id' => array(
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
        'sstart' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ssend' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('crs_timings_user', array('ref_id', 'usr_id'));
}
?>
<#5420>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('read_results', 'Access Results', 'object', 2500);
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('svy');
if ($type_id && $new_ops_id) {
    ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
}
?>

<#5421>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_results');
ilDBUpdateNewObjectType::cloneOperation('svy', $src_ops_id, $tgt_ops_id);
?>
<#5422>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5423>
<?php
// Possibly missing primaries
$ilDB->modifyTableColumn('il_mm_translation', 'identification', array(
    'length' => 255
));

$ilDB->modifyTableColumn('il_gs_providers', 'provider_class', array(
    'length' => 255
));

$ilDB->modifyTableColumn('il_gs_providers', 'purpose', array(
    'length' => 255
));

$ilDB->modifyTableColumn('il_gs_identifications', 'provider_class', array(
    'length' => 255
));

$ilDB->modifyTableColumn('il_mm_custom_items', 'identifier', array(
    'length' => 255
));

$ilDB->modifyTableColumn('il_mm_actions', 'identification', array(
    'length' => 255
));


$manager = $ilDB->loadModule('Manager');

$const = $manager->listTableConstraints("il_mm_translation");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('il_mm_translation', array( 'id' ));
}
$const = $manager->listTableConstraints("il_gs_providers");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('il_gs_providers', array('provider_class'));
}
$const = $manager->listTableConstraints("il_gs_identifications");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('il_gs_identifications', array('identification'));
}
$const = $manager->listTableConstraints("il_mm_custom_items");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('il_mm_custom_items', array( 'identifier' ));
}
$const = $manager->listTableConstraints("il_mm_actions");
if (!in_array("primary", $const)) {
    $ilDB->addPrimaryKey('il_mm_actions', array( 'identification' ));
}
    
?>
<#5424>
<?php
if (!$ilDB->tableExists('booking_member')) {
    $ilDB->createTable('booking_member', array(
        'participant_id' => array(
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
        'booking_pool_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'assigner_user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('booking_member', array('participant_id', 'user_id', 'booking_pool_id'));
    $ilDB->createSequence('booking_member');
}
?>
<#5425>
<?php
if (!$ilDB->tableColumnExists('booking_reservation', 'assigner_id')) {
    $ilDB->addTableColumn("booking_reservation", "assigner_id", array("type" => "integer", "length" => 4, "notnull" => true, "default" => 0));
}
?>
<#5426>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5427>
<?php
$setting = new ilSetting();
$media_cont_mig = $setting->get('sty_media_cont_mig', 0);
if ($media_cont_mig == 0) {
    echo "<pre>

	DEAR ADMINISTRATOR !!

	Please read the following instructions CAREFULLY!

	-> If you are using content styles (e.g. for learning modules) style settings related
	to media container have been lost when migrating from ILIAS 5.0/5.1 to ILIAS 5.2/5.3/5.4.

	-> The following dbupdate step will fix this issue and set the media container properties to values
	   before the upgrade to ILIAS 5.2/5.3/5.4.

	-> If this issue has already been fixed manually in your content styles you may want to skip
	   this step. If you are running ILIAS 5.2/5.3/5.4 for a longer time period you may also not want to
	   restore old values anymore and skip this step.
	   If you would like to skip this step you need to modify the file setup/sql/dbupdate_04.php
	   Search for 'RUN_CONTENT_STYLE_MIGRATION' (around line 25205) and follow the instructions.
	
	=> To proceed the update process you now need to refresh the page (F5)

	Mantis Bug Report: https://ilias.de/mantis/view.php?id=23299

	</pre>";

    $setting->set('sty_media_cont_mig', 1);
    exit;
}
if ($media_cont_mig == 1) {
    //
    // RUN_CONTENT_STYLE_MIGRATION
    //
    // If you want to skip the migration of former style properties for the media container style classes
    // set the following value of $run_migration from 'true' to 'false'.
    //

    $run_migration = true;

    if ($run_migration) {
        $set = $ilDB->queryF(
            "SELECT * FROM style_parameter " .
            " WHERE type = %s AND tag = %s ",
            array("text", "text"),
            array("media_cont", "table")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $set2 = $ilDB->queryF(
                "SELECT * FROM style_parameter " .
                " WHERE style_id = %s " .
                " AND tag = %s " .
                " AND class = %s " .
                " AND parameter = %s " .
                " AND type = %s " .
                " AND mq_id = %s ",
                array("integer", "text", "text", "text", "text", "integer"),
                array($rec["style_id"], "figure", $rec["class"], $rec["parameter"], "media_cont", $rec["mq_id"])
            );
            if (!($rec2 = $ilDB->fetchAssoc($set2))) {
                $id = $ilDB->nextId("style_parameter");
                $ilDB->insert("style_parameter", array(
                    "id" => array("integer", $id),
                    "style_id" => array("integer", $rec["style_id"]),
                    "tag" => array("text", "figure"),
                    "class" => array("text", $rec["class"]),
                    "parameter" => array("text", $rec["parameter"]),
                    "value" => array("text", $rec["value"]),
                    "type" => array("text", $rec["type"]),
                    "mq_id" => array("integer", $rec["mq_id"]),
                    "custom" => array("integer", $rec["custom"]),
                ));
            }
        }
    }
    $setting->set('sty_media_cont_mig', 2);
}
?>