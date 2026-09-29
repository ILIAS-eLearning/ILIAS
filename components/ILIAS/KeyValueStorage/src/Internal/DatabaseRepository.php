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
use ILIAS\KeyValueStorage\SubjectRepository;

/**
 * Persistent storage in the table owned by this component.
 *
 * Installation-wide rows and subject rows share kvs_store. The subject of an
 * installation-wide row is the empty string, which is not a valid subject
 * segment, so the two cannot collide.
 *
 * The primary key is (subject, namespace, keyword). Every query is an equality
 * on subject, optionally followed by equality on namespace and keyword. A
 * composite index is used only from the left, so subject has to be first:
 * purging one subject is WHERE subject = ? and would not use a key that
 * starts with namespace. There is no range predicate, so no further index
 * is needed.
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
        return $this->readRow(self::GLOBAL_SUBJECT, $namespace, $key);
    }

    public function readAll(StorageNamespace $namespace): array
    {
        return $this->readRows(self::GLOBAL_SUBJECT, $namespace);
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->writeRow(self::GLOBAL_SUBJECT, $namespace, $key, $value);
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        $this->removeRow(self::GLOBAL_SUBJECT, $namespace, $key);
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        $this->removeRows(self::GLOBAL_SUBJECT, $namespace);
    }

    public function hasFor(SubjectId $subject, StorageNamespace $namespace, string $key): bool
    {
        return $this->readFor($subject, $namespace, $key) !== null;
    }

    public function readFor(SubjectId $subject, StorageNamespace $namespace, string $key): ?string
    {
        return $this->readRow($subject->storageSegment(), $namespace, $key);
    }

    public function readAllFor(SubjectId $subject, StorageNamespace $namespace): array
    {
        return $this->readRows($subject->storageSegment(), $namespace);
    }

    public function writeFor(SubjectId $subject, StorageNamespace $namespace, string $key, string $value): void
    {
        $this->writeRow($subject->storageSegment(), $namespace, $key, $value);
    }

    public function removeFor(SubjectId $subject, StorageNamespace $namespace, string $key): void
    {
        $this->removeRow($subject->storageSegment(), $namespace, $key);
    }

    public function removeAllFor(SubjectId $subject, StorageNamespace $namespace): void
    {
        $this->removeRows($subject->storageSegment(), $namespace);
    }

    public function removeSubject(SubjectId $subject): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE subject = %s',
            [\ilDBConstants::T_TEXT],
            [$subject->storageSegment()]
        );
    }

    public function removeSubjects(array $subjects): void
    {
        if ($subjects === []) {
            return;
        }

        $segments = [];
        foreach ($subjects as $subject) {
            if (!$subject instanceof SubjectId) {
                throw new \InvalidArgumentException('Expected a subject id.');
            }
            $segments[] = $subject->storageSegment();
        }

        $this->connection->manipulate(
            'DELETE FROM ' . self::TABLE . ' WHERE ' . $this->connection->in(
                'subject',
                $segments,
                false,
                \ilDBConstants::T_TEXT
            )
        );
    }

    private function readRow(string $subject, StorageNamespace $namespace, string $key): ?string
    {
        $db = $this->connection;

        $result = $db->queryF(
            'SELECT value FROM ' . self::TABLE
            . ' WHERE subject = %s AND namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$subject, $namespace->value(), $key]
        );

        $row = $db->fetchAssoc($result);

        return $row === null ? null : (string) $row['value'];
    }

    /**
     * @return array<string, string>
     */
    private function readRows(string $subject, StorageNamespace $namespace): array
    {
        $db = $this->connection;

        $result = $db->queryF(
            'SELECT keyword, value FROM ' . self::TABLE . ' WHERE subject = %s AND namespace = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$subject, $namespace->value()]
        );

        $entries = [];
        while ($row = $db->fetchAssoc($result)) {
            $entries[(string) $row['keyword']] = (string) $row['value'];
        }

        return $entries;
    }

    private function writeRow(string $subject, StorageNamespace $namespace, string $key, string $value): void
    {
        $this->assertFits(
            $subject,
            SubjectId::MAX_LENGTH,
            'Subject segment must not exceed '
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

    private function removeRow(string $subject, StorageNamespace $namespace, string $key): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE subject = %s AND namespace = %s AND keyword = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$subject, $namespace->value(), $key]
        );
    }

    private function removeRows(string $subject, StorageNamespace $namespace): void
    {
        $this->connection->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE subject = %s AND namespace = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            [$subject, $namespace->value()]
        );
    }
}
