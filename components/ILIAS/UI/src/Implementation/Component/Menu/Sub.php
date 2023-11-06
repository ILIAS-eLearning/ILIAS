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

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;

/**
 * Level of Drilldown Control
 */
class Sub extends Menu implements IMenu\Sub
{
    protected bool $active = false;

    /**
     * @param array <Sub|Component\Clickable|Component\Divider\Horizontal> $items
     */
    public function __construct(string $label, array $items)
    {
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
    }
}
