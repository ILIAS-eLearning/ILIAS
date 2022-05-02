<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilPDSelectedItemsBlockMembershipsProvider
 */
class ilPDSelectedItemsBlockSelectedItemsProvider implements ilPDSelectedItemsBlockProvider
{
    /**
     * @var ilObjUser
     */
    protected $actor;

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * ilPDSelectedItemsBlockSelectedItemsProvider constructor.
     * @param ilObjUser $actor
     */
    public function __construct(ilObjUser $actor)
    {
        global $DIC;

        $this->actor = $actor;
        $this->fav_manager = new ilFavouritesManager();
        $this->access = $DIC->access();
    }

    /**
     * @inheritdoc
     */
    public function getItems($object_type_white_list = array())
    {
        $favourites = $this->fav_manager->getFavouritesOfUser(
            $this->actor->getId(),
            count($object_type_white_list) > 0 ? $object_type_white_list : null
        );
        $access_granted_favourites = [];
        foreach ($favourites as $idx => $favourite) {
            if (!$this->access->checkAccess('visible', '', $favourite['ref_id'])) {
                continue;
            }
            $access_granted_favourites[$idx] = $favourite;
        }
        return $access_granted_favourites;
    }
}
