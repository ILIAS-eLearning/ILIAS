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
 * @ilCtrl_Calls ilTestToplistGUI: ilAccordionGUI
 */
class ilTestToplistGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	
	/**
	 * @var ilObjUser
	 */
	protected $user;
	
	/** @var $object ilObjTest */
	protected $object;

	/**
	 * @var ilTestTopList
	 */
	protected $toplist;

	/**
	 * @param ilObjTestGUI $a_object_gui
	 */
	public function __construct(ilObjTestGUI $a_object_gui)
	{
		$this->ctrl = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilCtrl'] : $GLOBALS['ilCtrl'];
		$this->tabs = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilTabs'] : $GLOBALS['ilTabs'];
		$this->tpl = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
		$this->lng = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['lng'] : $GLOBALS['lng'];
		$this->user = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilUser'] : $GLOBALS['ilUser'];
		
		$this->object = $a_object->object;
		$this->toplist = new ilTestTopList($a_object->object);
	}

	public function executeCommand()
	{
		if(!$this->object->getHighscoreEnabled())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirectByClass('ilObjTestGUI');
		}
		
		$this->ctrl->saveParameter($this, 'active_id');
		
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd)
		{
			default:
				$this->manageTabs();
				$this->showResultsToplistsCmd();
		}
	}
	
	protected function manageTabs()
	{
		$this->tabs->clearTargets();
		
		$this->tabs->setBackTarget( $this->lng->txt('tst_results_back_introduction'),
			$this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'infoScreen')
		);
	}
	
	protected function showResultsToplistsCmd()
	{
		include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
		$acc = new ilAccordionGUI();
		$acc->setId('tst_toplists');
		
		$acc->addItem($this->lng->txt('toplist_by_score'), $this->renderResultsToplistByScore());
		$acc->addItem($this->lng->txt('toplist_by_time'), $this->renderResultsToplistByTime());
		
		$this->tpl->setVariable("ADM_CONTENT", $this->ctrl->getHTML($acc));
	}
	
	protected function renderResultsToplistByScore()
	{
		$html = '';

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_OWN_TABLE)
		{
			$table_gui = new ilTable2GUI($this);
			$this->prepareTable($table_gui);

			$data = $this->toplist->getGeneralToplistByPercentage($_GET['ref_id'], $this->user->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($this->lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->toplist->getUserToplistByPercentage($_GET['ref_id'], $this->user->getID());

			$table_gui2->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui2->setData($data2);
			$table_gui2->setTitle($this->lng->txt('toplist_your_result'));

			$html .= $table_gui2->getHTML();
		}

		return $html;
	}
	
	protected function renderResultsToplistByTime()
	{
		$html = '';

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_OWN_TABLE)
		{
			$table_gui = new ilTable2GUI($this);
			$this->prepareTable($table_gui);

			$data = $this->toplist->getGeneralToplistByWorkingtime($_GET['ref_id'], $this->user->getId());

			$table_gui->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui->setData($data);
			$table_gui->setTitle(sprintf($this->lng->txt('toplist_top_n_results'), $this->object->getHighscoreTopNum()));

			$html .= $table_gui->getHTML();
		}

		if($this->object->getHighscoreMode() != ilObjTest::HIGHSCORE_SHOW_TOP_TABLE)
		{
			$table_gui2 = new ilTable2GUI($this);

			$this->prepareTable($table_gui2);

			$data2 = $this->toplist->getUserToplistByWorkingtime($_GET['ref_id'], $this->user->getID());

			$table_gui2->setRowTemplate('tpl.toplist_tbl_rows.html', 'Modules/Test');
			$table_gui2->setData($data2);
			$table_gui2->setTitle($this->lng->txt('toplist_your_result'));

			$html .= $table_gui2->getHTML();
		}

		return $html;
	}

	/**
	 * @param ilTable2GUI $table_gui
	 */
	private function prepareTable(ilTable2GUI $table_gui)
	{
		$table_gui->addColumn($this->lng->txt('toplist_col_rank'));
		$table_gui->addColumn($this->lng->txt('toplist_col_participant'));
		if($this->object->getHighscoreAchievedTS())
		{
			$table_gui->addColumn($this->lng->txt('toplist_col_achieved'));
		}

		if($this->object->getHighscoreScore())
		{
			$table_gui->addColumn($this->lng->txt('toplist_col_score'));
		}

		if($this->object->getHighscorePercentage())
		{
			$table_gui->addColumn($this->lng->txt('toplist_col_percentage'));
		}

		if($this->object->getHighscoreHints())
		{
			$table_gui->addColumn($this->lng->txt('toplist_col_hints'));
		}

		if($this->object->getHighscoreWTime())
		{
			$table_gui->addColumn($this->lng->txt('toplist_col_wtime'));
		}
		$table_gui->setEnableNumInfo(false);
		$table_gui->setLimit(10);
	}
}