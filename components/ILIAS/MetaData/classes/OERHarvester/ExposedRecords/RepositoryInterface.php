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

namespace ILIAS\MetaData\OERHarvester\ExposedRecords;

interface RepositoryInterface
{
    /**
     * Order of returned records is stable.
     * @return RecordInterface[]
     */
    public function getRecords(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null,
        ?int $limit = null,
        ?int $offset = null
    ): \Generator;

    /**
     * Order of returned records is stable.
     * @return RecordInfosInterface[]
     */
    public function getRecordInfos(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null,
        ?int $limit = null,
        ?int $offset = null
    ): \Generator;

    public function getRecordCount(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null
    ): int;

    public function getEarliestDatestamp(): \DateTimeImmutable;

    public function getRecordByIdentifier(string $identifier): ?RecordInterface;

    public function doesRecordWithIdentifierExist(string $identifier): bool;

    public function doesRecordExistForObjID(int $obj_id): bool;

    public function createRecord(int $obj_id, string $identifier, \DOMDocument $metadata): void;

    public function updateRecord(int $obj_id, \DOMDocument $metadata): void;

    public function deleteRecord(int $obj_id): void;
}
