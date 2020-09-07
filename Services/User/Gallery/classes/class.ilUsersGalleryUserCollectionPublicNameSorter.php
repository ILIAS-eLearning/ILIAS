<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/classes/class.ilAbstractUsersGalleryUserCollectionSorter.php';

/**
 * Class ilUsersGalleryUserCollectionPublicNameSorter
 */
class ilUsersGalleryUserCollectionPublicNameSorter extends ilAbstractUsersGalleryUserCollectionSorter
{
    /**
     * @inheritdoc
     */
    protected function compare(ilUsersGalleryUser $left, ilUsersGalleryUser $right)
    {
        return strcmp($left->getSortablePublicName(), $right->getSortablePublicName());
    }
}
