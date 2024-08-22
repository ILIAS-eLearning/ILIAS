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
use ILIAS\LegalDocuments\DocumentId;
use ILIAS\LegalDocuments\DocumentId\HashId;
use ILIAS\LegalDocuments\DocumentId\NumberId;
use ILIAS\LegalDocuments\UserAction;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\Edit;
use ILIAS\LegalDocuments\Value\Meta;
use ilDBConstants;
use ilDBInterface;

class DatabaseDocumentRepository implements DocumentRepository, DocumentRepositoryMeta
{
    use DeriveFieldTypes;

    public function __construct(
        private readonly string $id,
        private readonly ilDBInterface $database,
        private readonly UserAction $action,
    ) {
    }

    public function createDocument(string $title, DocumentContent $content): void
    {
        $creation = $this->action->modifiedNow();
        $this->insert($this->documentTable(), [
            'title' => $title,
            'creation_ts' => $creation->time(),
            'owner_usr_id' => $creation->user(),
            'text' => $content->value(),
            'type' => $content->type(),
            'sorting' => $this->nextSorting(),
            'provider' => $this->id,
        ]);
    }

    public function createCriterion(Document $document, CriterionContent $content): void
    {
        $creation = $this->action->modifiedNow();
        $this->insert($this->criterionTable(), [
            'doc_id' => $document->id(),
            'assigned_ts' => $creation->time(),
            'owner_usr_id' => $creation->user(),
            ...$this->criterionFields($content),
        ]);
    }

    public function deleteDocument(Document $document): void
    {
        $this->deleteEntry($this->documentTable(), $document->id(), 'id', true);
    }

    public function deleteCriterion(int $criterion_id): void
    {
        $this->deleteEntry($this->criterionTable(), $criterion_id, 'doc_id');
    }

    public function updateDocumentTitle(DocumentId $document_id, string $title): void
    {
        $this->updateDocument($document_id, ['title' => $title]);
    }

    public function updateDocumentContent(DocumentId $document_id, DocumentContent $content): void
    {
        $this->updateDocument($document_id, ['text' => $content->value(), 'type' => $content->type()]);
    }

    public function updateDocumentOrder(DocumentId $document_id, int $order): void
    {
        $this->updateDocument($document_id, ['sorting' => $order]);
    }

    public function updateCriterionContent(int $criterion_id, CriterionContent $content): void
    {
        $modification = $this->action->modifiedNow();

        $this->database->update($this->criterionTable(), $this->deriveFieldTypes([
            'modification_ts' => $modification->time(),
            'last_modified_usr_id' => $modification->user(),
            ...$this->criterionFields($content),
        ]), $this->deriveFieldTypes([
            'id' => $criterion_id,
        ]));
    }

    /**
     * @param array<string, string> $fields_and_values
     */
    private function updateDocument(DocumentId $document_id, array $fields_and_values): void
    {
        match ($document_id::class) {
            HashId::class => $this->lazyDocFields($fields_and_values, $document_id->hash()),
            NumberId::class => $this->setDocFields($fields_and_values, $document_id->number()),
        };
    }

    public function countAll(): int
    {
        return (int) current($this->queryF('SELECT COUNT(1) as c FROM ' . $this->documentTable() . ' WHERE provider = %s', [$this->id]))['c'];
    }

    /**
     * @return Document[]
     */
    public function all(int $offset = 0, ?int $limit = null): array
    {
        return $this->queryDocuments('1', $limit === null ? '' : ' LIMIT ' . $offset . ', ' . $limit);
    }

