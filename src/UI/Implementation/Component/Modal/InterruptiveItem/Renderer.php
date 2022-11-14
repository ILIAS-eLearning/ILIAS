<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Modal\InterruptiveItem as ItemInterface;

class Renderer extends AbstractComponentRenderer
{
    public function render(
        Component $component,
        RendererInterface $default_renderer
    ): string {
        $this->checkComponent($component);
        $tpl = $this->getTemplate('tpl.interruptiveItem.html', true, true);

        if ($component instanceof ItemInterface\Standard) {
            $tpl->setCurrentBlock('standard_item');
            $icon = ($component->getIcon()) ?
                $default_renderer->render($component->getIcon()) : '';
            $desc = ($component->getDescription()) ?
                '<br>' . $component->getDescription() : '';
            $tpl->setVariable('ITEM_ICON', $icon);
            $tpl->setVariable('ITEM_ID', $component->getId());
            $tpl->setVariable('ITEM_TITLE', $component->getTitle());
            $tpl->setVariable('ITEM_DESCRIPTION', $desc);
            $tpl->parseCurrentBlock();
        } elseif ($component instanceof ItemInterface\KeyValue) {
            $tpl->setCurrentBlock('standard_item');
            $tpl->setVariable('ITEM_KEY', $component->getKey());
            $tpl->setVariable('ITEM_VALUE', $component->getValue());
            $tpl->parseCurrentBlock();
        } else {
            return '';
        }

        $tpl->setVariable('ITEM_ID', $component->getId());

        return $tpl->get();
    }

    protected function getComponentInterfaceName(): array
    {
        return array(
            ItemInterface\Standard::class,
            ItemInterface\KeyValue::class
        );
    }
}
