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

    protected array $taxIds = array();
    
    private bool $taxonomyFilterEnabled = false;
    
    private bool $answerStatusFilterEnabled = false;
    
    protected ?ilTestDynamicQuestionSetFilterSelection $filterSelection = null;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $a_parent_obj, $a_parent_cmd, $tableId)
    {
        $this->setId($tableId);
        $this->setPrefix($tableId);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

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
    
    public function getFilterSelection() : ?ilTestDynamicQuestionSetFilterSelection
    {
        return $this->filterSelection;
    }

    public function setFilterSelection(ilTestDynamicQuestionSetFilterSelection $filterSelection) : void
    {
        $this->filterSelection = $filterSelection;
    }
    
    public function initTitle(string $titleLangVar) : void
    {
        $this->setTitle($this->lng->txt($titleLangVar));
    }
    
    public function initColumns(string $totalQuestionsColumnHeaderLangVar) : void
    {
        $this->addColumn($this->lng->txt($totalQuestionsColumnHeaderLangVar), 'num_total_questions', '250');
        
        $this->addColumn($this->lng->txt("tst_num_correct_answered_questions"), 'num_correct_answered_questions', '');
        $this->addColumn($this->lng->txt("tst_num_wrong_answered_questions"), 'num_wrong_answered_questions', '');
        $this->addColumn($this->lng->txt("tst_num_non_answered_questions_skipped"), 'num_non_answered_questions_skipped', '');
        $this->addColumn($this->lng->txt("tst_num_non_answered_questions_notseen"), 'num_non_answered_questions_notseen', '');
    }

    public function initFilter() : void
    {
        if ($this->isTaxonomyFilterEnabled()) {
            require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
            
            foreach ($this->taxIds as $taxId) {
                $postvar = "tax_$taxId";

                $inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
                $this->addFilterItem($inp);
                #$inp->readFromSession();
                
                if ($this->getFilterSelection()->hasSelectedTaxonomy($taxId)) {
                    $inp->setValue($this->getFilterSelection()->getSelectedTaxonomy($taxId));
                }
                
                $this->filter[$postvar] = $inp->getValue();
            }
        }

        if ($this->isAnswerStatusFilterEnabled()) {
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
            
            if ($this->getFilterSelection()->hasAnswerStatusSelection()) {
                $inp->setValue($this->getFilterSelection()->getAnswerStatusSelection());
            }
            
            $this->filter['question_answer_status'] = $inp->getValue();
        }
    }

    public function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('NUM_ALL_QUESTIONS', $a_set['total_all']);
        $this->tpl->setVariable('NUM_CORRECT_ANSWERED_QUESTIONS', $a_set['correct_answered']);
        $this->tpl->setVariable('NUM_WRONG_ANSWERED_QUESTIONS', $a_set['wrong_answered']);
        $this->tpl->setVariable('NUM_NON_ANSWERED_QUESTIONS_SKIPPED', $a_set['non_answered_skipped']);
        $this->tpl->setVariable('NUM_NON_ANSWERED_QUESTIONS_NOTSEEN', $a_set['non_answered_notseen']);
    }

    public function setTaxIds(array $taxIds)
    {
        $this->taxIds = $taxIds;
    }

    public function getTaxIds() : array
    {
        return $this->taxIds;
    }

    public function isAnswerStatusFilterEnabled() : bool
    {
        return $this->answerStatusFilterEnabled;
    }

    public function setAnswerStatusFilterEnabled(bool $answerStatusFilterEnabled)
    {
        $this->answerStatusFilterEnabled = $answerStatusFilterEnabled;
    }

    public function isTaxonomyFilterEnabled() : bool
    {
        return $this->taxonomyFilterEnabled;
    }

    public function setTaxonomyFilterEnabled(bool $taxonomyFilterEnabled) : void
    {
        $this->taxonomyFilterEnabled = $taxonomyFilterEnabled;
    }
}
