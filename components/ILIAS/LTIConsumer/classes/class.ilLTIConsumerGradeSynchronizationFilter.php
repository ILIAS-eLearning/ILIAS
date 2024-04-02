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

/**
 * Class ilLTIConsumerGradeSynchronizationFilter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationFilter
{
    protected string $activityId;

    protected int $limit = 0;

    protected int $offset = 0;

    protected string $orderField = "";

    protected string $orderDirection = "";

    protected ?int $actor = null;

    protected ?string $activity_progress = null;

    protected ?string $grading_progress = null;

    protected ?ilDateTime $startDate = null;

    protected ?ilDateTime $endDate = null;


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

    public function getActor(): ?int
    {
        return $this->actor;
    }

    public function setActor(int $actor): void
    {
        $this->actor = $actor;
    }

    public function getActivityProgress(): ?string
    {
        return $this->activity_progress;
    }

    public function setActivityProgress(string $activityProgress): void
    {
        $this->activity_progress = $activityProgress;
    }

    public function getGradingProgress(): ?string
    {
        return $this->grading_progress;
    }

    public function setGradingProgress(string $gradingProgress): void
    {
        $this->grading_progress = $gradingProgress;
    }

    public function getStartDate(): ?\ilDateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\ilDateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\ilDateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\ilDateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}
