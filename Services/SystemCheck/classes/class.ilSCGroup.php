<?php

include_once './Services/Calendar/classes/class.ilDateTime.php';

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check group including different tasks of a component
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroup
{
    private $id = 0;
    private $component_id = '';
    private $component_type = '';
    private $last_update = null;
    private $status = 0;
    
    
    /**
     * Constructor
     * @param type $a_id
     */
    public function __construct($a_id = 0)
    {
        $this->id = $a_id;
        $this->read();
    }
    
    /**
     * lookup component by id
     * @global type $ilDB
     * @param type $a_id
     * @return string
     */
    public static function lookupComponent($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT component FROM sysc_groups ' .
                'WHERE id = ' . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->component;
        }
        return '';
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    
    public function setComponentId($a_comp)
    {
        $this->component_id = $a_comp;
    }
    
    /**
     * Get component
     * @return string
     */
    public function getComponentId()
    {
        return $this->component_id;
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
     * Read group
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return false;
        }
        
        $query = 'SELECT * FROM sysc_groups ' .
                'WHERE id = ' . $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setComponentId($row->component);
            $this->setLastUpdate(new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setStatus($row->status);
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
        
        $this->id = $ilDB->nextId('sysc_groups');
        
        $query = 'INSERT INTO sysc_groups (id,component,status) ' .
                'VALUES ( ' .
                $ilDB->quote($this->getId(), 'integer') . ', ' .
                $ilDB->quote($this->getComponentId(), 'text') . ', ' .
                $ilDB->quote($this->getStatus(), 'integer') . ' ' .
                ')';
        $ilDB->manipulate($query);
        return $this->getId();
    }
}
