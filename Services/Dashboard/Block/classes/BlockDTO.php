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

namespace ILIAS\Services\Dashboard\Block;

use ilDateTime;

class BlockDTO
{
    public function __construct(
        private string $type,
        private readonly int $ref_id,
        private readonly int $obj_id,
        private string $title,
        private string $description,
        private ?ilDateTime $start_date = null,
        private ?ilDateTime $end_date = null,
        private array $additional_data = []
    ) {
    }

    final public function getType(): string
    {
        return $this->type;
    }

    final public function setType(string $type): void
    {
        $this->type = $type;
    }

    final public function getRefId(): int
    {
        return $this->ref_id;
    }

    final public function getObjId(): int
    {
        return $this->obj_id;
    }

    final public function getTitle(): string
    {
        return $this->title;
    }

    final public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    final public function getDescription(): string
    {
        return $this->description;
    }

    final public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    final public function getStartDate(): ?ilDateTime
    {
        return $this->start_date;
    }

    final public function setStartDate(?ilDateTime $start_date): void
    {
        $this->start_date = $start_date;
    }

    final public function getEndDate(): ?ilDateTime
    {
        return $this->end_date;
    }

    final public function setEndDate(?ilDateTime $end_date): void
    {
        $this->end_date = $end_date;
    }

    final public function hasNotStarted(): bool
    {
        return $this->start_date && $this->start_date->get(IL_CAL_UNIX) > time();
    }

    final public function hasEnded(): bool
    {
        return $this->end_date && $this->end_date->get(IL_CAL_UNIX) < time();
    }

    final public function isRunning(): bool
    {
        return !$this->hasNotStarted() && !$this->hasEnded();
    }

    final public function isDated(): bool
    {
        return $this->start_date || $this->end_date;
    }

    final public function getAdditionalData(): array
    {
        return $this->additional_data;
    }

    final public function setAdditionalData(array $additional_data): void
    {
        $this->additional_data = $additional_data;
    }

    final public function toArray(): array
    {
        return [
            'type' => $this->type,
            'ref_id' => $this->ref_id,
            'obj_id' => $this->obj_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->get(IL_CAL_DATETIME),
            'end_date' => $this->end_date?->get(IL_CAL_DATETIME),
            'additional_data' => $this->additional_data
        ];
    }
}
