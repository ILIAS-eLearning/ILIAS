<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/interfaces/interface.ilUsersGalleryUserCollection.php';

/**
 * Class ilUsersGalleryGroup
 */
class ilUsersGalleryGroup implements ilUsersGalleryUserCollection
{
    /**
     * @var ilUsersGalleryUser[]
     */
    protected $users = [];

    /**
     * @var bool
     */
    protected $highlighted = false;

    /**
     * @var string
     */
    protected $label = '';

    /**
     * ilUsersGalleryGroupImpl constructor.
     * @param ilUsersGalleryUser[] $users
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * @inheritdoc
     */
    public function setHighlighted($status)
    {
        $this->highlighted = (bool) $status;
    }

    /**
     * @inheritdoc
     */
    public function isHighlighted()
    {
        return (bool) $this->highlighted;
    }

    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items)
    {
        $this->users = $items;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->users;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->users);
    }

    /**
     * @inheritdoc
     * @return ilUsersGalleryUser
     */
    public function current()
    {
        return current($this->users);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        next($this->users);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        key($this->users);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return key($this->users) !== null;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->users);
    }
}
