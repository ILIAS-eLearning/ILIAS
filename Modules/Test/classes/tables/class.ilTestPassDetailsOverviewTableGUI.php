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
    private ?string $singleAnswerScreenCmd = null;

    private bool $answerListAnchorEnabled = false;

    private bool $showHintCount = false;

    private bool $showSuggestedSolution = false;

    private $activeId = null;

    private bool $is_pdf_generation_request = false;

    private bool $objectiveOrientedPresentationEnabled = false;

    private bool $multipleObjectivesInvolved = true;

    private bool $passColumnEnabled = false;

    private array $tableIdsByParentClasses = array(
        'ilTestEvaluationGUI' => 1,
        'ilTestServiceGUI' => 2
    );

    private ?ilTestQuestionRelatedObjectivesList $questionRelatedObjectivesList = null;

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

    public function initColumns(): void
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
            $this->addColumn($this->lng->txt('actions'), '', '1');
        }
    }

    public function initFilter(): void
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
    }

    public function isPdfGenerationRequest(): bool
    {
        return $this->is_pdf_generation_request;
    }

    public function setIsPdfGenerationRequest(bool $is_print_request): void
    {
        $this->is_pdf_generation_request = $is_print_request;
    }

    public function fillRow(array $a_set): void
    {
        $this->ctrl->setParameter($this->parent_obj, 'evaluation', $a_set['qid']);

        if (isset($a_set['pass'])) {
            $this->ctrl->setParameter($this->parent_obj, 'pass', $a_set['pass']);
        }

        if ($this->isQuestionTitleLinkPossible()) {
            $questionTitleLink = $this->getQuestionTitleLink($a_set['qid']);

            if (strlen($questionTitleLink)) {
                $this->tpl->setVariable('URL_QUESTION_TITLE', $questionTitleLink);

                $this->tpl->setCurrentBlock('title_link_end_tag');
                $this->tpl->touchBlock('title_link_end_tag');
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->isObjectiveOrientedPresentationEnabled() && $this->areMultipleObjectivesInvolved()) {
            $objectives = $this->questionRelatedObjectivesList->getQuestionRelatedObjectiveTitles($a_set['qid']);
            $this->tpl->setVariable('VALUE_LO_OBJECTIVES', strlen($objectives) ? $objectives : '&nbsp;');
        }

        if ($this->getShowHintCount()) {
            $this->tpl->setVariable('VALUE_HINT_COUNT', (int) $a_set['requested_hints']);
        }

        if ($this->getShowSuggestedSolution()) {
            $this->tpl->setVariable('SOLUTION_HINT', $a_set['solution']);
        }

        if ($this->areActionListsRequired()) {
            $this->tpl->setVariable('ACTIONS_MENU', $this->getActionList($a_set['qid']));
        }

        $this->tpl->setVariable('VALUE_QUESTION_TITLE', $a_set['title']);
        $this->tpl->setVariable('VALUE_QUESTION_ID', $a_set['qid']);

        if ($this->isPassColumnEnabled()) {
            $this->tpl->setVariable('VALUE_QUESTION_PASS', $a_set['pass'] + 1);
        } else {
            $this->tpl->setVariable('VALUE_QUESTION_COUNTER', $a_set['nr']);
        }

        $this->tpl->setVariable('VALUE_MAX_POINTS', $a_set['max']);
        $this->tpl->setVariable('VALUE_REACHED_POINTS', $a_set['reached']);
        $this->tpl->setVariable('VALUE_PERCENT_SOLVED', $a_set['percent']);

        $this->tpl->setVariable('ROW_ID', $this->getRowId($a_set['qid']));
    }

    private function getRowId($questionId): string
    {
        return "pass_details_tbl_row_act_{$this->getActiveId()}_qst_{$questionId}";
    }

    private function getQuestionTitleLink($questionId): string
    {
        if ($this->getAnswerListAnchorEnabled()) {
            return $this->getAnswerListAnchor($questionId);
        }

        if (strlen($this->getSingleAnswerScreenCmd())) {
            return $this->ctrl->getLinkTarget($this->parent_obj, $this->getSingleAnswerScreenCmd());
        }

        return '';
    }

    private function isQuestionTitleLinkPossible(): bool
    {
        if ($this->getAnswerListAnchorEnabled()) {
            return true;
        }

        if (strlen($this->getSingleAnswerScreenCmd())) {
            return true;
        }

        return false;
    }

    private function areActionListsRequired(): bool
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

    private function getActionList($questionId): string
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

    public function setSingleAnswerScreenCmd($singleAnswerScreenCmd): void
    {
        $this->singleAnswerScreenCmd = $singleAnswerScreenCmd;
    }

    public function getSingleAnswerScreenCmd(): ?string
    {
        return $this->singleAnswerScreenCmd;
    }

    public function setAnswerListAnchorEnabled($answerListAnchorEnabled): void
    {
        $this->answerListAnchorEnabled = $answerListAnchorEnabled;
    }

    public function getAnswerListAnchorEnabled(): bool
    {
        return $this->answerListAnchorEnabled;
    }

    private function getAnswerListAnchor($questionId): string
    {
        return "#detailed_answer_block_act_{$this->getActiveId()}_qst_{$questionId}";
    }

    public function setShowHintCount($showHintCount): void
    {
        // Has to be called before column initialization
        $this->showHintCount = (bool) $showHintCount;
    }

    public function getShowHintCount(): bool
    {
        return $this->showHintCount;
    }

    public function setShowSuggestedSolution(bool $showSuggestedSolution): void
    {
        $this->showSuggestedSolution = $showSuggestedSolution;
    }

    public function getShowSuggestedSolution(): bool
    {
        return $this->showSuggestedSolution;
    }

    public function setActiveId($activeId): void
    {
        $this->activeId = $activeId;
    }

    public function getActiveId()
    {
        return $this->activeId;
    }

    public function isObjectiveOrientedPresentationEnabled(): bool
    {
        return $this->objectiveOrientedPresentationEnabled;
    }

    public function setObjectiveOrientedPresentationEnabled(bool $objectiveOrientedPresentationEnabled): void
    {
        $this->objectiveOrientedPresentationEnabled = $objectiveOrientedPresentationEnabled;
    }

    public function areMultipleObjectivesInvolved(): bool
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

    public function getQuestionRelatedObjectivesList(): ?ilTestQuestionRelatedObjectivesList
    {
        return $this->questionRelatedObjectivesList;
    }

    public function setQuestionRelatedObjectivesList(ilTestQuestionRelatedObjectivesList $questionRelatedObjectivesList): void
    {
        $this->questionRelatedObjectivesList = $questionRelatedObjectivesList;
    }

    public function isPassColumnEnabled(): bool
    {
        return $this->passColumnEnabled;
    }

    public function setPassColumnEnabled(bool $passColumnEnabled)
    {
        $this->passColumnEnabled = $passColumnEnabled;
    }
}
