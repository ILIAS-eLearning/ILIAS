<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilPDSelectedItemsBlockGroup
 */
class ilPDSelectedItemsBlockGroup
{
    protected bool $has_icon = false;
    protected string $icon_path = '';
    protected string $label = '';
    protected array $items = array();

    public function getLabel() : string
    {
        return $this->label;
    }

    public function hasIcon() : bool
    {
        return strlen($this->icon_path) > 0;
    }

    public function getIconPath() : string
    {
        return $this->icon_path;
    }

    /**
     * @param array[] $items
     */
    public function setItems(array $items) : void
    {
        $this->items = $items;
    }

    /**
     * @param array $item
     */
    public function pushItem(array $item) : void
    {
        $this->items[] = $item;
    }

    public function setHasIcon(bool $has_icon) : void
    {
        $this->has_icon = $has_icon;
    }

    public function setIconPath(string $icon_path) : void
    {
        $this->icon_path = $icon_path;
    }

    public function setLabel(string $label) : void
    {
        $this->label = $label;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
