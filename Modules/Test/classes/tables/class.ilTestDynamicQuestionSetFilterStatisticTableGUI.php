<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 * 
 * @ilCtrl_Calls ilTestDynamicQuestionSetFilterStatisticTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestDynamicQuestionSetFilterStatisticTableGUI extends ilTable2GUI
{
	protected $taxIds = array();

	/**
	 * @var bool
	 */
	private $showNumMarkedQuestionsEnabled = false;
	
	/**
	 * @var bool
	 */
	private $showNumPostponedQuestionsEnabled = false;

	/**
	 * Constructor
	 *
	 * @global ilObjUser $ilUser
	 */
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, $a_parent_obj, $a_parent_cmd, $taxIds)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->taxIds = $taxIds;
		
		$this->setFormName('filteredquestions');
		$this->setStyle('table', 'fullwidth');

		$this->setTitle($this->lng->txt('tst_dynamic_question_set_selection'));
	
		$this->setRowTemplate("tpl.il_as_tst_dynamic_question_set_selection_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');
		$this->disable('numinfo');
	}
	
	public function initColumns()
	{

		$this->addColumn($this->lng->txt("tst_num_open_questions"),'num_open_questions', '');
		$this->addColumn($this->lng->txt("tst_num_non_answered_questions"),'num_non_answered_questions', '');
		$this->addColumn($this->lng->txt("tst_num_wrong_answered_questions"),'num_wrong_answered_questions', '');
		
		$this->addColumn($this->lng->txt("tst_num_postponed_questions"),'num_postponed_questions', '');
		
		$this->addColumn($this->lng->txt("tst_num_marked_questions"),'num_marked_questions', '');
	}

	/**
	 * Init filter
	 */
	public function initFilter()
	{
		require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
		require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		require_once 'Services/Form/classes/class.ilRadioOption.php';

		foreach($this->taxIds as $taxId)
		{
			$postvar = "tax_$taxId";

			$inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
			$this->addFilterItem($inp);
			$inp->readFromSession();
			$this->filter[$postvar] = $inp->getValue();
		}
		
		$inp = new ilSelectInputGUI($this->lng->txt('tst_question_answer_status'), 'question_answer_status');
		$inp->setOptions(array(
			ilAssQuestionList::ANSWER_STATUS_FILTER_ALL_NON_CORRECT => $this->lng->txt('tst_question_answer_status_all_non_correct'),
			ilAssQuestionList::ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY => $this->lng->txt('tst_question_answer_status_non_answered'),
			ilAssQuestionList::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY => $this->lng->txt('tst_question_answer_status_wrong_answered')
		));
		$this->addFilterItem($inp);
		$inp->readFromSession();
		$this->filter['question_answer_status'] = $inp->getValue();
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		$this->tpl->setVariable('NUM_OPEN_QUESTIONS', $data['total_open']);
		$this->tpl->setVariable('NUM_NON_ANSWERED_QUESTIONS', $data['non_answered']);
		$this->tpl->setVariable('NUM_WRONG_ANSWERED_QUESTIONS', $data['wrong_answered']);
		
		if( $this->isShowNumPostponedQuestionsEnabled() )
		{
			$this->tpl->setCurrentBlock('num_postponed');
			$this->tpl->setVariable("NUM_POSTPONED_QUESTIONS", $data['postponed']);
			$this->tpl->parseCurrentBlock();
		}

		if( $this->isShowNumMarkedQuestionsEnabled() )
		{
			$this->tpl->setCurrentBlock('num_marked');
			$this->tpl->setVariable("NUM_MARKED_QUESTIONS", $data['marked']);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * @param boolean $showNumMarkedQuestionsEnabled
	 */
	public function setShowNumMarkedQuestionsEnabled($showNumMarkedQuestionsEnabled)
	{
		$this->showNumMarkedQuestionsEnabled = $showNumMarkedQuestionsEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isShowNumMarkedQuestionsEnabled()
	{
		return $this->showNumMarkedQuestionsEnabled;
	}

	/**
	 * @param boolean $showNumPostponedQuestionsEnabled
	 */
	public function setShowNumPostponedQuestionsEnabled($showNumPostponedQuestionsEnabled)
	{
		$this->showNumPostponedQuestionsEnabled = $showNumPostponedQuestionsEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isShowNumPostponedQuestionsEnabled()
	{
		return $this->showNumPostponedQuestionsEnabled;
	}
}
?>