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

namespace ILIAS\KeyValueStorage\Setup;

/**
 * The schema of the persistent key-value storage.
 *
 * The column lengths are literals on purpose. A step describes one historical
 * change and must keep describing it, even when the validation limits of the
 * component move. Today they match Internal\StorageNamespace::MAX_LENGTH (128) and
 * KeyRules::MAX_LENGTH (255); 128 + 255 characters stay well below the 3072
 * byte limit InnoDB puts on a utf8mb4 primary key.
 */
final class DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const string TABLE = 'kvs_store';

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists(self::TABLE)) {
            return;
        }

        $this->db->createTable(self::TABLE, [
            'namespace' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 128,
                'notnull' => true,
            ],
            'keyword' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 255,
                'notnull' => true,
            ],
            'value' => [
                'type' => \ilDBConstants::T_CLOB,
                'notnull' => false,
            ],
        ]);

        $this->db->addPrimaryKey(self::TABLE, ['namespace', 'keyword']);
    }
}
