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

namespace ILIAS\KeyValueStorage\Internal;

use ILIAS\Database\Connection;
use ILIAS\KeyValueStorage\Repository;
use ILIAS\KeyValueStorage\StorageNamespace;

/**
 * Persistent storage in the table owned by this component.
 *
 * The connection is resolved per operation, never in the constructor: this
 * repository is built while the component bootstrap runs, where no database
 * exists yet.
 *
 * @internal
 */
final readonly class DatabaseRepository implements Repository
{
    public const string TABLE = 'kvs_store';

    public function __construct(private Connection $connection)
    {
    }

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return $this->read($namespace, $key) !== null;
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        $db = $this->connection->get();

        $result = $db->queryF(
            'SELECT value FROM ' . self::TABLE . ' WHERE namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$namespace->value(), $key]
        );

        $row = $db->fetchAssoc($result);

        return $row === null ? null : (string) $row['value'];
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->connection->get()->replace(
            self::TABLE,
            [
                'namespace' => [\ilDBConstants::T_TEXT, $namespace->value()],
                'keyword' => [\ilDBConstants::T_TEXT, $key],
            ],
            [
                'value' => [\ilDBConstants::T_CLOB, $value],
            ]
        );
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        $this->connection->get()->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$namespace->value(), $key]
        );
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        $this->connection->get()->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE namespace = %s',
            [\ilDBConstants::T_TEXT],
            [$namespace->value()]
        );
    }
}
