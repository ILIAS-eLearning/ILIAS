<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
