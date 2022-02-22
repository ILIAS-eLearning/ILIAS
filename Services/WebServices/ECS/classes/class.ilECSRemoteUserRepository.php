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
 * @author Per Pascal Seeland <pascal.seeland@tik.uni-stuttgart.de>
 */
class ilECSRemoteUserRepository
{
    private ilDBInterface $db;

    /**
     * Constructor (Singleton)
     */
    private function __construct()
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
    }

    /**
     * Check if entry exists for user
     */
    private function exists(
        int $sid,
        int $mid,
        int $usr_id
    ) : bool {
        $query = 'SELECT eru_id FROM ecs_remote_user ' .
            'WHERE sid = ' . $this->db->quote($sid, 'integer') . ' ' .
            'AND mid = ' . $this->db->quote($mid, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($usr_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->eru_id;
        }
        return false;
    }

//     /**
//      * Update remote user entry
//      * @return boolean
//      */
//     public function update(\ilECSRemoteUser $remoteUser): bool
//     {

//         $query = 'UPDATE ecs_remote_user SET ' .
//             'sid = ' . $this->db->quote($this->getServerId(), 'integer') . ', ' .
//             'mid = ' . $this->db->quote($this->getMid(), 'integer') . ', ' .
//             'usr_id = ' . $this->db->quote($this->getUserId(), 'text') . ', ' .
//             'remote_usr_id = ' . $this->db->quote($this->getRemoteUserId(), 'text') . ' ' .
//             'WHERE eru_id = ' . $this->db->quote($this->getId());
//         $this->db->manipulate($query);
//         return true;
//     }

    /**
     * Create new remote user entry
     */
    public function createIfNotExisting(
        int $sid,
        int $mid,
        int $usr_id,
        string $remote_usr_id
    ) {
        if (!$this->exists($sid, $mid, $usr_id)) {
            $next_id = $this->db->nextId('ecs_remote_user');
            $query = 'INSERT INTO ecs_remote_user (eru_id, sid, mid, usr_id, remote_usr_id) ' .
                'VALUES( ' .
                $this->db->quote($next_id) . ', ' .
                $this->db->quote($this->getServerId(), 'integer') . ', ' .
                $this->db->quote($this->getMid(), 'integer') . ', ' .
                $this->db->quote($this->getUserId(), 'integer') . ', ' .
                $this->db->quote($this->getRemoteUserId(), 'text') . ' ' .
                ')';
            $this->db->manipulate($query);
        }
    }
    /**
     * Read data set
     */
    public function getECSRemoteUserById(int $remoteUserId) : \ilECSRemoteUser
    {
        $query = 'SELECT * FROM ecs_remote_user ' .
            'WHERE eru_id = ' . $this->db->quote($remoteUserId, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilECSRemoteUser(
                $remoteUserId,
                $row->sid,
                $row->mid,
                $row->usr_id,
                $row->remote_usr_id
            );
        }
        return null;
    }

    /**
     * Get instance for usr_id
     * @param type $a_usr_id
     * @return \ilECSRemoteUser|null
     */
    public function getECSRemoteUserByUsrId($a_usr_id) : \ilECSRemoteUser
    {
        $query = 'SELECT eru_id FROM ecs_remote_user ' .
            'WHERE usr_id = ' . $this->db->quote($a_usr_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $this->getECSRemoteUserById($row->eru_id);
        }
        return null;
    }
}
