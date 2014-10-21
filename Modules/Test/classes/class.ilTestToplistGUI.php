<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Scoring class for tests
 * @author     Maximilian Becker <mbecker@databay.de>
 * @version    $Id$
 * @ingroup    ModulesTest
 */
class ilTestToplistGUI
{
	/** @var $object ilObjTest */
	protected $object;

	/**
	 * @param ilObjTestGUI $a_object
	 */
	public function __construct($a_object)
	{
		$this->object = $a_object->object;
	}

	public function executeCommand()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $ilTabs, $lng;

		if(!$this->object->getHighscoreEnabled())
		{
			ilUtil::sendFailure($lng->txt('permission_denied'), true);
			$ilCtrl->redirectByClass('ilObjTestGUI');
		}

		$ilTabs->activateTab('info_short');
		$ilTabs->addSubTabTarget('toplist_by_score', $ilCtrl->getLinkTarget($this, 'showResultsToplistByScore'), array('outResultsToplist', 'showResultsToplistByScore'));
		$ilTabs->addSubTabTarget('toplist_by_time', $ilCtrl->getLinkTarget($this, 'showResultsToplistByTime'), array('showResultsToplistByTime'));

		$cmd = $ilCtrl->getCmd();

		$ilCtrl->saveParameter($this, 'active_id');

