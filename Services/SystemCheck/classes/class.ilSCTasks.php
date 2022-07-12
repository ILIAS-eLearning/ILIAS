<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTasks
{
    private static array $instances = array();

    private int $grp_id = 0;
    private array $tasks = array();

    protected ilDBInterface $db;

    private function __construct(int $a_grp_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->grp_id = $a_grp_id;
        $this->read();
    }

    public static function getInstanceByGroupId(int $a_group_id) : ilSCTasks
    {
        if (!array_key_exists($a_group_id, self::$instances)) {
            return self::$instances[$a_group_id] = new self($a_group_id);
        }
        return self::$instances[$a_group_id];
    }

    /**
     * @throws \ilDatabaseException
     */
    public static function lookupIdentifierForTask(int $a_task_id) : string
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select identifier from sysc_tasks ' .
            'where id = ' . $db->quote($a_task_id, ilDBConstants::T_INTEGER);
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->identifier;
        }
        return '';
    }

    public function updateFromComponentDefinition(string $a_identifier) : int
    {
        foreach ($this->getTasks() as $task) {
            if ($task->getIdentifier() === $a_identifier) {
                return 1;
            }
        }

        $task = new ilSCTask();
        $task->setGroupId($this->getGroupId());
        $task->setIdentifier($a_identifier);
        $task->create();

        return $task->getId();
    }

    public static function lookupGroupId(int $a_task_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT grp_id FROM sysc_tasks ' .
            'WHERE id = ' . $ilDB->quote($a_task_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->grp_id;
        }
        return 0;
    }

    public static function lookupCompleted(int $a_grp_id) : int
    {
        $tasks = self::getInstanceByGroupId($a_grp_id);

        $num_completed = 0;
        foreach ($tasks->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }
            if ($task->getStatus() === ilSCTask::STATUS_COMPLETED) {
                $num_completed++;
            }
        }
        return $num_completed;
    }

    public static function lookupFailed(int $a_grp_id) : int
    {
        $tasks = self::getInstanceByGroupId($a_grp_id);

        $num_failed = 0;
        foreach ($tasks->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }

            if ($task->getStatus() === ilSCTask::STATUS_FAILED) {
                $num_failed++;
            }
        }
        return $num_failed;
    }

    public static function lookupLastUpdate(int $a_grp_id) : ilDateTime
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT MAX(last_update) last_update FROM sysc_tasks ' .
            'WHERE status = ' . $ilDB->quote(ilSCTask::STATUS_FAILED, ilDBConstants::T_INTEGER) . ' ' .
            'AND grp_id = ' . $ilDB->quote($a_grp_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC);
        }
        return new ilDateTime(time(), IL_CAL_UNIX);
    }

    public function getGroupId() : int
    {
        return $this->grp_id;
    }

    /**
     * @return ilSCTask[]
     */
    public function getTasks() : array
    {
        return $this->tasks;
    }

    protected function read() : void
    {
        $query = 'SELECT id, grp_id FROM sysc_tasks ' .
            'ORDER BY id ';
        $res = $this->db->query($query);

        $this->tasks = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->tasks[] = ilSCComponentTaskFactory::getTask($row->grp_id, $row->id);
        }
    }
}
