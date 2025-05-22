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

declare(strict_types=1);
/**
* Class ilSearchObjectListFactory
*
* Factory for ListGUI's
* create instances of these classes by type and disables commands like link, delete ...
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-search
*/
class ilSearchObjectListFactory
{
    /*
     * get reference of ilObj<type>ListGUI. Prepare output for search presentation ( Disable link, delete ...)
     *
     * @param string object  type
     * @return object reference of ilObj<type>ListGUI
     */
    public function _getInstance(string $a_type): ilObjectListGUI
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);

        $full_class = "ilObj" . $class . "ListGUI";

        include_once($location . "/class." . $full_class . ".php");
        $item_list_gui = new $full_class();

        $item_list_gui->enableDelete(false);
        $item_list_gui->enablePath(true);
        $item_list_gui->enableCut(false);
        $item_list_gui->enableSubscribe(false);
        $item_list_gui->enableLink(false);

        return $item_list_gui;
    }
}
