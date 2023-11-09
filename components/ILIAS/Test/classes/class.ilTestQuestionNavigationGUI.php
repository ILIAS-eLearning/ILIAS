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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Button\Button;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilTestQuestionNavigationGUI
{
    public const SHOW_DISABLED_COMMANDS = false;

    public const CSS_CLASS_SUBMIT_BUTTONS = 'ilc_qsubmit_Submit';
    private \ILIAS\DI\UIServices $ui;

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
    private $anythingRendered = false;

    /**
     * @param ilLanguage $lng
     */


    public function __construct(
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer
    ) {
    }

    /**
     * @return string
     */
    public function getEditSolutionCommand(): string
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
    public function isQuestionWorkedThrough(): bool
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
    public function getSubmitSolutionCommand(): string
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
    public function getRevertChangesLinkTarget(): string
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
    public function isDiscardSolutionButtonEnabled(): bool
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
    public function getSkipQuestionLinkTarget(): string
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
    public function getInstantFeedbackCommand(): string
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
    public function isAnswerFreezingEnabled(): bool
    {
        return $this->answerFreezingEnabled;
    }

    /**
     * @return boolean
     */
    public function isForceInstantResponseEnabled(): bool
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
    public function getRequestHintCommand(): string
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
    public function getShowHintsCommand(): string
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
    public function hintRequestsExist(): bool
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
    public function getQuestionMarkLinkTarget(): string
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
    public function isQuestionMarked(): bool
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
    public function isAnythingRendered(): bool
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

    public function getActionsHTML(): string
    {
        $tpl = $this->getTemplate('actions');
        $actions = [];

        if ($this->getQuestionMarkLinkTarget()) {
            $this->renderActionsIcon(
                $tpl,
                $this->getQuestionMarkIconSource(),
                $this->getQuestionMarkIconLabel(),
                'ilTestMarkQuestionIcon'
            );
            $actions[] = $this->ui_factory->button()->shy(
                $this->getQuestionMarkActionLabel(),
                $this->getQuestionMarkLinkTarget()
            );
        }

        if ($this->getSkipQuestionLinkTarget()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->lng->txt('postpone_question'),
                $this->getSkipQuestionLinkTarget()
            );
        }

        if ($actions !== []) {
            $actions[] = $this->ui_factory->divider()->horizontal();
        }

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('tst_revert_changes'),
            $this->getRevertChangesLinkTarget()
        )->withUnavailableAction(!$this->getRevertChangesLinkTarget());

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('discard_answer'),
            '#'
        )
        ->withUnavailableAction(!$this->isDiscardSolutionButtonEnabled())
        ->withAdditionalOnLoadCode(
            fn($id) => "document.getElementById('$id').addEventListener(
                'click',
                 ()=>$('#tst_discard_solution_modal').modal('show')
            )"
        );

        $list = $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt("actions"));
        $tpl->setVariable('ACTION_MENU', $this->ui_renderer->render($list));

        return $tpl->get();
    }


    /**
     * @return string
     */
    public function getHTML(): string
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
            $this->renderInstantFeedbackButton(
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

    private function getEditSolutionButtonLabel(): string
    {
        if ($this->isQuestionWorkedThrough()) {
            return $this->lng->txt('edit_answer');
        }

        return $this->lng->txt('answer_question');
    }

    private function getCheckButtonLabel(): string
    {
        if ($this->isAnswerFreezingEnabled()) {
            return $this->lng->txt('submit_and_check');
        }

        return $this->lng->txt('check');
    }

    private function getRequestHintButtonLabel(): string
    {
        if ($this->hintRequestsExist()) {
            return $this->lng->txt('button_request_next_question_hint');
        }

        return $this->lng->txt('button_request_question_hint');
    }

    // fau: testNav - adjust mark icon and action labels
    private function getQuestionMarkActionLabel(): string
    {
        if ($this->isQuestionMarked()) {
            return $this->lng->txt('tst_remove_mark');
        }

        return $this->lng->txt('tst_question_mark');
    }


    private function getQuestionMarkIconLabel(): string
    {
        if ($this->isQuestionMarked()) {
            return $this->lng->txt('tst_question_marked');
        }

        return$this->lng->txt('tst_question_not_marked');
    }
    // fau.

    private function getQuestionMarkIconSource(): string
    {
        if ($this->isQuestionMarked()) {
            return ilUtil::getImagePath('object/marked.svg');
        }

        return ilUtil::getImagePath('object/marked_.svg');
    }

    // fau: testNav - add parameter for template purpose
    /**
     * Get the template
     * @param	string	$a_purpose ('toolbar' | 'actions')
     * @return ilTemplate
     */
    private function getTemplate($a_purpose = 'toolbar'): ilTemplate
    {
        switch ($a_purpose) {
            case 'toolbar':
                return new ilTemplate(
                    'tpl.tst_question_navigation.html',
                    true,
                    true,
                    'components/ILIAS/Test'
                );
            default:
            case 'actions':
                return new ilTemplate(
                    'tpl.tst_question_actions.html',
                    true,
                    true,
                    'components/ILIAS/Test'
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
    private function renderButtonInstance(ilTemplate $tpl, Button $button)
    {
        $tpl->setCurrentBlock("button_instance");
        $tpl->setVariable("BUTTON_INSTANCE", $this->ui_renderer->render($button));
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
    private function renderSubmitButton(
        ilTemplate $tpl,
        string $command,
        string $label
    ): void {
        $this->renderButtonInstance(
            $tpl,
            $this->ui_factory->button()->standard($label, $command)
        );
    }

    private function renderInstantFeedbackButton(
        ilTemplate $tpl,
        string $command,
        string $label,
        bool $is_primary
    ): void {
        $on_load_code = $this->getOnLoadCode($command);
        if ($is_primary) {
            $this->renderButtonInstance(
                $tpl,
                $this->ui_factory->button()->primary($label, '')->withAdditionalOnLoadCode($on_load_code)
            );
            return;
        }

        $this->renderButtonInstance(
            $tpl,
            $this->ui_factory->button()->standard($label, '')->withAdditionalOnLoadCode($on_load_code)
        );
    }

    private function getOnLoadCode(string $command): Closure
    {
        return static function ($id) use ($command): string {
            return "document.getElementById('$id').addEventListener('click', "
                . '(e) => {'
                . "  e.target.setAttribute('name', 'cmd[$command]');"
                . '  e.target.form.requestSubmit(e.target);'
                . '});';
        };
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
