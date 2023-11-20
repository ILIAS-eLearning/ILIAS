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

declare(strict_types=1);

use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Button\Button;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestNavigationToolbarGUI extends ilToolbarGUI
{
    private bool $suspendTestButtonEnabled = false;
    private bool $questionTreeVisible = false;
    private bool $questionSelectionButtonEnabled = false;
    private bool $finishTestButtonEnabled = false;
    private string $finishTestCommand = '';
    private bool $finishTestButtonPrimary = false;
    private bool $disabledStateEnabled = false;
    private bool $user_has_attempts_left = true;
    protected ?InterruptiveModal $finish_test_modal = null;
    protected bool $user_pass_overview_button_enabled = false;

    public function __construct(
        protected ilCtrl $ctrl,
        protected ilTestPlayerAbstractGUI $player_gui
    ) {
        parent::__construct();
    }

    public function userHasAttemptsLeft(): bool
    {
        return $this->user_has_attempts_left;
    }

    public function setUserHasAttemptsLeft(bool $user_has_attempts_left): void
    {
        $this->user_has_attempts_left = $user_has_attempts_left;
    }

    /**
     * @return boolean
     */
    public function isSuspendTestButtonEnabled(): bool
    {
        return $this->suspendTestButtonEnabled;
    }

    /**
     * @param boolean $suspendTestButtonEnabled
     */
    public function setSuspendTestButtonEnabled($suspendTestButtonEnabled)
    {
        $this->suspendTestButtonEnabled = $suspendTestButtonEnabled;
    }

    /**
     * @return boolean
     */
    public function isUserPassOverviewEnabled(): bool
    {
        return $this->user_pass_overview_button_enabled;
    }

    /**
     * @param boolean $questionListButtonEnabled
     */
    public function setUserPassOverviewEnabled(bool $user_pass_overview_button_enabled)
    {
        $this->user_pass_overview_button_enabled = $user_pass_overview_button_enabled;
    }

    /**
     * @return boolean
     */
    public function isQuestionTreeVisible(): bool
    {
        return $this->questionTreeVisible;
    }

    public function setQuestionTreeVisible(bool $questionTreeVisible): void
    {
        $this->questionTreeVisible = $questionTreeVisible;
    }

    /**
     * @return boolean
     */
    public function isQuestionSelectionButtonEnabled(): bool
    {
        return $this->questionSelectionButtonEnabled;
    }

    /**
     * @param boolean $questionSelectionButtonEnabled
     */
    public function setQuestionSelectionButtonEnabled($questionSelectionButtonEnabled)
    {
        $this->questionSelectionButtonEnabled = $questionSelectionButtonEnabled;
    }

    /**
     * @return boolean
     */
    public function isFinishTestButtonEnabled(): bool
    {
        return $this->finishTestButtonEnabled;
    }

    /**
     * @param boolean $finishTestButtonEnabled
     */
    public function setFinishTestButtonEnabled($finishTestButtonEnabled)
    {
        $this->finishTestButtonEnabled = $finishTestButtonEnabled;
    }

    /**
     * @return string
     */
    public function getFinishTestCommand(): string
    {
        return $this->finishTestCommand;
    }

    /**
     * @param string $finishTestCommand
     */
    public function setFinishTestCommand($finishTestCommand)
    {
        $this->finishTestCommand = $finishTestCommand;
    }

    /**
     * @return boolean
     */
    public function isFinishTestButtonPrimary(): bool
    {
        return $this->finishTestButtonPrimary;
    }

    /**
     * @param boolean $finishTestButtonPrimary
     */
    public function setFinishTestButtonPrimary($finishTestButtonPrimary)
    {
        $this->finishTestButtonPrimary = $finishTestButtonPrimary;
    }

    /**
     * @return boolean
     */
    public function isDisabledStateEnabled(): bool
    {
        return $this->disabledStateEnabled;
    }

    /**
     * @param boolean $disabledStateEnabled
     */
    public function setDisabledStateEnabled($disabledStateEnabled)
    {
        $this->disabledStateEnabled = $disabledStateEnabled;
    }

    public function build()
    {
        if ($this->isUserPassOverviewEnabled()) {
            $this->addPassOverviewButton();
        }

        if ($this->isQuestionSelectionButtonEnabled()) {
            $this->addQuestionSelectionButton();
        }

        if ($this->isSuspendTestButtonEnabled()) {
            $this->addSuspendTestButton();
        }

        if ($this->isFinishTestButtonEnabled()) {
            $this->addStickyItem($this->retrieveFinishTestButton());
        }
    }

    public function getFinishTestModalHTML(): string
    {
        if ($this->finish_test_modal === null) {
            return '';
        }
        return $this->ui->renderer()->render($this->finish_test_modal);
    }

    private function addSuspendTestButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('cancel_test'),
            $this->ctrl->getLinkTarget($this->player_gui, ilTestPlayerCommands::SUSPEND_TEST)
        );
        $this->addComponent($button);
    }

    private function addPassOverviewButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('question_summary_btn'),
            $this->ctrl->getLinkTarget($this->player_gui, ilTestPlayerCommands::QUESTION_SUMMARY)
        );
        $this->addComponent($button);
    }

    private function addQuestionSelectionButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('tst_change_dyn_test_question_selection'),
            $this->ctrl->getLinkTarget($this->player_gui, ilTestPlayerCommands::SHOW_QUESTION_SELECTION)
        );
        $this->addComponent($button);
    }

    private function retrieveFinishTestButton(): Button
    {
        // Examview enabled & !reviewed & requires_confirmation? test_submission_overview (review gui)
        if ($this->player_gui->getObject()->getMainSettings()->getFinishingSettings()->getShowAnswerOverview()
            && $this->getFinishTestCommand() !== ilTestPlayerCommands::QUESTION_SUMMARY) {
            return $this->buildShowSubmissionOverviewButton();
        }

        if ($this->getFinishTestCommand() === ilTestPlayerCommands::QUESTION_SUMMARY) {
            return $this->buildShowQuestionSummaryButton();
        }

        return $this->buildShowConfirmationModalButton();
    }

    private function buildShowSubmissionOverviewButton(): Button
    {
        $target = $this->ctrl->getLinkTargetByClass('ilTestSubmissionReviewGUI', 'show');
        $button = $this->getStandardOrPrimaryFinishButtonInstance('');

        return $button->withAdditionalOnLoadCode(
            static function (string $id) use ($target): string {
                return "document.getElementById('$id').addEventListener('click', "
                    . '(e) => {'
                    . " il.TestPlayerQuestionEditControl.checkNavigation('{$target}', 'show', e);"
                    . '});';
            }
        );
    }

    private function buildShowQuestionSummaryButton(): Button
    {
        $action = $this->ctrl->getLinkTarget(
            $this->player_gui,
            ilTestPlayerCommands::QUESTION_SUMMARY
        );
        $button = $this->getStandardOrPrimaryFinishButtonInstance($action);

        return $button->withAdditionalOnLoadCode(
            $this->getAdditionalOnLoadCodeForFinishTestButton('')
        );
    }

    private function buildShowConfirmationModalButton(): Button
    {
        $button = $this->getStandardOrPrimaryFinishButtonInstance('');

        if ($this->userHasAttemptsLeft()) {
            $message = $this->lng->txt('tst_finish_confirmation_question');
        } else {
            $message = $this->lng->txt('tst_finish_confirmation_question_no_attempts_left');
        }

        $this->finish_test_modal = $this->ui->factory()->modal()->interruptive(
            $this->lng->txt('finish_test'),
            $message,
            $this->ctrl->getLinkTarget(
                $this->player_gui,
                $this->getFinishTestCommand()
            )
        )->withActionButtonLabel($this->lng->txt('tst_finish_confirm_button'));

        $signal = $this->finish_test_modal->getShowSignal()->getId();

        return $button->withAdditionalOnLoadCode(
            $this->getAdditionalOnLoadCodeForFinishTestButton($signal)
        );
    }

    private function getStandardOrPrimaryFinishButtonInstance(string $action): Button
    {
        if ($this->isFinishTestButtonPrimary()) {
            return $this->ui->factory()->button()->primary($this->lng->txt('finish_test'), $action);
        }

        return $this->ui->factory()->button()->standard($this->lng->txt('finish_test'), $action);
    }

    private function getAdditionalOnLoadCodeForFinishTestButton(string $signal): Closure
    {
        return static function (string $id) use ($signal): string {
            return "document.getElementById('$id').addEventListener('click', "
                . '(e) => {'
                . ' if (il.TestPlayerQuestionEditControl !== "undefined") {'
                . "     il.TestPlayerQuestionEditControl.checkNavigationForKSButton(e, '{$signal}');"
                . '     return;'
                . ' };'
                . ' e.target.setAttribute("name", "cmd[' . ilTestPlayerCommands::QUESTION_SUMMARY . ']");'
                . ' e.target.form.requestSubmit(e.target);'
                . '});';
        };
    }
}
