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
final class ilPDSelectedItemBlockMembershipsDTO
{
    public function __construct(
        private readonly int $refId,
        private readonly int $objId,
        private readonly string $type,
        private readonly string $title,
        private readonly string $description,
        private readonly int $parentRefId,
        private readonly int $parentLftTree,
        private readonly bool $objectPeriodHasTime,
        private readonly ?DateTimeImmutable $periodStart,
        private readonly ?DateTimeImmutable $periodEnd
    ) {
    }

    public function getRefId(): int
    {
        return $this->refId;
    }

    public function getObjId(): int
    {
        return $this->objId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getParentRefId(): int
    {
        return $this->parentRefId;
    }

    public function getParentLftTree(): int
    {
        return $this->parentLftTree;
    }

    public function objectPeriodHasTime(): bool
    {
        return $this->objectPeriodHasTime;
    }

    public function getPeriodStart(): ?DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): ?DateTimeImmutable
    {
        return $this->periodEnd;
    }
}
