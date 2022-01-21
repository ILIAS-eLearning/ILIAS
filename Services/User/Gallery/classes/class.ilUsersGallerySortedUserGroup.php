<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilUsersGallerySortedUserGroup implements ilUsersGalleryUserCollection
{
    protected ilUsersGalleryUserCollection $collection;
    protected ilUsersGalleryUserCollectionSorter $sorter;

    public function __construct(ilUsersGalleryUserCollection $collection, ilUsersGalleryUserCollectionSorter $sorter)
    {
        $this->collection = $collection;
        $this->sorter = $sorter;
    }

    public function setItems(array $items) : void
    {
        $this->collection->setItems($items);
    }

    public function getItems() : array
    {
        return $this->collection->getItems();
    }

    public function current() : ilUsersGalleryUser
    {
        return $this->collection->current();
    }

    public function next() : void
    {
        $this->collection->next();
    }

    public function key()
    {
        return $this->collection->key();
    }

    public function valid() : bool
    {
        return $this->collection->valid();
    }

    public function rewind() : void
    {
        $this->collection->setItems($this->sorter->sort($this->collection->getItems()));
        $this->collection->rewind();
    }

    public function count() : int
    {
        return $this->collection->count();
    }

    public function setHighlighted(bool $status) : void
    {
        $this->collection->setHighlighted($status);
    }

    public function isHighlighted() : bool
    {
        return $this->collection->isHighlighted();
    }

    public function setLabel(string $label) : void
    {
        $this->collection->setLabel($label);
    }

    public function getLabel() : string
    {
        return $this->collection->getLabel();
    }
}
