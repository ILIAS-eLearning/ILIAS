<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Forum listener. Listens to events of other components.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilTaggingAppEventListener
{
    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
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
