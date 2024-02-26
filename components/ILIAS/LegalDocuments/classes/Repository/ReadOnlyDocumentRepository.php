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

use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\DocumentId;
use ILIAS\Data\Result;
use Exception;

class ReadOnlyDocumentRepository implements DocumentRepository, DocumentRepositoryMeta
{
    /** @var list<string> */
    private const WHITELIST = [
            'all',
            'countAll',
            'select',
            'find',
            'findId',
            'documentFromRow',
            'documentTable',
            'exists',
        ];

    public function __construct(private readonly DocumentRepository $repository)
    {
    }

    /**
     * @return list<Document>
     */
    public function all(int $offset = 0, ?int $limit = null): array
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->all();
    }

    public function countAll(): int
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->countAll();
    }

    /**
     * @param list<int> $ids
     * @return list<Document>
     */
    public function select(array $ids): array
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->select($ids);
    }

    /**
     * @return Result<Document>
     */
    public function find(int $id): Result
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->find($id);
    }

    /**
     * @return Result<Document>
     */
    public function findId(DocumentId $document_id): Result
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->findId($id);
    }

    public function createDocument(string $title, DocumentContent $content): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->createDocument($title, $content);
    }

    public function createCriterion(Document $document, CriterionContent $content): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->createCriterion($document, $content);
    }

    public function deleteDocument(Document $document): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->deleteDocument($document);
    }

    public function deleteCriterion(int $criterion_id): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->deleteCriterion($criterion_id);
    }

    public function updateDocumentTitle(DocumentId $document_id, string $title): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->updateDocumentTitle($document_id, $title);
    }

    public function updateDocumentContent(DocumentId $document_id, DocumentContent $content): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->updateDocumentContent($document_id, $content);
    }

    public function updateDocumentOrder(DocumentId $document_id, int $order): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->updateDocumentOrder($document_id, $order);
    }

    public function updateCriterionContent(int $criterion_id, CriterionContent $content): void
    {
        $this->checkAccess(__FUNCTION__);
        $this->repository->updateCriterionContent($criterion_id, $content);
    }

    public function documentFromRow(array $row, array $criteria): Document
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->documentFromRow($row, $criteria);
    }

    public function documentTable(): string
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->documentTable();
    }

    public function exists(string $doc_id_name): string
    {
        $this->checkAccess(__FUNCTION__);
        return $this->repository->exists($doc_id_name);
    }

    private function checkAccess(string $method): void
    {
        if (!in_array($method, self::WHITELIST, true)) {
            throw new Exception('You are not allowed to call this method.');
        }
    }
}
