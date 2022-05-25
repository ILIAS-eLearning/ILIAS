<?php declare(strict_types=1);

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

class ilUsersGallerySortedUserGroup implements ilUsersGalleryUserCollection
{
    protected ilUsersGalleryUserCollection $collection;
    protected ilUsersGalleryUserCollectionSorter $sorter;

    public function __construct(ilUsersGalleryUserCollection $collection, ilUsersGalleryUserCollectionSorter $sorter)
    {
        $this->collection = $collection;
        $this->sorter = $sorter;
    }

    public function setItems(array $items) : void // Missing array type.
    {
        $this->collection->setItems($items);
    }

    public function getItems() : array // Missing array type.
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
