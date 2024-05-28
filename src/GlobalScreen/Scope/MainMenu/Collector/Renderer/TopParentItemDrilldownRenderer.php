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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\Data\Factory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;

/**
 * Render a TopItem as Drilldown (DD in Slate)
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class TopParentItemDrilldownRenderer extends BaseTypeRenderer
{
    public function getComponentWithContent(isItem $item): Component
    {
        $entries = [];
        foreach ($item->getChildren() as $child) {
            if (!$child->isVisible()) {
                continue;
            }
            $component = $this->buildEntry($child, $item);
            if ($component === null) {
                continue;
            }
            $entries[] = $component;
        }

        $dd = $this->ui_factory->menu()->drilldown($item->getTitle(), $entries);

        return $this->ui_factory->mainControls()->slate()->drilldown(
            $item->getTitle(),
            $this->getStandardSymbol($item),
            $dd
        );
    }

    protected function buildEntry(AbstractChildItem $item, isTopItem $parent): ?Component
    {
        $title = $item->getTitle();
        $symbol = $this->getStandardSymbol($item);
        $type = get_class($item);

        switch ($type) {
            case RepositoryLink::class:
            case Link::class:
                $act = $this->getDataFactory()->uri(
                    $this->getBaseURL()
                    . '/'
                    . $item->getAction()
                );
                $entry = $this->ui_factory->link()->bulky($symbol, $title, $act);
                break;

            case LinkList::class:
                $links = [];
                foreach ($item->getLinks() as $child) {
                    if (!$child->isVisible()) {
                        continue;
                    }
                    $links[] = $this->buildEntry($child, $parent);
                }
                $entry = $this->ui_factory->menu()->sub($title, $links);
                break;
            case Separator::class:
                $entry = $this->ui_factory->divider()->horizontal()->withLabel($title);
                break;

            default:
                $entry = $this->ui_factory->divider()->horizontal()->withLabel(
                    sprintf($this->txt('unable_to_render'), $title, $parent->getTitle())
                );
        }

        return $entry;
    }

    protected function getDataFactory(): Factory
    {
        return new Factory();
    }

    private function getBaseURL(): string
    {
        return ILIAS_HTTP_PATH;
    }

    private function txt(string $key): string
    {
        return $this->lng->txt($key);
    }
}
