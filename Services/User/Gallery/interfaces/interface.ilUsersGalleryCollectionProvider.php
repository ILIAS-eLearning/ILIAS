<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilUsersGalleryCollectionProvider
{
    /**
     * @return ilUsersGalleryUserCollection[]
     */
    public function getGroupedCollections() : array;

    public function hasRemovableUsers() : bool;
}
