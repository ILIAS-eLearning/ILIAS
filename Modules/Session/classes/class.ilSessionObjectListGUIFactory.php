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
 ********************************************************************
 */

/**
 * @classDescription List GUI factory for session materials in session objects
 * @author Stefan Meyer <meyer@leifos.com>
 * @id $Id$
 *
 * @ingroup ModulesSession
 */
class ilSessionObjectListGUIFactory
{
    private static array $item_list_gui = [];

    /**
     * Get list gui by type
     * This method caches all the returned list guis
     */
    public static function factory(string $a_type): ?ilObjectListGUI
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        if (isset(self::$item_list_gui[$a_type])) {
            return self::$item_list_gui[$a_type];
        }

        if (!$a_type) {
            return null;
        }

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);

        $full_class = "ilObj" . $class . "ListGUI";

        $item_list_gui = new $full_class();

        $item_list_gui->enableDelete(false);
        $item_list_gui->enableCut(false);
        $item_list_gui->enableCopy(false);
        $item_list_gui->enableSubscribe(true);
        $item_list_gui->enableIcon(true);
        $item_list_gui->enableLink(false);
        $item_list_gui->enablePath(false);
        $item_list_gui->enableLinkedPath(false);
        $item_list_gui->enableSearchFragments(false);
        $item_list_gui->enableRelevance(false);
        $item_list_gui->enableCheckbox(false);
        return self::$item_list_gui[$a_type] = $item_list_gui;
    }
}
