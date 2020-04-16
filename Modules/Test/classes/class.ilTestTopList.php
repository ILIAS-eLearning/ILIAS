<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestTopList
 */
class ilTestTopList
{
    /** @var $object ilObjTest */
    protected $object;

    /**
     * @param ilObjTest $a_object
     */
    public function __construct(ilObjTest $a_object)
    {
        $this->object = $a_object;
    }

    /**
     * @param int $a_test_ref_id
     * @param int $a_user_id
     * @return array
     */
    public function getUserToplistByWorkingtime($a_test_ref_id, $a_user_id)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Get placement of user
        $result = $ilDB->query(
            '
			SELECT count(tst_pass_result.workingtime) as count
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND workingtime <
			(
				SELECT workingtime
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
		'
        );

        $row = $ilDB->fetchAssoc($result);
        $better_participants = $row['count'];
        $own_placement = $better_participants + 1;

        $result = $ilDB->query(
            '
			SELECT count(tst_pass_result.workingtime) as count
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer')
        );
        $row = $ilDB->fetchAssoc($result);
        $number_total = $row['count'];

        $result = $ilDB->query(
            '
		SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage ,
			tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
		FROM object_reference
		INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
		INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
		INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
		INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
			AND tst_pass_result.pass = tst_result_cache.pass
		INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi

		WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
		AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '

		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND workingtime >=
			(
				SELECT tst_pass_result.workingtime
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
			ORDER BY workingtime DESC
			LIMIT 0,3
		)
		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND workingtime <
			(
				SELECT tst_pass_result.workingtime
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
			ORDER BY workingtime DESC
			LIMIT 0,3
		)
		ORDER BY workingtime ASC
		LIMIT 0, 7
		'
        );

        $i = $own_placement - (($better_participants >= 3) ? 3 : $better_participants);

        $data = array();

        if ($i > 1) {
            $item = array('Rank' => '...');
            $data[] = $item;
        }

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            $item = $this->getResultTableRow($row, $i, $a_user_id);
            $i++;
            $data[] = $item;
        }

        if ($number_total > $i) {
            $item = array('Rank' => '...');
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param int $a_test_ref_id
     * @param int $a_user_id
     * @return array
     */
    public function getGeneralToplistByPercentage($a_test_ref_id, $a_user_id)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            '
			SELECT tst_result_cache.*, round(points/maxpoints*100,2) as percentage, tst_pass_result.workingtime, usr_data.usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			ORDER BY percentage DESC
			LIMIT 0, ' . $ilDB->quote($this->object->getHighscoreTopNum(), 'integer') . '
			'
        );
        $i = 0;
        $data = array();
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            $i++;
            $item = $this->getResultTableRow($row, $i, $a_user_id);

            $data[] = $item;
        }
        return $data;
    }

    /**
     * @param int $a_test_ref_id
     * @param int $a_user_id
     * @return array
     */
    public function getGeneralToplistByWorkingtime($a_test_ref_id, $a_user_id)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->query(
            '
			SELECT tst_result_cache.*, round(points/maxpoints*100,2) as percentage, tst_pass_result.workingtime, usr_data.usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			ORDER BY workingtime ASC
			LIMIT 0, ' . $ilDB->quote($this->object->getHighscoreTopNum(), 'integer') . '
			'
        );
        $i = 0;
        $data = array();
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            $i++;
            $item = $this->getResultTableRow($row, $i, $a_user_id);
            $data[] = $item;
        }
        return $data;
    }

    /**
     * @param int $a_test_ref_id
     * @param int $a_user_id
     * @return array
     */
    public function getUserToplistByPercentage($a_test_ref_id, $a_user_id)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Get placement of user
        $result = $ilDB->query(
            '
			SELECT count(tst_pass_result.workingtime) as count
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND round(reached_points/max_points*100) >=
			(
				SELECT round(reached_points/max_points*100)
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
		'
        );

        $row = $ilDB->fetchAssoc($result);
        $better_participants = $row['count'];
        $own_placement = $better_participants + 1;

        $result = $ilDB->query(
            '
			SELECT count(tst_pass_result.workingtime) as count
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer')
        );
        $row = $ilDB->fetchAssoc($result);
        $number_total = $row['count'];

        $result = $ilDB->query(
            '
		SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage ,
			tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
		FROM object_reference
		INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
		INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
		INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
		INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
			AND tst_pass_result.pass = tst_result_cache.pass
		INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi

		WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
		AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '

		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND round(reached_points/max_points*100) >=
			(
				SELECT round(reached_points/max_points*100)
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
			ORDER BY round(reached_points/max_points*100) ASC
			LIMIT 0,3
		)
		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $ilDB->quote($a_user_id, 'integer') . '
			AND round(reached_points/max_points*100) <=
			(
				SELECT round(reached_points/max_points*100)
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $ilDB->quote($a_user_id, 'integer') . '
			)
			ORDER BY round(reached_points/max_points*100) ASC
			LIMIT 0,3
		)
		ORDER BY round(reached_points/max_points*100) DESC, tstamp ASC
		LIMIT 0, 7
		'
        );

        $i = $own_placement - (($better_participants >= 3) ? 3 : $better_participants);

        $data = array();

        if ($i > 1) {
            $item = array('Rank' => '...');
            $data[] = $item;
        }

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $ilDB->fetchAssoc($result)) {
            $item = $this->getResultTableRow($row, $i, $a_user_id);
            $i++;
            $data[] = $item;
        }

        if ($number_total > $i) {
            $item = array('Rank' => '...');
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param array $row
     * @param int   $i
     * @param int   $a_user_id
     * @return array
     */
    private function getResultTableRow($row, $i, $a_user_id)
    {
        $item = array();
        $item['Rank'] = $i . '. ';

        if ($this->object->isHighscoreAnon() && $row['usr_id'] != $a_user_id) {
            $item['Participant'] = "-, -";
        } else {
            $item['Participant'] = $row['lastname'] . ', ' . $row['firstname'];
        }

        if ($this->object->getHighscoreAchievedTS()) {
            $item['Achieved'] = new ilDateTime($row['tstamp'], IL_CAL_UNIX);
        }

        if ($this->object->getHighscoreScore()) {
            $item['Score'] = $row['reached_points'] . ' / ' . $row['max_points'];
        }

        if ($this->object->getHighscorePercentage()) {
            $item['Percentage'] = $row['percentage'] . '%';
        }

        if ($this->object->getHighscoreHints()) {
            $item['Hints'] = $row['hint_count'];
        }

        if ($this->object->getHighscoreWTime()) {
            $item['time'] = $this->formatTime($row['workingtime']);
        }

        $item['Highlight'] = ($row['usr_id'] == $a_user_id) ? 'tblrowmarked' : '';
        return $item;
    }

    /**
     * @param int $seconds
     * @return string
     */
    private function formatTime($seconds)
    {
        $retval = '';
        $hours = intval(intval($seconds) / 3600);
        $retval .= str_pad($hours, 2, "0", STR_PAD_LEFT) . ":";
        $minutes = intval(($seconds / 60) % 60);
        $retval .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
        $seconds = intval($seconds % 60);
        $retval .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        return $retval;
    }
}
