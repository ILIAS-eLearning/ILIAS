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
use InvalidArgumentException;
use ilDBConstants;

trait DeriveFieldTypes
{
    /**
     * @param array<string, int|string|DateTimeImmutable> $fields_and_values
     * @return array<string, array{0: string, 1: int|string}>
     */
    private function deriveFieldTypes(array $fields_and_values): array
    {
        $valid_date_time = static function ($value) {
            if ($value instanceof DateTimeImmutable) {
                return ilDBConstants::T_DATETIME;
            }
            throw new InvalidArgumentException('Only DateTimeImmutable objects allowed.');
        };

        $expected_db_form = static fn($value): array => match (gettype($value)) {
            'integer' => [ilDBConstants::T_INTEGER, $value],
            'string' => [ilDBConstants::T_TEXT, $value],
            'object' => [$valid_date_time($value), $value->getTimeStamp()],
        };

        return array_map(
            $expected_db_form,
            $fields_and_values
        );
    }

    private function query(string $query): array
    {
        return $this->database->fetchAll($this->database->query($query));
    }

    /**
     * @param array<string, mixed> $values
     */
    private function queryF(string $query, array $values): array
    {
        $values = $this->deriveFieldTypes($values);
        return $this->database->fetchAll($this->database->queryF($query, array_column($values, 0), array_column($values, 1)));
    }
}
