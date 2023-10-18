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

class ilUserTableUpdateSteps implements ilDatabaseUpdateSteps
{
    public const TABLE_NAME = 'usr_data';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $query = 'ALTER TABLE ' . self::TABLE_NAME . ' MODIFY firstname VARCHAR(128);';
        $this->db->manipulate($query);
    }
    public function step_2(): void
    {
        $query = 'ALTER TABLE ' . self::TABLE_NAME . ' MODIFY lastname VARCHAR(128);';
        $this->db->manipulate($query);
    }
    public function step_3(): void
    {
        $query = 'ALTER TABLE ' . self::TABLE_NAME . ' MODIFY email VARCHAR(128);';
        $this->db->manipulate($query);
    }

}
