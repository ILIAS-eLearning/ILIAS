<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* List Gui factory for subitems (forum threads, lm pages...)
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesSearch
*/
class ilLuceneSubItemListGUIFactory
{
    private static $instances = array();

    /**
     * get instance by type
     *
     * @param string	$a_type	Object type
     * @return
     * @static
     */
    public static function getInstanceByType($a_type, $a_cmd_class)
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
            return self::$instances[$a_type] = new $full_class($a_cmd_class);
        } else {
            include_once './Services/Object/classes/class.ilObjectSubItemListGUI.php';
            return self::$instances[$a_type] = new ilObjectSubItemListGUI($a_cmd_class);
        }
    }
}
