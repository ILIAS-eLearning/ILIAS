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

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Modules/Test/classes/class.ilTestPlayerNavButton.php';

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
    public function isQuestionTreeButtonEnabled(): bool
    {
        return $this->questionTreeButtonEnabled;
    }

    /**
     * @param boolean $questionTreeButtonEnabled
     */
    public function setQuestionTreeButtonEnabled($questionTreeButtonEnabled)
    {
        $this->questionTreeButtonEnabled = $questionTreeButtonEnabled;
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
        if ($this->isQuestionTreeButtonEnabled()) {
            $this->addQuestionTreeButton();
        }

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

    private function addSuspendTestButton()
    {
        $btn = ilTestPlayerNavButton::getInstance();
        $btn->setNextCommand(ilTestPlayerCommands::SUSPEND_TEST);
        $btn->setUrl($this->ctrl->getLinkTarget(
            $this->playerGUI,
            ilTestPlayerCommands::SUSPEND_TEST
        ));
        $btn->setCaption('cancel_test');
        //$btn->setDisabled($this->isDisabledStateEnabled());
        $btn->addCSSClass('ilTstNavElem');
        $this->addButtonInstance($btn);
    }

    private function addQuestionListButton()
    {
        $btn = ilTestPlayerNavButton::getInstance();
        $btn->setNextCommand(ilTestPlayerCommands::QUESTION_SUMMARY);
        $btn->setUrl($this->ctrl->getLinkTarget(
            $this->playerGUI,
            ilTestPlayerCommands::QUESTION_SUMMARY
        ));
        $btn->setCaption('question_summary_btn');
        //$btn->setDisabled($this->isDisabledStateEnabled());
        $btn->addCSSClass('ilTstNavElem');
        $this->addButtonInstance($btn);
    }

    private function addQuestionSelectionButton()
    {
        $btn = ilTestPlayerNavButton::getInstance();
        $btn->setNextCommand(ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
        $btn->setUrl($this->ctrl->getLinkTarget(
            $this->playerGUI,
            ilTestPlayerCommands::SHOW_QUESTION_SELECTION
        ));
        $btn->setCaption('tst_change_dyn_test_question_selection');
        //$btn->setDisabled($this->isDisabledStateEnabled());
        $btn->addCSSClass('ilTstNavElem');
        $this->addButtonInstance($btn);
    }

    private function addQuestionTreeButton()
    {
        $btn = ilTestPlayerNavButton::getInstance();
        $btn->setNextCommand(ilTestPlayerCommands::TOGGLE_SIDE_LIST);
        $btn->setUrl($this->ctrl->getLinkTarget(
            $this->playerGUI,
            ilTestPlayerCommands::TOGGLE_SIDE_LIST
        ));
        if ($this->isQuestionTreeVisible()) {
            $btn->setCaption('tst_hide_side_list');
        } else {
            $btn->setCaption('tst_show_side_list');
        }
        //$btn->setDisabled($this->isDisabledStateEnabled());
        $btn->addCSSClass('ilTstNavElem');
        $this->addButtonInstance($btn);
    }

    private function addFinishTestButton()
    {
        $btn = ilTestPlayerNavButton::getInstance();
        $btn->setNextCommand($this->getFinishTestCommand());
        $btn->setUrl($this->ctrl->getLinkTarget(
            $this->playerGUI,
            $this->getFinishTestCommand()
        ));
        $btn->setCaption('finish_test');
        //$btn->setDisabled($this->isDisabledStateEnabled());
        $btn->setPrimary($this->isFinishTestButtonPrimary());
        $btn->addCSSClass('ilTstNavElem');
        $this->addButtonInstance($btn);
    }
}
