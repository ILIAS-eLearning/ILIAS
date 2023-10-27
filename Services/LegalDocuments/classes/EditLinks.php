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

namespace ILIAS\LegalDocuments;

use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;

interface EditLinks
{
    public function addCriterion(Document $document): string;
    public function editDocument(Document $document): string;
    public function deleteDocument(Document $document): string;

    public function editCriterion(Document $document, Criterion $criterion): string;
    public function deleteCriterion(Document $document, Criterion $criterion): string;
}
