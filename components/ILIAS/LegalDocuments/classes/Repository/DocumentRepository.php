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

use ILIAS\Data\Result;
use ILIAS\LegalDocuments\DocumentId;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\DocumentContent;

interface DocumentRepository
{
    /**
     * @return list<Document>
     */
    public function all(int $offset = 0, ?int $limit = null): array;
    public function countAll(): int;

    /**
     * @param list<int> $ids
     * @return list<Document>
     */
    public function select(array $ids): array;

    /**
     * @return Result<Document>
     */
    public function find(int $id): Result;

    public function createDocument(string $title, DocumentContent $content): void;
    public function createCriterion(Document $document, CriterionContent $content): void;
    public function deleteDocument(Document $document): void;
    public function deleteCriterion(int $criterion_id): void;
    public function updateDocumentTitle(DocumentId $document_id, string $title): void;
    public function updateDocumentContent(DocumentId $document_id, DocumentContent $content): void;
    public function updateDocumentOrder(DocumentId $document_id, int $order): void;
    public function updateCriterionContent(int $criterion_id, CriterionContent $content): void;
}
