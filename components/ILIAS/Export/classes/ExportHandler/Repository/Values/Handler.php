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

namespace ILIAS\Export\ExportHandler\Repository\Values;

use DateTimeImmutable;
use ILIAS\Export\ExportHandler\I\Repository\Values\HandlerInterface as ilExportHandlerRepositoryValuesInterface;

class Handler implements ilExportHandlerRepositoryValuesInterface
{
    protected int $owner_id;
    protected DateTimeImmutable $creation_date;

    public function withOwnerId(int $owner_id): ilExportHandlerRepositoryValuesInterface
    {
        $clone = clone $this;
        $clone->owner_id = $owner_id;
        return $clone;
    }

    public function withCreationDate(DateTimeImmutable $creation_date): ilExportHandlerRepositoryValuesInterface
    {
        $clone = clone $this;
        $clone->creation_date = $creation_date;
        return $clone;
    }

    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function isValid(): bool
    {
        return (
            isset($this->owner_id) and
            isset($this->creation_date)
        );
    }

    public function equals(
        ilExportHandlerRepositoryValuesInterface $other_repository_values
    ): bool {
        $owner_id_equals =
            (
                isset($this->owner_id) and
                isset($other_repository_values->owner_id) and
                $this->owner_id = $other_repository_values->owner_id
            ) || (
                !isset($this->owner_id) and
                !isset($other_repository_values->owner_id)
            );
        $creation_date_equals =
            (
                isset($this->creation_date) and
                isset($other_repository_values->creation_date) and
                $this->creation_date->getTimestamp() === $other_repository_values->creation_date->getTimestamp()
            ) || (
                !isset($this->creation_date) and
                !isset($other_repository_values->creation_date)
            );
        return $owner_id_equals and $creation_date_equals;
    }
}
