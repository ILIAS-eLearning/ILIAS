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
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Component\Prompt\Prompt) {
            return $this->renderPrompt($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/prompt.min.js');
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
        $tpl->setVariable('CLOSE_LABEL', $this->txt('close'));

        return $tpl->get();
    }

}
