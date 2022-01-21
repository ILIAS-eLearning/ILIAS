<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilUsersGalleryUserCollectionPublicNameSorter extends ilAbstractUsersGalleryUserCollectionSorter
{
    protected function compare(ilUsersGalleryUser $left, ilUsersGalleryUser $right) : int
    {
        return strcmp($left->getSortablePublicName(), $right->getSortablePublicName());
    }
}
