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
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use LogicException;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data\URI;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(C\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof State) {
            return $this->renderState($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderState(State $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.prompt_state.html', true, true);

        $instruction = $component->getInstruction();

        $tpl->setVariable('INSTRUCTION', $instruction->value);
        $tpl->setVariable('REDIRECT_URL', (string) $component->getRedirect());

        if ($instruction === Instruction::CLOSE || $instruction === Instruction::REDIRECT) {
            return $tpl->get();
        }

        $primary_content = $component->getPrimaryContent();
        if (null === $primary_content) {
            throw new LogicException('Prompt State Show MUST have primary content.');
        }

        $tpl->setVariable('TITLE', (string) $component->getTitle());
        $tpl->setVariable('PRIMARY_CONTENT', $default_renderer->render($primary_content));
        $tpl->setVariable('BUTTONS', $default_renderer->render($this->getPromptButtons($primary_content)));

        foreach ($component->getSecondaryContent() as $secondary_content) {
            // checking interface level for placement is sufficient
            if ($secondary_content instanceof C\MessageBox\MessageBox) {
                $tpl->setCurrentBlock('secondary_content_top');
                $tpl->setVariable('SECONDARY_CONTENT_TOP', $default_renderer->render($secondary_content));
            } else {
                $tpl->setCurrentBlock('secondary_content_bottom');
                $tpl->setVariable('SECONDARY_CONTENT_BOTTOM', $default_renderer->render($secondary_content));
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /** @return I\Button\Button[] */
    protected function getPromptButtons(C\Prompt\IsPromptContent $content): array
    {
        if ($content instanceof I\Input\Container\Form\Standard) {
            return $this->createFormSubmitButtons($content);
        }
        if ($content instanceof I\MessageBox\MessageBox) {
            return $content->getButtons();
        }
        return [];
    }

    /** @return I\Button\Button[] */
    protected function createFormSubmitButtons(I\Input\Container\Form\Standard $form): array
    {
        foreach ($form->getAdditionalFormActions() as $action => $label) {
            $buttons[] = $this->getUIFactory()->button()->standard($label, $form->getSubmitSignal()->withOptions(['formaction' => $action]));
        }
        $buttons[] = $this->getUIFactory()->button()->standard($form->getSubmitLabel() ?? $this->txt('save'), $form->getSubmitSignal());

        return $buttons;
    }
}
