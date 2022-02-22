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
     * @param type $a_server_id
     * @param type $a_mid
     * @param type $a_econtent_id
     * @return string content id
     */
    public function lookupContentId($a_server_id, $a_mid, $a_econtent_id)
    {
        $query = 'SELECT * from ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND econtent_id = ' . $this->db->quote($a_econtent_id, 'text');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->content_id;
        }
        return '';
    }

    /**
     * Lookup obj_id by content id
     * @param type $a_server_id
     * @param type $a_mid
     * @param type $a_content_id
     * @param type $a_sub_id
     */
    public function lookupObjIdByContentId($a_server_id, $a_mid, $a_content_id, $a_sub_id = null)
    {
        $query = "SELECT obj_id FROM ecs_import " .
            "WHERE content_id = " . $this->db->quote($a_content_id, 'integer') . " " .
            "AND mid = " . $this->db->quote($a_mid, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ';

        if (!is_null($a_sub_id)) {
            $query .= 'AND sub_id = ' . $this->db->quote($a_sub_id, 'text');
        } else {
            $query .= 'AND sub_id IS NULL';
        }
        $res = $this->db->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        return 0;
    }

    public function lookupObjIdsByContentId($a_content_id)
    {
        $query = "SELECT obj_id FROM ecs_import " .
            "WHERE content_id = " . $this->db->quote($a_content_id, 'integer');

        $res = $this->db->query($query);

        $obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return  $obj_ids;
    }

    /**
     * Lookup econtent id
     * The econtent id is the unique id from ecs
     * @param type $a_server_id
     * @param type $a_mid
     * @param type $a_econtent_id
     * @return int content id
     */
    public function lookupEContentIdByContentId($a_server_id, $a_mid, $a_content_id)
    {
        $query = 'SELECT * from ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND content_id = ' . $this->db->quote($a_content_id, 'text');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->econtent_id;
        }
        return 0;
    }

    /**
     * get all imported links
     *
     */
    public function getAllImportedRemoteObjects($a_server_id)
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
    public function _lookupObjIdsByMID($a_server_id, $a_mid)
    {
        $query = "SELECT * FROM ecs_import " .
            "WHERE mid = " . $this->db->quote($a_mid, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids ? $obj_ids : array();
    }

    /**
     * get econent_id
     *
     * @param int obj_id
     */
    public function _lookupEContentId($a_obj_id)
    {
        $query = "SELECT * FROM ecs_import WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->econtent_id;
        }
        return 0;
    }

    /**
     * Lookup server id of imported content
     */
    public function lookupServerId(int $a_obj_id) : int
    {
        $query = 'SELECT * FROM ecs_import WHERE obj_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return intval($row->server_id);
        }
        return 0;
    }

    /**
     * Lookup obj_id
     *
     */
    public function _lookupObjIds($a_server_id, $a_econtent_id)
    {
        $query = "SELECT obj_id FROM ecs_import WHERE econtent_id  = " . $this->db->quote($a_econtent_id, 'text') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = intval($row->obj_id);
        }
        return $obj_ids;
    }

    /**
     * loogup obj_id by econtent and mid and server_id
     *
     * @param int econtent_id
     *
     */
    public function _lookupObjId($a_server_id, $a_econtent_id, $a_mid, $a_sub_id = null)
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

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        return 0;
    }

    /**
     * Lookup mid
     *
     */
    public function _lookupMID($a_server_id, $a_obj_id)
    {
        $query = "SELECT * FROM ecs_emport WHERE obj_id = " . $this->db->quote($a_obj_id) . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->mid;
        }
        return 0;
    }

    /**
     * Lookup mids by
     *
     * @param int econtent_id
     */
    public function _lookupMIDs($a_server_id, $a_econtent_id)
    {
        $query = "SELECT mid FROM ecs_import WHERE econtent_id = " . $this->db->quote($a_econtent_id, 'text') . " " .
            'AND server_id = ' . $this->db->quote($a_server_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mids[] = $row->mid;
        }
        return $mids ? $mids : array();
    }

    /**
     * Delete by obj_id
     *
     * @param int obj_id
     */
    public function _deleteByObjId($a_obj_id)
    {
        $query = "DELETE FROM ecs_import " .
            "WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete by server id
     * @param int $a_server_id
     */
    public function deleteByServer($a_server_id)
    {
        $query = 'DELETE FROM ecs_import ' .
            'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Delete ressources
     * @param type $a_server_id
     * @param type $a_mid
     * @param type $a_econtent_ids
     */
    public function deleteRessources($a_server_id, $a_mid, $a_econtent_ids)
    {
        $query = 'DELETE FROM ecs_import ' .
                'WHERE server_id = ' . $this->db->quote($a_server_id, 'integer') . ' ' .
                'AND mid = ' . $this->db->quote($a_mid, 'integer') . ' ' .
                'AND ' . $this->db->in('econtent_id', (array) $a_econtent_ids, false, 'text');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * check if econtent is imported for a specific mid
     *
     * @param int econtent id
     * @param int mid
     */
    public function _isImported($a_server_id, $a_econtent_id, $a_mid, $a_sub_id = null)
    {
        return $this->_lookupObjId($a_server_id, $a_econtent_id, $a_mid, $a_sub_id);
    }

    public function resetServerId($a_server_id)
    {
        $query = 'UPDATE ecs_import SET server_id = ' . $this->db->quote(0, 'integer') .
            ' WHERE server_id = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }
}
