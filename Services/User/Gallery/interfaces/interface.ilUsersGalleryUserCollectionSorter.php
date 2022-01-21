<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilUsersGalleryUserCollectionSorter
{
    /**
     * @param ilUsersGalleryUser[] $users
     * @return ilUsersGalleryUser[]
     */
    public function sort(array $users) : array;
}
