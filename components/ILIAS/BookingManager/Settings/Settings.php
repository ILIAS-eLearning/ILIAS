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

namespace ILIAS\BookingManager\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected bool $public_log,
        protected int $schedule_type,
        protected int $overall_limit = 0,
        protected int $reservation_period = 0,
        protected bool $reminder_status = false,
        protected int $reminder_day = 1,
        protected int $pref_deadline = 0,
        protected int $preference_nr = 0,
        protected bool $messages = false
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getPublicLog(): bool
    {
        return $this->public_log;
    }

    public function getScheduleType(): int
    {
        return $this->schedule_type;
    }

    public function getOverallLimit(): int
    {
        return $this->overall_limit;
    }

    public function getReservationPeriod(): int
    {
        return $this->reservation_period;
    }

    public function getReminderStatus(): bool
    {
        return $this->reminder_status;
    }

    public function getReminderDay(): int
    {
        return $this->reminder_day;
    }

    public function getPrefDeadline(): int
    {
        return $this->pref_deadline;
    }

    public function getPreferenceNr(): int
    {
        return $this->preference_nr;
    }

    public function getMessages(): bool
    {
        return $this->messages;
    }
}
