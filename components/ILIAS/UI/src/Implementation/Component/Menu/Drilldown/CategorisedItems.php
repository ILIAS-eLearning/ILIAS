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

namespace ILIAS\UI\Implementation\Component\Menu\Drilldown;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\Menu\Drilldown;

/**
 * Standard Drilldown Menu Control
 */
class CategorisedItems extends Drilldown\Drilldown implements IMenu\Drilldown\CategorisedItems
{
    protected bool $filter_enabled = false;

    public function withItemsFilter(bool $enabled): self
    {
        $clone = clone $this;
        $clone->filter_enabled = $enabled;
        return $clone;
    }

    public function getItemsFilter(): bool
    {
        return $this->filter_enabled;
    }

    protected function checkItemParameter(array $items): void
    {
        $classes = [
            IMenu\Sub::class,
        ];
        $this->checkArgListElements("items", $items, $classes);
    }
}
