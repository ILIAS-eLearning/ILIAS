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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use ILIAS\GlobalScreen\Collector\Map\AbstractMap;
use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Map extends AbstractMap
{
    public function __construct(private readonly MainMenuItemFactory $factory)
    {
        parent::__construct();
    }

    protected function getTitleSorter(): Closure
    {
        return static function (isItem $item_one, isItem $item_two): int {
            if (!$item_one instanceof hasTitle || !$item_two instanceof hasTitle) {
                return 0;
            }

            return strnatcmp($item_one->getTitle(), $item_two->getTitle());
        };
    }

    protected function getPositionSorter(): Closure
    {
        return static fn(isItem $item_one, isItem $item_two): int => $item_one->getPosition() - $item_two->getPosition();
    }

    public function sort(): void
    {
        parent::sort();

        $replace_children_sorted = function (isItem &$item): void {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                uasort($children, $this->getPositionSorter());
                $item = $item->withChildren($children);
            }
        };
        $this->walk($replace_children_sorted);
    }

    protected function getLostItem(IdentificationInterface $identification): Lost
    {
        return $this->factory->custom(Lost::class, new NullIdentification($identification))
                             ->withAlwaysAvailable(true)
                             ->withVisibilityCallable(
                                 fn(): bool => false
                             )->withTitle('Lost');
    }
}
