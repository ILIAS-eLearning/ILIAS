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

namespace ILIAS\UI\Implementation\Component\Layout\Alignment;

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
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        if ($component instanceof Component\Layout\Alignment\PreferHorizontal) {
            return $this->renderHorizontalAlignment($component, $default_renderer, false);
        }
        if ($component instanceof Component\Layout\Alignment\ForceHorizontal) {
            return $this->renderHorizontalAlignment($component, $default_renderer, true);
        }
        throw new \LogicException("Cannot render: " . get_class($component));
    }

    protected function renderHorizontalAlignment(
        I\PreferHorizontal|I\ForceHorizontal $component,
        RendererInterface $default_renderer,
        bool $force_horizontal
    ): string {
        $tpl = $this->getTemplate('tpl.alignment_horizontal.html', true, true);
        foreach ($component->getBlocksets() as $col => $set) {
            $tpl_block = $this->getTemplate('tpl.alignment_block.html', true, true);
            foreach ($set as $block) {
                $tpl_block->setCurrentBlock('block');
                $tpl_block->setVariable('CONTENT', $default_renderer->render($block));
                $tpl_block->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('group');
            $tpl->setVariable('CONTENT', $tpl_block->get());
            $tpl->parseCurrentBlock();
        }

        if ($force_horizontal) {
            $tpl->touchBlock('forced');
        }

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Layout\Alignment\PreferHorizontal::class,
            Component\Layout\Alignment\ForceHorizontal::class
        ];
    }
}
