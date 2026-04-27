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

namespace ILIAS\Questions\Setup;

use QstsTempAttemptRepository;
use ILIAS\Database\FieldDefinition;

class TempTables implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    #[\Override]
    public function prepare(
        \ilDBInterface $db
    ): void {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $table_name = QstsTempAttemptRepository::TABLE_NAME;

        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'user_id' => [
                    'type' => FieldDefinition::T_INTEGER,
                    'length' => 4,
                    'notnull' => true
                ],
                'attempt_id' => [
                    'type' => FieldDefinition::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['user_id'])) {
            $this->db->addPrimaryKey($table_name, ['user_id']);
        }
    }
}
