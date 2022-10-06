<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTestPassOverviewTableGUI
 */
class ilTestPassOverviewTableGUI extends ilTable2GUI
{
    protected bool $resultPresentationEnabled = false;

    protected bool $pdfPresentationEnabled = false;

    protected bool $objectiveOrientedPresentationEnabled = false;

    protected ?int $activeId = null;

    protected string $passDetailsCommand = '';

    protected string $passDeletionCommand = '';

    public function __construct($parent, $cmd)
    {
        $this->setId('tst_pass_overview_' . $parent->getObject()->getId());
        $this->setDefaultOrderField('pass');
        $this->setDefaultOrderDirection('ASC');

        parent::__construct($parent, $cmd);

        // Don't set any limit because of print/pdf views. Furthermore, this view is part of different summary views, and no cmd ist passed to he calling method.
        $this->setLimit(PHP_INT_MAX);
        $this->disable('sort');

        $this->setRowTemplate('tpl.il_as_tst_pass_overview_row.html', 'Modules/Test');
    }

    public function init(): void
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

    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'pass':
            case 'date':
            case 'percentage':
                return true;
        }

        return false;
    }

    public function fillRow(array $a_set): void
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

    protected function initColumns(): void
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
            $this->addColumn($this->lng->txt('actions'), '', '10%');
        }
    }

    public function isResultPresentationEnabled(): bool
    {
        return $this->resultPresentationEnabled;
    }

    public function setResultPresentationEnabled(bool $resultPresentationEnabled): void
    {
        $this->resultPresentationEnabled = $resultPresentationEnabled;
    }

    public function isPdfPresentationEnabled(): bool
    {
        return $this->pdfPresentationEnabled;
    }

    public function setPdfPresentationEnabled(bool $pdfPresentationEnabled): void
    {
        $this->pdfPresentationEnabled = $pdfPresentationEnabled;
    }

    public function isObjectiveOrientedPresentationEnabled(): bool
    {
        return $this->objectiveOrientedPresentationEnabled;
    }

    public function setObjectiveOrientedPresentationEnabled(bool $objectiveOrientedPresentationEnabled): void
    {
        $this->objectiveOrientedPresentationEnabled = $objectiveOrientedPresentationEnabled;
    }

    public function getActiveId(): ?int
    {
        return $this->activeId;
    }

    public function setActiveId($activeId): void
    {
        $this->activeId = (int) $activeId;
    }

    public function getPassDetailsCommand(): string
    {
        return $this->passDetailsCommand;
    }

    public function setPassDetailsCommand(string $passDetailsCommand): void
    {
        $this->passDetailsCommand = $passDetailsCommand;
    }

    public function getPassDeletionCommand(): string
    {
        return $this->passDeletionCommand;
    }

    public function setPassDeletionCommand(string $passDeletionCommand): void
    {
        $this->passDeletionCommand = $passDeletionCommand;
    }

    private function formatDate($date): string
    {
        $oldValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $date = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_UNIX));
        ilDatePresentation::setUseRelativeDates($oldValue);
        return $date;
    }

    private function buildWorkedThroughQuestionsString($numQuestionsWorkedThrough, $numQuestionsTotal): string
    {
        return "{$numQuestionsWorkedThrough} {$this->lng->txt('of')} {$numQuestionsTotal}";
    }

    private function buildReachedPointsString($reachedPoints, $maxPoints): string
    {
        return "{$reachedPoints} {$this->lng->txt('of')} {$maxPoints}";
    }

    private function getRequiredActions($isScoredPass): array
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

    private function buildActionsHtml($actions, $pass): string
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

    protected function getPassNumberPresentation($pass): int
    {
        return $pass + 1;
    }
}
