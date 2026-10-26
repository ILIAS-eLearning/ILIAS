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
 * provider, subject, namespace and keyword are the primary key. ILIAS requires
 * the InnoDB DYNAMIC row format, whose index limit is 3072 bytes. utf8mb4 stores
 * up to 4 bytes per character, so the key uses
 * (64 + 128 + 128 + 255) * 4 = 2300 bytes and fits. value is not indexed.
 *
 * The lengths are literals. They match SubjectProvider::MAX_NAME_LENGTH (64),
 * SubjectId::MAX_LENGTH (128),
 * StorageNamespace::MAX_LENGTH (128), KeyRules::MAX_LENGTH (255) and
 * DatabaseRepository::MAX_VALUE_LENGTH (4000).
 */
final class DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const string TABLE = 'kvs_store';

    /**
     * InnoDB maximum index length in bytes for the DYNAMIC row format.
     */
    public const int INNODB_INDEX_LIMIT_BYTES = 3072;

    public const int UTF8MB4_BYTES_PER_CHARACTER = 4;

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
            'provider' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 64,
                'notnull' => true,
                'default' => ''
            ],
            'subject' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 128,
                'notnull' => true,
                'default' => ''
            ],
            'namespace' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 128,
                'notnull' => true
            ],
            'keyword' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 255,
                'notnull' => true
            ],
            'value' => [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 4000,
                'notnull' => false
            ]
        ]);

        $this->db->addPrimaryKey(self::TABLE, ['provider', 'subject', 'namespace', 'keyword']);
    }
}
