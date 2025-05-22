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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use ILIAS\GlobalScreen\Collector\Map\AbstractMap;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Metabar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;
use ILIAS\GlobalScreen\isGlobalScreenItem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Map extends AbstractMap
{
    private readonly MetaBarItemFactory $factory;

    public function __construct(?MetaBarItemFactory $factory = null)
    {
        parent::__construct();
        //        $this->factory = $factory;
    }

    #[\Override]
    protected function getTitleSorter(): Closure
    {
        return static function (isItem $item_one, isItem $item_two): int {
            if (!$item_one instanceof hasTitle || !$item_two instanceof hasTitle) {
                return 0;
            }

            return strnatcmp($item_one->getTitle(), $item_two->getTitle());
        };
    }

    #[\Override]
    protected function getPositionSorter(): Closure
    {
        return static fn(isItem $item_one, isItem $item_two): int => $item_one->getPosition() - $item_two->getPosition();
    }

    protected function getLostItem(IdentificationInterface $identification): isGlobalScreenItem
    {
        return $this->factory->topParentItem($identification)
                             ->withVisibilityCallable(
                                 fn(): bool => false
                             )->withTitle('Lost');
    }
}
