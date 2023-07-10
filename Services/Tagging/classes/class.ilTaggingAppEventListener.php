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

/**
 * Forum listener. Listens to events of other components.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaggingAppEventListener
{
    /**
    * Handle an event in a listener.
     * @param string $a_component   component, e.g. "Modules/Forum" or "Services/User"
     * @param string $a_event       component, e.g. "Modules/Forum" or "Services/User"
     * @param array  $a_parameter   parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent(
        string $a_component,
        string $a_event,
        array $a_parameter
    ): void {
        // PHP8 Review: 'switch' with single 'case'
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "toTrash":
                        if (!ilObject::_hasUntrashedReference($a_parameter["obj_id"])) {
                            ilTagging::setTagsOfObjectOffline(
                                $a_parameter["obj_id"],
                                ilObject::_lookupType($a_parameter["obj_id"]),
                                0,
                                ""
                            );
                        }
                        break;

                    case "undelete":
                        ilTagging::setTagsOfObjectOffline(
                            $a_parameter["obj_id"],
                            ilObject::_lookupType($a_parameter["obj_id"]),
                            0,
                            "",
                            false
                        );
                        break;

                    case "delete":
                        $ref_ids = ilObject::_getAllReferences($a_parameter["obj_id"]);
                        if (count($ref_ids) == 0) {
                            ilTagging::deleteTagsOfObject(
                                $a_parameter["obj_id"],
                                $a_parameter["type"],
                                0,
                                ""
                            );
                        }
                        break;
                }
                break;
        }
    }
}
