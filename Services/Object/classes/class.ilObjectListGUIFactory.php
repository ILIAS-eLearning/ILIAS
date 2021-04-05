<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectListGUIFactory
{
    public static function _getListGUIByType($a_type, $a_context = ilObjectListGUI::CONTEXT_REPOSITORY)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        
        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "ListGUI";
        if (file_exists($location . "/class." . $full_class . ".php")) {
            include_once($location . "/class." . $full_class . ".php");
            return new $full_class($a_context);
        }

        // php7-todo JL: throw exception instead?
        return new ilObjectListGUI($a_context);
    }
}
