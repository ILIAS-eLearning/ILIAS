<?php

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
 * Class ilLearningSequenceAppEventListener
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioAppEventListener
{
    public static function handleEvent(
        string $component,
        string $event,
        array $parameter
    ): void {
        switch ($component) {
            case "Services/Object":
                switch ($event) {
                    case "beforeDeletion":
                        self::beforeDeletion($parameter);
                        break;
                }
                break;
            case "Services/User":
                switch ($event) {
                    case "firstLogin":
                        self::firstLogin($parameter);
                        break;
                }
                break;
        }
    }

    protected static function beforeDeletion(
        array $parameter
    ): void {
        if (is_object($parameter["object"])) {
            /** @var ilObject $obj */
            $obj = $parameter["object"];
            if ($obj instanceof \ilObjBlog) {
                $blog_id = $obj->getId();
                $action = new ilPortfolioPageAction();
                $action->deletePagesOfBlog($blog_id);
            }
        }
    }

    protected static function firstLogin(
        array $parameter
    ): void {
        $manager = new \ILIAS\Portfolio\Administration\PortfolioRoleAssignmentManager();
        if (isset($parameter["user_obj"]) && is_object($parameter["user_obj"])) {
            /** @var ilObjUser $obj */
            $obj = $parameter["user_obj"];
            if ($obj instanceof  \ilObjUser) {
                $manager->assignPortfoliosOnLogin($obj->getId());
            }
        }
    }
}
