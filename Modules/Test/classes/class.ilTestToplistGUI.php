<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Modules/Test/classes/class.ilTestTopList.php';
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
	 * @var ilTestTopList
	 */
	protected $toplist;

	/**
	 * @param ilObjTestGUI $a_object
	 */
	public function __construct(ilObjTestGUI $a_object)
	{
		$this->object = $a_object->object;
		$this->toplist = new ilTestTopList($a_object->object);
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

		$cmd = $ilCtrl->getCmd();

		$ilCtrl->saveParameter($this, 'active_id');

		switch($cmd)
		{
			case 'showResultsToplistByTime':
				$this->manageTabs($ilTabs, $ilCtrl, $lng, 'toplist_by_time');
				$this->showResultsToplistByTime();
				break;

			case 'showResultsToplistByScore':
			default:
				$this->manageTabs($ilTabs, $ilCtrl, $lng, 'toplist_by_score');
				$this->showResultsToplistByScore();
		}
	}
	
	protected function manageTabs(ilTabsGUI $tabsGUI, ilCtrl $ctrl, ilLanguage $lng, $activeTabId)
	{
		$tabsGUI->clearTargets();

		$tabsGUI->setBackTarget(
			$lng->txt('tst_results_back_introduction'), $ctrl->getLinkTargetByClass('ilObjTestGUI', 'infoScreen')
		);

		$tabsGUI->addTab(
			'toplist_by_score', $lng->txt('toplist_by_score'), $ctrl->getLinkTarget($this, 'showResultsToplistByScore')
		);
		
		$tabsGUI->addTab(
			'toplist_by_time', $lng->txt('toplist_by_time'), $ctrl->getLinkTarget($this, 'showResultsToplistByTime')
		);

		$tabsGUI->setTabActive($activeTabId);
	}

	public function showResultsToplistByScore()
	{
		global $ilUser, $lng, $tpl;

		$html = '';

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_OWN_TABLE)
		{
			$table_gui = new ilTable2GUI($this);
			$this->prepareTable($table_gui);

			$data = $this->toplist->getGeneralToplistByPercentage($_GET['ref_id'], $ilUser->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->toplist->getUserToplistByPercentage($_GET['ref_id'], $ilUser->getID());

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

			$data = $this->toplist->getGeneralToplistByWorkingtime($_GET['ref_id'], $ilUser->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->toplist->getUserToplistByWorkingtime($_GET['ref_id'], $ilUser->getID());

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
}