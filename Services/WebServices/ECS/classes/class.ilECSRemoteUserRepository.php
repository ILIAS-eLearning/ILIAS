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

    public function __construct()
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
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->eru_id;
        }
        return false;
    }

    private function remoteUserExists(
        int $sid,
        int $mid,
        string $remote_usr_id
    ) : bool {
        $query = 'SELECT eru_id FROM ecs_remote_user ' .
            'WHERE sid = ' . $this->db->quote($sid, 'integer') . ' ' .
            'AND mid = ' . $this->db->quote($mid, 'integer') . ' ' .
            'AND remote_usr_id = ' . $this->db->quote($remote_usr_id, 'text');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->eru_id;
        }
        return false;
    }

    /**
     * Create new remote user entry
     */
    public function createIfNotExisting(
        int $sid,
        int $mid,
        int $usr_id,
        string $remote_usr_id
    ) : void {
        if (!$this->exists($sid, $mid, $usr_id)) {
            $next_id = $this->db->nextId('ecs_remote_user');
            $query = 'INSERT INTO ecs_remote_user (eru_id, sid, mid, usr_id, remote_usr_id) ' .
                'VALUES( ' .
                $this->db->quote($next_id) . ', ' .
                $this->db->quote($sid, 'integer') . ', ' .
                $this->db->quote($mid, 'integer') . ', ' .
                $this->db->quote($usr_id, 'integer') . ', ' .
                $this->db->quote($remote_usr_id, 'text') . ' ' .
                ')';
            $this->db->manipulate($query);
        }
    }
    public function createIfRemoteUserNotExisting(
        int $sid,
        int $mid,
        int $usr_id,
        string $remote_usr_id
    ) : void {
        if (!$this->remoteUserExists($sid, $mid, $remote_usr_id)) {
            $next_id = $this->db->nextId('ecs_remote_user');
            $query = 'INSERT INTO ecs_remote_user (eru_id, sid, mid, usr_id, remote_usr_id) ' .
                'VALUES( ' .
                $this->db->quote($next_id) . ', ' .
                $this->db->quote($sid, 'integer') . ', ' .
                $this->db->quote($mid, 'integer') . ', ' .
                $this->db->quote($usr_id, 'integer') . ', ' .
                $this->db->quote($remote_usr_id, 'text') . ' ' .
                ')';
            $this->db->manipulate($query);
        }
    }
    /**
     * Read data set
     */
    public function getECSRemoteUserById(int $remoteUserId) : ?ilECSRemoteUser
    {
        $query = 'SELECT * FROM ecs_remote_user ' .
            'WHERE eru_id = ' . $this->db->quote($remoteUserId, 'integer');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
     */
    public function getECSRemoteUserByUsrId(int $a_usr_id) : ?ilECSRemoteUser
    {
        $query = 'SELECT eru_id FROM ecs_remote_user ' .
            'WHERE usr_id = ' . $this->db->quote($a_usr_id, 'integer');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $this->getECSRemoteUserById($row->eru_id);
        }
        return null;
    }

    /**
     * Get instance for remote usr_id (login|external_account)
     */
    public function getECSRemoteUserByRemoteId(string $remoteUserId) : ?ilECSRemoteUser
    {
        $query = 'SELECT eru_id FROM ecs_remote_user ' .
            'WHERE remote_usr_id = ' . $this->db->quote($remoteUserId, 'text');
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $this->getECSRemoteUserById($row->eru_id);
        }
        return null;
    }
}
