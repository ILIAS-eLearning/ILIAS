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
    public function &_getInstance($a_type)
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
