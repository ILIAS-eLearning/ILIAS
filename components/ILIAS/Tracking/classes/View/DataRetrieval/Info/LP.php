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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval\Info;

use ilDateTime;
use ILIAS\Tracking\View\DataRetrieval\Info\LPInterface;

class LP implements LPInterface
{
    public function __construct(
        protected int $user_id,
        protected int $object_id,
        protected int $lp_status,
        protected int $percentage,
        protected int $lp_mode,
        protected int $spend_seconds,
        protected ilDateTime $status_changed,
        protected int $visits,
        protected int $read_count,
        protected bool $has_percentage
    ) {
    }

    public function hasPercentage(): bool
    {
        return $this->has_percentage;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getLPStatus(): int
    {
        return $this->lp_status;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function getLPMode(): int
    {
        return $this->lp_mode;
    }

    public function getStatusChanged(): ilDateTime
    {
        return $this->status_changed;
    }

    public function getReadCount(): int
    {
        return $this->read_count;
    }

    public function getSpentSeconds(): int
    {
        return $this->spend_seconds;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }
}
