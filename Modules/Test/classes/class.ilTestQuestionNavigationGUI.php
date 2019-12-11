<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilTestQuestionNavigationGUI
{
    const SHOW_DISABLED_COMMANDS = false;
    
    const CSS_CLASS_SUBMIT_BUTTONS = 'ilc_qsubmit_Submit';
    
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var string
     */
    private $editSolutionCommand = '';

    /**
     * @var bool
     */
    private $questionWorkedThrough = false;

    /**
     * @var string
     */
    private $submitSolutionCommand = '';

    // fau: testNav - new variable for 'revert changes' link target
    /**
     * @var string
     */
    private $revertChangesLinkTarget = '';
    // fau.

    /**
     * @var bool
     */
    private $discardSolutionButtonEnabled = false;

    /**
     * @var string
     */
    private $skipQuestionLinkTarget = '';

    /**
     * @var string
     */
    private $instantFeedbackCommand = '';

    /**
     * @var bool
     */
    private $answerFreezingEnabled = false;

    /**
     * @var bool
     */
    private $forceInstantResponseEnabled = false;

    /**
     * @var string
     */
    private $requestHintCommand = '';

    /**
     * @var string
     */
    private $showHintsCommand = '';

    /**
     * @var bool
     */
    private $hintRequestsExist = false;

    // fau: testNav - change question mark command to link target
    /**
     * @var string
     */
    private $questionMarkLinkTarget = '';
    // fau.

    /**
     * @var bool
     */
    private $questionMarked = false;

    /**
     * @var bool
     */
    private $charSelectorEnabled = false;

    /**
     * @var bool
     */
    private $anythingRendered = false;
    
    /**
     * @param ilLanguage $lng
     */
    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return string
     */
    public function getEditSolutionCommand()
    {
        return $this->editSolutionCommand;
    }

    /**
     * @param string $editSolutionCommand
     */
    public function setEditSolutionCommand($editSolutionCommand)
    {
        $this->editSolutionCommand = $editSolutionCommand;
    }

    /**
     * @return boolean
     */
    public function isQuestionWorkedThrough()
    {
        return $this->questionWorkedThrough;
    }

    /**
     * @param boolean $questionWorkedThrough
     */
    public function setQuestionWorkedThrough($questionWorkedThrough)
    {
        $this->questionWorkedThrough = $questionWorkedThrough;
    }

    /**
     * @return string
     */
    public function getSubmitSolutionCommand()
    {
        return $this->submitSolutionCommand;
    }

    /**
     * @param string $submitSolutionCommand
     */
    public function setSubmitSolutionCommand($submitSolutionCommand)
    {
        $this->submitSolutionCommand = $submitSolutionCommand;
    }

    // fau: testNav - get/set revertChangesCommand
    /**
     * @return string
     */
    public function getRevertChangesLinkTarget()
    {
        return $this->revertChangesLinkTarget;
    }

    /**
     * @param string
     */
    public function setRevertChangesLinkTarget($revertChangesLinkTarget)
    {
        $this->revertChangesLinkTarget = $revertChangesLinkTarget;
    }
    // fau.

    /**
     * @return bool
     */
    public function isDiscardSolutionButtonEnabled()
    {
        return $this->discardSolutionButtonEnabled;
    }

    /**
     * @param bool $discardSolutionButtonEnabled
     */
    public function setDiscardSolutionButtonEnabled($discardSolutionButtonEnabled)
    {
        $this->discardSolutionButtonEnabled = $discardSolutionButtonEnabled;
    }

    /**
     * @return string
     */
    public function getSkipQuestionLinkTarget()
    {
        return $this->skipQuestionLinkTarget;
    }

    /**
     * @param string $skipQuestionLinkTarget
     */
    public function setSkipQuestionLinkTarget($skipQuestionLinkTarget)
    {
        $this->skipQuestionLinkTarget = $skipQuestionLinkTarget;
    }

    /**
     * @return string
     */
    public function getInstantFeedbackCommand()
    {
        return $this->instantFeedbackCommand;
    }

    /**
     * @param string $instantFeedbackCommand
     */
    public function setInstantFeedbackCommand($instantFeedbackCommand)
    {
        $this->instantFeedbackCommand = $instantFeedbackCommand;
    }

    /**
     * @return boolean
     */
    public function isAnswerFreezingEnabled()
    {
        return $this->answerFreezingEnabled;
    }

    /**
     * @return boolean
     */
    public function isForceInstantResponseEnabled()
    {
        return $this->forceInstantResponseEnabled;
    }

    /**
     * @param boolean $forceInstantResponseEnabled
     */
    public function setForceInstantResponseEnabled($forceInstantResponseEnabled)
    {
        $this->forceInstantResponseEnabled = $forceInstantResponseEnabled;
    }

    /**
     * @param boolean $answerFreezingEnabled
     */
    public function setAnswerFreezingEnabled($answerFreezingEnabled)
    {
        $this->answerFreezingEnabled = $answerFreezingEnabled;
    }

    /**
     * @return string
     */
    public function getRequestHintCommand()
    {
        return $this->requestHintCommand;
    }

    /**
     * @param string $requestHintCommand
     */
    public function setRequestHintCommand($requestHintCommand)
    {
        $this->requestHintCommand = $requestHintCommand;
    }

    /**
     * @return string
     */
    public function getShowHintsCommand()
    {
        return $this->showHintsCommand;
    }

    /**
     * @param string $showHintsCommand
     */
    public function setShowHintsCommand($showHintsCommand)
    {
        $this->showHintsCommand = $showHintsCommand;
    }

    /**
     * @return boolean
     */
    public function hintRequestsExist()
    {
        return $this->hintRequestsExist;
    }

    /**
     * @param boolean $hintRequestsExist
     */
    public function setHintRequestsExist($hintRequestsExist)
    {
        $this->hintRequestsExist = $hintRequestsExist;
    }

    // fau: testNav - change setter/getter of question mark command to link target
    /**
     * @return string
     */
    public function getQuestionMarkLinkTarget()
    {
        return $this->questionMarkLinkTarget;
    }

    /**
     * @param string $questionMarkLinkTarget
     */
    public function setQuestionMarkLinkTarget($questionMarkLinkTarget)
    {
        $this->questionMarkLinkTarget = $questionMarkLinkTarget;
    }
    // fau.

    /**
     * @return boolean
     */
    public function isQuestionMarked()
    {
        return $this->questionMarked;
    }

    /**
     * @param boolean $questionMarked
     */
    public function setQuestionMarked($questionMarked)
    {
        $this->questionMarked = $questionMarked;
    }

    /**
     * @return boolean
     */
    public function isAnythingRendered()
    {
        return $this->anythingRendered;
    }

    /**
     * @param boolean $buttonRendered
     */
    public function setAnythingRendered()
    {
        $this->anythingRendered = true;
    }

    /**
     * @return boolean
     */
    public function isCharSelectorEnabled()
    {
        return $this->charSelectorEnabled;
    }

    /**
     * @param boolean $charSelectorEnabled
     */
    public function setCharSelectorEnabled($charSelectorEnabled)
    {
        $this->charSelectorEnabled = $charSelectorEnabled;
    }
    
    // fau: testNav - generate question actions menu
    /**
     * Get the HTML of an actions menu below the title
     * @return string
     */
    public function getActionsHTML()
    {
        $tpl = $this->getTemplate('actions');

        include_once("Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $actions = new ilGroupedListGUI();
        $actions->setAsDropDown(true, true);

        if ($this->getQuestionMarkLinkTarget()) {
            $actions->addEntry(
                $this->getQuestionMarkActionLabel(),
                $this->getQuestionMarkLinkTarget(),
                '',
                '',
                'ilTestQuestionAction',
                'tst_mark_question_action'
            );
            $actions->addSeparator();
        }

        if ($this->getRevertChangesLinkTarget()) {
            $actions->addEntry(
                $this->lng->txt('tst_revert_changes'),
                $this->getRevertChangesLinkTarget(),
                '',
                '',
                'ilTestQuestionAction ilTestRevertChangesAction',
                'tst_revert_changes_action'
            );
        } else {
            $actions->addEntry(
                $this->lng->txt('tst_revert_changes'),
                '#',
                '',
                '',
                'ilTestQuestionAction ilTestRevertChangesAction disabled',
                'tst_revert_changes_action'
            );
        }

        if ($this->isDiscardSolutionButtonEnabled()) {
            $actions->addEntry(
                $this->lng->txt('discard_answer'),
                '#',
                '',
                '',
                'ilTestQuestionAction ilTestDiscardSolutionAction',
                'tst_discard_solution_action'
            );
        } else {
            $actions->addEntry(
                $this->lng->txt('discard_answer'),
                '#',
                '',
                '',
                'ilTestQuestionAction ilTestDiscardSolutionAction disabled',
                'tst_discard_solution_action'
            );
        }

        if ($this->getSkipQuestionLinkTarget()) {
            $actions->addEntry(
                $this->lng->txt('postpone_question'),
                $this->getSkipQuestionLinkTarget(),
                '',
                '',
                'ilTestQuestionAction',
                'tst_skip_question_action'
            );
        } elseif (self::SHOW_DISABLED_COMMANDS) {
            $actions->addEntry(
                $this->lng->txt('postpone_question'),
                '#',
                '',
                '',
                'ilTestQuestionAction disabled',
                'tst_skip_question_action'
            );
        }

        if ($this->isCharSelectorEnabled()) {
            $actions->addSeparator();
            $actions->addEntry(
                $this->lng->txt('char_selector_btn_label'),
                '#',
                '',
                '',
                'ilTestQuestionAction ilCharSelectorMenuToggle',
                'ilCharSelectorMenuToggleLink'
            );
        }

        // render the mark icon
        if ($this->getQuestionMarkLinkTarget()) {
            $this->renderActionsIcon(
                $tpl,
                $this->getQuestionMarkIconSource(),
                $this->getQuestionMarkIconLabel(),
                'ilTestMarkQuestionIcon'
            );
        }

        // render the action menu
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('btn-primary');
        $list->setId('QuestionActions');
        $list->setListTitle($this->lng->txt("actions"));
        $list->setStyle(1);
        $list->setGroupedList($actions);
        $tpl->setVariable('ACTION_MENU', $list->getHTML());

        return $tpl->get();
    }
    // fau.


    /**
     * @return string
     */
    public function getHTML()
    {
        // fau: testNav - add parameter for toolbar template purpose
        $tpl = $this->getTemplate('toolbar');
        // fau.
        if ($this->getEditSolutionCommand()) {
            $this->renderSubmitButton(
                $tpl,
                $this->getEditSolutionCommand(),
                $this->getEditSolutionButtonLabel()
            );
        }
        
        // fau: testNav - don't show the standard submit button.
        // fau: testNav - discard answer is moved to the actions menu.
        // fau: testNav - skip question (postpone) is moved to the actions menu.

        if ($this->getInstantFeedbackCommand()) {
            $this->renderSubmitButton(
                $tpl,
                $this->getInstantFeedbackCommand(),
                $this->getCheckButtonLabel(),
                $this->isForceInstantResponseEnabled()
            );
        }

        if ($this->getRequestHintCommand()) {
            $this->renderSubmitButton(
                $tpl,
                $this->getRequestHintCommand(),
                $this->getRequestHintButtonLabel()
            );
        }

        if ($this->getShowHintsCommand()) {
            $this->renderSubmitButton(
                $tpl,
                $this->getShowHintsCommand(),
                'button_show_requested_question_hints'
            );
        }

        // fau: testNav - question mark is moved to the actions menu.
        // fau: testNav - char selector is moved to the actions menu.
        
        if ($this->isAnythingRendered()) {
            $this->parseNavigation($tpl);
        }
        
        return $tpl->get();
    }

    private function getEditSolutionButtonLabel()
    {
        if ($this->isQuestionWorkedThrough()) {
            return 'edit_answer';
        }

        return 'answer_question';
    }

    private function getSubmitSolutionButtonLabel()
    {
        if ($this->isForceInstantResponseEnabled()) {
            return 'submit_and_check';
        }

        // fau: testNav - rename the submit button to simply "Save"
        return 'save';
        // fau.
    }
    
    private function getCheckButtonLabel()
    {
        if ($this->isAnswerFreezingEnabled()) {
            return 'submit_and_check';
        }
        
        return 'check';
    }
    
    private function getRequestHintButtonLabel()
    {
        if ($this->hintRequestsExist()) {
            return 'button_request_next_question_hint';
        }
        
        return 'button_request_question_hint';
    }

    // fau: testNav - adjust mark icon and action labels
    private function getQuestionMarkActionLabel()
    {
        if ($this->isQuestionMarked()) {
            return $this->lng->txt('tst_remove_mark');
        }

        return $this->lng->txt('tst_question_mark');
    }


    private function getQuestionMarkIconLabel()
    {
        if ($this->isQuestionMarked()) {
            return $this->lng->txt('tst_question_marked');
        }

        return$this->lng->txt('tst_question_not_marked');
    }
    // fau.

    private function getQuestionMarkIconSource()
    {
        if ($this->isQuestionMarked()) {
            return ilUtil::getImagePath('marked.svg');
        }

        return ilUtil::getImagePath('marked_.svg');
    }

    // fau: testNav - add parameter for template purpose
    /**
     * Get the template
     * @param	string	$a_purpose ('toolbar' | 'actions')
     * @return ilTemplate
     */
    private function getTemplate($a_purpose = 'toolbar')
    {
        switch ($a_purpose) {
            case 'toolbar':
        return new ilTemplate(
            'tpl.tst_question_navigation.html',
            true,
            true,
            'Modules/Test'
        );

            case 'actions':
                return new ilTemplate(
                    'tpl.tst_question_actions.html',
                    true,
                    true,
                    'Modules/Test'
                );
    }
    }
    // fau.

    /**
     * @param ilTemplate $tpl
     */
    private function parseNavigation(ilTemplate $tpl)
    {
        $tpl->setCurrentBlock('question_related_navigation');
        $tpl->parseCurrentBlock();
    }

    /**
     * @param ilTemplate $tpl
     */
    private function parseButtonsBlock(ilTemplate $tpl)
    {
        $tpl->setCurrentBlock('buttons');
        $tpl->parseCurrentBlock();
    }

    /**
     * @param ilTemplate $tpl
     * @param $button
     */
    private function renderButtonInstance(ilTemplate $tpl, $button)
    {
        $tpl->setCurrentBlock("button_instance");
        $tpl->setVariable("BUTTON_INSTANCE", $button->render());
        $tpl->parseCurrentBlock();

        $this->parseButtonsBlock($tpl);
        $this->setAnythingRendered();
    }

    /**
     * @param ilTemplate $tpl
     * @param $command
     * @param $label
     * @param bool|false $primary
     */
    private function renderSubmitButton(ilTemplate $tpl, $command, $label, $primary = false)
    {
        $button = ilSubmitButton::getInstance();
        $button->setCommand($command);
        $button->setCaption($label);
        $button->setPrimary($primary);
        $button->addCSSClass(self::CSS_CLASS_SUBMIT_BUTTONS);
        
        $this->renderButtonInstance($tpl, $button);
    }

    /**
     * @param ilTemplate $tpl
     * @param $htmlId
     * @param $label
     * @param $cssClass
     */
    private function renderLinkButton(ilTemplate $tpl, $href, $label)
    {
        $button = ilLinkButton::getInstance();
        $button->setUrl($href);
        $button->setCaption($label);

        $this->renderButtonInstance($tpl, $button);
    }

    /**
     * @param ilTemplate $tpl
     * @param $htmlId
     * @param $label
     * @param $cssClass
     */
    private function renderJsLinkedButton(ilTemplate $tpl, $htmlId, $label, $cssClass)
    {
        $button = ilLinkButton::getInstance();
        $button->setId($htmlId);
        $button->addCSSClass($cssClass);
        $button->setCaption($label);

        $this->renderButtonInstance($tpl, $button);
    }

    /**
     * @param ilTemplate $tpl
     * @param $command
     * @param $iconSrc
     * @param $label
     * @param $cssClass
     */
    private function renderIcon(ilTemplate $tpl, $command, $iconSrc, $label, $cssClass)
    {
        $tpl->setCurrentBlock("submit_icon");
        $tpl->setVariable("SUBMIT_ICON_CMD", $command);
        $tpl->setVariable("SUBMIT_ICON_SRC", $iconSrc);
        $tpl->setVariable("SUBMIT_ICON_TEXT", $label);
        $tpl->setVariable("SUBMIT_ICON_CLASS", $cssClass);
        $tpl->parseCurrentBlock();

        $this->parseButtonsBlock($tpl);
        $this->setAnythingRendered();
    }

    // fau: testNav - render an icon beneath the actions menu
    private function renderActionsIcon(ilTemplate $tpl, $iconSrc, $label, $cssClass)
    {
        $tpl->setCurrentBlock("actions_icon");
        $tpl->setVariable("ICON_SRC", $iconSrc);
        $tpl->setVariable("ICON_TEXT", $label);
        $tpl->setVariable("ICON_CLASS", $cssClass);
        $tpl->parseCurrentBlock();
    }
    // fau.
}
