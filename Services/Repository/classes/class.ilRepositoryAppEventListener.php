<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/**
 * Repository app event listener
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRepositoryAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent($a_component, $a_event, $a_params)
    {
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "deleteReference":
                        // remove recommended content
                        $rec_manager = new ilRecommendedContentManager();
                        $rec_manager->removeRecommendationsOfRefId((int) $a_params["ref_id"]);

                        // remove favourites
                        $rec_manager = new ilFavouritesManager();
                        $rec_manager->removeFavouritesOfRefId((int) $a_params["ref_id"]);
                        break;

                    case "beforeDeletion":


                        if ($a_params["object"]->getType() == "usr") {

                            // remove recommended content
                            $rec_manager = new ilRecommendedContentManager();
                            $rec_manager->removeRecommendationsOfUser((int) $a_params["object"]->getId());

                            // remove favourites
                            $rec_manager = new ilFavouritesManager();
                            $rec_manager->removeFavouritesOfUser((int) $a_params["object"]->getId());
                        }

                        if ($a_params["object"]->getType() == "role") {

                            // remove recommended content
                            $rec_manager = new ilRecommendedContentManager();
                            $rec_manager->removeRecommendationsOfRole((int) $a_params["object"]->getId());
                        }
                        break;
                }
                break;
        }

        return true;
    }
}
