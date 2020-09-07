<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
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
    private static $item_list_gui = array();
    
    /**
     * Get list gui by type
     * This method caches all the returned list guis
     * @param string $a_type object type
     * @return object item_list_gui
     * @static
     */
    public static function factory($a_type)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilLog = $DIC->logger()->sess();
        $ilCtrl = $DIC['ilCtrl'];
        
        if (isset(self::$item_list_gui[$a_type])) {
            return self::$item_list_gui[$a_type];
        }
        
        if (!$a_type) {
            return self::$item_list_gui[$a_type] = $item_list_gui;
        }

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);

        $full_class = "ilObj" . $class . "ListGUI";

        include_once($location . "/class." . $full_class . ".php");
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
