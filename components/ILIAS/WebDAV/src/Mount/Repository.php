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

namespace ILIAS\WebDAV\Mount;

interface Repository
{
    public function createMountInstructionsDocumentEntry(Document $document): void;

    public function getNextMountInstructionsDocumentId(): int;

    public function getHighestSortingNumber(): int;

    public function getMountInstructionsDocumentById(int $id): Document;

    public function getMountInstructionsByLanguage(string $language): Document;

    public function getAllMountInstructions(): array;

    public function doMountInstructionsExistByLanguage(string $language): int;

    public function updateMountInstructions(Document $document): void;

    public function updateSortingValueById(int $id, int $a_new_sorting_value): void;

    public function deleteMountInstructionsById(int $id): void;
}
