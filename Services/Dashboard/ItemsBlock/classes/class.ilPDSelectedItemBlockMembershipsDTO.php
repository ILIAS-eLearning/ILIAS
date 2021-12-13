<?php declare(strict_types=1);

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
 ********************************************************************
 */
final class ilPDSelectedItemBlockMembershipsDTO
{
    private $refId;
    private $objId;
    private $type;
    private $title;
    private $description;
    private $parentRefId;
    private $parentLftTree;
    private $objectPeriodHasTime;
    private $periodStart;
    private $periodEnd;

    public function __construct(
        int $refId,
        int $objId,
        string $type,
        string $title,
        string $description,
        int $parentRefId,
        int $parentLftTree,
        bool $objectPeriodHasTime,
        ?DateTimeImmutable $periodStart,
        ?DateTimeImmutable $periodEnd
    ) {
        $this->refId = $refId;
        $this->objId = $objId;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->parentRefId = $parentRefId;
        $this->parentLftTree = $parentLftTree;
        $this->objectPeriodHasTime = $objectPeriodHasTime;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function getRefId() : int
    {
        return $this->refId;
    }

    public function getObjId() : int
    {
        return $this->objId;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getParentRefId() : int
    {
        return $this->parentRefId;
    }

    public function getParentLftTree() : int
    {
        return $this->parentLftTree;
    }

    public function objectPeriodHasTime() : bool
    {
        return $this->objectPeriodHasTime;
    }

    public function getPeriodStart() : ?DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getPeriodEnd() : ?DateTimeImmutable
    {
        return $this->periodEnd;
    }
}
