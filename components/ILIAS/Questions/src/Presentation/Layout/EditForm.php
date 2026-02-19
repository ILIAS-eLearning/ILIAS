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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class EditForm implements Renderable
{
    private const string MAIN_SECTION_NAME = 'form';

    private StandardForm $form;

    private ?StandardPanel $content_before_form = null;
    private ?StandardPanel $content_after_form = null;
    private ?InterruptiveModal $confirmation = null;
    private ?MessageBox $insert_legacy_text_button = null;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        Input|InputsBuilder $inputs,
        private URLBuilder $default_form_action,
        ?URLBuilder $back_form_action,
        private bool $is_final_step
    ) {
        $this->form = $this->buildForm(
            $inputs,
            $default_form_action,
            $back_form_action,
            $is_final_step
        );
    }

    #[\Override]
    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render($this->buildContent());
    }

    public function isFinalStep(): bool
    {
        return $this->is_final_step;
    }

    public function withContentBeforeForm(
        StandardPanel $content
    ): self {
        $clone = clone $this;
        $clone->content_before_form = $content;
        return $clone;
    }

    public function withContentAfterForm(
        StandardPanel $content
    ): self {
        $clone = clone $this;
        $clone->content_after_form = $content;
        return $clone;
    }

    public function withConfirmation(
        InterruptiveModal $confirmation_modal
    ): self {
        $clone = clone $this;
        $clone->confirmation = $confirmation_modal;
        return $clone;
    }

    public function withInsertLegacyTextsButton(
        URLBuilder $target_builder
    ): self {
        $clone = clone $this;
        $clone->insert_legacy_text_button = $this->ui_factory->messageBox()->info(
            $this->lng->txt('insert_legacy_texts_info')
        )->withButtons([
            $this->ui_factory->button()->standard(
                $this->lng->txt('insert_legacy_texts'),
                $target_builder->buildURI()->__toString()
            )
        ]);
        return $clone;
    }

    public function withRequest(
        ServerRequestInterface $request
    ): self {
        $clone = clone $this;
        $clone->form = $clone->form->withRequest($request);
        return $clone;
    }

    public function getData(): mixed
    {
        $data = $this->form->getData();
        return $data[self::MAIN_SECTION_NAME] ?? null;
    }

    public function withAdditionalAction(
        URLBuilderToken $parameter_token,
        string $parameter_value,
        string $label
    ): self {
        $clone = clone $this;
        $clone->form = $this->form->withAdditionalFormAction(
            $this->default_form_action->buildURI()->withParameter(
                $parameter_token->getName(),
                $parameter_value
            )->__toString(),
            $label
        );
        return $clone;
    }

    private function buildContent(): array
    {
        $content = [];

        if ($this->content_before_form !== null) {
            $content[] = $this->content_before_form;
        }

        if ($this->confirmation !== null) {
            $content[] = $this->confirmation->withOnLoad(
                $this->confirmation->getShowSignal()
            )->withAdditionalOnLoadCode(
                function ($id) {
                    return "var button = {$id}.querySelector('input[type=\"submit\"]'); "
                    . "button.addEventListener('click', (e) => {e.preventDefault();"
                    . 'const form = button.closest("dialog").nextElementSibling;'
                    . "form.action = '{$this->confirmation->getFormAction()}';"
                    . 'form.submit();});';
                }
            );
        }

        if ($this->insert_legacy_text_button !== null) {
            $content[] = $this->insert_legacy_text_button;
        }

        $content[] = $this->form;

        if ($this->content_after_form !== null) {
            $content[] = $this->content_after_form;
        }

        return $content;
    }

    private function buildForm(
        Input|InputsBuilder $inputs,
        URLBuilder $default_form_action,
        ?URLBuilder $back_form_action,
        bool $is_final_step
    ): StandardForm {
        if ($inputs instanceof InputsBuilder) {
            $inputs = $inputs->getInputs();
        }
        $form = $this->ui_factory->input()->container()->form()->standard(
            $default_form_action->buildURI()->__toString(),
            [
                self::MAIN_SECTION_NAME => $inputs
            ]
        );

        if ($back_form_action !== null) {
            $form = $form->withAdditionalFormAction(
                $back_form_action->buildURI()->__toString(),
                $this->lng->txt('previous')
            );
        }

        $submit_action_label = 'next';
        if ($is_final_step) {
            $submit_action_label = 'save';
        }

        return $form->withSubmitLabel(
            $this->lng->txt($submit_action_label)
        );
    }
}
