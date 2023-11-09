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

use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;

interface DocumentRepositoryMeta
{
    /**
     * @param array<string, mixed> $row
     * @param list<Criterion> $criteria
     */
    public function documentFromRow(array $row, array $criteria): Document;
    public function documentTable(): string;
    public function exists(string $doc_id_name): string;
}
