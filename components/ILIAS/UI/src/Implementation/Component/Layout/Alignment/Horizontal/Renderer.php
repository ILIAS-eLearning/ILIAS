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

namespace ILIAS\UI\Implementation\Component\Layout\Alignment\Horizontal;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Layout\Alignment as I;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        switch(true) {
            case ($component instanceof Component\Layout\Alignment\Horizontal\EvenlyDistributed):
                return $this->renderAlignment($component, 'alignment_horizontal_evenly', $default_renderer);
            case ($component instanceof Component\Layout\Alignment\Horizontal\DynamicallyDistributed):
                return $this->renderAlignment($component, 'alignment_horizontal_dynamic', $default_renderer);

            default:
                return null;
        }
    }

    protected function renderAlignment(
        I\Alignment $component,
        string $alignment_type,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate('tpl.alignment.html', true, true);
        foreach ($component->getBlocks() as $idx => $block) {
            $tpl->setCurrentBlock('block');
            $tpl->setVariable('CONTENT', $default_renderer->render($block));
            $tpl->parseCurrentBlock();
        }
        $tpl->touchBlock($alignment_type);
        return $tpl->get();
    }
}
