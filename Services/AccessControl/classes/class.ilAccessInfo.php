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

define("IL_NO_PERMISSION", "no_permission");
define("IL_MISSING_PRECONDITION", "missing_precondition");
define("IL_NO_OBJECT_ACCESS", "no_object_access");
define("IL_NO_PARENT_ACCESS", "no_parent_access");
define("IL_DELETED", 'object_deleted');
define("IL_STATUS_INFO", 'object_status');
define("IL_STATUS_MESSAGE", IL_STATUS_INFO);
/**
* class ilAccessInfo
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilAccessInfo
{
    const IL_STATUS_INFO = 'object_status';


    public function __construct()
    {
        $this->info_items = array();
    }

    public function clear()
    {
        $this->info_items = array();
    }

    /**
    * add an info item
    */
    public function addInfoItem($a_type, $a_text, $a_data = "")
    {
        $this->info_items[] = array(
                                    "type" => $a_type,
                                    "text" => $a_text,
                                    "data" => $a_data
                                    );
    }

    /**
    * get all info items
    */
    public function getInfoItems()
    {
        return $this->info_items;
    }
    
    public function setQueryData($a_result)
    {
        $this->query = array(
                            "ref_id" => $a_result[1],
                            "permission" => $a_result[2],
                            "cmd" => $a_result[3],
                            "user_id" => $a_result[4]
                            );
        $this->result = $a_result[0];
    }
}
