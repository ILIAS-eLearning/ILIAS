<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/interfaces/interface.ilUsersGalleryUserCollection.php';

/**
 * Class ilUsersGallerySortedUserGroup
 */
class ilUsersGallerySortedUserGroup implements ilUsersGalleryUserCollection
{
    /**
     * @var ilUsersGalleryUserCollection
     */
    protected $collection;

    /**
     * @var ilUsersGalleryUserCollectionSorter
     */
    protected $sorter;

    /**
     * ilUsersGallerySortedUserCollection constructor.
     * @param ilUsersGalleryUserCollection       $collection
     * @param ilUsersGalleryUserCollectionSorter $sorter
     */
    public function __construct(ilUsersGalleryUserCollection $collection, ilUsersGalleryUserCollectionSorter $sorter)
    {
        $this->collection = $collection;
        $this->sorter     = $sorter;
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items)
    {
        $this->collection->setItems($items);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->collection->getItems();
    }

    /**
     * @inheritdoc
     * @return ilUsersGalleryUser
     */
    public function current()
    {
        return $this->collection->current();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->collection->next();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->collection->key();
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->collection->valid();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->collection->setItems($this->sorter->sort($this->collection->getItems()));
        $this->collection->rewind();
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @inheritdoc
     */
    public function setHighlighted($status)
    {
        $this->collection->setHighlighted($status);
    }

    /**
     * @inheritdoc
     */
    public function isHighlighted()
    {
        return $this->collection->isHighlighted();
    }

    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        $this->collection->setLabel($label);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->collection->getLabel();
    }
}
