<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Factory for component tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCComponentTaskFactory
{

    /**
     * get task gui for group
     * @param type $a_group_id
     * @return \ilSCTreeTasksGUI
     */
    public static function getComponentTaskGUIForGroup($a_group_id, $a_task_id = null)
    {
        include_once './Services/SystemCheck/classes/class.ilSCGroup.php';
        $component_id = ilSCGroup::lookupComponent($a_group_id);

        $task = null;
        if ($a_task_id) {
            $task = self::getTask($a_group_id, $a_task_id);
        }
        
        // this switch should not be used
        // find class by naming convention and component service
        switch ($component_id) {
            case 'tree':
                include_once './Services/Tree/classes/class.ilSCTreeTasksGUI.php';
                include_once './Services/SystemCheck/classes/class.ilSCTask.php';
                return new ilSCTreeTasksGUI($task);
        }
    }

    /**
     * @param int $a_group_id
     * @param string $a_task_id
     */
    public static function getTask($a_group_id, $a_task_id)
    {
        $component_id = ilSCGroup::lookupComponent($a_group_id);
        switch ($component_id) {
            case 'tree':
                if (ilSCTasks::lookupIdentifierForTask($a_task_id) == ilSCTreeTasksGUI::TYPE_DUMP) {
                    return new \ilSCTreeDumpTask($a_task_id);
                }
        }
        return new \ilSCTask($a_task_id);
    }


    
    
    /**
     *
     * @param type $a_task_id
     * @return \ilSCTreeTasksGUI
     */
    public static function getComponentTask($a_task_id)
    {
        include_once './Services/SystemCheck/classes/class.ilSCTasks.php';
        $group_id = ilSCTasks::lookupGroupId($a_task_id);
        
        return self::getComponentTaskGUIForGroup($group_id, $a_task_id);
    }
}
