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
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectListGUIFactory
{
    public static function _getListGUIByType(
        string $type,
        int $context = ilObjectListGUI::CONTEXT_REPOSITORY
    ): ilObjectListGUI {
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