    /**
     * @param list<int> $ids
     * @return Document[]
     */
    public function select(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }
        return $this->queryDocuments($this->database->in('id', $ids, false, ilDBConstants::T_INTEGER));
    }

    /**
     * @return Result<Document>
     */
    public function find(int $id): Result
    {
        return $this->first(
            $this->select([$id]),
            'Document with ID ' . $id . ' not found.'
        );
    }

    public function findId(DocumentId $document_id): Result
    {
        return match ($document_id::class) {
            HashId::class => $this->findHash($document_id->hash()),
            NumberId::class => $this->find($document_id->number()),
        };
    }

    /**
     * @param array{
     *     creation_ts: string,
     *     id: string,
     *     last_modified_usr_id: string,
     *     modification_ts: string,
     *     owner_usr_id: string,
     *     sorting: string,
     *     text: ?string,
     *     title: ?string,
     * } $row
     * @param list<Criterion> $criteria
     */
    public function documentFromRow(array $row, array $criteria): Document
    {
        return new Document((int) $row['id'], new Meta(
            (int) $row['sorting'],
            new Edit((int) $row['last_modified_usr_id'], new DateTimeImmutable('@' . $row['modification_ts'])),
            new Edit((int) $row['owner_usr_id'], new DateTimeImmutable('@' . $row['creation_ts']))
        ), new DocumentContent($row['type'], $row['title'] ?? '', $row['text'] ?? ''), $criteria);
    }

    public function documentTable(): string
    {
        return 'ldoc_documents';
    }

    public function exists(string $doc_id_name): string
    {
        $documents = $this->documentTable();
        $provider = $this->database->quote($this->id, ilDBConstants::T_TEXT);
        $table = 't' . random_int(0, 100);
        return "EXISTS (SELECT 1 FROM $documents AS $table WHERE $table.id = $doc_id_name AND $table.provider = $provider)";
    }

    /**
     * @param array<string, string> $fields_and_values
     */
    private function setDocFields(array $fields_and_values, int $doc_id): void
    {
        $modification = $this->action->modifiedNow();
        $this->database->update($this->documentTable(), $this->deriveFieldTypes([
            'modification_ts' => $modification->time(),
            'last_modified_usr_id' => $modification->user(),
            ...$fields_and_values,
        ]), $this->deriveFieldTypes([
            'id' => $doc_id,
            'provider' => $this->id,
        ]));
    }

    /**
     * @param array<string, string> $fields_and_values
     */
    private function lazyDocFields(array $fields_and_values, string $hash): void
    {
        $modification = $this->action->modifiedNow();
        $affected_rows = $this->database->update($this->documentTable(), $this->deriveFieldTypes([
            ...$fields_and_values,
            'modification_ts' => $modification->time(),
            'last_modified_usr_id' => $modification->user(),
        ]), $this->deriveFieldTypes([
            'hash' => $hash,
            'provider' => $this->id,
        ]));

        if (0 === $affected_rows) {
            $this->database->insert($this->documentTable(), $this->deriveFieldTypes([
                'id' => $this->database->nextId($this->documentTable()),
                'creation_ts' => $modification->time(),
                'owner_usr_id' => $modification->user(),
                'sorting' => $this->nextSorting(),
                'provider' => $this->id,
                'title' => 'Unnamed document',
                'hash' => $hash,
                ...$fields_and_values,
            ]));
        }
    }

    private function criterionFields(CriterionContent $content): array
    {
        return [
            'criterion_id' => $content->type(),
            'criterion_value' => json_encode($content->arguments()),
        ];
    }

    private function queryDocuments(string $where = '1', string $limit = ''): array
    {
        $doc_table = $this->documentTable();
        $provider = $this->database->quote($this->id, ilDBConstants::T_TEXT);
        $documents = $this->query('SELECT * FROM ' . $doc_table . ' WHERE ' . $where . ' AND provider = ' . $provider . ' ORDER BY sorting ' . $limit);
        $doc_ids = array_map(fn($doc) => (int) $doc['id'], $documents);
        $array = $this->query(join(' ', [
            'SELECT * FROM',
            $this->criterionTable(),
            'WHERE',
            $this->database->in('doc_id', $doc_ids, false, ilDBConstants::T_INTEGER),
            'AND',
            $this->exists('doc_id')
        ]));

        $assignments = [];
        foreach ($array as $row) {
            $document_id = (int) $row['doc_id'];
            $assignments[$document_id] ??= [];
            $assignments[$document_id][] = $this->criterionFromRow($row);
        }


        return array_map(
            fn($doc) => $this->documentFromRow($doc, $assignments[(int) $doc['id']] ?? []),
            $documents
        );
    }

    private function criterionFromRow(array $row): Criterion
    {
        return new Criterion(
            (int) $row['id'],
            new CriterionContent(
                $row['criterion_id'],
                json_decode($row['criterion_value'], true)
            ),
            new Edit((int) $row['last_modified_usr_id'], new DateTimeImmutable('@' . $row['modification_ts'])),
            new Edit((int) $row['owner_usr_id'], new DateTimeImmutable('@' . $row['assigned_ts']))
        );
    }

    private function criterionTable(): string
    {
        return 'ldoc_criteria';
    }

    /**
     * @param array<string, mixed> $fields_and_values
     */
    private function insert(string $table, array $fields_and_values): void
    {
        $id = $this->database->nextId($table);

        $this->database->insert($table, $this->deriveFieldTypes([...$fields_and_values, 'id' => $id]));
    }

    /**
     * @param array<string, mixed> $fields_and_values
     */
    private function update(int $id, string $table, array $fields_and_values): void
    {
        $this->database->update($table, $this->deriveFieldTypes($fields_and_values), $this->deriveFieldTypes([
            'id' => $id,
        ]));
    }

    private function deleteEntry(string $table, int $id, string $doc_field, bool $cleanup = false): void
    {
        $id = $this->database->quote($id, ilDBConstants::T_INTEGER);
        $this->database->manipulate("DELETE FROM $table WHERE id = $id AND " . $this->exists($table . '.' . $doc_field));
        if ($cleanup) {
            $this->cleanupCriteria();
        }
    }

    private function cleanupCriteria(): void
    {
        $criteria = $this->criterionTable();
        $documents = $this->documentTable();
        // The provider is not specified because this statement cleans up dead criteria independent of the provider.
        $this->database->manipulate("DELETE FROM $criteria WHERE doc_id NOT IN (SELECT id FROM $documents)");
    }

    private function nextSorting(): int
    {
        $documents = $this->documentTable();
        $sorting = (int) ($this->database->fetchAssoc($this->database->query(
            "SELECT MAX(sorting) as s FROM $documents WHERE " . $this->exists($documents . '.id')
        ))['s'] ?? 0);

        return $sorting + 10;
    }

    private function findHash(string $hash): Result
    {
        return $this->first(
            $this->queryDocuments($this->database->in('hash', [$hash], false, ilDBConstants::T_TEXT)),
            'Document with hash . ' . json_encode($hash) . ' not found.'
        );
    }

    private function first(array $array, string $message): Result
    {
        $document = current($array) ?: null;
        return $document ? new Ok($document) : new Error($message);
    }
}
