<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDSelectedItemsBlockGroup
 */
class ilPDSelectedItemsBlockGroup
{
    /**
     * @var bool
     */
    protected $has_icon = false;

    /**
     * @var string
     */
    protected $icon_path = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return boolean
     */
    public function hasIcon()
    {
        return strlen($this->icon_path) > 0;
    }

    /**
     * @string
     */
    public function getIconPath()
    {
        return $this->icon_path;
    }

    /**
     * @param array[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param array $item
     */
    public function pushItem(array $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param bool $has_icon
     */
    public function setHasIcon($has_icon)
    {
        $this->has_icon = $has_icon;
    }

    /**
     * @param string $icon_path
     */
    public function setIconPath($icon_path)
    {
        $this->icon_path = $icon_path;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
