<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/class.ilDateTime.php';

/**
 * Defines a system check task
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTask
{
    const STATUS_NOT_ATTEMPTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    
    
    
    private $id = 0;
    private $grp_id = 0;
    private $last_update = null;
    private $status = 0;
    private $identifier = '';
    
    
    /**
     * Constructor
     * @param type $a_id
     */
    public function __construct($a_id = 0)
    {
        $this->id = $a_id;
        $this->read();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setGroupId($a_id)
    {
        $this->grp_id = $a_id;
    }
    
    public function getGroupId()
    {
        return $this->grp_id;
    }
    
    public function setIdentifier($a_ide)
    {
        $this->identifier = $a_ide;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    
    public function setLastUpdate(ilDateTime $a_update)
    {
        $this->last_update = $a_update;
    }
    
    /**
     * Get last update date
     * @return ilDateTime
     */
    public function getLastUpdate()
    {
        if (!$this->last_update) {
            return $this->last_update = new ilDateTime();
        }
        return $this->last_update;
    }
    
    public function setStatus($a_status)
    {
        $this->status = $a_status;
    }
    
    /**
     * Get status
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool check if task is active
     */
    public function isActive()
    {
        return true;
    }
    
    /**
     * Read group
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return false;
        }
        
        $query = 'SELECT * FROM sysc_tasks ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setGroupId($row->grp_id);
            $this->setLastUpdate(new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setStatus($row->status);
            $this->setIdentifier($row->identifier);
        }
        return true;
    }
    
    /**
     * Create new group
     */
    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->id = $ilDB->nextId('sysc_tasks');
        
        $query = 'INSERT INTO sysc_tasks (id,grp_id,status,identifier) ' .
                'VALUES ( ' .
                $ilDB->quote($this->getId(), 'integer') . ', ' .
                $ilDB->quote($this->getGroupId(), 'integer') . ', ' .
                $ilDB->quote($this->getStatus(), 'integer') . ', ' .
                $ilDB->quote($this->getIdentifier(), 'text') . ' ' .
                ')';
        $ilDB->manipulate($query);
        return $this->getId();
    }
    
    /**
     * Update task
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE sysc_tasks SET ' .
                'last_update = ' . $ilDB->quote($this->getLastUpdate()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), 'timestamp') . ', ' .
                'status = ' . $ilDB->quote($this->getStatus(), 'integer') . ', ' .
                'identifier = ' . $ilDB->quote($this->getIdentifier(), 'text') . ' ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($query);
    }
}
