<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilUsersGalleryUserImpl implements ilUsersGalleryUser
{
    protected ilObjUser $aggregated_user;
    protected string $public_name;
    protected string $sortable_public_name;

    public function __construct(ilObjUser $aggregated_user, string $public_name, string $sortable_public_name)
    {
        $this->aggregated_user = $aggregated_user;
        $this->public_name = $public_name;
        $this->sortable_public_name = $sortable_public_name;
    }

    public function hasPublicProfile() : bool
    {
        global $DIC;

        return (
            (!$DIC->user()->isAnonymous() && $this->aggregated_user->getPref('public_profile') === 'y') ||
            $this->aggregated_user->getPref('public_profile') === 'g'
        );
    }

    public function getPublicName() : string
    {
        return $this->public_name;
    }

    public function getSortablePublicName() : string
    {
        return $this->sortable_public_name;
    }

    public function getAggregatedUser() : ilObjUser
    {
        return $this->aggregated_user;
    }
}
