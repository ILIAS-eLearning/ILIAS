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
 * List Gui factory for subitems (forum threads, lm pages...)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesSearch
 */
class ilLuceneSubItemListGUIFactory
{
    private static array $instances = [];

    /**
     * get instance by type
     */
    public static function getInstanceByType(string $a_type, object $a_cmd_class): ilSubItemListGUI
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        if (isset(self::$instances[$a_type])) {
            return self::$instances[$a_type];
        }

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "SubItemListGUI";
        if (@include_once($location . "/class." . $full_class . ".php")) {
            return self::$instances[$a_type] = new $full_class(get_class($a_cmd_class));
        } else {
            return self::$instances[$a_type] = new ilObjectSubItemListGUI(get_class($a_cmd_class));
        }
    }
}
