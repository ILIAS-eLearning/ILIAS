<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Manage the ECS exported contents.
* This class contains mainly helper functions to work with exported objects.
*
* @author Per Pascal Seeland<pascal.seeland@tik.uni-stuttgart.de>
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSExportManager
{
    protected ilDBInterface $db;

    private static ilECSExportManager $instance;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get the singelton instance of this ilECSExportManager
     * @return ilECSExportManager
     */
    public static function getInstance() : ilECSExportManager
    {
        if (!isset(self::$instance)) {
            self::$instance = new ilECSExportManager();
        }
        return self::$instance;
    }

    /**
     * Check if object is exported
     * @param int $a_obj_id
     * @return bool
     */
    public function _isExported($a_obj_id)
    {
        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        while ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
    public function _getAllEContentIds($a_server_id)
    {
        $econtent_ids = array();
        $query = "SELECT econtent_id,obj_id FROM ecs_export " .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $econtent_ids[$row->econtent_id] = $row->obj_id;
        }
        return $econtent_ids;
    }

    /**
     * Get exported ids
     * @global ilDB $ilDB
     * @return array
     */
    public function getExportedIds()
    {
        $query = "SELECT obj_id FROM ecs_export ";
        $res = $this->db->query($query);
        $obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * Get exported ids by type
     * @global ilDB $ilDB
     * @return array
     */
    public function getExportedIdsByType($a_type)
    {
        $obj_ids = array();
        $query = "SELECT e.obj_id FROM ecs_export e" .
            " JOIN object_data o ON (e.obj_id = o.obj_id)" .
            " WHERE o.type = " . $this->db->quote($a_type, "text");
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * lookup server ids of exported materials
     * @global ilDB $ilDB
     * @param int $a_obj_id
     * @return array
     */
    public function getExportServerIds($a_obj_id)
    {
        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);

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
    public function _getExportedIDsByServer($a_server_id)
    {
        $query = "SELECT obj_id FROM ecs_export " .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
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
    public function lookupServerIds($a_obj_id)
    {
        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer') . ' ';
        $res = $this->db->query($query);
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
    public function _deleteEContentIds($a_server_id, $a_ids)
    {
        if (!is_array($a_ids) or !count($a_ids)) {
            return true;
        }
        #$query = "DELETE FROM ecs_export WHERE econtent_id IN (".implode(',',ilUtil::quoteArray($a_ids)).')';
        $query = "DELETE FROM ecs_export WHERE " . $this->db->in('econtent_id', $a_ids, false, 'integer') . ' ' .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete by server id
     * @global ilDB $ilDB
     * @param int $a_server_id
     */
    public function deleteByServer($a_server_id)
    {
        $query = 'DELETE FROM ecs_export ' .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * is remote object
     *
     * @access public
     * @static
     *
     * @param int econtent_id
     */
    public function _isRemote($a_server_id, $a_econtent_id)
    {
        $query = "SELECT obj_id FROM ecs_export " .
            "WHERE econtent_id = " . $this->db->quote($a_econtent_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        while ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return false;
        }
        return true;
    }
}
