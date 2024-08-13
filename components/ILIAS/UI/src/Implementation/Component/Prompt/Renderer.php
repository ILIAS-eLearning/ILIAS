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

namespace ILIAS\UI\Implementation\Component\Prompt;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Implementation\Component\Prompt\Response;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Prompt\Prompt) {
            return $this->renderPrompt($component, $default_renderer);
        }
        if ($component instanceof Component\Prompt\Response) {
            return $this->renderResponse($component, $default_renderer);
        }

        throw new \LogicException(self::class . " cannot render component '" . get_class($component) . "'.");
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/prompt.min.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Prompt\Prompt::class,
            Component\Prompt\Response::class,
            Component\Prompt\PromptContent::class,
        ];
    }

    protected function renderPrompt(Component\Prompt\Prompt $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.prompt.html', true, true);
        $show_signal = $component->getShowSignal();
        $close_signal = $component->getCloseSignal();
        $url = $component->getAsyncUrl()->__toString();
        $component = $component->withAdditionalOnLoadCode(
            fn($id) => "
                il.UI.prompt.init('$id');
                $(document).on('$show_signal', (e, data) => {il.UI.prompt.get('$id').show(data.options.url);})
                $(document).on('$close_signal', () => {il.UI.prompt.get('$id').close();})
            "
        );
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable('URI', $component->getAsyncUrl()->__toString());

        return $tpl->get();
    }

    protected function renderResponse(Component\Prompt\Response $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.promptresponse.html', true, true);
        $tpl->setVariable('COMMAND', $component->getCommand());

        foreach ($component->getParameters() as $key => $value) {
            $tpl->setCurrentBlock('param');
            $tpl->setVariable('KEY', $key);
            $tpl->setVariable('VALUE', $value);
            $tpl->parseCurrentBlock();
        }

        $content_component = $component->getContent();
        if($content_component === null) {
            return $tpl->get();
        }

        $context_renderer = $default_renderer->withAdditionalContext($component);
        $tpl->setVariable('CONTENT', $context_renderer->render($content_component));
        $tpl->setVariable('TITLE', $component->getTitle());

        $buttons = $component->getButtons();
        if($content_component instanceof \ILIAS\UI\Component\Input\Container\Form\Form) {
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
