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
    private bool $finishTestButtonEnabled = false;
    private string $finishTestCommand = '';
    private bool $finishTestButtonPrimary = false;
    private bool $disabledStateEnabled = false;
    protected ?InterruptiveModal $finish_test_modal = null;
    protected bool $user_pass_overview_button_enabled = false;

    public function __construct(
        protected ilCtrl $ctrl,
        protected ilTestPlayerAbstractGUI $player_gui
    ) {
        parent::__construct();
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

    public function isUserPassOverviewEnabled(): bool
    {
        return $this->user_pass_overview_button_enabled;
    }

    public function setUserPassOverviewEnabled(bool $user_pass_overview_button_enabled): void
    {
        $this->user_pass_overview_button_enabled = $user_pass_overview_button_enabled;
    }

    public function isQuestionTreeVisible(): bool
    {
        return $this->questionTreeVisible;
    }

    public function setQuestionTreeVisible(bool $questionTreeVisible): void
    {
        $this->questionTreeVisible = $questionTreeVisible;
    }

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

        if ($this->isSuspendTestButtonEnabled()) {
            $this->addSuspendTestButton();
        }

        if ($this->isFinishTestButtonEnabled()) {
            $this->addStickyItem($this->retrieveFinishTestButton());
        }
    }

    private function addSuspendTestButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('cancel_test'),
            ''
        )->withAdditionalOnLoadCode(
            $this->buildCheckNavigationClosure(
                $this->ctrl->getLinkTarget($this->player_gui, ilTestPlayerCommands::SUSPEND_TEST)
            )
        );
        $this->addComponent($button);
    }

    private function addPassOverviewButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('question_summary_btn'),
            ''
        )->withAdditionalOnLoadCode(
            $this->buildCheckNavigationClosure(
                $this->ctrl->getLinkTarget($this->player_gui, ilTestPlayerCommands::QUESTION_SUMMARY)
            )
        );
        $this->addComponent($button);
    }

    private function retrieveFinishTestButton(): Button
    {
        $target = $this->ctrl->getLinkTarget($this->player_gui, $this->getFinishTestCommand());
        if ($this->player_gui->getObject()->getMainSettings()->getFinishingSettings()->getShowAnswerOverview()
            && $this->getFinishTestCommand() !== ilTestPlayerCommands::QUESTION_SUMMARY) {
            $target = $this->ctrl->getLinkTargetByClass('ilTestSubmissionReviewGUI', 'show');
        }

        $button = $this->getStandardOrPrimaryFinishButtonInstance();
        return $button->withAdditionalOnLoadCode(
            $this->buildCheckNavigationClosure($target)
        );
    }

    private function getStandardOrPrimaryFinishButtonInstance(): Button
    {
        if ($this->isFinishTestButtonPrimary()) {
            return $this->ui->factory()->button()->primary($this->lng->txt('finish_test'), '');
        }

        return $this->ui->factory()->button()->standard($this->lng->txt('finish_test'), '');
    }

    private function buildCheckNavigationClosure(string $target): Closure
    {
        return static function (string $id) use ($target): string {
            return "document.getElementById('$id').addEventListener('click', "
                . '(e) => {'
                . " il.TestPlayerQuestionEditControl.checkNavigation('{$target}', 'show', e);"
                . '});';
        };
    }
}
