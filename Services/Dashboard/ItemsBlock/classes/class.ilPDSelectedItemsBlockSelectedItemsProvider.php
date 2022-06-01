<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

class ilPDSelectedItemsBlockSelectedItemsProvider implements ilPDSelectedItemsBlockProvider
{
    protected ilObjUser $actor;
    protected ilFavouritesManager $fav_manager;
    protected ilAccessHandler $access;

    public function __construct(ilObjUser $actor)
    {
        global $DIC;

        $this->actor = $actor;
        $this->fav_manager = new ilFavouritesManager();
        $this->access = $DIC->access();
    }

    public function getItems(array $object_type_white_list = array()) : array
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
