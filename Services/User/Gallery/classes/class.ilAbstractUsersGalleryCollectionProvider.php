<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/interfaces/interface.ilUsersGalleryCollectionProvider.php';
require_once 'Services/User/classes/class.ilUserUtil.php';
require_once 'Services/User/Gallery/classes/class.ilUsersGalleryGroup.php';
require_once 'Services/User/Gallery/classes/class.ilUsersGalleryUserImpl.php';

/**
 * Class ilAbstractUsersGalleryCollectionProvider
 */
abstract class ilAbstractUsersGalleryCollectionProvider implements ilUsersGalleryCollectionProvider
{
    /**
     * @param ilObjUser[] $users An array of ilObjUser instances, with the respective user id as array key
     * @return ilUsersGalleryGroup
     */
    protected function getPopulatedGroup(array $users)
    {
        $sortable_names = ilUserUtil::getNamePresentation(array_keys($users));
        $names          = ilUserUtil::getNamePresentation(
            array_keys($users),
            false,
            false,
            '',
            false,
            true,
            false,
            false
        );

        return new ilUsersGalleryGroup(array_map(function (ilObjUser $user) use ($names, $sortable_names) {
            return  new ilUsersGalleryUserImpl($user, $names[$user->getId()], $sortable_names[$user->getId()]);
        }, $users));
    }

    /**
     * @inheritdoc
     */
    public function hasRemovableUsers()
    {
        return false;
    }
}
