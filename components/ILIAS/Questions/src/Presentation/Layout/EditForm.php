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
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface;

class EditForm implements Renderable
{
    private const string MAIN_SECTION_NAME = 'form';
    public const string CARRY_SECTION_NAME = 'carry';

    private StandardForm $form;

    private ?StandardPanel $content_before_form = null;
    private ?StandardPanel $content_after_form = null;
    private ?InterruptiveModal $confirmation = null;

    public function __construct(
        private readonly FormFactory $form_factory,
        private readonly Language $lng,
        private readonly URLBuilder $url_builder,
        private readonly Section $main_section_inputs,
        private readonly bool $is_final_step,
        private readonly ?Group $carry_inputs
    ) {
        $this->form = $this->buildForm();
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

    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render($this->buildContent());
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

        $content[] = $this->form;

        if ($this->content_after_form !== null) {
            $content[] = $this->content_after_form;
        }

        return $content;
    }

    private function buildForm(): StandardForm
    {
        $form = $this->form_factory->standard(
            $this->url_builder->buildURI()->__toString(),
            $this->buildFormInputs()
        );

        if ($this->is_final_step) {
            return $form->withSubmitLabel($this->lng->txt('save'));
        }

        return $form->withSubmitLabel($this->lng->txt('next'));
    }

    private function buildFormInputs(): array
    {
        $form_inputs = [
            self::MAIN_SECTION_NAME => $this->main_section_inputs
        ];

        if ($this->carry_inputs !== null) {
            $form_inputs[self::CARRY_SECTION_NAME] = $this->carry_inputs
                ->withDedicatedName(self::CARRY_SECTION_NAME);
        }

        return $form_inputs;
    }
}
