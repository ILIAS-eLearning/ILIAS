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
 * @ilCtrl_Calls ilTestDynamicQuestionSetStatisticTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestDynamicQuestionSetStatisticTableGUI extends ilTable2GUI
{
	const COMPLETE_TABLE_ID = 'tstDynQuestCompleteStat';
	const FILTERED_TABLE_ID = 'tstDynQuestFilteredStat';

	/**
	 * @var array
	 */
	protected $taxIds = array();

	/**
	 * @var bool
	 */
	private $showNumMarkedQuestionsEnabled = false;
	
	/**
	 * @var bool
	 */
	private $showNumPostponedQuestionsEnabled = false;
	
	private $taxonomyFilterEnabled = false;
	
	private $answerStatusFilterEnabled = false;

	/**
	 * Constructor
	 *
	 * @global ilObjUser $ilUser
	 */
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, $a_parent_obj, $a_parent_cmd, $tableId)
	{
		$this->setId($tableId);
		$this->setPrefix($tableId);

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		global $lng, $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->setFormName('filteredquestions');
		$this->setStyle('table', 'fullwidth');

		$this->setRowTemplate("tpl.il_as_tst_dynamic_question_set_selection_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');
		$this->disable('numinfo');
		
		$this->setDisableFilterHiding(true);
	}
	
	public function initTitle($titleLangVar)
	{
		$this->setTitle($this->lng->txt($titleLangVar));
	}
	
	public function initColumns($totalQuestionsColumnHeaderLangVar)
	{
		$this->addColumn($this->lng->txt($totalQuestionsColumnHeaderLangVar), 'num_total_questions', '250');
		
		$this->addColumn($this->lng->txt("tst_num_correct_answered_questions"),'num_correct_answered_questions', '');
		$this->addColumn($this->lng->txt("tst_num_wrong_answered_questions"),'num_wrong_answered_questions', '');
		$this->addColumn($this->lng->txt("tst_num_non_answered_questions"),'num_non_answered_questions', '');

		if( $this->isShowNumPostponedQuestionsEnabled() )
		{
			$this->addColumn($this->lng->txt("tst_num_postponed_questions"),'num_postponed_questions', '');
		}

		if( $this->isShowNumMarkedQuestionsEnabled() )
		{
			$this->addColumn($this->lng->txt("tst_num_marked_questions"),'num_marked_questions', '');
		}
	}

	/**
	 * Init filter
	 */
	public function initFilter()
	{
		if( $this->isTaxonomyFilterEnabled() )
		{
			require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
			
			foreach($this->taxIds as $taxId)
			{
				$postvar = "tax_$taxId";

				$inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
				$this->addFilterItem($inp);
				$inp->readFromSession();
				$this->filter[$postvar] = $inp->getValue();
			}
		}

		if( $this->isAnswerStatusFilterEnabled() )
		{
			require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
			require_once 'Services/Form/classes/class.ilRadioOption.php';
			
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
		$this->tpl->setVariable('NUM_ALL_QUESTIONS', $data['total_all']);
		$this->tpl->setVariable('NUM_CORRECT_ANSWERED_QUESTIONS', $data['correct_answered']);
		$this->tpl->setVariable('NUM_WRONG_ANSWERED_QUESTIONS', $data['wrong_answered']);
		$this->tpl->setVariable('NUM_NON_ANSWERED_QUESTIONS', $data['non_answered']);
		
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
	 * @param array $taxIds
	 */
	public function setTaxIds($taxIds)
	{
		$this->taxIds = $taxIds;
	}

	/**
	 * @return array
	 */
	public function getTaxIds()
	{
		return $this->taxIds;
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

	/**
	 * @return boolean
	 */
	public function isAnswerStatusFilterEnabled()
	{
		return $this->answerStatusFilterEnabled;
	}

	/**
	 * @param boolean $answerStatusFilterEnabled
	 */
	public function setAnswerStatusFilterEnabled($answerStatusFilterEnabled)
	{
		$this->answerStatusFilterEnabled = $answerStatusFilterEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isTaxonomyFilterEnabled()
	{
		return $this->taxonomyFilterEnabled;
	}

	/**
	 * @param boolean $taxonomyFilterEnabled
	 */
	public function setTaxonomyFilterEnabled($taxonomyFilterEnabled)
	{
		$this->taxonomyFilterEnabled = $taxonomyFilterEnabled;
	}
}
?>