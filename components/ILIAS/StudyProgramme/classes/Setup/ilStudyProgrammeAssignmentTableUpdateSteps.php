<?php

declare(strict_types=1);

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

class ilStudyProgrammeAssignmentTableUpdateSteps implements ilDatabaseUpdateSteps
{
    public const TABLE_NAME = 'prg_usr_assignments';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $column_name = 'assigned_manually';

        if (!$this->db->tableColumnExists(self::TABLE_NAME, $column_name)) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                $column_name,
                [
                    'type' => 'integer',
                    'length' => 1,
                    'default' => 0,
                    'notnull' => false
                ]
            );
        }

        $query = 'UPDATE ' . self::TABLE_NAME
            . ' JOIN object_data ON last_change_by = object_data.obj_id'
            . ' SET ' . $column_name . ' = 1'
            . ' WHERE type = "usr";'
        ;
        $this->db->manipulate($query);
    }
}
