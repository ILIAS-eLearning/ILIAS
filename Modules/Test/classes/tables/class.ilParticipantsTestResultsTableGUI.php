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

use ILIAS\DI\UIServices;

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 * @ingroup ModulesTest
 */

class ilParticipantsTestResultsTableGUI extends ilTable2GUI
{
    private UIServices $ui;

    protected bool $accessResultsCommandsEnabled = false;
    protected bool $manageResultsCommandsEnabled = false;

    protected $anonymity;

    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->setId('tst_participants_' . $a_parent_obj->getTestObj()->getRefId());
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $this->ui = $DIC->ui();

        $this->setStyle('table', 'fullwidth');

        $this->setFormName('partResultsForm');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setRowTemplate("tpl.il_as_tst_scorings_row.html", "Modules/Test");

        $this->enable('header');
        $this->enable('sort');

        $this->setSelectAllCheckbox('chbUser');

        $this->setDefaultOrderField('name');
        $this->setDefaultOrderDirection('asc');
    }

    public function isAccessResultsCommandsEnabled(): bool
    {
        return $this->accessResultsCommandsEnabled;
    }

    public function setAccessResultsCommandsEnabled(bool $accessResultsCommandsEnabled): void
    {
        $this->accessResultsCommandsEnabled = $accessResultsCommandsEnabled;
    }

    public function isManageResultsCommandsEnabled(): bool
    {
        return $this->manageResultsCommandsEnabled;
    }

    public function setManageResultsCommandsEnabled(bool $manageResultsCommandsEnabled): void
    {
        $this->manageResultsCommandsEnabled = $manageResultsCommandsEnabled;
    }

    public function getAnonymity()
    {
        return $this->anonymity;
    }

    public function setAnonymity($anonymity)
    {
        $this->anonymity = $anonymity;
    }

    public function numericOrdering(string $a_field): bool
    {
        return in_array($a_field, array(
            'scored_pass', 'answered_questions', 'points', 'percent_result'
        ));
    }

    public function init(): void
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->setShowRowsSelector(true);
        }

        $this->initColumns();
        $this->initCommands();
        $this->initFilter();
    }

    public function initColumns(): void
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->addColumn('', '', '1%', true);
        }

        $this->addColumn($this->lng->txt("name"), 'name');
        $this->addColumn($this->lng->txt("login"), 'login');

        $this->addColumn($this->lng->txt("tst_tbl_col_scored_pass"), 'scored_pass');
        $this->addColumn($this->lng->txt("tst_tbl_col_pass_finished"), 'scored_pass_finished_timestamp');

        $this->addColumn($this->lng->txt("tst_tbl_col_answered_questions"), 'answered_questions');
        $this->addColumn($this->lng->txt("tst_tbl_col_reached_points"), 'reached_points');
        $this->addColumn($this->lng->txt("tst_tbl_col_percent_result"), 'percent_result');

        $this->addColumn($this->lng->txt("tst_tbl_col_passed_status"), 'passed_status');
        $this->addColumn($this->lng->txt("tst_tbl_col_final_mark"), 'final_mark');

        if ($this->isActionsColumnRequired()) {
            $this->addColumn($this->lng->txt('actions'), '', '');
        }
    }

    public function initCommands(): void
    {
        if ($this->isAccessResultsCommandsEnabled() && !$this->getAnonymity()) {
            $this->addMultiCommand('showPassOverview', $this->lng->txt('show_pass_overview'));
            $this->addMultiCommand('showUserAnswers', $this->lng->txt('show_user_answers'));
            $this->addMultiCommand('showDetailedResults', $this->lng->txt('show_detailed_results'));
        }

        if ($this->isManageResultsCommandsEnabled()) {
            $this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));
        }
    }

    public function fillRow(array $a_set): void
    {
        if ($this->isMultiRowSelectionRequired()) {
            $this->tpl->setCurrentBlock('checkbox_column');
            $this->tpl->setVariable("CHB_ROW_KEY", $a_set['active_id']);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isActionsColumnRequired()) {
            $this->tpl->setCurrentBlock('actions_column');
            $this->tpl->setVariable('ACTIONS', $this->buildActionsMenu($a_set)->getHTML());
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ROW_KEY", $a_set['active_id']);
        $this->tpl->setVariable("LOGIN", $a_set['login']);
        $this->tpl->setVariable("FULLNAME", $a_set['name']);

        $this->tpl->setVariable("SCORED_PASS", $this->buildScoredPassString($a_set));
        $this->tpl->setVariable("SCORED_PASS_FINISHED", $this->buildScoredPassFinishedString($a_set));

        $this->tpl->setVariable("ANSWERED_QUESTIONS", $this->buildAnsweredQuestionsString($a_set));
        $this->tpl->setVariable("REACHED_POINTS", $this->buildReachedPointsString($a_set));
        $this->tpl->setVariable("PERCENT_RESULT", $this->buildPercentResultString($a_set));

        $this->tpl->setVariable("PASSED_STATUS", $this->buildPassedStatusString($a_set));
        $this->tpl->setVariable("FINAL_MARK", $a_set['final_mark']);
    }

    protected function buildActionsMenu(array $data): ilAdvancedSelectionListGUI
    {
        $asl = new ilAdvancedSelectionListGUI();

        $this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);

        if ($this->isAccessResultsCommandsEnabled()) {
            $resultsHref = $this->ctrl->getLinkTargetByClass([ilTestResultsGUI::class, ilParticipantsTestResultsGUI::class, ilTestEvaluationGUI::class], 'outParticipantsResultsOverview');
            $asl->addItem($this->lng->txt('tst_show_results'), $resultsHref, $resultsHref);
        }

        return $asl;
    }

    protected function isActionsColumnRequired(): bool
    {
        if ($this->isAccessResultsCommandsEnabled()) {
            return true;
        }

        return false;
    }

    protected function isMultiRowSelectionRequired(): bool
    {
        if ($this->isAccessResultsCommandsEnabled() && !$this->getAnonymity()) {
            return true;
        }

        if ($this->isManageResultsCommandsEnabled()) {
            return true;
        }

        return false;
    }

    protected function buildPassedStatusString(array $data): string
    {
        if ($data['passed_status']) {
            return $this->buildPassedIcon() . ' ' . $this->lng->txt('tst_passed');
        }

        return $this->buildFailedIcon() . ' ' . $this->lng->txt('tst_failed');
    }

    protected function buildPassedIcon(): string
    {
        return $this->buildImageIcon(ilUtil::getImagePath("icon_ok.svg"), $this->lng->txt("passed"));
    }

    protected function buildFailedIcon(): string
    {
        return $this->buildImageIcon(ilUtil::getImagePath("icon_not_ok.svg"), $this->lng->txt("failed"));
    }

    protected function buildImageIcon(string $icon_name, string $label): string
    {
        $icon = $this->ui->factory()->symbol()->icon()->custom(
            $icon_name,
            $label
        );
        return $this->ui->renderer()->render($icon);
    }

    protected function buildFormattedAccessDate(array $data): string
    {
        return ilDatePresentation::formatDate(new ilDateTime($data['access'], IL_CAL_DATETIME));
    }

    protected function buildScoredPassString(array $data): string
    {
        return $this->lng->txt('pass') . ' ' . ($data['scored_pass'] + 1);
    }

    protected function buildScoredPassFinishedString(array $data): string
    {
        if (isset($data['scored_pass_finished_timestamp'])) {
            return ilDatePresentation::formatDate(new ilDateTime($data['scored_pass_finished_timestamp'], IL_CAL_UNIX));
        }
        return '';
    }

    protected function buildAnsweredQuestionsString(array $data): string
    {
        return sprintf(
            $this->lng->txt('tst_answered_questions_of_total'),
            $data['answered_questions'],
            $data['total_questions']
        );
    }

    protected function buildReachedPointsString(array $data): string
    {
        return sprintf(
            $this->lng->txt('tst_reached_points_of_max'),
            $data['reached_points'],
            $data['max_points']
        );
    }

    protected function buildPercentResultString(array $data): string
    {
        return sprintf('%0.2f %%', $data['percent_result'] * 100);
    }
}
