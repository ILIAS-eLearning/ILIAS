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
 *
 *********************************************************************/

declare(strict_types=1);

class LSOAddModeDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const TABLE_NAME = "lso_settings";
    private const COLUMN_NAME = "lso_mod";

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, self::COLUMN_NAME)) {
            $this->db->addTableColumn(
                self::TABLE_NAME,
                self::COLUMN_NAME,
                [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true,
                    "default" => 0
                ]
            );
        }
    }

    public function step_2(): void
    {
        $this->db->manipulate(
            "UPDATE " . self::TABLE_NAME . " SET " . self::COLUMN_NAME . " = 0 WHERE " . self::COLUMN_NAME . " IS NULL"
        );
    }
}
