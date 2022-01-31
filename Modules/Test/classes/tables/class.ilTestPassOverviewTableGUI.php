<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * Class ilTestPassOverviewTableGUI
 */
class ilTestPassOverviewTableGUI extends ilTable2GUI
{
    /**
     * @var bool
     */
    protected $resultPresentationEnabled = false;
    
    /**
     * @var bool
     */
    protected $pdfPresentationEnabled = false;

    /**
     * @var bool
     */
    protected $objectiveOrientedPresentationEnabled = false;

    /**
     * @var integer
     */
    protected $activeId = null;

    /**
     * @var string
     */
    protected $passDetailsCommand = '';

    /**
     * @var string
     */
    protected $passDeletionCommand = '';

    /**
     * @param        $parent
     * @param string $cmd
     * @param int    $context
     */
    public function __construct($parent, $cmd)
    {
        $this->setId('tst_pass_overview_' . $parent->object->getId());
        $this->setDefaultOrderField('pass');
        $this->setDefaultOrderDirection('ASC');

        parent::__construct($parent, $cmd);
        
        // Don't set any limit because of print/pdf views. Furthermore, this view is part of different summary views, and no cmd ist passed to he calling method.
        $this->setLimit(PHP_INT_MAX);
        $this->disable('sort');

        $this->setRowTemplate('tpl.il_as_tst_pass_overview_row.html', 'Modules/Test');
    }
    
    public function init()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->setParameter($this->parent_obj, 'active_id', $this->getActiveId());
        
        $this->initColumns();

