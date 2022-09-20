<?php

declare(strict_types=1);

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

/**
 * Class ilCmiXapiReportFilter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReportFilter
{
    protected string $activityId;

    protected int $limit;

    protected int $offset;

    protected string $orderField;

    protected string $orderDirection;

    protected ?ilCmiXapiUser $actor = null;

    protected ?string $verb = null;

    protected ?ilCmiXapiDateTime $startDate = null;

    protected ?ilCmiXapiDateTime $endDate = null;

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function getOrderField(): string
    {
        return $this->orderField;
    }

    public function setOrderField(string $orderField): void
    {
        $this->orderField = $orderField;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    public function setOrderDirection(string $orderDirection): void
    {
        $this->orderDirection = $orderDirection;
    }

    public function getActor(): ?\ilCmiXapiUser
    {
        return $this->actor;
    }

    public function setActor(\ilCmiXapiUser $actor): void
    {
        $this->actor = $actor;
    }

    public function getVerb(): ?string
    {
        return $this->verb;
    }

    public function setVerb(string $verb): void
    {
        $this->verb = $verb;
    }

    public function getStartDate(): ?\ilCmiXapiDateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\ilCmiXapiDateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\ilCmiXapiDateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\ilCmiXapiDateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}
