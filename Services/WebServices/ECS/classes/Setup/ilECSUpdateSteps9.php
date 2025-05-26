<?php

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
 */

declare(strict_types=1);

/**
 * Class ilECSUpdateSteps9
 * contains update steps for release 9
 * @author Per Pascal Seeland <pascal.seeland@tik.uni-stuttgart.de>
 */
class ilECSUpdateSteps9 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    private function ensure_index_exists(string $table, array $fields, string $name): void
    {
        if (!$this->db->indexExistsByFields($table, $fields)) {
            $this->db->addIndex($table, $fields, $name);
        }
    }

    private function ensure_indices_exist(): void
    {
        $this->ensure_index_exists('ecs_course_assignments', ['obj_id'], 'i1');
        $this->ensure_index_exists('ecs_import', ['obj_id'], 'i1');
        $this->ensure_index_exists('ecs_import', ['sub_id'], 'i2');
    }

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Fix wrong data entries in ecs_course_assignments
     */
    public function step_1(): void
    {
        $this->ensure_indices_exist();
        $this->db->manipulate('UPDATE `ecs_course_assignments` inner join ecs_import on ecs_course_assignments.obj_id = ecs_import.obj_id SET ecs_course_assignments.`cms_sub_id`=ecs_import.sub_id WHERE ecs_import.sub_id is null;');
    }

    public function step_2(): void
    {
        /**
         * step_1 was initially without ensure_indices_exist.
         * Ensure that everyone has these indices, even if step_1 has already been executed
        */
        $this->ensure_indices_exist();
    }
}
