<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for component tasks
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCComponentTaskFactory
{
    public static function getComponentTaskGUIForGroup(int $a_group_id, ?int $a_task_id = null) : ?ilSCComponentTaskGUI
    {
        $component_id = ilSCGroup::lookupComponent($a_group_id);

        $task = null;
        if ($a_task_id) {
            $task = self::getTask($a_group_id, $a_task_id);
        }

        // this switch should not be used
        // find class by naming convention and component service
        switch ($component_id) {
            case 'tree':

                return new ilSCTreeTasksGUI($task);
        }
        return null;
    }

    public static function getTask(int $a_group_id, int $a_task_id) : ilSCTask
    {
        $component_id = ilSCGroup::lookupComponent($a_group_id);
        switch ($component_id) {
            case 'tree':
                if (ilSCTasks::lookupIdentifierForTask($a_task_id) === ilSCTreeTasksGUI::TYPE_DUMP) {
                    return new ilSCTreeDumpTask($a_task_id);
                }
        }
        return new ilSCTask($a_task_id);
    }

    public static function getComponentTask(int $a_task_id) : ilSCTreeTasksGUI
    {
        $group_id = ilSCTasks::lookupGroupId($a_task_id);

        return self::getComponentTaskGUIForGroup($group_id, $a_task_id);
    }
}
