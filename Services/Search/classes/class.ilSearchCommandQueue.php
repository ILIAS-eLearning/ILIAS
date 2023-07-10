<?php

declare(strict_types=1);
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
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchCommandQueue
{
    private static ?self $instance = null;

    protected ilDBInterface $db;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * get singleton instance
     */
    public static function factory(): ilSearchCommandQueue
    {
        if (self::$instance instanceof ilSearchCommandQueue) {
            return self::$instance;
        }
        return self::$instance = new ilSearchCommandQueue();
    }

    /**
     * update / save new entry
     */
    public function store(ilSearchCommandQueueElement $element): void
    {
        $query = "SELECT obj_id, obj_type FROM search_command_queue " .
            "WHERE obj_id = " . $this->db->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $this->db->quote($element->getObjType(), 'text');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $this->update($element);
        } else {
            $this->insert($element);
        }
    }

    /**
     * Insert new entry
     */
    protected function insert(ilSearchCommandQueueElement $element): void
    {
        $query = "INSERT INTO search_command_queue (obj_id,obj_type,sub_id,sub_type,command,last_update,finished) " .
            "VALUES( " .
            $this->db->quote($element->getObjId(), 'integer') . ", " .
            $this->db->quote($element->getObjType(), 'text') . ", " .
            "0, " .
            "''," .
            $this->db->quote($element->getCommand(), 'text') . ", " .
            $this->db->now() . ", " .
            "0 " .
            ")";
        $res = $this->db->manipulate($query);
    }

    /**
     * Update existing entry
     */
    protected function update(ilSearchCommandQueueElement $element): void
    {
        $query = "UPDATE search_command_queue " .
            "SET command = " . $this->db->quote($element->getCommand(), 'text') . ", " .
            "last_update = " . $this->db->now() . ", " .
            "finished = " . $this->db->quote(0, 'integer') . " " .
            "WHERE obj_id = " . $this->db->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $this->db->quote($element->getObjType(), 'text');
        $res = $this->db->manipulate($query);
    }
}
