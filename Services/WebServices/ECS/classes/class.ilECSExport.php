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
* Storage of ECS exported objects.
* This class stores the econent id and informations whether an object is exported or not.
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSExport
{
    protected $db = null;

    protected $server_id = 0;
    protected $obj_id = 0;
    protected $econtent_id = 0;
    protected $exported = false;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_server_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->server_id = $a_server_id;
        $this->obj_id = $a_obj_id;
        
        $this->db = $ilDB;
        $this->read();
    }

    /**
     * Get server id
     * @return int
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     * Set server id
     * @param int $a_server_id
     */
    public function setServerId($a_server_id)
    {
        $this->server_id = $a_server_id;
    }

    /**
     * Check if object is exported
     * @param int $a_obj_id
     * @return bool
     */
    public static function _isExported($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }


    /**
     * get all exported econtent ids per server
     *
     * @access public
     * @static
     *
     */
    public static function _getAllEContentIds($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT econtent_id,obj_id FROM ecs_export " .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $econtent_ids[$row->econtent_id] = $row->obj_id;
        }
        return $econtent_ids ? $econtent_ids : array();
    }

    /**
     * Get exported ids
     * @global ilDB $ilDB
     * @return array
     */
    public static function getExportedIds()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT obj_id FROM ecs_export ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids ? $obj_ids : array();
    }
    
    /**
     * Get exported ids by type
     * @global ilDB $ilDB
     * @return array
     */
    public static function getExportedIdsByType($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT e.obj_id FROM ecs_export e" .
            " JOIN object_data o ON (e.obj_id = o.obj_id)" .
            " WHERE o.type = " . $ilDB->quote($a_type, "text");
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids ? $obj_ids : array();
    }

    /**
     * lookup server ids of exported materials
     * @global ilDB $ilDB
     * @param int $a_obj_id
     * @return array
     */
    public static function getExportServerIds($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        $sids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sids[] = $row->server_id;
        }
        return $sids;
    }

    /**
     * get exported ids for server
     *
     * @access public
     * @return
     * @static
     */
    public static function _getExportedIDsByServer($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT obj_id FROM ecs_export " .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids ? $obj_ids : array();
    }

    /**
     * Lookup server ids of exported objects
     * @global ilDB $ilDB
     * @param <type> $a_obj_id
     * @return <type>
     */
    public static function lookupServerIds($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ';
        $res = $ilDB->query($query);
        $sids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sids[] = $row->server_id;
        }
        return $sids;
    }
    
    /**
     * Delete econtent ids for server
     *
     * @access public
     * @static
     *
     * @param array array of econtent ids
     */
    public static function _deleteEContentIds($a_server_id, $a_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!is_array($a_ids) or !count($a_ids)) {
            return true;
        }
        #$query = "DELETE FROM ecs_export WHERE econtent_id IN (".implode(',',ilUtil::quoteArray($a_ids)).')';
        $query = "DELETE FROM ecs_export WHERE " . $ilDB->in('econtent_id', $a_ids, false, 'integer') . ' ' .
            'AND server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete by server id
     * @global ilDB $ilDB
     * @param int $a_server_id
     */
    public static function deleteByServer($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_export ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
    }
    
    /**
     * is remote object
     *
     * @access public
     * @static
     *
     * @param int econtent_id
     */
    public static function _isRemote($a_server_id, $a_econtent_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT obj_id FROM ecs_export " .
            "WHERE econtent_id = " . $ilDB->quote($a_econtent_id, 'integer') . " " .
            'AND server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return false;
        }
        return true;
    }
    
    /**
     * Set exported
     *
     * @access public
     * @param bool export status
     *
     */
    public function setExported($a_status)
    {
        $this->exported = $a_status;
    }
    
    /**
     * check if an object is exported or not
     *
     * @access public
     *
     */
    public function isExported()
    {
        return (bool) $this->exported;
    }

    /**
     * set econtent id
     *
     * @access public
     * @param int econtent id (received from ECS::addResource)
     *
     */
    public function setEContentId($a_id)
    {
        $this->econtent_id = $a_id;
    }
    
    /**
     * get econtent id
     *
     * @access public
     * @return int econtent id
     *
     */
    public function getEContentId()
    {
        return $this->econtent_id;
    }
    
    /**
     * Save
     *
     * @access public
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM ecs_export " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            'AND server_id = ' . $ilDB->quote($this->getServerId());
        $res = $ilDB->manipulate($query);
    
        if ($this->isExported()) {
            $query = "INSERT INTO ecs_export (server_id,obj_id,econtent_id) " .
                "VALUES ( " .
                $this->db->quote($this->getServerId(), 'integer') . ', ' .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote($this->getEContentId(), 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        
        return true;
    }
    
    /**
     * Read
     * @access private
     */
    private function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM ecs_export WHERE " .
            "obj_id = " . $this->db->quote($this->obj_id, 'integer') . " AND " .
            'server_id = ' . $ilDB->quote($this->getServerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->econtent_id = $row->econtent_id;
            $this->exported = true;
        }
    }
    
    public static function deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_export' .
            ' WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
}
