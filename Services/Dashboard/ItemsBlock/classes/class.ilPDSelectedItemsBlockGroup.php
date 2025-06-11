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

class ilPDSelectedItemsBlockGroup
{
    protected bool $has_icon = false;
    protected string $icon_path = '';
    protected string $label = '';
    protected array $items = [];

    final public function getLabel(): string
    {
        return $this->label;
    }

    final public function hasIcon(): bool
    {
        return $this->icon_path !== '';
    }

    final public function getIconPath(): string
    {
        return $this->icon_path;
    }

    /**
     * @param array[] $items
     */
    final public function setItems(array $items): void
    {
        $this->items = $items;
    }

    final public function pushItem(array $item): void
    {
        $this->items[] = $item;
    }

    final public function setHasIcon(bool $has_icon): void
    {
        $this->has_icon = $has_icon;
    }

    final public function setIconPath(string $icon_path): void
    {
        $this->icon_path = $icon_path;
    }

    final public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    final public function getItems(): array
    {
        return $this->items;
    }
}
