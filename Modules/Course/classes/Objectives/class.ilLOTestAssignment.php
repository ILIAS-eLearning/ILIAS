<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestAssignment
{
    private $assignment_id = 0;
    private $container_id = 0;
    private $assignment_type = 0;
    private $objective_id = 0;
    private $test_ref_id = 0;
    
    
    /**
     * constructor
     * @param type $a_id
     */
    public function __construct($a_id = 0)
    {
        $this->setAssignmentId($a_id);
        $this->read();
    }
    
    public function setAssignmentId($a_id)
    {
        $this->assignment_id = $a_id;
    }
    
    public function getAssignmentId()
    {
        return $this->assignment_id;
    }
    
    public function setContainerId($a_id)
    {
        $this->container_id = $a_id;
    }
    
    public function getContainerId()
    {
        return $this->container_id;
    }
    
    public function setAssignmentType($a_type)
    {
        $this->assignment_type = $a_type;
    }
    
    public function getAssignmentType()
    {
        return $this->assignment_type;
    }
    
    public function setObjectiveId($a_id)
    {
        $this->objective_id = $a_id;
    }
    
    public function getObjectiveId()
    {
        return $this->objective_id;
    }
    
    public function setTestRefId($a_id)
    {
        $this->test_ref_id = $a_id;
    }
    
    public function getTestRefId()
    {
        return $this->test_ref_id;
    }
    
    /**
     * save settings
     * @return type
     */
    public function save()
    {
        if ($this->getAssignmentId()) {
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    /**
     * Create new aassignment
     * @global type $ilDB
     */
    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->setAssignmentId($ilDB->nextId('loc_tst_assignments'));
        $query = 'INSERT INTO loc_tst_assignments (assignment_id, container_id, assignment_type, objective_id, tst_ref_id) ' .
                'VALUES ( ' .
                $ilDB->quote($this->getAssignmentId(), 'integer') . ', ' .
                $ilDB->quote($this->getContainerId(), 'integer') . ', ' .
                $ilDB->quote($this->getAssignmentType(), 'integer') . ', ' .
                $ilDB->quote($this->getObjectiveId(), 'integer') . ', ' .
                $ilDB->quote($this->getTestRefId(), 'integer') . ' ' .
                ') ';
        $GLOBALS['DIC']['ilLog']->write($query);
        $ilDB->manipulate($query);
    }
    
    /**
     * Update assignment
     * @global type $ilDB
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE loc_tst_assignments ' .
                'SET container_id = ' . $ilDB->quote($this->getContainerId(), 'integer') . ', ' .
                'assignment_type = ' . $ilDB->quote($this->getAssignmentType(), 'integer') . ', ' .
                'objective_id = ' . $ilDB->quote($this->getObjectiveId(), 'integer') . ', ' .
                'tst_ref_id = ' . $ilDB->quote($this->getTestRefId(), 'integer') . ' ' .
                'WHERE assignment_id = ' . $ilDB->quote($this->getAssignmentId(), 'integer');
        $ilDB->manipulate($query);
    }
    
    /**
     * Delete assignment
     * @global type $ilDB
     * @return boolean
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'DELETE FROM loc_tst_assignments ' .
                'WHERE assignment_id = ' . $ilDB->quote($this->getAssignmentId(), 'integer') . ' ';
        $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Read db entry
     * @global type $ilDB
     * @return boolean
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getAssignmentId()) {
            return false;
        }
        
        $query = 'SELECT * FROM loc_tst_assignments ' .
                'WHERE assignment_id = ' . $ilDB->quote($this->getAssignmentId(), 'integer') . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setContainerId($row->container_id);
            $this->setObjectiveId($row->objective_id);
            $this->setAssignmentType($row->assignment_type);
            $this->setTestRefId($row->tst_ref_id);
        }
        return true;
    }
    
    /**
     * Clone assignments
     * @param type $a_target_id
     * @param type $a_copy_id
     */
    public function cloneSettings($a_copy_id, $a_target_id, $a_objective_id)
    {
        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $options->getMappings();
        
        if (!array_key_exists($this->getTestRefId(), $mappings)) {
            return false;
        }
        
        $copy = new ilLOTestAssignment();
        $copy->setContainerId($a_target_id);
        $copy->setAssignmentType($this->getAssignmentType());
        $copy->setObjectiveId($a_objective_id);
        $copy->setTestRefId($mappings[$this->getTestRefId()]);
        $copy->create();
    }
}
