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

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Signal as SignalImplementation;
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

    private string $edit_solution_command = '';
    private bool $question_worked_through = false;
    private string $revert_changes_link_target = '';
    private bool $discard_solution_button_enabled = false;
    private string $skip_question_link_target = '';
    private string $instant_feedback_command = '';
    private bool $answer_freezing_enabled = false;
    private bool $force_instant_response_enabled = false;
    private string $question_mark_link_target = '';
    private bool $question_marked = false;
    private bool $anything_rendered = false;
    private ?Signal $show_discard_modal_signal = null;

    public function __construct(
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer
    ) {
    }

    public function setEditSolutionCommand(string $edit_solution_command): void
    {
        $this->edit_solution_command = $edit_solution_command;
    }

    public function setQuestionWorkedThrough(bool $question_worked_through): void
    {
        $this->question_worked_through = $question_worked_through;
    }

    public function setRevertChangesLinkTarget(string $revert_changes_link_target): void
    {
        $this->revert_changes_link_target = $revert_changes_link_target;
    }

    public function setDiscardSolutionButtonEnabled(bool $discard_solution_button_enabled): void
    {
        $this->discard_solution_button_enabled = $discard_solution_button_enabled;
    }

    public function setSkipQuestionLinkTarget(string $skip_question_link_target): void
    {
        $this->skip_question_link_target = $skip_question_link_target;
    }

    public function setInstantFeedbackCommand(string $instant_feedback_command): void
    {
        $this->instant_feedback_command = $instant_feedback_command;
    }

    public function setForceInstantResponseEnabled(bool $force_instant_response_enabled): void
    {
        $this->force_instant_response_enabled = $force_instant_response_enabled;
    }

    public function setAnswerFreezingEnabled(bool $answer_freezing_enabled): void
    {
        $this->answer_freezing_enabled = $answer_freezing_enabled;
    }

    public function setQuestionMarkLinkTarget(string $question_mark_link_target): void
    {
        $this->question_mark_link_target = $question_mark_link_target;
    }

    public function setQuestionMarked(bool $question_marked): void
    {
        $this->question_marked = $question_marked;
    }

    public function setAnythingRendered(): void
    {
        $this->anything_rendered = true;
    }

    public function setShowDiscardModalSignal(Signal $signal): void
    {
        $this->show_discard_modal_signal = $signal;
    }

    private function getShowDiscardModalSignal(): Signal
    {
        return $this->show_discard_modal_signal ?? new SignalImplementation('');
    }

    public function getActionsHTML(): string
    {
        $tpl = $this->getTemplate('actions');
        $actions = [];

        if ($this->question_mark_link_target) {
            $this->renderActionsIcon(
                $tpl,
                $this->getQuestionMarkIconSource(),
                $this->getQuestionMarkIconLabel(),
                'ilTestMarkQuestionIcon'
            );
            $target = $this->question_mark_link_target;
            $actions[] = $this->ui_factory->button()->shy(
                $this->getQuestionMarkActionLabel(),
                ''
            )->withAdditionalOnLoadCode(
                static function (string $id) use ($target): string {
                    return "document.getElementById('$id').addEventListener('click', "
                        . '(e) => {'
                        . " il.TestPlayerQuestionEditControl.checkNavigation('{$target}', 'show', e);"
                        . '});';
                }
            );
        }

        if ($this->skip_question_link_target) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->lng->txt('postpone_question'),
                $this->skip_question_link_target
            );
        }

        if ($actions !== []) {
            $actions[] = $this->ui_factory->divider()->horizontal();
        }

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('tst_revert_changes'),
            $this->revert_changes_link_target
        )
        ->withUnavailableAction(!$this->revert_changes_link_target)
        ->withAdditionalOnLoadCode(
            static fn(string $id): string => "document.getElementById('{$id}').classList.add('ilTestRevertChangesAction')"
        );

        $actions[] = $this->ui_factory->button()->shy($this->lng->txt('discard_answer'), '#')
        ->withUnavailableAction(!$this->discard_solution_button_enabled)
        ->withOnClick($this->getShowDiscardModalSignal());

        $list = $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt('actions'));
        $tpl->setVariable('ACTION_MENU', $this->ui_renderer->render($list));

        return $tpl->get();
    }

    public function getHTML(): string
    {
        $tpl = $this->getTemplate('toolbar');
        if ($this->edit_solution_command) {
            $this->renderSubmitButton(
                $tpl,
                $this->edit_solution_command,
                $this->getEditSolutionButtonLabel()
            );
        }

        if ($this->instant_feedback_command !== '') {
            $this->renderInstantFeedbackButton(
                $tpl,
                $this->instant_feedback_command,
                $this->getCheckButtonLabel(),
                $this->force_instant_response_enabled
            );
        }

        if ($this->anything_rendered) {
            $this->parseNavigation($tpl);
        }

        return $tpl->get();
    }

    private function getEditSolutionButtonLabel(): string
    {
        if ($this->question_worked_through) {
            return $this->lng->txt('edit_answer');
        }

        return $this->lng->txt('answer_question');
    }

    private function getCheckButtonLabel(): string
    {
        if ($this->answer_freezing_enabled) {
            return $this->lng->txt('submit_and_check');
        }

        return $this->lng->txt('check');
    }

    private function getQuestionMarkActionLabel(): string
    {
        if ($this->question_marked) {
            return $this->lng->txt('tst_remove_mark');
        }

        return $this->lng->txt('tst_question_mark');
    }


    private function getQuestionMarkIconLabel(): string
    {
        if ($this->question_marked) {
            return $this->lng->txt('tst_question_marked');
        }

        return$this->lng->txt('tst_question_not_marked');
    }

    private function getQuestionMarkIconSource(): string
    {
        if ($this->question_marked) {
            return ilUtil::getImagePath('object/marked.svg');
        }

        return ilUtil::getImagePath('object/marked_.svg');
    }

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

    private function parseNavigation(ilTemplate $tpl): void
    {
        $tpl->setCurrentBlock('question_related_navigation');
        $tpl->parseCurrentBlock();
    }

    private function parseButtonsBlock(ilTemplate $tpl): void
    {
        $tpl->setCurrentBlock('buttons');
        $tpl->parseCurrentBlock();
    }

    private function renderButtonInstance(ilTemplate $tpl, Button $button): void
    {
        $tpl->setCurrentBlock('button_instance');
        $tpl->setVariable('BUTTON_INSTANCE', $this->ui_renderer->render($button));
        $tpl->parseCurrentBlock();

        $this->parseButtonsBlock($tpl);
        $this->setAnythingRendered();
    }

    private function renderSubmitButton(
        ilTemplate $tpl,
        string $command,
        string $label
    ): void {
        $on_load_code = $this->getOnLoadCode($command);
        $this->renderButtonInstance(
            $tpl,
            $this->ui_factory->button()->standard($label, '')->withAdditionalOnLoadCode($on_load_code)
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

    private function renderActionsIcon(
        ilTemplate $tpl,
        string $icon_src,
        string $label,
        string $css_class
    ): void {
        $tpl->setCurrentBlock('actions_icon');
        $tpl->setVariable('ICON_SRC', $icon_src);
        $tpl->setVariable('ICON_TEXT', $label);
        $tpl->setVariable('ICON_CLASS', $css_class);
        $tpl->parseCurrentBlock();
    }
}
