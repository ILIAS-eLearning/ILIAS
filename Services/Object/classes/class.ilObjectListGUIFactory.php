<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectListGUI.php';

/**
* Class ilObjectListGUIFactory
*
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
*/
class ilObjectListGUIFactory
{
    public static function _getListGUIByType(
        $type,
        $context = ilObjectListGUI::CONTEXT_REPOSITORY
    ) {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $class = $objDefinition->getClassName($type);
        $full_class = "ilObj" . $class . "ListGUI";
        if (class_exists($full_class)) {
            return new $full_class($context);
        }

        // php7-todo JL: throw exception instead?
        return new ilObjectListGUI($context);
    }
} // END class.ilObjectListGUIFactory
