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

namespace ILIAS\AdvancedMetaData\Repository\FieldDefinition\TypeSpecificData\Select;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select\SelectSpecificData;

interface Gateway
{
    public function create(int $field_id, SelectSpecificData $data): void;

    public function readByID(int $field_id): ?SelectSpecificData;

    /**
     * @return SelectSpecificData[]
     */
    public function readByIDs(int ...$field_ids): \Generator;

    public function update(SelectSpecificData $data): void;

    public function delete(int ...$field_ids): void;
}