		switch($cmd)
		{
			case 'showResultsToplistByScore':
				$ilTabs->setSubTabActive('toplist_by_score');
				$this->showResultsToplistByScore();
				break;

			case 'showResultsToplistByTime':
				$ilTabs->setSubTabActive('toplist_by_time');
				$this->showResultsToplistByTime();
				break;
			default:
				$this->showResultsToplistByScore();
		}
	}

	public function showResultsToplistByScore()
	{
		global $ilUser, $lng, $tpl;

		$html = '';

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_OWN_TABLE)
		{
			$table_gui = new ilTable2GUI($this);
			$this->prepareTable($table_gui);

			$data = $this->getGeneralToplistByPercentage($_GET['ref_id'], $ilUser->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->getUserToplistByPercentage($_GET['ref_id'], $ilUser->getID());

			$table_gui2->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui2->setData($data2);
			$table_gui2->setTitle($lng->txt('toplist_your_result'));

			$html .= $table_gui2->getHTML();
		}

		$tpl->setVariable("ADM_CONTENT", $html);
	}

	public function showResultsToplistByTime()
	{
		global $ilUser, $lng, $tpl;

		$html = '';

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_OWN_TABLE)
		{
			$table_gui = new ilTable2GUI($this);
			$this->prepareTable($table_gui);

			$data = $this->getGeneralToplistByWorkingtime($_GET['ref_id'], $ilUser->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->getUserToplistByWorkingtime($_GET['ref_id'], $ilUser->getID());

			$table_gui2->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui2->setData($data2);
			$table_gui2->setTitle($lng->txt('toplist_your_result'));

			$html .= $table_gui2->getHTML();
		}

		$tpl->setVariable("ADM_CONTENT", $html);

	}

	/**
	 * @param ilTable2GUI $table_gui
	 */
	private function prepareTable(ilTable2GUI $table_gui)
	{
		global $lng;

		$table_gui->addColumn($lng->txt('toplist_col_rank'));
		$table_gui->addColumn($lng->txt('toplist_col_participant'));
		if($this->object->getHighscoreAchievedTS())
		{
			$table_gui->addColumn($lng->txt('toplist_col_achieved'));
		}

		if($this->object->getHighscoreScore())
		{
			$table_gui->addColumn($lng->txt('toplist_col_score'));
		}

		if($this->object->getHighscorePercentage())
		{
			$table_gui->addColumn($lng->txt('toplist_col_percentage'));
		}

		if($this->object->getHighscoreHints())
		{
			$table_gui->addColumn($lng->txt('toplist_col_hints'));
		}

		if($this->object->getHighscoreWTime())
		{
			$table_gui->addColumn($lng->txt('toplist_col_wtime'));
		}
		$table_gui->setEnableNumInfo(false);
		$table_gui->setLimit(10);
	}

	/**
	 * @param int $seconds
	 * @return string
	 */
	private function formatTime($seconds)
	{
		$retval = '';
		$hours  = intval(intval($seconds) / 3600);
		$retval .= str_pad($hours, 2, "0", STR_PAD_LEFT) . ":";
		$minutes = intval(($seconds / 60) % 60);
		$retval .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
		$seconds = intval($seconds % 60);
		$retval .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
		return $retval;
	}

	/**
	 * @param int $a_test_ref_id
	 * @param int $a_user_id
	 * @return array
	 */
	private function getGeneralToplistByPercentage($a_test_ref_id, $a_user_id)
	{
		/** @var ilDB $ilDB */
		global $ilDB;
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
		$i      = 0;
		$data   = array();
		/** @noinspection PhpAssignmentInConditionInspection */
		while($row = $ilDB->fetchAssoc($result))
		{
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
	private function getGeneralToplistByWorkingtime($a_test_ref_id, $a_user_id)
	{
		/** @var ilDB $ilDB */
		global $ilDB;
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
		$i      = 0;
		$data   = array();
		/** @noinspection PhpAssignmentInConditionInspection */
		while($row = $ilDB->fetchAssoc($result))
		{
			$i++;
			$item   = $this->getResultTableRow($row, $i, $a_user_id);
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
		$item         = array();
		$item['Rank'] = $i . '. ';

		if($this->object->isHighscoreAnon() && $row['usr_id'] != $a_user_id)
		{
			$item['Participant'] = "-, -";
		}
		else
		{
			$item['Participant'] = $row['lastname'] . ', ' . $row['firstname'];
		}

		if($this->object->getHighscoreAchievedTS())
		{
			$item['Achieved'] = new ilDateTime($row['tstamp'], IL_CAL_UNIX);

		}

		if($this->object->getHighscoreScore())
		{
			$item['Score'] = $row['reached_points'] . ' / ' . $row['max_points'];
		}

		if($this->object->getHighscorePercentage())
		{
			$item['Percentage'] = $row['percentage'] . '%';
		}

		if($this->object->getHighscoreHints())
		{
			$item['Hints'] = $row['hint_count'];
		}

		if($this->object->getHighscoreWTime())
		{
			$item['time'] = $this->formatTime($row['workingtime']);
		}

		$item['Highlight'] = ($row['usr_id'] == $a_user_id) ? 'tblrowmarked' : '';
		return $item;
	}

	/**
	 * @param int $a_test_ref_id
	 * @param int $a_user_id
	 * @return array
	 */
	private function getUserToplistByWorkingtime($a_test_ref_id, $a_user_id)
	{
		/** @var ilDB $ilDB */
		global $ilDB;

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

		$row                 = $ilDB->fetchAssoc($result);
		$better_participants = $row['count'];
		$own_placement       = $better_participants + 1;

		$result       = $ilDB->query(
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
		$row          = $ilDB->fetchAssoc($result);
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
		');

		$i = $own_placement - (($better_participants >= 3) ? 3 : $better_participants);

		$data = array();

		if($i > 1)
		{
			$item   = array('Rank' => '...');
			$data[] = $item;
		}

		/** @noinspection PhpAssignmentInConditionInspection */
		while($row = $ilDB->fetchAssoc($result))
		{

			$item = $this->getResultTableRow($row, $i, $a_user_id);
			$i++;
			$data[] = $item;
		}

		if($number_total > $i)
		{
			$item   = array('Rank' => '...');
			$data[] = $item;
		}

		return $data;

	}

	/**
	 * @param int $a_test_ref_id
	 * @param int $a_user_id
	 * @return array
	 */
	private function getUserToplistByPercentage($a_test_ref_id, $a_user_id)
	{
		/** @var ilDB $ilDB */
		global $ilDB;

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

		$row                 = $ilDB->fetchAssoc($result);
		$better_participants = $row['count'];
		$own_placement       = $better_participants + 1;

		$result       = $ilDB->query(
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
		$row          = $ilDB->fetchAssoc($result);
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
		');

		$i = $own_placement - (($better_participants >= 3) ? 3 : $better_participants);

		$data = array();

		if($i > 1)
		{
			$item   = array('Rank' => '...');
			$data[] = $item;
		}

		/** @noinspection PhpAssignmentInConditionInspection */
		while($row = $ilDB->fetchAssoc($result))
		{

			$item = $this->getResultTableRow($row, $i, $a_user_id);
			$i++;
			$data[] = $item;
		}

		if($number_total > $i)
		{
			$item   = array('Rank' => '...');
			$data[] = $item;
		}

		return $data;
	}

}