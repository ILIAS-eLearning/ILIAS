<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionSkillAssignmentsTableGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilSkillSelectorGUI
 */
class ilAssQuestionSkillAssignmentsGUI
{
	const CMD_SHOW_SKILL_QUEST_ASSIGNS = 'showSkillQuestionAssignments';
	const CMD_SAVE_SKILL_POINTS = 'saveSkillPoints';
	const CMD_SHOW_SKILL_SELECT = 'showSkillSelection';
	const CMD_ADD_SKILL_QUEST_ASSIGN = 'addSkillQuestionAssignment';
	const CMD_REMOVE_SKILL_QUEST_ASSIGN = 'removeSkillQuestionAssignment';
	
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
	 * @var ilAssQuestionList
	 */
	private $questionList;

	/**
	 * @var integer
	 */
	private $parentObjId;

	/**
	 * @param ilCtrl $ctrl
	 * @param ilTemplate $tpl
	 * @param ilLanguage $lng
	 * @param ilDB $db
	 */
	public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng, ilDB $db)
	{
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
	}

	/**
	 * @return ilAssQuestionList
	 */
	public function getQuestionList()
	{
		return $this->questionList;
	}

	/**
	 * @param ilAssQuestionList $questionList
	 */
	public function setQuestionList($questionList)
	{
		$this->questionList = $questionList;
	}

	/**
	 * @return int
	 */
	public function getParentObjId()
	{
		return $this->parentObjId;
	}

	/**
	 * @param int $parentObjId
	 */
	public function setParentObjId($parentObjId)
	{
		$this->parentObjId = $parentObjId;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd(self::CMD_SHOW_SKILL_QUEST_ASSIGNS) . 'Cmd';

		$this->$cmd();
	}

	private function addSkillQuestionAssignmentCmd()
	{
		$questionId = (int)$_GET['question_id'];

		$skillParameter = explode(':',$_GET['selected_skill']);
		$skillBaseId = (int)$skillParameter[0];
		$skillTrefId = (int)$skillParameter[1];

		if( $this->isTestQuestion($questionId) && $skillBaseId )
		{
			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';
			$assignment = new ilAssQuestionSkillAssignment($this->db);

			$assignment->setParentObjId($this->getParentObjId());
			$assignment->setQuestionId($questionId);
			$assignment->setSkillBaseId($skillBaseId);
			$assignment->setSkillTrefId($skillTrefId);

			if( !$assignment->dbRecordExists() )
			{
				$assignment->setSkillPoints(ilAssQuestionSkillAssignment::DEFAULT_COMPETENCE_POINTS);

				$assignment->saveToDb();
			}
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
	}

	private function removeSkillQuestionAssignmentCmd()
	{
		$questionId = (int)$_GET['question_id'];
		$skillBaseId = (int)$_GET['skill_base_id'];
		$skillTrefId = (int)$_GET['skill_tref_id'];

		if( $this->isTestQuestion($questionId) && $skillBaseId )
		{
			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';
			$assignment = new ilAssQuestionSkillAssignment($this->db);

			$assignment->setParentObjId($this->getParentObjId());
			$assignment->setQuestionId($questionId);
			$assignment->setSkillBaseId($skillBaseId);
			$assignment->setSkillTrefId($skillTrefId);

			if( $assignment->dbRecordExists() )
			{
				$assignment->deleteFromDb();
			}
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
	}

	private function showSkillSelectionCmd()
	{
		$skillSelectorGUI = $this->buildSkillSelectorGUI();

		if( !$skillSelectorGUI->handleCommand() )
		{
			$this->ctrl->saveParameter($this, 'question_id');

			$this->tpl->setContent($this->ctrl->getHTML($skillSelectorGUI));
		}
	}

	private function saveSkillPointsCmd()
	{
		if( is_array($_POST['quantifiers']) )
		{
			require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';

			$success = false;
			
			foreach($_POST['quantifiers'] as $assignmentKey => $quantifier)
			{
				$assignmentKey = explode(':',$assignmentKey);
				$skillBaseId = (int)$assignmentKey[0];
				$skillTrefId = (int)$assignmentKey[1];
				$questionId = (int)$assignmentKey[2];

				if( $this->isTestQuestion($questionId) && (int)$quantifier > 0 )
				{
					$assignment = new ilAssQuestionSkillAssignment($this->db);

					$assignment->setParentObjId($this->getParentObjId());
					$assignment->setQuestionId($questionId);
					$assignment->setSkillBaseId($skillBaseId);
					$assignment->setSkillTrefId($skillTrefId);

					if( $assignment->dbRecordExists() )
					{
						$assignment->setSkillPoints((int)$quantifier);
						$assignment->saveToDb();
					}
				}
			}
		}

		ilUtil::sendSuccess($this->lng->txt('tst_msg_skl_qst_assign_points_saved'), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
	}

	private function showSkillQuestionAssignmentsCmd()
	{
		$table = $this->buildTableGUI();

		$assignmentList = $this->buildSkillQuestionAssignmentList();
		$assignmentList->loadFromDb();
		$assignmentList->loadAdditionalSkillData();
		$table->setSkillQuestionAssignmentList($assignmentList);

		$table->setData($this->questionList->getQuestionDataArray());

		$this->tpl->setContent($this->ctrl->getHTML($table));
	}

	private function buildTableGUI()
	{
		require_once 'Modules/TestQuestionPool/classes/tables/class.ilAssQuestionSkillAssignmentsTableGUI.php';
		$table = new ilAssQuestionSkillAssignmentsTableGUI($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS, $this->ctrl, $this->lng);

		return $table;
	}

	private function buildSkillQuestionAssignmentList()
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
		$assignmentList->setParentObjId($this->getParentObjId());

		return $assignmentList;
	}

	private function buildSkillSelectorGUI()
	{
		require_once 'Services/Skill/classes/class.ilSkillSelectorGUI.php';

		$skillSelectorGUI = new ilSkillSelectorGUI(
			$this, self::CMD_SHOW_SKILL_SELECT, $this, self::CMD_ADD_SKILL_QUEST_ASSIGN
		);

		return $skillSelectorGUI;
	}

	private function isTestQuestion($questionId)
	{
		foreach($this->questionList->getQuestionDataArray() as $question)
		{
			if( $question['question_id'] == $questionId )
			{
				return true;
			}
		}

		return false;
	}
}
