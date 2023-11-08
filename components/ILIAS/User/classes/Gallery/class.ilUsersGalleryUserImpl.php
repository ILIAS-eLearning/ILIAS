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
class ilUsersGalleryUserImpl implements ilUsersGalleryUser
{
    public function __construct(
        protected ilObjUser $aggregated_user,
        protected string $public_name,
        protected string $sortable_public_name
    ) {
    }

    public function hasPublicProfile(): bool
    {
        global $DIC;

        return (
            (!$DIC->user()->isAnonymous() && $this->aggregated_user->getPref('public_profile') === 'y') ||
            $this->aggregated_user->getPref('public_profile') === 'g'
        );
    }

    public function getPublicName(): string
    {
        return $this->public_name;
    }

    public function getSortablePublicName(): string
    {
        return $this->sortable_public_name;
    }

    public function getAggregatedUser(): ilObjUser
    {
        return $this->aggregated_user;
    }
}
