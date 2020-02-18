<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTasks
{

    /**
     * @var ilSCGroup
     */
    private static $instances = array();
    
    private $grp_id = 0;
    private $tasks = array();
    
    /**
     * Singleton constructor
     */
    private function __construct($a_grp_id)
    {
        $this->grp_id = $a_grp_id;
        $this->read();
    }
    
    /**
     * Get singleton instance
     * @return ilSCTasks
     */
    public static function getInstanceByGroupId($a_group_id)
    {
        if (!array_key_exists($a_group_id, self::$instances)) {
            return self::$instances[$a_group_id] = new self($a_group_id);
        }
        return self::$instances[$a_group_id];
    }

    /**
     * @param int $a_task_id
     * @return string
     * @throws \ilDatabaseException
     */
    public static function lookupIdentifierForTask($a_task_id)
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select identifier from sysc_tasks ' .
            'where id = ' . $db->quote($a_task_id, 'integer');
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->identifier;
        }
        return '';
    }


    /**
     * Update from module/service reader
     * @param type $a_identifier
     * @return boolean
     */
    public function updateFromComponentDefinition($a_identifier)
    {
        foreach ($this->getTasks() as $task) {
            if ($task->getIdentifier() == $a_identifier) {
                return true;
            }
        }
        
        $task = new ilSCTask();
        $task->setGroupId($this->getGroupId());
        $task->setIdentifier($a_identifier);
        $task->create();
        
        return $task->getId();
    }
    
    
    
    /**
     * Lookup group id by task id
     * @global type $ilDB
     * @param type $a_task_id
     * @return int
     */
    public static function lookupGroupId($a_task_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT grp_id FROM sysc_tasks ' .
                'WHERE id = ' . $ilDB->quote($a_task_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->grp_id;
        }
        return 0;
    }
    
    /**
     */
    public static function lookupCompleted($a_grp_id)
    {
        $tasks = self::getInstanceByGroupId($a_grp_id);

        $num_completed = 0;
        foreach ($tasks->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }
            if ($task->getStatus() == ilSCTask::STATUS_COMPLETED) {
                $num_completed++;
            }
        }
        return $num_completed;
    }
    
    /**
     */
    public static function lookupFailed($a_grp_id)
    {
        $tasks = self::getInstanceByGroupId($a_grp_id);

        $num_failed = 0;
        foreach ($tasks->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }

            if ($task->getStatus() == ilSCTask::STATUS_FAILED) {
                $num_failed++;
            }
        }
        return $num_failed;
    }
    
    /**
     * Lookup last update of group tasks
     * @global type $ilDB
     * @param type $a_grp_id
     * @return \ilDateTime
     */
    public static function lookupLastUpdate($a_grp_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT MAX(last_update) last_update FROM sysc_tasks ' .
                'WHERE status = ' . $ilDB->quote(ilSCTask::STATUS_FAILED, 'integer') . ' ' .
                'AND grp_id = ' . $ilDB->quote($a_grp_id, 'integer');
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC);
        }
        return new ilDateTime(time(), IL_CAL_UNIX);
    }
    
    public function getGroupId()
    {
        return $this->grp_id;
    }

    /**
     * Get groups
     * @return ilSCTask[]
     */
    public function getTasks()
    {
        return (array) $this->tasks;
    }
    
    /**
     * read groups
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT id, grp_id FROM sysc_tasks ' .
                'ORDER BY id ';
        $res = $ilDB->query($query);
        
        $this->tasks = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->tasks[] = ilSCComponentTaskFactory::getTask($row->grp_id, $row->id);
        }
    }
}
