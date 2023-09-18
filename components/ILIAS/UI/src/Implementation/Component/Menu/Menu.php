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

use ILIAS\UI\Component;
use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Basic Menu Control
 */
abstract class Menu implements IMenu\Menu
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Component\Component[]
     */
    protected array $items = [];

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    protected function checkItemParameter(array $items): void
    {
        $classes = [
            Sub::class,
            Component\Clickable::class,
            Component\Link\Link::class,
            Component\Divider\Horizontal::class
        ];
        $this->checkArgListElements("items", $items, $classes);
    }
}
