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

use ILIAS\UI\Component\Modal\Interruptive;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestNavigationToolbarGUI extends ilToolbarGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTestPlayerAbstractGUI
     */
    protected $playerGUI;

    /**
     * @var bool
     */
    private $suspendTestButtonEnabled = false;

    /**
     * @var bool
     */
    private $questionListButtonEnabled = false;

    /**
     * @var bool
     */
    private $questionTreeButtonEnabled = false;

    private bool $questionTreeVisible = false;

    /**
     * @var bool
     */
    private $questionSelectionButtonEnabled = false;

    /**
     * @var bool
     */
    private $finishTestButtonEnabled = false;

    /**
     * @var string
     */
    private $finishTestCommand = '';

    /**
     * @var bool
     */
    private $finishTestButtonPrimary = false;

    /**
     * @var bool
     */
    private $disabledStateEnabled = false;
    private bool $user_has_attempts_left = true;
    protected ?Interruptive $finish_test_modal = null;

    /**
     * @param ilCtrl $ctrl
     * @param ilLanguage $lng
     * @param ilTestPlayerAbstractGUI $playerGUI
     */
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilTestPlayerAbstractGUI $playerGUI)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->playerGUI = $playerGUI;

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
    public function isQuestionListButtonEnabled(): bool
    {
        return $this->questionListButtonEnabled;
    }

    /**
     * @param boolean $questionListButtonEnabled
     */
    public function setQuestionListButtonEnabled($questionListButtonEnabled)
    {
        $this->questionListButtonEnabled = $questionListButtonEnabled;
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
        if ($this->isQuestionListButtonEnabled()) {
            $this->addQuestionListButton();
        }

        if ($this->isQuestionSelectionButtonEnabled()) {
            $this->addQuestionSelectionButton();
        }

        if ($this->isSuspendTestButtonEnabled()) {
            $this->addSuspendTestButton();
        }

        if ($this->isFinishTestButtonEnabled()) {
            $this->addFinishTestButton();
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
            $this->ctrl->getLinkTarget($this->playerGUI, ilTestPlayerCommands::SUSPEND_TEST)
        );
        $this->addComponent($button);
    }

    private function addQuestionListButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('question_summary_btn'),
            $this->ctrl->getLinkTarget($this->playerGUI, ilTestPlayerCommands::QUESTION_SUMMARY)
        );
        $this->addComponent($button);
    }

    private function addQuestionSelectionButton()
    {
        $button = $this->ui->factory()->button()->standard(
            $this->lng->txt('tst_change_dyn_test_question_selection'),
            $this->ctrl->getLinkTarget($this->playerGUI, ilTestPlayerCommands::SHOW_QUESTION_SELECTION)
        );
        $this->addComponent($button);
    }

<<<<<<< HEAD
    private function addQuestionTreeButton()
    {
        if ($this->isQuestionTreeVisible()) {
            $btn_cap = $this->lng->txt('tst_hide_side_list');
        } else {
            $btn_cap = $this->lng->txt('tst_show_side_list');
        }

        $button = $this->ui->factory()->button()->standard(
            $btn_cap,
            $this->ctrl->getLinkTarget($this->playerGUI, ilTestPlayerCommands::TOGGLE_SIDE_LIST)
        );
        $this->addComponent($button);
    }

=======
>>>>>>> 6212957342 (TA: QuestionList in mainbar)
    private function addFinishTestButton(): void
    {
        if ($this->userHasAttemptsLeft()) {
            $message = $this->lng->txt('tst_finish_confirmation_question');
        } else {
            $message = $this->lng->txt('tst_finish_confirmation_question_no_attempts_left');
        }

        $action = '';
        if ($this->getFinishTestCommand() === ilTestPlayerCommands::QUESTION_SUMMARY) {
            $action = $this->ctrl->getLinkTarget(
                $this->playerGUI,
                ilTestPlayerCommands::QUESTION_SUMMARY
            );
        } else {
            $this->finish_test_modal = $this->ui->factory()->modal()->interruptive(
                $this->lng->txt('finish_test'),
                $message,
                $this->ctrl->getLinkTarget(
                    $this->playerGUI,
                    $this->getFinishTestCommand()
                )
            )->withActionButtonLabel($this->lng->txt('tst_finish_confirm_button'));
        }
        if ($this->isFinishTestButtonPrimary()) {
            $button = $this->ui->factory()->button()->primary($this->lng->txt('finish_test'), $action);
        } else {
            $button = $this->ui->factory()->button()->standard($this->lng->txt('finish_test'), $action);
        }
        $button =
            isset($this->finish_test_modal) ?
                $button->withOnClick($this->finish_test_modal->getShowSignal()) :
                $button->withAdditionalOnLoadCode(
                    static function (string $id): string {
                        return "document.getElementById('$id').addEventListener('click', "
                            . '(e) => {'
                            . ' if (il.TestPlayerQuestionEditControl !== "undefined") {'
                            . '     il.TestPlayerQuestionEditControl.checkNavigationForKSButton(e);'
                            . ' } else {'
                            . '     e.target.setAttribute("name", "cmd[' . ilTestPlayerCommands::QUESTION_SUMMARY . ']");'
                            . '     e.target.form.requestSubmit(e.target);'
                            . ' };'
                            . '});';
                    }
                );

        $this->addStickyItem($button);
    }
}
