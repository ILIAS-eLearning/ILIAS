<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilUsersGalleryCollectionProvider
 */
interface ilUsersGalleryCollectionProvider
{
    /**
     * @return ilUsersGalleryUserCollection[]
     */
    public function getGroupedCollections();

    /**
     * @return boolean
     */
    public function hasRemovableUsers();
}
