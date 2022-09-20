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
    protected ilSetting $settings;

    public function __construct(ilObjUser $actor)
    {
        global $DIC;

        $this->actor = $actor;
        $this->fav_manager = new ilFavouritesManager();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
    }

    public function getItems(array $object_type_white_list = array()): array
    {
        $short_desc = $this->settings->get("rep_shorten_description");
        $short_desc_max_length = (int) $this->settings->get("rep_shorten_description_length");

        $favourites = $this->fav_manager->getFavouritesOfUser(
            $this->actor->getId(),
            count($object_type_white_list) > 0 ? $object_type_white_list : null
        );
        $access_granted_favourites = [];
        foreach ($favourites as $idx => $favourite) {
            if (!$this->access->checkAccess('visible', '', $favourite['ref_id'])) {
                continue;
            }

            if ($short_desc && $short_desc_max_length !== 0) {
                $favourite['description'] = ilStr::shortenTextExtended($favourite['description'], $short_desc_max_length, true);
            }

            $access_granted_favourites[$idx] = $favourite;
        }
        return $access_granted_favourites;
    }
}
