<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/Gallery/interfaces/interface.ilUsersGalleryUser.php';

/**
 * Class ilUsersGalleryUserImpl
 */
class ilUsersGalleryUserImpl implements ilUsersGalleryUser
{
    /**
     * @var ilObjUser
     */
    protected $aggregated_user;

    /**
     * @var string
     */
    protected $public_name = '';

    /**
     * @var string
     */
    protected $sortable_public_name = '';

    /**
     * ilUsersGalleryUserImpl constructor.
     * @param ilObjUser $aggregated_user
     * @param string    $public_name
     * @param string    $sortable_public_name
     */
    public function __construct(ilObjUser $aggregated_user, $public_name, $sortable_public_name)
    {
        $this->aggregated_user = $aggregated_user;
        $this->public_name = $public_name;
        $this->sortable_public_name = $sortable_public_name;
    }

    /**
     * @inheritdoc
     */
    public function hasPublicProfile()
    {
        global $DIC;

        return (
            (!$DIC->user()->isAnonymous() && $this->aggregated_user->getPref('public_profile') == 'y') ||
            $this->aggregated_user->getPref('public_profile') == 'g'
        );
    }

    /**
     * @inheritdoc
     */
    public function getPublicName()
    {
        return $this->public_name;
    }

    /**
     * @inheritdoc
     */
    public function getSortablePublicName()
    {
        return $this->sortable_public_name;
    }

    /**
     * @inheritdoc
     */
    public function getAggregatedUser()
    {
        return $this->aggregated_user;
    }
}
