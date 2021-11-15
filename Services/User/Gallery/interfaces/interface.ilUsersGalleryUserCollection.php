<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilUsersGalleryUserCollection extends Iterator, Countable
{
    public function setHighlighted(bool $status) : void;

    public function isHighlighted() : bool;

    public function getLabel() : string;

    public function setLabel(string $label);

    /**
     * @param ilUsersGalleryUser[] $items
     */
    public function setItems(array $items) : void;

    /**
     * @return ilUsersGalleryUser[]
     */
    public function getItems() : array;
}
