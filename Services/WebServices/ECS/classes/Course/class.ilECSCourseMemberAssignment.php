<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Storage of ecs course assignments
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseMemberAssignment
{
    const STATUS_ASSIGNED = 0;
    const STATUS_LOCAL_DELETED = 1;
    
    private $id;
    private $server;
    private $mid;
    private $cms_id;
    private $cms_sub_id = 0;
    private $obj_id;
    private $uid;
    private $status = 0;
    
    
    /**
     * Constructor
     */
    public function __construct($a_id = 0)
    {
        $this->id = $a_id;
        
        $this->read();
    }
    
    /**
     * Lookup missing assignments;
     * @global type $ilDB
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
        
        $obj_ids = array();
        
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
     * @global type $ilDB
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
     * @global type $ilDB
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
     * @global type $ilDB
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
     * @global type $ilDB
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->id = $ilDB->nextId('ecs_course_assignments');
        
        
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
                $ilDB->quote($this->getId(), 'integer') . ', ' .
                $ilDB->quote($this->getServer(), 'integer') . ', ' .
                $ilDB->quote($this->getMid(), 'integer') . ', ' .
                $ilDB->quote($this->getCmsId(), 'integer') . ', ' .
                $ilDB->quote($this->getCmsSubId(), 'integer') . ', ' .
                $ilDB->quote($this->getObjId(), 'integer') . ', ' .
                $ilDB->quote($this->getUid(), 'text') . ', ' .
                $ilDB->quote($this->getStatus(), 'integer') . ' ' .
                ')';
        $ilDB->manipulate($query);
    }
    
    /**
     * Update assignemt
     * @global type $ilDB
     * @return boolean
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE ecs_course_assignments ' .
                'SET ' .
                'sid = ' . $ilDB->quote($this->getServer(), 'integer') . ', ' .
                'mid = ' . $ilDB->quote($this->getMid(), 'integer') . ', ' .
                'cms_id = ' . $ilDB->quote($this->getCmsId(), 'integer') . ', ' .
                'cms_sub_id = ' . $ilDB->quote($this->getCmsSubId(), 'integer') . ', ' .
                'obj_id = ' . $ilDB->quote($this->getObjId(), 'integer') . ', ' .
                'usr_id = ' . $ilDB->quote($this->getUid(), 'text') . ', ' .
                'status = ' . $ilDB->quote($this->getStatus(), 'integer') . ' ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Delete entry
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'DELETE FROM ecs_course_assignments ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    


    /**
     * Read from db
     * @return bool
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return false;
        }
        
        $query = 'SELECT * FROM ecs_course_assignments ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->query($query);
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
