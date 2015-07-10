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
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilToolbarGUI
 */
class ilAssQuestionSkillAssignmentsGUI
{
	const CMD_SHOW_SKILL_QUEST_ASSIGNS = 'showSkillQuestionAssignments';
	const CMD_SAVE_SKILL_POINTS = 'saveSkillPoints';
	const CMD_SHOW_SKILL_SELECT = 'showSkillSelection';
	const CMD_UPDATE_SKILL_QUEST_ASSIGNS = 'updateSkillQuestionAssignments';
	const CMD_SHOW_SKILL_QUEST_ASSIGN_EDIT_FORM = 'showSkillQuestionAssignmentEditForm';
	const CMD_SAVE_SKILL_QUEST_ASSIGN_EDIT_FORM = 'saveSkillQuestionAssignmentEditForm';
	
	const PARAM_SKILL_SELECTION = 'skill_ids';
	
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

	private function updateSkillQuestionAssignmentsCmd()
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		
		$questionId = (int)$_GET['question_id'];

		if( $this->isTestQuestion($questionId) )
		{
			$assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
			$assignmentList->setParentObjId($this->getParentObjId());
			$assignmentList->loadFromDb();

			$handledSkills = array();
			
			$skillIds = (array)$_POST['skill_ids'];
			
			foreach($skillIds as $skillId)
			{
				$skill = explode(':',$skillId);
				$skillBaseId = (int)$skill[0];
				$skillTrefId = (int)$skill[1];
				
				if( $skillBaseId )
				{
					if( !$assignmentList->isAssignedToQuestionId($skillBaseId, $skillTrefId, $questionId) )
					{
						$assignment = new ilAssQuestionSkillAssignment($this->db);

						$assignment->setParentObjId($this->getParentObjId());
						$assignment->setQuestionId($questionId);
						$assignment->setSkillBaseId($skillBaseId);
						$assignment->setSkillTrefId($skillTrefId);

						$assignment->setSkillPoints(ilAssQuestionSkillAssignment::DEFAULT_COMPETENCE_POINTS);
						$assignment->saveToDb();
					}
					
					$handledSkills[$skillId] = $skill;
				}
			}
			
			foreach($assignmentList->getAssignmentsByQuestionId($questionId) as $assignment)
			{
				if( isset($handledSkills["{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}"]) )
				{
					continue;
				}

				$assignment->deleteFromDb();
			}
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
	}

	private function showSkillSelectionCmd()
	{
		$this->ctrl->saveParameter($this, 'question_id');
		$questionId = (int)$_GET['question_id'];

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
		$assignmentList->setParentObjId($this->getParentObjId());
		$assignmentList->loadFromDb();

		$skillSelectorExplorerGUI = $this->buildSkillSelectorExplorerGUI(
			$assignmentList->getAssignmentsByQuestionId($questionId)
		);

		if( !$skillSelectorExplorerGUI->handleCommand() )
		{
			$skillSelectorToolbarGUI = $this->buildSkillSelectorToolbarGUI();

			$skillSelectorToolbarGUI->setOpenFormTag(true);
			$skillSelectorToolbarGUI->setCloseFormTag(false);
			$skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("arrow_upright.png"), " ");
			$html = $this->ctrl->getHTML($skillSelectorToolbarGUI);
			
			$html .= $this->ctrl->getHTML($skillSelectorExplorerGUI).'<br />';

			$skillSelectorToolbarGUI->setOpenFormTag(false);
			$skillSelectorToolbarGUI->setCloseFormTag(true);
			$skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("arrow_downright.png"), " ");
			$html .= $this->ctrl->getHTML($skillSelectorToolbarGUI);
			
			$this->tpl->setContent($html);
		}
	}

	// perhaps we can keep?
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
	
	private function showSkillQuestionAssignmentEditFormCmd()
	{
		$form = 
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

	/**
	 * @return ilSkillSelectorGUI
	 */
	private function buildSkillSelectorExplorerGUI($assignments)
	{
		require_once 'Services/Skill/classes/class.ilSkillSelectorGUI.php';

		$skillSelectorExplorerGUI = new ilSkillSelectorGUI(
			$this, self::CMD_SHOW_SKILL_SELECT, $this, self::CMD_UPDATE_SKILL_QUEST_ASSIGNS, self::PARAM_SKILL_SELECTION
		);

		$skillSelectorExplorerGUI->setSelectMode(self::PARAM_SKILL_SELECTION, true);
		$skillSelectorExplorerGUI->setNodeOnclickEnabled(false);
		
		// parameter name for skill selection is actually taken from value passed to constructor,
		// but passing a non empty name to setSelectMode is neccessary to keep input fields enabled

		foreach($assignments as $assignment)
		{
			$id = "{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}";
			$skillSelectorExplorerGUI->setNodeSelected($id);
		}

		return $skillSelectorExplorerGUI;
	}

	/**
	 * @return ilToolbarGUI
	 */
	private function buildSkillSelectorToolbarGUI()
	{
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

		$skillSelectorToolbarGUI = new ilToolbarGUI();

		$skillSelectorToolbarGUI->setFormAction($this->ctrl->getFormAction($this));
		$skillSelectorToolbarGUI->addFormButton($this->lng->txt('assign'), self::CMD_UPDATE_SKILL_QUEST_ASSIGNS);
		$skillSelectorToolbarGUI->addFormButton($this->lng->txt('cancel'), self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
		
		return $skillSelectorToolbarGUI;
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
