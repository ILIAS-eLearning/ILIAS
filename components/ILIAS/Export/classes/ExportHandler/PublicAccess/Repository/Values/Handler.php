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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Values;

use DateTimeImmutable;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\HandlerInterface as ilExportHandlerPublicAccessRepositoryValuesInterface;

class Handler implements ilExportHandlerPublicAccessRepositoryValuesInterface
{
    protected string $identification;
    protected string $export_option_id;
    protected DateTimeImmutable $last_modified;

    public function __clone(): void
    {
        $this->last_modified = new DateTimeImmutable();
    }

    public function withIdentification(
        string $identification
    ): ilExportHandlerPublicAccessRepositoryValuesInterface {
        $clone = clone $this;
        $clone->identification = $identification;
        return $clone;
    }

    public function withExportOptionId(
        string $type
    ): ilExportHandlerPublicAccessRepositoryValuesInterface {
        $clone = clone $this;
        $clone->export_option_id = $type;
        return $clone;
    }

    public function getExportOptionId(): string
    {
        return $this->export_option_id;
    }

    public function getIdentification(): string
    {
        return $this->identification ?? "";
    }

    public function getLastModified(): DateTimeImmutable
    {
        return $this->last_modified;
    }

    public function isValid(): bool
    {
        return (
            isset($this->identification) and
            isset($this->export_option_id) and
            isset($this->last_modified)
        );
    }

    public function equals(
        ilExportHandlerPublicAccessRepositoryValuesInterface $other
    ): bool {
        $identification_equals =
            (
                (
                    !isset($this->identification) and
                    !isset($other->identification)
                ) or (
                    isset($this->identification) and
                    isset($other->identification) and
                    $this->identification === $other->identification
                )
            );
        $export_option_id_equals =
            (
                (
                    !isset($this->export_option_id) and
                    !isset($other->export_option_id)
                ) or (
                    isset($this->export_option_id) and
                    isset($other->export_option_id) and
                    $this->export_option_id === $other->export_option_id
                )
            );
        return $identification_equals and $export_option_id_equals;
    }
}
