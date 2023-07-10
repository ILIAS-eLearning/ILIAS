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

class ilStudyProgrammeProgressTableUpdateSteps implements ilDatabaseUpdateSteps
{
    private const TABLE_NAME = 'prg_usr_progress';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $column_name = 'individual';

        if (!$this->db->tableColumnExists(self::TABLE_NAME, $column_name)) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                $column_name,
                [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true,
                    "default" => 0
                ]
            );
            $query = 'UPDATE ' . self::TABLE_NAME
                . ' SET ' . $column_name . ' = 1'
                . ' WHERE last_change_by IS NOT NULL';
            $this->db->manipulate($query);
        }
    }

    public function step_2(): void
    {
        $old = "risky_to_fail_mail_send";
        $new = "sent_mail_risky_to_fail";

        if ($this->db->tableColumnExists(self::TABLE_NAME, $old) && !$this->db->tableColumnExists(self::TABLE_NAME, $new)) {
            $this->db->renameTableColumn(self::TABLE_NAME, $old, $new);
        }
    }

    public function step_3(): void
    {
        $column_name = 'sent_mail_expires';

        if (!$this->db->tableColumnExists(self::TABLE_NAME, $column_name)) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                $column_name,
                [
                    "type" => "timestamp",
                    "notnull" => false
                ]
            );
        }
    }
}
