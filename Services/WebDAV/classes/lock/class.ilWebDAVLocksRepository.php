<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVLocksRepository
{
    protected ilDBInterface $db;
    
    private string $lock_table = 'dav_lock';
    
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }
    
    public function checkIfLockExistsInDB(string $token) : bool
    {
        $select_query = "SELECT SELECT EXISTS(SELECT 1 FROM $this->lock_table WHERE token = " .
            $this->db->quote($token, 'text') . ") AS count";
        $select_result = $this->db->query($select_query);
        $select_result->numRows();
        $row = $this->db->fetchAssoc($select_result);
        if (isset($row)) {
            return true;
        }
        return false;
    }
    
    public function getLockObjectWithTokenFromDB(string $token) : ?ilWebDAVLockObject
    {
        $query = "SELECT obj_id, ilias_owner, dav_owner, expires, depth, type, scope FROM $this->lock_table"
                        . " WHERE token = " . $this->db->quote($token, 'text')
                        . " AND expires > " . $this->db->quote(time(), 'integer');
        
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if ($row) {
            return new ilWebDAVLockObject(
                $token,
                (int) $row['obj_id'],
                (int) $row['ilias_owner'],
                $row['dav_owner'],
                (int) $row['expires'],
                (int) $row['depth'],
                $row['type'],
                (int) $row['scope']
            );
        }
        
        return null;
    }
    
    public function getLockObjectWithObjIdFromDB(int $obj_id) : ?ilWebDAVLockObject
    {
        $query = "SELECT token, ilias_owner, dav_owner, expires, depth, type, scope FROM $this->lock_table WHERE obj_id = "
                    . $this->db->quote($obj_id, 'integer')
                    . " AND expires > " . $this->db->quote(time(), 'integer');
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if ($row) {
            return new ilWebDAVLockObject(
                $row['token'],
                $obj_id,
                (int) $row['ilias_owner'],
                $row['dav_owner'],
                (int) $row['expires'],
                (int) $row['depth'],
                $row['type'],
                (int) $row['scope']
            );
        }
        
        return null;
    }
    
    public function saveLockToDB(ilWebDAVLockObject $ilias_lock) : void
    {
        $this->db->insert($this->lock_table, array(
            'token' => array('text', $ilias_lock->getToken()),
            'obj_id' => array('integer', $ilias_lock->getObjId()),
            'ilias_owner' => array('integer', $ilias_lock->getIliasOwner()),
            'dav_owner' => array('text', $ilias_lock->getDavOwner()),
            'expires' => array('integer', $ilias_lock->getExpires()),
            'depth' => array('integer', $ilias_lock->getDepth()),
            'type' => array('text', $ilias_lock->getType()),
            'scope' => array('integer', $ilias_lock->getScope())
        ));
    }
    
    public function removeLockWithTokenFromDB(string $token) : int
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE token = " . $this->db->quote($token, "integer"));
    }
    
    public function purgeExpiredLocksFromDB() : int
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE expires < " . $this->db->quote(time(), 'integer'));
    }
    
    public function updateLocks(int $old_obj_id, int $new_obj_id) : int
    {
        return $this->db->update(
            $this->lock_table,
            ["obj_id" => ["integer", $new_obj_id]],
            ["obj_id" => ["integer", $old_obj_id]]
        );
    }
}
