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
use ILIAS\UI\Implementation\Component\Prompt\State\State;
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

        $this->cannotHandleComponent($component);
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

        $tpl->setVariable('CONTENT', $default_renderer->render($content_component));
        $tpl->setVariable('TITLE', $component->getTitle());

        $buttons = $component->getButtons();
        if ($content_component instanceof \ILIAS\UI\Component\Input\Container\Form\Form) {
            $submit_button = $this->getUIFactory()->button()->standard(
                $content_component->getSubmitLabel() ?? $this->txt("save"),
                $content_component->getSubmitSignal()
            );
            $buttons[] = $submit_button;
        }

        $buttons[] = $this->getUIFactory()->button()
            ->standard($this->txt('close_prompt'), '')
            ->withOnLoadCode(
                fn($id) => "$('#$id').on('click', (e)=> {
                    let promptId = e.target.closest('dialog').parentNode.id;
                    il.UI.prompt.get(promptId).close();
                });"
            );

        $tpl->setVariable('BUTTONS', $default_renderer->render($buttons));
        return $tpl->get();
    }

}
