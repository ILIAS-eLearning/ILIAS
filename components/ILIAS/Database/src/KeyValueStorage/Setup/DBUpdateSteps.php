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

namespace ILIAS\Database\KeyValueStorage\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

/**
 * Database schema steps for the persistent KeyValueStorage backend.
 *
 * Column lengths are intentionally hard-coded literals: a migration step must
 * describe a fixed historical schema change and must never shift when an unrelated
 * constant elsewhere changes. The chosen values mirror the KeyValueStorage validation
 * limits at the time of writing — see the "Design Decisions" section in the Database
 * component README for the relationship to those constants and the utf8mb4 InnoDB
 * primary-key reasoning.
 */
final class DBUpdateSteps implements ilDatabaseUpdateSteps
{
    private const string TABLE = 'il_kv_storage';
    private const int NAMESPACE_LENGTH = 128;
    private const int KEYWORD_LENGTH = 255;

    protected ilDBInterface $database;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    public function step_1(): void
    {
        if ($this->database->tableExists(self::TABLE)) {
            return;
        }

        $this->database->createTable(self::TABLE, [
            'namespace' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => self::NAMESPACE_LENGTH,
                'notnull' => true,
            ],
            'keyword' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => self::KEYWORD_LENGTH,
                'notnull' => true,
            ],
            'value' => [
                'type' => ilDBConstants::T_CLOB,
                'notnull' => false,
            ],
        ]);

        $this->database->addPrimaryKey(self::TABLE, ['namespace', 'keyword']);
    }
}
