<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

abstract class ilAbstractUsersGalleryCollectionProvider implements ilUsersGalleryCollectionProvider
{
    /**
     * @param array<int, ilObjUser> $users An map of ilObjUser instances, with the respective user id as array key
     * @return ilUsersGalleryUserCollection
     */
    protected function getPopulatedGroup(array $users) : ilUsersGalleryUserCollection
    {
        $sortable_names = ilUserUtil::getNamePresentation(array_keys($users));
        $names = ilUserUtil::getNamePresentation(
            array_keys($users),
            false,
            false,
            '',
            false,
            true,
            false,
            false
        );

        return new ilUsersGalleryGroup(array_map(static function (ilObjUser $user) use ($names, $sortable_names) : ilUsersGalleryUser {
            return  new ilUsersGalleryUserImpl($user, $names[$user->getId()], $sortable_names[$user->getId()]);
        }, $users));
    }

    public function hasRemovableUsers() : bool
    {
        return false;
    }
}
