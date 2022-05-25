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
 * Media Pool listener. Listens to events of other components.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolAppEventListener
{
    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(
        string $a_component,
        string $a_event,
        array $a_parameter
    ) : void {
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "update":
                        if ($a_parameter["obj_type"] === "mob") {
                            ilMediaPoolItem::updateObjectTitle($a_parameter["obj_id"]);
                        }
                        break;
                }
                break;
        }
    }
}