        if ($this->isPdfPresentationEnabled()) {
            $this->disable('linkbar');
            $this->disable('numinfo');
            $this->disable('numinfo_header');
            $this->disable('hits');
        }
    }

    /**
     * @param string $a_field
     * @return bool
     */
    public function numericOrdering(string $a_field) : bool
    {
        switch ($a_field) {
            case 'pass':
            case 'date':
            case 'percentage':
                return true;
        }

        return false;
    }

    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set) : void
    {
        if (array_key_exists('percentage', $a_set)) {
            $a_set['percentage'] = sprintf('%.2f', $a_set['percentage']) . '%';
        }

        // fill columns
        
        if (!$this->isObjectiveOrientedPresentationEnabled()) {
            if ($this->isResultPresentationEnabled()) {
                $this->tpl->setVariable('VAL_SCORED', $a_set['scored'] ? '&otimes;' : '');
            }
            
            $this->tpl->setVariable('VAL_PASS', $this->getPassNumberPresentation($a_set['pass']));
        }
        
        $this->tpl->setVariable('VAL_DATE', $this->formatDate($a_set['date']));

        if ($this->isObjectiveOrientedPresentationEnabled()) {
            $this->tpl->setVariable('VAL_LO_OBJECTIVES', $a_set['objectives']);
            
            $this->tpl->setVariable('VAL_LO_TRY', sprintf(
                $this->lng->txt('tst_res_lo_try_n'),
                $this->getPassNumberPresentation($a_set['pass'])
            ));
        }

        if ($this->isResultPresentationEnabled()) {
            $this->tpl->setVariable('VAL_ANSWERED', $this->buildWorkedThroughQuestionsString(
                $a_set['num_workedthrough_questions'],
                $a_set['num_questions_total']
            ));

            if ($this->getParentObject()->object->isOfferingQuestionHintsEnabled()) {
                $this->tpl->setVariable('VAL_HINTS', $a_set['hints']);
            }

            $this->tpl->setVariable('VAL_REACHED', $this->buildReachedPointsString(
                $a_set['reached_points'],
                $a_set['max_points']
            ));

            $this->tpl->setVariable('VAL_PERCENTAGE', $a_set['percentage']);
        }

        if (!$this->isPdfPresentationEnabled()) {
            $actions = $this->getRequiredActions($a_set['scored']);
            $this->tpl->setVariable('VAL_ACTIONS', $this->buildActionsHtml($actions, $a_set['pass']));
        }
    }

    protected function initColumns()
    {
        if ($this->isResultPresentationEnabled() && !$this->isObjectiveOrientedPresentationEnabled()) {
            $this->addColumn($this->lng->txt('scored_pass'), '', '150');
        }
        
        if (!$this->isObjectiveOrientedPresentationEnabled()) {
            $this->addColumn($this->lng->txt('pass'), '', '1%');
        }
        
        $this->addColumn($this->lng->txt('date'));
        
        if ($this->isObjectiveOrientedPresentationEnabled()) {
            $this->addColumn($this->lng->txt('tst_res_lo_objectives_header'), '');
            $this->addColumn($this->lng->txt('tst_res_lo_try_header'), '');
        }
        
        if ($this->isResultPresentationEnabled()) {
            $this->addColumn($this->lng->txt('tst_answered_questions'));
            if ($this->getParentObject()->object->isOfferingQuestionHintsEnabled()) {
                $this->addColumn($this->lng->txt('tst_question_hints_requested_hint_count_header'));
            }
            $this->addColumn($this->lng->txt('tst_reached_points'));
            $this->addColumn($this->lng->txt('tst_percent_solved'));
        }
        
        // actions
        if (!$this->isPdfPresentationEnabled()) {
            $this->addColumn('', '', '10%');
        }
    }

    /**
     * @return boolean
     */
    public function isResultPresentationEnabled()
    {
        return $this->resultPresentationEnabled;
    }

    /**
     * @param boolean $resultPresentationEnabled
     */
    public function setResultPresentationEnabled($resultPresentationEnabled)
    {
        $this->resultPresentationEnabled = $resultPresentationEnabled;
    }

    /**
     * @return boolean
     */
    public function isPdfPresentationEnabled()
    {
        return $this->pdfPresentationEnabled;
    }

    /**
     * @param boolean $pdfPresentationEnabled
     */
    public function setPdfPresentationEnabled($pdfPresentationEnabled)
    {
        $this->pdfPresentationEnabled = $pdfPresentationEnabled;
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
     * @return int
     */
    public function getActiveId()
    {
        return $this->activeId;
    }

    /**
     * @param int $activeId
     */
    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    /**
     * @return string
     */
    public function getPassDetailsCommand()
    {
        return $this->passDetailsCommand;
    }

    /**
     * @param string $passDetailsCommand
     */
    public function setPassDetailsCommand($passDetailsCommand)
    {
        $this->passDetailsCommand = $passDetailsCommand;
    }

    /**
     * @return string
     */
    public function getPassDeletionCommand()
    {
        return $this->passDeletionCommand;
    }

    /**
     * @param string $passDeletionCommand
     */
    public function setPassDeletionCommand($passDeletionCommand)
    {
        $this->passDeletionCommand = $passDeletionCommand;
    }

    /**
     * @param integer $dateTS
     * @return string $dateFormated
     */
    private function formatDate($date)
    {
        $oldValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $date = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_UNIX));
        ilDatePresentation::setUseRelativeDates($oldValue);
        return $date;
    }
    
    private function buildWorkedThroughQuestionsString($numQuestionsWorkedThrough, $numQuestionsTotal)
    {
        return "{$numQuestionsWorkedThrough} {$this->lng->txt('of')} {$numQuestionsTotal}";
    }
    
    private function buildReachedPointsString($reachedPoints, $maxPoints)
    {
        return "{$reachedPoints} {$this->lng->txt('of')} {$maxPoints}";
    }

    private function getRequiredActions($isScoredPass)
    {
        $actions = array();
        
        if ($this->getPassDetailsCommand()) {
            $actions[$this->getPassDetailsCommand()] = $this->lng->txt('tst_pass_details');
        }
        
        if (!$isScoredPass && $this->getPassDeletionCommand()) {
            $actions[$this->getPassDeletionCommand()] = $this->lng->txt('delete');
        }
        
        return $actions;
    }
    
    private function buildActionsHtml($actions, $pass)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        if (!count($actions)) {
            return '';
        }

        $ilCtrl->setParameter($this->parent_obj, 'pass', $pass);
        
        if (count($actions) > 1) {
            $aslgui = new ilAdvancedSelectionListGUI();
            $aslgui->setListTitle($this->lng->txt('actions'));
            $aslgui->setId($pass);
            
            foreach ($actions as $cmd => $label) {
                $aslgui->addItem($label, $cmd, $ilCtrl->getLinkTarget($this->parent_obj, $cmd));
            }

            $html = $aslgui->getHTML();
        } else {
            $cmd = key($actions);
            $label = current($actions);
            
            $href = $ilCtrl->getLinkTarget($this->parent_obj, $cmd);
            $html = '<a href="' . $href . '">' . $label . '</a>';
        }

        $ilCtrl->setParameter($this->parent_obj, 'pass', '');

        return $html;
    }
    
    /**
     * @param integer $pass
     * @return mixed
     */
    protected function getPassNumberPresentation($pass)
    {
        return $pass + 1;
    }
}
