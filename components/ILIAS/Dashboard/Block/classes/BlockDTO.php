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

namespace ILIAS\components\Dashboard\Block;

use ilDateTime;

class BlockDTO
{
    public function __construct(
        private string $type,
        private int $ref_id,
        private int $obj_id,
        private string $title,
        private string $description,
        private ?ilDateTime $startDate = null,
        private ?ilDateTime $endDate = null,
        private array $additional_data = []
    ) {
        $this->type = $type;
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->title = $title;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->additional_data = $additional_data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStartDate(): ?ilDateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?ilDateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?ilDateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?ilDateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function hasNotStarted(): bool
    {
        return $this->startDate && $this->startDate->get(IL_CAL_UNIX) > time();
    }

    public function hasEnded(): bool
    {
        return $this->endDate && $this->endDate->get(IL_CAL_UNIX) < time();
    }

    public function isRunning(): bool
    {
        return !$this->hasNotStarted() && !$this->hasEnded();
    }

    public function isDated(): bool
    {
        return $this->startDate || $this->endDate;
    }

    public function getAdditionalData(): array
    {
        return $this->additional_data;
    }

    public function setAdditionalData(array $additional_data): void
    {
        $this->additional_data = $additional_data;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'ref_id' => $this->ref_id,
            'obj_id' => $this->obj_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->startDate?->get(IL_CAL_DATETIME),
            'end_date' => $this->endDate?->get(IL_CAL_DATETIME),
            'additional_data' => $this->additional_data
        ];
    }
}
