<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
abstract class ilAbstractUsersGalleryCollectionProvider implements ilUsersGalleryCollectionProvider
{
    /**
     * @param array<int, ilObjUser> $users An map of ilObjUser instances, with the respective user id as array key
     * @return ilUsersGalleryUserCollection
     */
    protected function getPopulatedGroup(array $users): ilUsersGalleryUserCollection
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

        return new ilUsersGalleryGroup(array_map(static function (ilObjUser $user) use ($names, $sortable_names): ilUsersGalleryUser {
            return  new ilUsersGalleryUserImpl($user, $names[$user->getId()], $sortable_names[$user->getId()]);
        }, $users));
    }

    public function hasRemovableUsers(): bool
    {
        return false;
    }
}
