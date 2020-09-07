<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilUsersGalleryUserCollection
 */
interface ilUsersGalleryUserCollection extends Iterator, Countable
{
    /**
     * Set whether or not this group is highlighted
     * @param boolean $status
     */
    public function setHighlighted($status);

    /**
     * Returns whether or not it is a highlighted group
     * @return boolean
     */
    public function isHighlighted();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param $label string
     */
    public function setLabel($label);

    /**
     * @param array $items
     */
    public function setItems(array $items);

    /**
     * @return array
     */
    public function getItems();
}
