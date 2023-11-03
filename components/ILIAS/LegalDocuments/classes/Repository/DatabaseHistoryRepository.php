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

namespace ILIAS\LegalDocuments\Repository;

use DateTimeImmutable;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Change;
use ILIAS\LegalDocuments\ChangeSet;
use ILIAS\LegalDocuments\Change\AcceptedDocument;
use ILIAS\LegalDocuments\UserAction;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Edit;
use ILIAS\LegalDocuments\Value\History;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ilDBConstants;
use ilDBInterface;
use ilObjUser;
use Exception;
use Closure;

class DatabaseHistoryRepository implements HistoryRepository
{
    use DeriveFieldTypes;

    public function __construct(
        private readonly string $id,
        private readonly DocumentRepositoryMeta $document_meta,
        private readonly ilDBInterface $database,
        private readonly UserAction $action
    ) {
    }

    public function acceptDocument(ilObjUser $user, Document $document): void
    {
        $this->trackUser($user->getId(), $document, $this->documentVersion($document)->except(
            fn() => new Ok($this->addDocumentVersion($document))
        )->value());
    }

    public function alreadyAccepted(ilObjUser $user, Document $document): bool
    {
        $versions = $this->versionTable();
        $tracking = $this->trackingTable();
        $hash = $this->database->quote($this->documentHash($document), ilDBConstants::T_TEXT);
        $user = $this->database->quote($user->getId(), ilDBConstants::T_INTEGER);
        $doc_id = $this->database->quote($document->id(), ilDBConstants::T_INTEGER);

        return null !== $this->database->fetchAssoc($this->database->query(
            "SELECT 1 FROM $versions INNER JOIN $tracking ON $versions.id = $tracking.tosv_id WHERE $versions.doc_id = $doc_id AND hash = $hash AND usr_id = $user AND "
            . $this->document_meta->exists('doc_id')
        ));
    }

    public function acceptedDocument(ilObjUser $user): Result
    {
        $versions = $this->versionTable();
        $tracking = $this->trackingTable();
        $user_id = $this->database->quote($user->getId(), ilDBConstants::T_INTEGER);

        $result = $this->database->fetchAssoc($this->database->query(
            "SELECT $versions.* from $versions inner join $tracking on $versions.id = $tracking.tosv_id where usr_id = $user_id and " . $this->document_meta->exists('doc_id')
        ));

        if ($result === null) {
            return new Error('Not found.');
        }

        return new Ok(new DocumentContent($result['type'], $result['title'] ?? '', $result['text'] ?? ''));
    }

