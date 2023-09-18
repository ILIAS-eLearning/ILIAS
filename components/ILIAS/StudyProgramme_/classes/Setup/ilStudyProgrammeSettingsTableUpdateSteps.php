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


class ilStudyProgrammeSettingsTableUpdateSteps implements ilDatabaseUpdateSteps
{
    public const TABLE_NAME = 'prg_settings';
    public const TABLE_PROGRESSES = 'prg_usr_progress';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $column_name = 'vq_restart_recheck';

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
    }

    public function step_2(): void
    {
        $this->db->dropPrimaryKey(self::TABLE_PROGRESSES);
        $this->db->addPrimaryKey(
            self::TABLE_PROGRESSES,
            [
                'assignment_id',
                'prg_id',
                'usr_id'
            ]
        );
    }
}
