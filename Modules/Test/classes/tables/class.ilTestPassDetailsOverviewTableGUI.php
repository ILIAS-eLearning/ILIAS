<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 *
 * @ilCtrl_Calls ilTestPassDetailsOverviewTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestPassDetailsOverviewTableGUI extends ilTable2GUI
{
    private $singleAnswerScreenCmd = null;

    private $answerListAnchorEnabled = false;

    private $showHintCount = false;

    private $showSuggestedSolution = false;

    private $activeId = null;
    
    private $is_pdf_generation_request = false;
    
    private $objectiveOrientedPresentationEnabled = false;
    
    private $multipleObjectivesInvolved = true;
    
    private $passColumnEnabled = false;
    
    private $tableIdsByParentClasses = array(
        'ilTestEvaluationGUI' => 1,
        'ilTestServiceGUI' => 2
    );

    /**
     * @var ilTestQuestionRelatedObjectivesList
     */
    private $questionRelatedObjectivesList = null;

    public function __construct(ilCtrl $ctrl, $parent, $cmd)
    {
        $tableId = 0;
        if (isset($this->tableIdsByParentClasses[get_class($parent)])) {
            $tableId = $this->tableIdsByParentClasses[get_class($parent)];
        }

        $this->ctrl = $ctrl;

        $this->setId('tst_pdo_' . $tableId);
        $this->setPrefix('tst_pdo_' . $tableId);

        $this->setDefaultOrderField('nr');
        $this->setDefaultOrderDirection('ASC');

        parent::__construct($parent, $cmd);

        $this->setFormName('tst_pass_details_overview');
        $this->setFormAction($this->ctrl->getFormAction($parent, $cmd));

        // Don't set any limit because of print/pdf views.
        $this->setLimit(PHP_INT_MAX);
        $this->setExternalSegmentation(true);

        $this->disable('linkbar');
        $this->disable('hits');
        $this->disable('sort');

        //$this->disable('numinfo');
        //$this->disable('numinfo_header');
        // KEEP THIS ENABLED, SINCE NO TABLE FILTER ARE PROVIDED OTHERWISE

        $this->setRowTemplate('tpl.il_as_tst_pass_details_overview_qst_row.html', 'Modules/Test');
    }

    /**
     * @return ilTestPassDetailsOverviewTableGUI $this
     */
    public function initColumns()
    {
        if ($this->isPassColumnEnabled()) {
            if ($this->isObjectiveOrientedPresentationEnabled()) {
                $passHeaderLabel = $this->lng->txt("tst_attempt");
            } else {
                $passHeaderLabel = $this->lng->txt("pass");
            }

            $this->addColumn($passHeaderLabel, 'pass', '');
        } else {
            $this->addColumn($this->lng->txt("tst_question_no"), '', '');
        }

        $this->addColumn($this->lng->txt("question_id"), '', '');
        $this->addColumn($this->lng->txt("tst_question_title"), '', '');
        
        if ($this->isObjectiveOrientedPresentationEnabled() && $this->areMultipleObjectivesInvolved()) {
            $this->addColumn($this->lng->txt('tst_res_lo_objectives_header'), '', '');
        }
        
        $this->addColumn($this->lng->txt("tst_maximum_points"), '', '');
        $this->addColumn($this->lng->txt("tst_reached_points"), '', '');

        if ($this->getShowHintCount()) {
            $this->addColumn($this->lng->txt("tst_question_hints_requested_hint_count_header"), '', '');
        }

        $this->addColumn($this->lng->txt("tst_percent_solved"), '', '');

        if ($this->getShowSuggestedSolution()) {
            $this->addColumn($this->lng->txt("solution_hint"), '', '');
        }

        if ($this->areActionListsRequired()) {
            $this->addColumn('', '', '1');
        }

        return $this;
    }

    /**
     * @return ilTestPassDetailsOverviewTableGUI $this
     */
    public function initFilter()
    {
        if (count($this->parent_obj->object->getResultFilterTaxIds())) {
            require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

            foreach ($this->parent_obj->object->getResultFilterTaxIds() as $taxId) {
                $postvar = "tax_$taxId";

                $inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
                $this->addFilterItem($inp);
                $inp->readFromSession();
                $this->filter[$postvar] = $inp->getValue();
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPdfGenerationRequest()
    {
        return $this->is_pdf_generation_request;
    }

    /**
     * @param boolean $is_print_request
     */
    public function setIsPdfGenerationRequest($is_print_request)
    {
        $this->is_pdf_generation_request = $is_print_request;
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        $this->ctrl->setParameter($this->parent_obj, 'evaluation', $row['qid']);
        
        if (isset($row['pass'])) {
            $this->ctrl->setParameter($this->parent_obj, 'pass', $row['pass']);
        }

        if ($this->isQuestionTitleLinkPossible()) {
            $questionTitleLink = $this->getQuestionTitleLink($row['qid']);

            if (strlen($questionTitleLink)) {
                $this->tpl->setVariable('URL_QUESTION_TITLE', $questionTitleLink);

                $this->tpl->setCurrentBlock('title_link_end_tag');
                $this->tpl->touchBlock('title_link_end_tag');
                $this->tpl->parseCurrentBlock();
            }
        }
        
        if ($this->isObjectiveOrientedPresentationEnabled() && $this->areMultipleObjectivesInvolved()) {
            $objectives = $this->questionRelatedObjectivesList->getQuestionRelatedObjectiveTitles($row['qid']);
            $this->tpl->setVariable('VALUE_LO_OBJECTIVES', strlen($objectives) ? $objectives : '&nbsp;');
        }

        if ($this->getShowHintCount()) {
            $this->tpl->setVariable('VALUE_HINT_COUNT', (int) $row['requested_hints']);
        }

        if ($this->getShowSuggestedSolution()) {
            $this->tpl->setVariable('SOLUTION_HINT', $row['solution']);
        }

        if ($this->areActionListsRequired()) {
            $this->tpl->setVariable('ACTIONS_MENU', $this->getActionList($row['qid']));
        }

        $this->tpl->setVariable('VALUE_QUESTION_TITLE', $row['title']);
        $this->tpl->setVariable('VALUE_QUESTION_ID', $row['qid']);

        if ($this->isPassColumnEnabled()) {
            $this->tpl->setVariable('VALUE_QUESTION_PASS', $row['pass'] + 1);
        } else {
            $this->tpl->setVariable('VALUE_QUESTION_COUNTER', $row['nr']);
        }

        $this->tpl->setVariable('VALUE_MAX_POINTS', $row['max']);
        $this->tpl->setVariable('VALUE_REACHED_POINTS', $row['reached']);
        $this->tpl->setVariable('VALUE_PERCENT_SOLVED', $row['percent']);

        $this->tpl->setVariable('ROW_ID', $this->getRowId($row['qid']));
    }

    private function getRowId($questionId)
    {
        return "pass_details_tbl_row_act_{$this->getActiveId()}_qst_{$questionId}";
    }

    private function getQuestionTitleLink($questionId)
    {
        if ($this->getAnswerListAnchorEnabled()) {
            return $this->getAnswerListAnchor($questionId);
        }

        if (strlen($this->getSingleAnswerScreenCmd())) {
            return $this->ctrl->getLinkTarget($this->parent_obj, $this->getSingleAnswerScreenCmd());
        }

        return '';
    }

    private function isQuestionTitleLinkPossible()
    {
        if ($this->getAnswerListAnchorEnabled()) {
            return true;
        }

        if (strlen($this->getSingleAnswerScreenCmd())) {
            return true;
        }

        return false;
    }

    private function areActionListsRequired()
    {
        if ($this->isPdfGenerationRequest()) {
            return false;
        }

        if (!$this->getAnswerListAnchorEnabled()) {
            return false;
        }

        if (!strlen($this->getSingleAnswerScreenCmd())) {
            return false;
        }

        return true;
    }

    private function getActionList($questionId)
    {
        $aslGUI = new ilAdvancedSelectionListGUI();
        $aslGUI->setListTitle($this->lng->txt('tst_answer_details'));
        $aslGUI->setId("act{$this->getActiveId()}_qst{$questionId}");

        if ($this->getAnswerListAnchorEnabled()) {
            $aslGUI->addItem(
                $this->lng->txt('tst_list_answer_details'),
                'tst_pass_details',
                $this->getAnswerListAnchor($questionId)
            );
        }

        if (strlen($this->getSingleAnswerScreenCmd())) {
            $aslGUI->addItem(
                $this->lng->txt('tst_single_answer_details'),
                'tst_pass_details',
                $this->ctrl->getLinkTarget($this->parent_obj, $this->getSingleAnswerScreenCmd())
            );
        }

        return $aslGUI->getHTML();
    }

    public function setSingleAnswerScreenCmd($singleAnswerScreenCmd)
    {
        $this->singleAnswerScreenCmd = $singleAnswerScreenCmd;
    }

    public function getSingleAnswerScreenCmd()
    {
        return $this->singleAnswerScreenCmd;
    }

    public function setAnswerListAnchorEnabled($answerListAnchorEnabled)
    {
        $this->answerListAnchorEnabled = $answerListAnchorEnabled;
    }

    public function getAnswerListAnchorEnabled()
    {
        return $this->answerListAnchorEnabled;
    }

    private function getAnswerListAnchor($questionId)
    {
        return "#detailed_answer_block_act_{$this->getActiveId()}_qst_{$questionId}";
    }

    public function setShowHintCount($showHintCount)
    {
        // Has to be called before column initialization
        $this->showHintCount = $showHintCount;
    }

    public function getShowHintCount()
    {
        return $this->showHintCount;
    }

    public function setShowSuggestedSolution($showSuggestedSolution)
    {
        $this->showSuggestedSolution = $showSuggestedSolution;
    }

    public function getShowSuggestedSolution()
    {
        return $this->showSuggestedSolution;
    }

    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    public function getActiveId()
    {
        return $this->activeId;
    }

    /**
     * @return boolean
     */
    public function isObjectiveOrientedPresentationEnabled()
    {
        return $this->objectiveOrientedPresentationEnabled;
    }

    /**
     * @param boolean $objectiveOrientedPresentationEnabled
     */
    public function setObjectiveOrientedPresentationEnabled($objectiveOrientedPresentationEnabled)
    {
        $this->objectiveOrientedPresentationEnabled = $objectiveOrientedPresentationEnabled;
    }

    /**
     * @return boolean
     */
    public function areMultipleObjectivesInvolved()
    {
        return $this->multipleObjectivesInvolved;
    }

    /**
     * @param boolean $multipleObjectivesInvolved
     */
    public function setMultipleObjectivesInvolved($multipleObjectivesInvolved)
    {
        $this->multipleObjectivesInvolved = $multipleObjectivesInvolved;
    }

    /**
     * @return ilTestQuestionRelatedObjectivesList
     */
    public function getQuestionRelatedObjectivesList()
    {
        return $this->questionRelatedObjectivesList;
    }

    /**
     * @param ilTestQuestionRelatedObjectivesList $questionRelatedObjectivesList
     */
    public function setQuestionRelatedObjectivesList($questionRelatedObjectivesList)
    {
        $this->questionRelatedObjectivesList = $questionRelatedObjectivesList;
    }

    /**
     * @return boolean
     */
    public function isPassColumnEnabled()
    {
        return $this->passColumnEnabled;
    }

    /**
     * @param boolean $passColumnEnabled
     */
    public function setPassColumnEnabled($passColumnEnabled)
    {
        $this->passColumnEnabled = $passColumnEnabled;
    }
}