    /**
     * @param array<string, mixed> $filter
     * @param array<string, 'asc'|'desc'> $order_by
     */
    public function all(array $filter = [], array $order_by = [], int $offset = 0, ?int $limit = null): array
    {
        $tracking = $this->trackingTable();
        $version = $this->versionTable();
        $documents = $this->document_meta->documentTable();

        [$filter, $join] = $this->filterAndJoin($filter, $order_by);

        $order_by = $this->mapKeys(fn($field) => match ($field) {
            'created' => "$tracking.ts",
            'document' => "$documents.text",
            'login' => "usr_data.login",
            'firstname' => "usr_data.firstname",
            'lastname' => "usr_data.lastname",
        }, $order_by);

        $rows = $this->database->fetchAll($this->database->query(
            "SELECT $documents.*, $tracking.*, $version.text as old_text, $version.title as old_title, $version.type as old_type, $tracking.ts as ts FROM $tracking INNER JOIN $version ON $tracking.tosv_id = $version.id INNER JOIN $documents ON $version.doc_id = $documents.id $join WHERE $filter AND " .
            $this->document_meta->exists('doc_id') .
            $this->orderSqlFromArray($order_by) .
            (null === $limit ? '' : ' LIMIT ' . $offset . ', ' . $limit)
        ));

        return array_map($this->recordFromRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $filter
     */
    public function countAll(array $filter = []): int
    {
        $tracking = $this->trackingTable();
        $version = $this->versionTable();
        $documents = $this->document_meta->documentTable();

        [$filter, $join] = $this->filterAndJoin($filter);

        return (int) $this->database->fetchAssoc($this->database->query(
            "SELECT COUNT(1) as count FROM $tracking INNER JOIN $version ON $tracking.tosv_id = $version.id INNER JOIN $documents ON $version.doc_id = $documents.id AND " .
            $this->document_meta->exists('doc_id') . "$join WHERE $filter"
        ))['count'];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function recordFromRow(array $row): History
    {
        return new History(
            $this->document_meta->documentFromRow($row, []),
            new Edit((int) $row['usr_id'], new DateTimeImmutable('@' . $row['ts'])),
            array_map(fn(array $criterion) => new CriterionContent($criterion['id'], $criterion['value']), json_decode($row['criteria'], true)),
            new DocumentContent($row['old_type'], $row['old_title'] ?? '', $row['old_text'] ?? '')
        );
    }

    private function documentHash(Document $document): string
    {
        return md5($document->content()->value());
    }

    /**
     * @return Result<int>
     */
    private function documentVersion(Document $document): Result
    {
        $versions = $this->versionTable();
        $documents = $this->document_meta->documentTable();
        $hash = $this->database->quote($this->documentHash($document), ilDBConstants::T_TEXT);
        $id = $this->database->quote($document->id(), ilDBConstants::T_INTEGER);
        $result = $this->database->fetchAssoc($this->database->query("SELECT id FROM $versions WHERE doc_id = $id AND hash = $hash AND " . $this->document_meta->exists('doc_id')));

        return $result ?
            new Ok((int) $result['id']) :
            new Error('Version not found');
    }

    private function trackUser(int $user, Document $document, int $version_id): void
    {
        $this->database->insert($this->trackingTable(), $this->deriveFieldTypes([
            'tosv_id' => $version_id,
            'usr_id' => $user,
            'ts' => $this->action->modifiedNow()->time(),
            'criteria' => json_encode(array_map(fn($x) => [
                'id' => $x->content()->type(),
                'value' => $x->content()->arguments(),
            ], $document->criteria())),
        ]));
    }

    private function addDocumentVersion(Document $document): int
    {
        $id = $this->database->nextId($this->versionTable());
        $this->database->insert($this->versionTable(), $this->deriveFieldTypes([
            'id' => $id,
            'text' => $document->content()->value(),
            'hash' => md5($document->content()->value()),
            'ts' => $this->action->modifiedNow()->time(),
            'doc_id' => $document->id(),
            'title' => $document->content()->title(),
        ]));

        return $id;
    }

    private function versionTable(): string
    {
        return 'ldoc_versions';
    }

    private function trackingTable(): string
    {
        return 'ldoc_acceptance_track';
    }

    private function error(string $message): void
    {
        throw new Exception($message);
    }

    /**
     * @param array<string, string> $order_by
     */
    private function orderSqlFromArray(array $order_by): string
    {
        $valid_field = fn(string $field) => (
            preg_match('/^([[:alnum:]_]+\.)?[[:alnum:]_]+$/i', $field) !== 1 &&
            $this->error('Invalid field name given: ' . print_r($field, true))
        );
        $valid_direction = fn(string $direction) => (
            !in_array(strtolower($direction), ['asc', 'desc'], true) &&
            $this->error('Invalid order direction given, only asc and desc allowed, given: ' . print_r($direction, true))
        );

        array_map($valid_direction, $order_by);
        array_map($valid_field, array_keys($order_by));

        $order_by = join(', ', array_map(
            fn(string $field, string $direction) => join(' ', [$field, $direction]),
            array_keys($order_by),
            $order_by,
        ));

        return $order_by !== '' ? ' ORDER BY ' . $order_by : '';
    }

    /**
     * @template A
     * @template B
     * @template C
     *
     * @param Closure(A): B $proc
     * @param array<A, C> $array
     * @return array<B, C>
     */
    private function mapKeys(Closure $proc, array $array): array
    {
        return array_combine(
            array_map($proc, array_keys($array)),
            array_values($array)
        );
    }

    /**
     * @template A
     *
     * @param array<string, A> $filter
     * @param Closure(string, A): string $filter_to_query
     */
    private function filterQuery(array $filter, Closure $filter_to_query): string
    {
        $query = '1';
        foreach ($filter as $key => $value) {
            $query .= ' AND ' . $filter_to_query($key, $value);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filter
     * @return array{join: string, filter: string}
     */
    private function filterAndJoin(array $filter, array $order_by = []): array
    {
        $tracking = $this->trackingTable();

        $join = array_intersect(['login', 'firstname', 'lastname'], array_keys($order_by)) !== [] || isset($filter['query']) ?
              " LEFT JOIN usr_data ON $tracking.usr_id = usr_data.usr_id " :
              '';

        $filter = $this->filterQuery($filter, fn($name, $value) => match ($name) {
            'start' => "$tracking.ts >= " . $this->database->quote($value, ilDBConstants::T_DATETIME),
            'end' => "$tracking.ts <= " . $this->database->quote($value, ilDBConstants::T_DATETIME),
            'query' => '(' . join(' OR ', array_map(
                fn($s, $v) => 'usr_data.' . $s . ' LIKE ' . $v,
                ['login', 'email', 'firstname', 'lastname'],
                array_fill(0, 4, $this->database->quote('%' . $value . '%', ilDBConstants::T_TEXT))
            )) . ')',
        });

        return [$filter, $join];
    }
}
