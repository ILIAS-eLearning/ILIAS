<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectListGUIFactory
{
    public static function _getListGUIByType(
        string $type,
        int $context = ilObjectListGUI::CONTEXT_REPOSITORY
    ) : ilObjectListGUI {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        
        $class = $objDefinition->getClassName($type);
        $location = $objDefinition->getLocation($type);
        $full_class = "ilObj" . $class . "ListGUI";
        if (file_exists($location . "/class." . $full_class . ".php")) {
            include_once($location . "/class." . $full_class . ".php");
            return new $full_class($context);
        }

        throw new ilObjectException("ilObjectListGUI for type $type not found.");
    }
}
