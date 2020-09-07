<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* TableGUI class for timings administration
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilTimingUser
{
    private $ref_id = 0;
    private $usr_id = 0;
    private $start = null;
    private $end = null;
    
    private $is_scheduled = false;
    
    /**
     * Constructor
     * @param type $a_ref_id
     * @param type $a_usr_id
     */
    public function __construct($a_ref_id, $a_usr_id)
    {
        $this->ref_id = $a_ref_id;
        $this->usr_id = $a_usr_id;
        
        $this->start = new ilDateTime(0, IL_CAL_UNIX);
        $this->end = new ilDateTime(0, IL_CAL_UNIX);
        
        $this->read();
    }
    
    /**
     * get user id
     * @return type
     */
    public function getUserId()
    {
        return $this->usr_id;
    }
    
    /**
     * Get ref_id
     * @return type
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * Check if an entry exists for user
     */
    public function isScheduled()
    {
        return $this->is_scheduled;
    }
    
    /**
     * Use to set start date
     * @return ilDateTime
     */
    public function getStart()
    {
        return $this->start;
    }
    
    /**
     * Use to set date
     * @return ilDateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
    
    /**
     * Create new entry
     */
    public function create()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($this->isScheduled()) {
            return $this->update();
        }
        
        $query = 'INSERT INTO crs_timings_user (ref_id, usr_id, sstart, ssend ) VALUES ( ' .
                $ilDB->quote($this->getRefId(), 'integer') . ', ' .
                $ilDB->quote($this->getUserId(), 'integer') . ', ' .
                $ilDB->quote($this->getStart()->get(IL_CAL_UNIX, 'integer')) . ', ' .
                $ilDB->quote($this->getEnd()->get(IL_CAL_UNIX, 'integer')) . ' ' .
                ')';
        $ilDB->manipulate($query);
        
        $this->is_scheduled = true;
    }
    
    /**
     * Update
     * @global type $ilDB
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$this->isScheduled()) {
            return $this->create();
        }
        
        $query = 'UPDATE crs_timings_user ' .
                'SET sstart = ' . $ilDB->quote($this->getStart()->get(IL_CAL_UNIX, 'integer')) . ', ' .
                'ssend = ' . $ilDB->quote($this->getEnd()->get(IL_CAL_UNIX, 'integer')) . ' ' .
                'WHERE ref_id = ' . $ilDB->quote($this->getRefId(), 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($this->getUserId(), 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Delete entry
     * @global type $ilDB
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM crs_timings_user ' . ' ' .
                'WHERE ref_id = ' . $ilDB->quote($this->getRefId(), 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($this->getUserId(), 'integer');
        $ilDB->manipulate($query);
        
        $this->is_scheduled = false;
    }

        
    
    /**
     * Read from db
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM crs_timings_user ' .
                'WHERE ref_id = ' . $ilDB->quote($this->getRefId(), 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($this->getUserId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->is_scheduled = true;
            $this->start = new ilDateTime($row->sstart, IL_CAL_UNIX);
            $this->end = new ilDateTime($row->ssend, IL_CAL_UNIX);
        }
        return $this->isScheduled();
    }
}
