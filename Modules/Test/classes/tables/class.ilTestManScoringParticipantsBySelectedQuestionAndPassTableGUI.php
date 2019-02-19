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
	
	private $manPointsPostData = array();
	
	private $curQuestionMaxPoints = null;
	
	/**
	 * @var bool
	 */
	protected $first_row_rendered = false;

	protected $selectable_columns = array('finalized_evaluation', 'finalized_by', 'finalized_on');

	/**
	 * @var bool
	 */
	protected $first_row = true;

	/**
	 * @var array
	 */
	protected $selected = array();

	public function __construct($parentObj)
	{
		$this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
		$this->setResetCommand(self::PARENT_RESET_FILTER_CMD);

		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$tpl = $DIC['tpl'];

		$tpl->addJavaScript('./Services/RTE/tiny_mce_3_5_11/tiny_mce.js');

		$this->setId('man_scor_by_qst_' . $parentObj->object->getId());

		parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);

		$this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));

		$this->setRowTemplate("tpl.il_as_tst_man_scoring_by_question_tblrow.html", "Modules/Test");

		$this->setShowRowsSelector(true);

		$this->addCommandButton(self::PARENT_SAVE_SCORING_CMD, $this->lng->txt('save'));

		$this->initColumns();
		$this->initFilter();
	}

	private function initColumns()
	{
		$this->addColumn($this->lng->txt('name'), 'lastname', '20%');
		$this->addColumn($this->lng->txt('tst_reached_points'), 'reached_points', '15%');
		$this->addColumn($this->lng->txt('tst_maximum_points'), 'max_points', '15%');
		$this->addColumn($this->lng->txt('tst_feedback'), 'test', '35%');
		$this->selected = $this->getSelectedColumns();

		foreach($this->selectable_columns as $column)
		{
			if(in_array($column, $this->selected))
			{
				$this->addColumn($this->lng->txt($column), $column, '5%');
			}
		}

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
		$correction = new ilSelectInputGUI($this->lng->txt('finalized_evaluation'), 'finalize_evaluation');
		$evaluated = array($this->lng->txt('all_users'),$this->lng->txt('evaluated_users'), $this->lng->txt('not_evaluated_users'));
		$correction->setOptions($evaluated);
		$this->addFilterItem($correction);
		$correction->readFromSession();
		$this->filter['finalize_evaluation'] = $correction->getValue();
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$columns = array();
		foreach($this->selectable_columns as $column)
		{
			$columns[$column] = array(
				'txt' => $this->lng->txt($column),
				'default' => true
			);
		}
		return $columns;
	}

	/**
	 * @global    ilCtrl     $ilCtrl
	 * @global    ilLanguage $lng
	 * @param    array       $row
	 */
	public function fillRow($row)
	{
		$disable = false;
		if(array_key_exists('feedback', $row) && $row['feedback']['finalized_evaluation'] == 1)
		{
			$disable = true;
		}

		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$ilAccess = $DIC['ilAccess'];

		if ( $this->getParentObject()->object->isFullyAnonymized()  ||
			( $this->getParentObject()->object->getAnonymity() == 2 && !$ilAccess->checkAccess('write','',$this->getParentObject()->object->getRefId())))
		{
			$this->tpl->setVariable('VAL_NAME', $this->lng->txt("anonymous"));
		}
		else
		{
			$this->tpl->setVariable('VAL_NAME', $row['participant']->getName());
		}

		if(!$this->first_row_rendered)
		{
			$this->first_row_rendered = true;
			$this->tpl->touchBlock('row_js');
		}

		$this->tpl->setVariable('VAL_NAME', $row['participant']->getName());
		$reached_points = new ilNumberInputGUI('', 'scoring[' . $row['pass_id'] . '][' . $row['active_id'] . '][' . $row['qst_id'] . ']');
		$reached_points->allowDecimals(true);
		$reached_points->setSize(5);
		if( count($this->manPointsPostData) )
		{
			if( $this->isMaxPointsExceededByPostValue($row['pass_id'], $row['active_id'], $row['qst_id']) )
			{
				$reached_points->setAlert( sprintf(
						$this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'), $row['maximum_points']
				));
				
				$this->tpl->setCurrentBlock("reached_points_alert");
				$this->tpl->setVariable("REACHED_POINTS_IMG_ALERT", ilUtil::getImagePath("icon_alert.svg"));
				$this->tpl->setVariable("REACHED_POINTS_ALT_ALERT", $this->lng->txt("alert"));
				$this->tpl->setVariable("REACHED_POINTS_TXT_ALERT", $reached_points->getAlert());
				$this->tpl->parseCurrentBlock();
			}
			
			$reached_points->setValue($this->manPointsPostData[$row['pass_id']][$row['active_id']][$row['qst_id']]);
		}
		else
		{
			$reached_points->setValue($row['reached_points']);
		}
		$this->tpl->setVariable('VAL_REACHED_POINTS', $reached_points->render());

		$this->tpl->setVariable('VAL_MAX_POINTS', $row['maximum_points']);
		require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
		require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$text_area = new ilTextAreaInputGUI('', 'feedback[' . $row['pass_id'] . '][' . $row['active_id'] . '][' . $row['qst_id'] . ']');
		$text_area->setUseRte(false);
		$text_area->setDisabled($disable);

		$text_area->addPlugin('contextmenu');
		$text_area->setRteTags(array('strong', 'em', 'u', 'strike', 'p' ,'contextmenu'));

		$text_area->setValue($row['feedback']['feedback']);
		$feedback = $row['feedback']['feedback'];
		$feedback = strip_tags($feedback);
		if(strlen($feedback) > 0)
		{
			$this->tpl->touchBlock('tst_manual_scoring');
		}
		$this->tpl->setVariable('VAL_MODAL_CORRECTION', $text_area->render());

		$evaluated = new ilCheckboxInputGUI('', 'evaluated[' . $row['pass_id'] . '][' . $row['active_id'] . '][' . $row['qst_id'] . ']');
		if(in_array('finalized_evaluation', $this->selected))
		{
			if(array_key_exists('finalized_evaluation', $row['feedback']) && $row['feedback']['finalized_evaluation'] == 1)
			{
				$evaluated->setChecked(true);
			}
			$this->tpl->setVariable('VAL_EVALUATED', $evaluated->render());
		}

		if(in_array('finalized_by', $this->selected))
		{
			$fin_usr_id = $row['feedback']['finalized_by_usr_id'];
			if($fin_usr_id > 0)
			{
				$this->tpl->setVariable('VAL_FINALIZED_BY', ilObjUser::_lookupFullname($fin_usr_id));
			}
		}

		if(in_array('finalized_on', $this->selected))
		{
			$fin_timestamp = $row['feedback']['finalized_tstamp'];
			if($fin_timestamp > 0)
			{
				$time = new ilDateTime($fin_timestamp, 3);
				$this->tpl->setVariable('VAL_FINALIZED_ON', $time->get(1));
			}
		}

		$this->tpl->setVariable('VAL_PASS', $row['pass_id']);
		$this->tpl->setVariable('VAL_ACTIVE_ID',  $row['active_id']);
		$this->tpl->setVariable('VAL_QUESTION_ID', $row['qst_id']);

		if($this->first_row)
		{
			$this->tpl->touchBlock('scoring_by_question_refresher');
			$this->tpl->parseCurrentBlock();
			$this->first_row = false;
		}

		$ilCtrl->setParameter($this->getParentObject(), 'qst_id', $row['qst_id']);
		$ilCtrl->setParameter($this->getParentObject(), 'active_id', $row['active_id']);
		$ilCtrl->setParameter($this->getParentObject(), 'pass_id', $row['pass_id']);
		$this->tpl->setVariable('VAL_LINK_ANSWER', $ilCtrl->getLinkTarget($this->getParentObject(), 'getAnswerDetail', '', true, false));
		$ilCtrl->setParameter($this->getParentObject(), 'qst_id', '');
		$ilCtrl->setParameter($this->getParentObject(), 'active_id', '');
		$ilCtrl->setParameter($this->getParentObject(), 'pass_id', '');
		$this->tpl->setVariable('VAL_TXT_ANSWER', $this->lng->txt('tst_eval_show_answer'));
	}
	
	private function isMaxPointsExceededByPostValue($pass_id, $active_id, $qst_id)
	{
		if( !isset($this->manPointsPostData[$pass_id]) )
		{
			return false;
		}
		
		if( !isset($this->manPointsPostData[$pass_id][$active_id]) )
		{
			return false;
		}
		
		if( !isset($this->manPointsPostData[$pass_id][$active_id][$qst_id]) )
		{
			return false;
		}
		
		$submittedPoints = $this->manPointsPostData[$pass_id][$active_id][$qst_id];
		
		return $submittedPoints > $this->getCurQuestionMaxPoints();
	}
	
	public function setManualScoringPointsPostData($manPointsPostData)
	{
		$this->manPointsPostData = $manPointsPostData;
	}

	public function getCurQuestionMaxPoints()
	{
		return $this->curQuestionMaxPoints;
	}

	public function setCurQuestionMaxPoints($curQuestionMaxPoints)
	{
		$this->curQuestionMaxPoints = $curQuestionMaxPoints;
	}
}
