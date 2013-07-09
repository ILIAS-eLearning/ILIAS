<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI
 * @author     Michael Jansen <mjansen@datababay.de>
 * @version    $Id $
 * @ingroup    ModulesTest
 */
class ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI extends ilTable2GUI
{
	const PARENT_DEFAULT_CMD      = 'showManScoringByQuestionParticipantsTable';
	const PARENT_APPLY_FILTER_CMD = 'applyManScoringByQuestionFilter';
	const PARENT_RESET_FILTER_CMD = 'resetManScoringByQuestionFilter';
	const PARENT_SAVE_SCORING_CMD = 'saveManScoringByQuestion';

	public function __construct($parentObj)
	{
		$this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
		$this->setResetCommand(self::PARENT_RESET_FILTER_CMD);

		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$this->setId('man_scor_by_qst_' . $parentObj->object->getId());

		parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);

		$this->disable('sort');

		$this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));

		$this->setRowTemplate("tpl.il_as_tst_man_scoring_by_question_tblrow.html", "Modules/Test");

		$this->setShowRowsSelector(true);

		$this->addCommandButton(self::PARENT_SAVE_SCORING_CMD, $this->lng->txt('save'));

		$this->initColumns();
		$this->initFilter();
	}

	private function initColumns()
	{
		$this->addColumn($this->lng->txt('name'), 'lastname', '40%');
		$this->addColumn($this->lng->txt('tst_reached_points'), 'lastname', '40%');
		$this->addColumn('', '', '20%');
	}

	public function initFilter()
	{
		$this->setDisableFilterHiding(true);

		include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		$available_questions = new ilSelectInputGUI($this->lng->txt('question'), 'question');
		$select_questions    = array();
		if(!$this->getParentObject()->object->isRandomTest())
		{
			$questions = $this->getParentObject()->object->getTestQuestions();
		}
		else
		{
			$questions = $this->getParentObject()->object->getPotentialRandomTestQuestions();
		}
		$scoring = ilObjAssessmentFolder::_getManualScoring();
		foreach($questions as $data)
		{
			include_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
			$info = assQuestion::_getQuestionInfo($data['question_id']);
			$type = $info["question_type_fi"];
			if(in_array($type, $scoring))
			{
				$maxpoints = assQuestion::_getMaximumPoints($data["question_id"]);
				if($maxpoints == 1)
				{
					$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')';
				}
				else
				{
					$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')';
				}
				
				$select_questions[$data["question_id"]] = $data['title'] . $maxpoints. ' ['. $this->lng->txt('question_id_short') . ': ' . $data["question_id"]  . ']';
			}
		}
		if(!$select_questions)
		{
			$select_questions[0] = $this->lng->txt('tst_no_scorable_qst_available');
		}
		$available_questions->setOptions(array('' => $this->lng->txt('please_choose')) + $select_questions);
		$this->addFilterItem($available_questions);
		$available_questions->readFromSession();
		$this->filter['question'] = $available_questions->getValue();

		$pass     = new ilSelectInputGUI($this->lng->txt('pass'), 'pass');
		$passes   = array();
		$max_pass = $this->getParentObject()->object->getMaxPassOfTest();
		for($i = 1; $i <= $max_pass; $i++)
		{
			$passes[$i] = $i;
		}
		$pass->setOptions($passes);
		$this->addFilterItem($pass);
		$pass->readFromSession();
		$this->filter['pass'] = $pass->getValue();
	}

	/**
	 * @global    ilCtrl     $ilCtrl
	 * @global    ilLanguage $lng
	 * @param    array       $row
	 */
	public function fillRow($row)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$this->tpl->setVariable('VAL_NAME', $row['participant']->getName());
		$reachted_points = new ilNumberInputGUI('', 'scoring[' . $row['pass_id'] . '][' . $row['active_id'] . '][' . $row['qst_id'] . ']');
		$reachted_points->setValue($row['reached_points']);
		$reachted_points->setSize(5);
		$this->tpl->setVariable('VAL_REACHED_POINTS', $reachted_points->render());

		$ilCtrl->setParameter($this->getParentObject(), 'qst_id', $row['qst_id']);
		$ilCtrl->setParameter($this->getParentObject(), 'active_id', $row['active_id']);
		$ilCtrl->setParameter($this->getParentObject(), 'pass_id', $row['pass_id']);
		$this->tpl->setVariable('VAL_LINK_ANSWER', $ilCtrl->getLinkTarget($this->getParentObject(), 'getAnswerDetail'));
		$ilCtrl->setParameter($this->getParentObject(), 'qst_id', '');
		$ilCtrl->setParameter($this->getParentObject(), 'active_id', '');
		$ilCtrl->setParameter($this->getParentObject(), 'pass_id', '');
		$this->tpl->setVariable('VAL_TXT_ANSWER', $this->lng->txt('tst_eval_show_answer'));
		
	}
}
