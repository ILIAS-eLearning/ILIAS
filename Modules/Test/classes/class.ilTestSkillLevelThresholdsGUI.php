<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillLevelThresholdsGUI: ilTestSkillLevelThresholdsTableGUI
 */
class ilTestSkillLevelThresholdsGUI
{
	const CMD_SHOW_SKILL_THRESHOLDS = 'showSkillThresholds';
	const CMD_SAVE_SKILL_THRESHOLDS = 'saveSkillThresholds';
	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var ilObjTest
	 */
	private $testOBJ;

	public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng, ilDB $db, ilObjTest $testOBJ)
	{
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd('show') . 'Cmd';

		$this->$cmd();
	}

	private function saveSkillThresholdsCmd()
	{
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThreshold.php';

		if( is_array($_POST['threshold']) )
		{
			$threshold = $_POST['threshold'];
			$assignmentList = $this->buildSkillQuestionAssignmentList();
			$assignmentList->loadFromDb();

			foreach($assignmentList->getUniqueAssignedSkills() as $data)
			{
				$skill = $data['skill'];
				$skillKey = $data['skill_base_id'].':'.$data['skill_tref_id'];
				$levels = $skill->getLevelData();

				foreach($levels as $level)
				{
					if( isset($threshold[$skillKey]) && isset($threshold[$skillKey][$level['id']]) )
					{
						$skillLevelThreshold = new ilTestSkillLevelThreshold($this->db);

						$skillLevelThreshold->setTestId($this->testOBJ->getTestId());
						$skillLevelThreshold->setSkillBaseId($data['skill_base_id']);
						$skillLevelThreshold->setSkillTrefId($data['skill_tref_id']);
						$skillLevelThreshold->setSkillLevelId($level['id']);

						$skillLevelThreshold->setThreshold($threshold[$skillKey][$level['id']]);

						$skillLevelThreshold->saveToDb();
					}
				}
			}
		}

		ilUtil::sendSuccess($this->lng->txt('tst_msg_skl_lvl_thresholds_saved'), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SKILL_THRESHOLDS);
	}

	private function showSkillThresholdsCmd()
	{
		$table = $this->buildTableGUI();

		$skillLevelThresholdList = $this->buildSkillLevelThresholdList();
		$skillLevelThresholdList->loadFromDb();
		$table->setSkillLevelThresholdList($skillLevelThresholdList);

		$assignmentList = $this->buildSkillQuestionAssignmentList();
		$assignmentList->loadFromDb();

		$table->setData($assignmentList->getUniqueAssignedSkills());

		$this->tpl->setContent($this->ctrl->getHTML($table));
	}

	private function buildTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestSkillLevelThresholdsTableGUI.php';
		$table = new ilTestSkillLevelThresholdsTableGUI($this, self::CMD_SHOW_SKILL_THRESHOLDS, $this->ctrl, $this->lng);

		return $table;
	}

	private function buildSkillQuestionAssignmentList()
	{
		require_once 'Modules/Test/classes/class.ilTestSkillQuestionAssignmentList.php';
		$assignmentList = new ilTestSkillQuestionAssignmentList($this->db);
		$assignmentList->setTestId($this->testOBJ->getTestId());

		return $assignmentList;
	}

	private function buildSkillLevelThresholdList()
	{
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
		$thresholdList = new ilTestSkillLevelThresholdList($this->db);
		$thresholdList->setTestId($this->testOBJ->getTestId());

		return $thresholdList;
	}
}