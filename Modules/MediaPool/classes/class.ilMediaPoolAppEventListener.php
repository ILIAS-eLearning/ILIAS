<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Media Pool listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesMediaPool
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
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        include_once("./Services/Tagging/classes/class.ilTagging.php");
        
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "update":
                        if ($a_parameter["obj_type"] == "mob") {
                            include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
                            ilMediaPoolItem::updateObjectTitle($a_parameter["obj_id"]);
                        }
                        break;
                }
                break;
        }
    }
}
