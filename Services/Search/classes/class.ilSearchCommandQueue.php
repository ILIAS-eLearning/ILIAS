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

include_once './Services/Search/classes/class.ilSearchCommandQueueElement.php';
/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesSearch
*/
class ilSearchCommandQueue
{
    private static $instance = null;


    /**
     * Constructor
     */
    protected function __construct()
    {
    }
    
    /**
     * get singleton instance
     */
    public static function factory()
    {
        if (isset(self::$instance) and self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilSearchCommandQueue();
    }
    
    /**
     * update / save new entry
     */
    public function store(ilSearchCommandQueueElement $element)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT obj_id, obj_type FROM search_command_queue " .
            "WHERE obj_id = " . $ilDB->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($element->getObjType(), 'text');
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $this->update($element);
        } else {
            $this->insert($element);
        }
    }
    
    /**
     * Insert new entry
     */
    protected function insert(ilSearchCommandQueueElement $element)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "INSERT INTO search_command_queue (obj_id,obj_type,sub_id,sub_type,command,last_update,finished) " .
            "VALUES( " .
            $ilDB->quote($element->getObjId(), 'integer') . ", " .
            $ilDB->quote($element->getObjType(), 'text') . ", " .
            "0, " .
            "''," .
            $ilDB->quote($element->getCommand(), 'text') . ", " .
            $ilDB->now() . ", " .
            "0 " .
            ")";
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Update existing entry
     */
    protected function update(ilSearchCommandQueueElement $element)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE search_command_queue " .
            "SET command = " . $ilDB->quote($element->getCommand(), 'text') . ", " .
            "last_update = " . $ilDB->now() . ", " .
            "finished = " . $ilDB->quote(0, 'integer') . " " .
            "WHERE obj_id = " . $ilDB->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($element->getObjType(), 'text');
        $res = $ilDB->manipulate($query);
        return true;
    }
}
