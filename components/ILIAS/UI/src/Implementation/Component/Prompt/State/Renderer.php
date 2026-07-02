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

namespace ILIAS\UI\Implementation\Component\Prompt\State;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof State) {
            return $this->renderState($component, $default_renderer);
        }

        if ($component instanceof Confirmation) {
            return $this->renderConfirmation($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderConfirmation(Confirmation $component, RendererInterface $default_renderer): string
    {
        return $default_renderer->render($component->getMessageBox())
            . $default_renderer->render($component->getForm());
    }

    protected function renderState(State $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.promptstate.html', true, true);
        $tpl->setVariable('COMMAND', $component->getCommand());

        foreach ($component->getParameters() as $key => $value) {
            $tpl->setCurrentBlock('param');
            $tpl->setVariable('KEY', $key);
            $tpl->setVariable('VALUE', $value);
            $tpl->parseCurrentBlock();
        }

        $content_component = $component->getContent();
        if ($content_component === null) {
            return $tpl->get();
        }

        if ($content_component instanceof Confirmation) {
            $tpl->setVariable('CONTENT', $this->renderConfirmation($content_component, $default_renderer));
            $buttons = $this->getConfirmationButtons($content_component);
        } else {
            $tpl->setVariable('CONTENT', $default_renderer->render($content_component));
            $buttons = $component->getButtons();

            if ($content_component instanceof \ILIAS\UI\Component\Input\Container\Form\Form) {
                $buttons[] = $this->getFormSubmitButton($content_component);
            }

            $buttons[] = $this->getPromptCloseButton();
        }

        $tpl->setVariable('TITLE', $component->getTitle());
        $tpl->setVariable('BUTTONS', $default_renderer->render($buttons));
        return $tpl->get();
    }

    /**
     * @return \ILIAS\UI\Component\Button\Button[]
     */
    protected function getConfirmationButtons(Confirmation $confirmation): array
    {
        return [
            $this->getUIFactory()->button()->primary(
                'Confirm',
                $confirmation->getForm()->getSubmitSignal()
            ),
            $this->getPromptCloseButton($this->txt('cancel')),
        ];
    }

    protected function getFormSubmitButton(
        \ILIAS\UI\Component\Input\Container\Form\Form $form
    ): \ILIAS\UI\Component\Button\Button {
        return $this->getUIFactory()->button()->standard(
            $form->getSubmitLabel() ?? $this->txt('save'),
            $form->getSubmitSignal()
        );
    }

    protected function getPromptCloseButton(?string $label = null): \ILIAS\UI\Component\Button\Button
    {
        return $this->getUIFactory()->button()
            ->standard($label ?? $this->txt('close'), '')
            ->withOnLoadCode(
                fn($id) => "$('#$id').on('click', (e)=> {
                    let promptId = e.target.closest('dialog').parentNode.id;
                    il.UI.prompt.get(promptId).close();
                });"
            );
    }
}
