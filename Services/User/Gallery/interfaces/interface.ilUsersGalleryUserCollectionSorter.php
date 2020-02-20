<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilUsersGalleryUserCollectionSorter
 */
interface ilUsersGalleryUserCollectionSorter
{
    /**
     * @param ilUsersGalleryUser[] $users
     * @return ilUsersGalleryUser[]
     */
    public function sort(array $users);
}
