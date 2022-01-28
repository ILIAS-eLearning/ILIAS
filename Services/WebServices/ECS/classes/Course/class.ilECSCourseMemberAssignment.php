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
 * Storage of ecs course assignments
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseMemberAssignment
{
    const STATUS_ASSIGNED = 0;
    const STATUS_LOCAL_DELETED = 1;
    
    private ilDBInterface $db;
    
    private $id;
    private $server;
    private $mid;
    private $cms_id;
    private int $cms_sub_id = 0;
    private $obj_id;
    private $uid;
    private int $status = 0;
    
    
    /**
     * Constructor
     */
    public function __construct($a_id = 0)
    {
        global $DIC;
        
        $this->db = $DIC->database();
        
        $this->id = $a_id;
        
        $this->read();
    }
    
    /**
     * Lookup missing assignments;
     * @param string account
     * @return ilECSCourseMemberAssignment[]
     */
    public static function lookupMissingAssignmentsOfUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT id FROM ecs_course_assignments ' .
                'WHERE usr_id = ' . $ilDB->quote($a_usr_id, 'text');
        $res = $ilDB->query($query);

        $assignments = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[] = new self($row->id);
        }
        return $assignments;
    }
    
    /**
     * Delete by obj_id
     */
    public static function deleteByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'DELETE FROM ecs_course_assignments ' .
                'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Delete by server id
     * @param type $a_server_id
     * @return boolean
     */
    public static function deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'DELETE FROM ecs_course_assignments ' .
                'WHERE sid = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Lookup user ids
     * @param type $a_cms_id
     * @param type $a_obj_id
     * @return type
     */
    public static function lookupUserIds($a_cms_id, $a_cms_sub_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $cms_sub_id_query = '';
        
        if (is_null($a_cms_sub_id)) {
            $cms_sub_id_query = 'AND cms_sub_id IS NULL ';
        } else {
            $cms_sub_id_query = 'AND cms_sub_id = ' . $ilDB->quote($a_cms_sub_id, 'integer') . ' ';
        }
        
        $query = 'SELECT usr_id FROM ecs_course_assignments ' .
                'WHERE cms_id = ' . $ilDB->quote($a_cms_id, 'integer') . ' ' .
                $cms_sub_id_query .
                'AND obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        
        $usr_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $usr_ids[] = $row->usr_id;
        }
        return $usr_ids;
    }

    /**
     * Lookup assignment of user
     * @param type $a_cms_id
     * @param type $a_obj_id
     * @param type $a_usr_id
     * @return \ilECSCourseMemberAssignment|null
     */
    public static function lookupAssignment($a_cms_id, $a_cms_sub_id, $a_obj_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $cms_sub_id_query = '';
        if (is_null($a_cms_sub_id)) {
            $cms_sub_id_query = 'AND cms_sub_id IS NULL ';
        } else {
            $cms_sub_id_query = 'AND cms_sub_id = ' . $ilDB->quote($a_cms_sub_id, 'integer') . ' ';
        }
        
        $query = 'SELECT id FROM ecs_course_assignments ' .
                'WHERE cms_id = ' . $ilDB->quote($a_cms_id, 'integer') . ' ' .
                $cms_sub_id_query .
                'AND obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($a_usr_id, 'text');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilECSCourseMemberAssignment($row->id);
        }
        return null;
    }


    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set server
     * @param int server_id
     */
    public function setServer($a_server)
    {
        $this->server = $a_server;
    }
    
    /**
     * Get server
     * @return int
     */
    public function getServer()
    {
        return $this->server;
    }
    
    public function setMid($a_mid)
    {
        $this->mid = $a_mid;
    }
    
    public function getMid()
    {
        return $this->mid;
    }
    
    public function setCmsId($a_id)
    {
        $this->cms_id = $a_id;
    }
    
    public function getCmsId()
    {
        return $this->cms_id;
    }
    
    public function setCmsSubId($a_id)
    {
        $this->cms_sub_id = $a_id;
    }
    
    public function getCmsSubId()
    {
        return $this->cms_sub_id;
    }
    
    public function setObjId($a_id)
    {
        $this->obj_id = $a_id;
    }
    
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    public function setUid($a_id)
    {
        $this->uid = $a_id;
    }
    
    public function getUid()
    {
        return $this->uid;
    }
    
    public function setStatus($a_status)
    {
        $this->status = $a_status;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Save new entry
     */
    public function save()
    {
        $this->id = $this->db->nextId('ecs_course_assignments');
        
        
        $assignment = self::lookupAssignment(
            $this->getCmsId(),
            $this->getCmsSubId(),
            $this->getObjId(),
            $this->getUid()
        );
        if ($assignment instanceof ilECSCourseMemberAssignment) {
            $assignment->update();
            return true;
        }
        
        $query = 'INSERT INTO ecs_course_assignments ' .
                '(id,sid,mid,cms_id,cms_sub_id,obj_id,usr_id,status) ' .
                'VALUES( ' .
                $this->db->quote($this->getId(), 'integer') . ', ' .
                $this->db->quote($this->getServer(), 'integer') . ', ' .
                $this->db->quote($this->getMid(), 'integer') . ', ' .
                $this->db->quote($this->getCmsId(), 'integer') . ', ' .
                $this->db->quote($this->getCmsSubId(), 'integer') . ', ' .
                $this->db->quote($this->getObjId(), 'integer') . ', ' .
                $this->db->quote($this->getUid(), 'text') . ', ' .
                $this->db->quote($this->getStatus(), 'integer') . ' ' .
                ')';
        $this->db->manipulate($query);
    }
    
    /**
     * Update assignemt
     * @return boolean
     */
    public function update()
    {
        $query = 'UPDATE ecs_course_assignments ' .
                'SET ' .
                'sid = ' . $this->db->quote($this->getServer(), 'integer') . ', ' .
                'mid = ' . $this->db->quote($this->getMid(), 'integer') . ', ' .
                'cms_id = ' . $this->db->quote($this->getCmsId(), 'integer') . ', ' .
                'cms_sub_id = ' . $this->db->quote($this->getCmsSubId(), 'integer') . ', ' .
                'obj_id = ' . $this->db->quote($this->getObjId(), 'integer') . ', ' .
                'usr_id = ' . $this->db->quote($this->getUid(), 'text') . ', ' .
                'status = ' . $this->db->quote($this->getStatus(), 'integer') . ' ' .
                'WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }
    
    /**
     * Delete entry
     */
    public function delete()
    {
        $query = 'DELETE FROM ecs_course_assignments ' .
            'WHERE id = ' . $$this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }
    


    /**
     * Read from db
     * @return bool
     */
    protected function read()
    {
        if (!$this->getId()) {
            return false;
        }
        
        $query = 'SELECT * FROM ecs_course_assignments ' .
            'WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setServer($row->sid);
            $this->setMid($row->mid);
            $this->setCmsId($row->cms_id);
            $this->setCmsSubId($row->cms_sub_id);
            $this->setObjId($row->obj_id);
            $this->setUid($row->usr_id);
            $this->setStatus($row->status);
        }
    }
}
