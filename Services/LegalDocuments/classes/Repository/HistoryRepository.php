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
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\History;
use ilObjUser;

interface HistoryRepository
{
    /**
     * @param array<string, mixed> $filter
     * @param array<string, string> $order_by
     * @return list<History>
     */
    public function all(array $filter = [], array $order_by = [], int $offset = 0, ?int $limit = null): array;

    /**
     * @param array<string, mixed> $filter
     */
    public function countAll(array $filter = []): int;
    public function acceptDocument(ilObjUser $user, Document $document);
    public function alreadyAccepted(ilObjUser $user, Document $document): bool;

    /**
     * @return Result<DocumentContent>
     */
    public function acceptedDocument(ilObjUser $user): Result;
}
