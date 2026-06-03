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

namespace ILIAS\Database\KeyValueStorage;

use ILIAS\KeyValueStorage\PersistentStoragePort;
use ILIAS\KeyValueStorage\StorageNamespace;

/**
 * Database-backed implementation of the persistent storage port.
 *
 * The database connection is resolved lazily on first port use so this class can
 * be constructed during build/bootstrap phases where the global $DIC is not yet
 * available (for example `composer du`).
 */
final readonly class DatabaseStoragePort implements PersistentStoragePort
{
    private const string TABLE = 'il_kv_storage';

    public function __construct(
        private DatabaseConnection $database_connection,
    ) {
    }

    public function has(StorageNamespace $namespace, string $key): bool
    {
        $db = $this->database_connection->get();
        $namespace_value = $namespace->value();
        $result = $db->query(
            'SELECT EXISTS(SELECT 1 FROM ' . self::TABLE .
            ' WHERE namespace = ' . $db->quote($namespace_value, \ilDBConstants::T_TEXT) .
            ' AND keyword = ' . $db->quote($key, \ilDBConstants::T_TEXT) . ') AS row_exists'
        );
        $row = $db->fetchAssoc($result);

        return (bool) ($row['row_exists'] ?? false);
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        $db = $this->database_connection->get();
        $namespace_value = $namespace->value();
        $result = $db->query(
            'SELECT value FROM ' . self::TABLE .
            ' WHERE namespace = ' . $db->quote($namespace_value, \ilDBConstants::T_TEXT) .
            ' AND keyword = ' . $db->quote($key, \ilDBConstants::T_TEXT)
        );
        $row = $db->fetchAssoc($result);

        if ($row === null) {
            return null;
        }

        return (string) $row['value'];
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->database_connection->get()->replace(
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
        $db = $this->database_connection->get();
        $namespace_value = $namespace->value();
        $db->manipulate(
            'DELETE FROM ' . self::TABLE .
            ' WHERE namespace = ' . $db->quote($namespace_value, \ilDBConstants::T_TEXT) .
            ' AND keyword = ' . $db->quote($key, \ilDBConstants::T_TEXT)
        );
    }

    public function clearNamespace(StorageNamespace $namespace): void
    {
        $db = $this->database_connection->get();
        $db->manipulate(
            'DELETE FROM ' . self::TABLE .
            ' WHERE namespace = ' . $db->quote($namespace->value(), \ilDBConstants::T_TEXT)
        );
    }
}
