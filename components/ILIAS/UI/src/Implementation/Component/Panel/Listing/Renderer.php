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

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Panel\Listing\Standard as ListingStandard;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Item\Standard as ItemStandard;
use ILIAS\UI\Implementation\Component\Panel\HasExpandableRenderer;

class Renderer extends AbstractComponentRenderer
{
    use HasExpandableRenderer;

    /**
     * @inheritdoc
     */
    public function render(Component $component, RendererInterface $default_renderer): string
    {
        return match (true) {
            $component instanceof ListingStandard => $this->renderStandard($component, $default_renderer),
            default => $this->cannotHandleComponent($component),
        };
    }

    protected function renderStandard(ListingStandard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.listing_standard.html', true, true);

        if ($component->isExpandable()) {
            $component = $this->parseActions($component);
        }

        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable('ID', $id);

        $tpl->setVariable(
            'HEADING',
            $this->parseHeading(
                $component,
                $id,
                $default_renderer,
                $this->getUIFactory(),
                4
            )->get()
        );

        if ($component->isExpandable()) {
            $tpl = $this->declareExpandable($component, $id, $tpl);
        }

        foreach ($component->getItemGroups() as $group) {
            if (!$group instanceof Group) {
                continue;
            }

            $group = $group->withHeadingLevel(5);
            $items = [];
            foreach ($group->getItems() as $item) {
                $items[] = $item instanceof ItemStandard ? $item->withHeadingLevel(6) : $item;
            }
            $group = $group->withItems($items);

            $tpl->setCurrentBlock('group');
            $tpl->setVariable('ITEM_GROUP', $default_renderer->render($group));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }
}
