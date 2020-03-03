<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/interfaces/interface.ilUsersGalleryUserCollectionSorter.php';

/**
 * Class ilAbstractUsersGalleryUserCollectionSorter
 */
abstract class ilAbstractUsersGalleryUserCollectionSorter implements ilUsersGalleryUserCollectionSorter
{
    /**
     * @param ilUsersGalleryUser $left
     * @param ilUsersGalleryUser $right
     * @return int
     */
    abstract protected function compare(ilUsersGalleryUser $left, ilUsersGalleryUser $right);

    /**
     * @inheritdoc
     */
    final public function sort(array $users)
    {
        $that = $this;
        uasort($users, function (ilUsersGalleryUser $left, ilUsersGalleryUser $right) use ($that) {
            return $that->compare($left, $right);
        });

        return $users;
    }
}
