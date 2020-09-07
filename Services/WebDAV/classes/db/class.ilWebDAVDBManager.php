<?php

/**
 * Class ilWebDAVDBManager
 *
 * According to the repository pattern, this class manages the DB actions from other WebDAV classes. At the moment, the
 * only direct interaction between WebDAV and the DB is for the locking mechanism.
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVDBManager
{
    /**
     * @var ilDB
     */
    protected $db;
    
    private $lock_table = 'dav_lock';
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function checkIfLockExistsInDB($token)
    {
        $select_query = "SELECT count(*) AS cnt FROM $this->locks_table WHERE token = " . $this->db->quote($token, 'text');
        $select_result = $this->db->query($select_query);
        $row = $this->db->fetchAssoc($select_result);
        if (isset($row)) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns lock Object from given tocken
     * @param string $token
     * @return ilWebDAVLockObject|boolean
     */
    public function getLockObjectWithTokenFromDB($token)
    {
        $query = "SELECT * FROM $this->lock_table"
                        . " WHERE token = " . $this->db->quote($token, 'text')
                        . " AND expires > " . $this->db->quote(time(), 'integer');
        
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if ($row) {
            return ilWebDAVLockObject::createFromAssocArray($row);
        }
        
        return false;
    }
    
    public function getLockObjectWithObjIdFromDB($obj_id)
    {
        $query = "SELECT * FROM $this->lock_table WHERE obj_id = "
                    . $this->db->quote($obj_id, 'integer')
                    . " AND expires > " . $this->db->quote(time(), 'integer');
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if ($row) {
            return ilWebDAVLockObject::createFromAssocArray($row);
        }
        
        return false;
    }
    
    public function saveLockToDB(ilWebDAVLockObject $ilias_lock)
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
    
    /**
     * Removes one specific lock
     *
     * @param integer $token
     * @return array with affected lock (if there was a lock)
     */
    public function removeLockWithTokenFromDB($token)
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE token = " . $this->db->quote($token, "integer"));
    }
    
    /**
     * Removes all locks from DB that are expired (expires < time())
     *
     * @return array with all affected locks
     */
    public function purgeExpiredLocksFromDB()
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE expires < " . $this->db->quote(time(), 'integer'));
    }
}
