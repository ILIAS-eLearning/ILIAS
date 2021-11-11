<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilUsersGalleryGroup implements ilUsersGalleryUserCollection
{
    /** @var ilUsersGalleryUser[] */
    protected array $users = [];
    protected bool $highlighted = false;
    protected string $label = '';

    /**
     * @param ilUsersGalleryUser[] $users
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    public function setHighlighted(bool $status) : void
    {
        $this->highlighted = $status;
    }

    public function isHighlighted() : bool
    {
        return $this->highlighted;
    }

    public function setLabel(string $label) : void
    {
        $this->label = $label;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function setItems(array $items) : void
    {
        $this->users = $items;
    }

    public function getItems() : array
    {
        return $this->users;
    }

    public function count() : int
    {
        return count($this->users);
    }

    public function current() : ilUsersGalleryUser
    {
        return current($this->users);
    }

    public function next() : void
    {
        next($this->users);
    }

    public function key()
    {
        return key($this->users);
    }

    public function valid() : bool
    {
        return key($this->users) !== null;
    }

    public function rewind() : void
    {
        reset($this->users);
    }
}
