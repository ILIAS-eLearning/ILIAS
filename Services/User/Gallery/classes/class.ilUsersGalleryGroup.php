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

    /**
     * @param ilUsersGalleryUser[] $items
     */
    public function setItems(array $items) : void
    {
        $this->users = $items;
    }

    /**
     * @return ilUsersGalleryUser[]
     */
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
