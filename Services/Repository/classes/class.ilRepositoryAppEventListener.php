<?php declare(strict_types=1);

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
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "deleteReference":
                        // remove recommended content
                        $rec_manager = new ilRecommendedContentManager();
                        $rec_manager->removeRecommendationsOfRefId((int) $a_parameter["ref_id"]);

                        // remove favourites
                        $rec_manager = new ilFavouritesManager();
                        $rec_manager->removeFavouritesOfRefId((int) $a_parameter["ref_id"]);
                        break;

                    case "beforeDeletion":


                        if ($a_parameter["object"]->getType() === "usr") {

                            // remove recommended content
                            $rec_manager = new ilRecommendedContentManager();
                            $rec_manager->removeRecommendationsOfUser((int) $a_parameter["object"]->getId());

                            // remove favourites
                            $rec_manager = new ilFavouritesManager();
                            $rec_manager->removeFavouritesOfUser((int) $a_parameter["object"]->getId());
                        }

                        if ($a_parameter["object"]->getType() === "role") {

                            // remove recommended content
                            $rec_manager = new ilRecommendedContentManager();
                            $rec_manager->removeRecommendationsOfRole((int) $a_parameter["object"]->getId());
                        }
                        break;
                }
                break;
        }
    }
}
