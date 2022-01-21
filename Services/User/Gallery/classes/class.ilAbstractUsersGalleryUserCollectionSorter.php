<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

abstract class ilAbstractUsersGalleryUserCollectionSorter implements ilUsersGalleryUserCollectionSorter
{
    abstract protected function compare(ilUsersGalleryUser $left, ilUsersGalleryUser $right) : int;

    final public function sort(array $users) : array
    {
        uasort($users, function (ilUsersGalleryUser $left, ilUsersGalleryUser $right) : int {
            return $this->compare($left, $right);
        });

        return $users;
    }
}
