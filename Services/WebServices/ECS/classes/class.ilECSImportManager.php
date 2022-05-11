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
* Manage the ECS imported contents.
* This class contains mainly helper functions to work with imported objects.
*
* @author Per Pascal Seeland<pascal.seeland@tik.uni-stuttgart.de>
*/
class ilECSImportManager
{
    protected ilDBInterface $db;
    private static ilECSImportManager $instance;

    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get the singleton instance of this ilECSImportManager
     * @return ilECSImportManager
     */
    public static function getInstance() : ilECSImportManager
    {
        if (!isset(self::$instance)) {
            self::$instance = new ilECSImportManager();
        }
        return self::$instance;
    }

    /**
     * Lookup content id
     * The content is the - not necessarily unique - id provided by the econtent type.
     * The econtent id is the unique id from ecs
     * @return string content id
     */
    public function lookupContentId($a_server_id, $a_mid, $a_econtent_id) : string
    {
        $query = 'SELECT * from ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND econtent_id = ' . $this->db->quote($a_econtent_id, 'text');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->content_id;
        }
        return '';
    }

    /**
     * Lookup obj_id by content id
     */
    public function lookupObjIdByContentId(int $a_server_id, int $a_mid, int $a_content_id, string $a_sub_id = null) : int
    {
        $query = "SELECT obj_id FROM ecs_import " .
            "WHERE content_id = " . $this->db->quote($a_content_id, 'integer') . " " .
            "&& mid = " . $this->db->quote($a_mid, 'integer') . " " .
            '&& server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ';

        if (!is_null($a_sub_id)) {
            $query .= 'AND sub_id = ' . $this->db->quote($a_sub_id, 'text');
        } else {
            $query .= 'AND sub_id IS NULL';
        }
        $res = $this->db->query($query);

        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return 0;
    }

    public function lookupObjIdsByContentId($a_content_id) : array
    {
        $query = "SELECT obj_id FROM ecs_import " .
            "WHERE content_id = " . $this->db->quote($a_content_id, 'integer');

        $res = $this->db->query($query);

        $obj_ids = array();
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return  $obj_ids;
    }

    /**
     * Lookup econtent id
     * The econtent id is the unique id from ecs
     * @return int content id
     */
    public function lookupEContentIdByContentId($a_server_id, $a_mid, $a_content_id) : int
    {
        $query = 'SELECT * from ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND content_id = ' . $this->db->quote($a_content_id, 'text');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->econtent_id;
        }
        return 0;
    }

    /**
     * get all imported links
     *
     */
    public function getAllImportedRemoteObjects($a_server_id) : array
    {
        $all = array();
        $query = "SELECT * FROM ecs_import ei JOIN object_data obd ON ei.obj_id = obd.obj_id " .
            'WHERE server_id = ' . $this->db->quote($a_server_id) . ' ' .
            'AND ' . $this->db->in('type', ilECSUtils::getPossibleRemoteTypes(), false, 'text');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $all[$row->econtent_id] = $row->obj_id;
        }

        return $all;
    }

    /**
     * lookup obj ids by mid
     *
     * @param int mid
     * @return array int
     */
    public function _lookupObjIdsByMID($a_server_id, $a_mid) : array
    {
        $query = "SELECT * FROM ecs_import " .
            "WHERE mid = " . $this->db->quote($a_mid, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');

        $res = $this->db->query($query);
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * get econent_id
     */
    public function _lookupEContentId(int $a_obj_id) : string
    {
        $query = "SELECT * FROM ecs_import WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->econtent_id;
        }
        return "";
    }

    /**
     * Lookup server id of imported content
     */
    public function lookupServerId(int $a_obj_id) : int
    {
        $query = 'SELECT * FROM ecs_import WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->server_id;
        }
        return 0;
    }

    /**
     * Lookup obj_id
     *
     */
    public function _lookupObjIds($a_server_id, $a_econtent_id) : array
    {
        $query = "SELECT obj_id FROM ecs_import WHERE econtent_id  = " . $this->db->quote($a_econtent_id, 'text') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = (int) $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * loogup obj_id by econtent and mid and server_id
     */
    public function _lookupObjId(int $a_server_id, string $a_econtent_id, int $a_mid, ?string $a_sub_id = null) : int
    {
        $query = "SELECT obj_id FROM ecs_import " .
            "WHERE econtent_id = " . $this->db->quote($a_econtent_id, 'text') . " " .
            "AND mid = " . $this->db->quote($a_mid, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ';

        if (!is_null($a_sub_id)) {
            $query .= 'AND sub_id = ' . $this->db->quote($a_sub_id, 'text');
        } else {
            $query .= 'AND sub_id IS NULL';
        }
        $res = $this->db->query($query);

        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return 0;
    }

    /**
     * Lookup mid
     *
     */
    public function _lookupMID(int $a_server_id, int $a_obj_id) : int
    {
        $query = "SELECT * FROM ecs_import WHERE obj_id = " . $this->db->quote($a_obj_id) . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->mid;
        }
        return 0;
    }

    /**
     * Lookup mids by
     *
     * @param int econtent_id
     */
    public function _lookupMIDs($a_server_id, $a_econtent_id) : array
    {
        $query = "SELECT mid FROM ecs_import WHERE econtent_id = " . $this->db->quote($a_econtent_id, 'text') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        $mids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mids[] = $row->mid;
        }
        return $mids;
    }

    /**
     * Delete by obj_id
     *
     * @param int obj_id
     */
    public function _deleteByObjId($a_obj_id) : bool
    {
        $query = "DELETE FROM ecs_import " .
            "WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete by server id
     */
    public function deleteByServer(int $a_server_id) : void
    {
        $query = 'DELETE FROM ecs_import ' .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Delete ressources
     *
     * @param string[] $a_econtent_ids
     */
    public function deleteRessources(int $a_server_id, int $a_mid, array $a_econtent_ids) : bool
    {
        $query = 'DELETE FROM ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND ' . $this->db->in('econtent_id', $a_econtent_ids, false, 'text');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * check if econtent is imported for a specific mid
     */
    public function _isImported(int $a_server_id, string $a_econtent_id, int $a_mid, ?string $a_sub_id = null) : int
    {
        return $this->_lookupObjId($a_server_id, $a_econtent_id, $a_mid, $a_sub_id);
    }

    public function resetServerId($a_server_id) : bool
    {
        $query = 'UPDATE ecs_import SET server_id = ' . $this->db->quote(0, 'integer') .
            ' WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }
}
