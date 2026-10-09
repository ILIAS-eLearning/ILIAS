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

use ILIAS\KeyValueStorage\Repository;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;
use ILIAS\KeyValueStorage\SubjectRepository;

/**
 * Persistent storage in the table owned by this component.
 *
 * Installation-wide rows and subject rows share kvs_store. The provider and
 * the subject of an installation-wide row are the empty string, which neither
 * is valid for a subject, so the two cannot collide. Every subject row carries
 * the provider that named the subject, so subjects of different providers can
 * share an id without sharing rows.
 *
 * The primary key is (provider, subject, namespace, keyword). Every query is an
 * equality on provider and subject, optionally followed by equality on
 * namespace and keyword. A composite index is used only from the left, so
 * provider and subject have to be first: purging one subject is
 * WHERE provider = ? AND subject = ?. There is no range predicate, so no
 * further index is needed.
 *
 * The connection is resolved per operation, never in the constructor: this
 * repository is built while the component bootstrap runs, where no database
 * exists yet.
 *
 * @internal
 */
final readonly class DatabaseRepository implements Repository, SubjectRepository
{
    public const string TABLE = 'kvs_store';

    public const int MAX_VALUE_LENGTH = 4000;

    private const string GLOBAL_PROVIDER = '';
    private const string GLOBAL_SUBJECT = '';

    public function __construct(private \ilDBInterface $connection)
    {
    }

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return $this->read($namespace, $key) !== null;
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        return $this->readRow(self::GLOBAL_PROVIDER, self::GLOBAL_SUBJECT, $namespace, $key);
    }

    public function readAll(StorageNamespace $namespace): array
    {
        return $this->readRows(self::GLOBAL_PROVIDER, self::GLOBAL_SUBJECT, $namespace);
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->writeRow(self::GLOBAL_PROVIDER, self::GLOBAL_SUBJECT, $namespace, $key, $value);
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        $this->removeRow(self::GLOBAL_PROVIDER, self::GLOBAL_SUBJECT, $namespace, $key);
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        $this->removeRows(self::GLOBAL_PROVIDER, self::GLOBAL_SUBJECT, $namespace);
    }

    public function hasFor(SubjectId $subject, StorageNamespace $namespace, string $key): bool
    {
        return $this->readFor($subject, $namespace, $key) !== null;
    }

    public function readFor(SubjectId $subject, StorageNamespace $namespace, string $key): ?string
    {
        return $this->readRow($subject->provider(), $subject->id(), $namespace, $key);
    }

    public function readAllFor(SubjectId $subject, StorageNamespace $namespace): array
    {
        return $this->readRows($subject->provider(), $subject->id(), $namespace);
    }

    public function writeFor(SubjectId $subject, StorageNamespace $namespace, string $key, string $value): void
    {
        $this->writeRow($subject->provider(), $subject->id(), $namespace, $key, $value);
    }

    public function removeFor(SubjectId $subject, StorageNamespace $namespace, string $key): void
    {
        $this->removeRow($subject->provider(), $subject->id(), $namespace, $key);
    }

    public function removeAllFor(SubjectId $subject, StorageNamespace $namespace): void
    {
        $this->removeRows($subject->provider(), $subject->id(), $namespace);
    }

    public function removeSubject(SubjectId $subject): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE provider = %s AND subject = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$subject->provider(), $subject->id()]
        );
    }

    public function removeSubjects(array $subjects): void
    {
        $ids_by_provider = [];
        foreach ($subjects as $subject) {
            if (!$subject instanceof SubjectId) {
                throw new \InvalidArgumentException('Expected a subject id.');
            }
            $ids_by_provider[$subject->provider()][] = $subject->id();
        }

        foreach ($ids_by_provider as $provider => $ids) {
            $this->connection->manipulate(
                'DELETE FROM ' . self::TABLE
                . ' WHERE provider = ' . $this->connection->quote($provider, \ilDBConstants::T_TEXT)
                . ' AND ' . $this->connection->in('subject', $ids, false, \ilDBConstants::T_TEXT)
            );
        }
    }

    private function readRow(string $provider, string $subject, StorageNamespace $namespace, string $key): ?string
    {
        $db = $this->connection;

        $result = $db->queryF(
            'SELECT value FROM ' . self::TABLE
            . ' WHERE provider = %s AND subject = %s AND namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$provider, $subject, $namespace->value(), $key]
        );

        $row = $db->fetchAssoc($result);

        return $row === null ? null : (string) $row['value'];
    }

    /**
     * @return array<string, string>
     */
    private function readRows(string $provider, string $subject, StorageNamespace $namespace): array
    {
        $db = $this->connection;

        $result = $db->queryF(
            'SELECT keyword, value FROM ' . self::TABLE . ' WHERE provider = %s AND subject = %s AND namespace = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$provider, $subject, $namespace->value()]
        );

        $entries = [];
        while ($row = $db->fetchAssoc($result)) {
            $entries[(string) $row['keyword']] = (string) $row['value'];
        }

        return $entries;
    }

    private function writeRow(string $provider, string $subject, StorageNamespace $namespace, string $key, string $value): void
    {
        $this->assertFits(
            $provider,
            SubjectProvider::MAX_NAME_LENGTH,
            'Subject provider name must not exceed '
        );
        $this->assertFits(
            $subject,
            SubjectId::MAX_LENGTH,
            'Subject id must not exceed '
        );
        $this->assertFits(
            $namespace->value(),
            StorageNamespace::MAX_LENGTH,
            'A storage namespace must not be longer than '
        );
        $this->assertFits(
            $key,
            KeyRules::MAX_LENGTH,
            'A storage key must not be longer than '
        );
        $this->assertFits(
            $value,
            self::MAX_VALUE_LENGTH,
            'Stored value must not exceed '
        );

        $this->connection->replace(
            self::TABLE,
            [
                'provider' => [\ilDBConstants::T_TEXT, $provider],
                'subject' => [\ilDBConstants::T_TEXT, $subject],
                'namespace' => [\ilDBConstants::T_TEXT, $namespace->value()],
                'keyword' => [\ilDBConstants::T_TEXT, $key],
            ],
            [
                'value' => [\ilDBConstants::T_TEXT, $value],
            ]
        );
    }

    /**
     * Rejects a string the column cannot store. Called before any connection
     * method, so the database never sees the value.
     */
    private function assertFits(string $value, int $maximum, string $message): void
    {
        $length = \mb_strlen($value, 'UTF-8');
        if ($length > $maximum) {
            throw new \InvalidArgumentException($message . $maximum . ' characters, got ' . $length . '.');
        }
    }

    private function removeRow(string $provider, string $subject, StorageNamespace $namespace, string $key): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE
            . ' WHERE provider = %s AND subject = %s AND namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$provider, $subject, $namespace->value(), $key]
        );
    }

    private function removeRows(string $provider, string $subject, StorageNamespace $namespace): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE provider = %s AND subject = %s AND namespace = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$provider, $subject, $namespace->value()]
        );
    }
}
