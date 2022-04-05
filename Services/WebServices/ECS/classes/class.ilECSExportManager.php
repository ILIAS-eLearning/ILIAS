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
*/
class ilECSExportManager
{
    protected ilDBInterface $db;

    private static ilECSExportManager $instance;

    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get the singelton instance of this ilECSExportManager
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
     */
    public function _isExported(int $a_obj_id) : bool
    {
        $query = 'SELECT * FROM ecs_export ' .
            'WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        if ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }


    /**
     * get all exported econtent ids per server
     */
    public function _getAllEContentIds(int $a_server_id) : array
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
     */
    public function getExportedIds() : array
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
     */
    public function getExportedIdsByType(string $a_type) : array
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
     */
    public function getExportServerIds(int $a_obj_id) : array
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
     */
    public function _getExportedIDsByServer(int $a_server_id) : array
    {
        $query = "SELECT obj_id FROM ecs_export " .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * Lookup server ids of exported objects
     */
    public function lookupServerIds(int $a_obj_id) : array
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
     * @param int $a_server_id id of the server
     * @param int[] $a_ids array of econtent ids
     */
    public function _deleteEContentIds(int $a_server_id, array $a_ids) : bool
    {
        if (!is_array($a_ids) || !count($a_ids)) {
            return true;
        }
        $query = "DELETE FROM ecs_export WHERE " . $this->db->in('econtent_id', $a_ids, false, 'integer') . ' ' .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete by server id
     */
    public function deleteByServer(int $a_server_id) : void
    {
        $query = 'DELETE FROM ecs_export ' .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * is remote object
     */
    public function _isRemote(int $a_server_id, int $a_econtent_id) : bool
    {
        $query = "SELECT obj_id FROM ecs_export " .
            "WHERE econtent_id = " . $this->db->quote($a_econtent_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        if ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return false;
        }
        return true;
    }
}
