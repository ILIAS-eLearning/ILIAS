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

namespace ILIAS\AdvancedMetaData\Repository\FieldDefinition\GenericData;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericData;

interface Gateway
{
    /**
     * Returns the field ID of the created data.
     */
    public function create(GenericData $data): int;

    /**
     * Inserts the data, but replaces position and import ID:
     * next position in the record, and a newly generated import ID is used.
     * Returns the field ID of the created data.
     */
    public function createFromScratch(GenericData $data): int;

    /**
     * Inserts the data, but replaces position by the next
     * position in the record. Import ID is not replaced.
     * Returns the field ID of the created data.
     */
    public function createWithNewPosition(GenericData $data): int;

    public function readByID(int $field_id): ?GenericData;

    /**
     * @return GenericData[]
     */
    public function readByIDs(int ...$field_ids): \Generator;

    /**
     * @return GenericData[]
     */
    public function readByRecords(bool $only_searchable, int ...$record_ids): \Generator;

    public function readByImportID(string $import_id): ?GenericData;

    public function update(GenericData $data): void;

    public function delete(int ...$field_ids): void;
}
