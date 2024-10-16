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

namespace ILIAS\Test\Participants;

class Participant extends \ilTestParticipant
{
    public const ATTEMPT_NOT_STARTED = 'not_started';
    public const ATTEMPT_RUNNING = 'running';
    public const ATTEMPT_FINISHED = 'finished';
    private const ATTEMPT_FINISHED_BY_PARTICIPANT = 'finished_by_participant';
    private const ATTEMPT_FINISHED_BY_ADMINISTRATOR = 'finished_by_administrator';
    private const ATTEMPT_FINISHED_BY_DURATION = 'finished_by_duration';
    private const ATTEMPT_FINISHED_BY_CRONJOB = 'finished_by_cronjob';

    protected ?int $test_id = null;
    protected int $extra_time = 0;
    protected int $tries = 0;
    protected ?string $client_ip_from = null;
    protected ?string $client_ip_to = null;
    protected ?int $invitation_date = null;
    protected ?bool $submitted = null;
    protected ?int $submitted_timestamp = null;
    protected ?int $last_started_pass = null;
    protected ?int $last_finished_pass = null;
    protected null|\DateTimeInterface|\Closure $test_start_date = null;
    protected null|\DateTimeInterface|\Closure $test_end_date = null;
    protected bool|\Closure $has_solutions;

    public function getExtraTime(): int
    {
        return $this->extra_time;
    }

    public function setExtraTime(int $extra_time): void
    {
        $this->extra_time = $extra_time;
    }

    public function addExtraTime(int $extra_time): void
    {
        $this->extra_time += $extra_time;
    }

    public function getClientIpFrom(): ?string
    {
        return $this->client_ip_from;
    }

    public function setClientIpFrom(?string $client_ip_from): void
    {
        $this->client_ip_from = $client_ip_from;
    }

    public function getClientIpTo(): ?string
    {
        return $this->client_ip_to;
    }

    public function setClientIpTo(?string $client_ip_to): void
    {
        $this->client_ip_to = $client_ip_to;
    }

    public function getTestId(): ?int
    {
        return $this->test_id;
    }

    public function setTestId(?int $test_id): void
    {
        $this->test_id = $test_id;
    }

    public function getTries(): int
    {
        return $this->tries;
    }

    public function setTries(int $tries): void
    {
        $this->tries = $tries;
    }

    public function setInvitationDate(?int $invitation_date): void
    {
        $this->invitation_date = $invitation_date;
    }

    public function isInvitedParticipant(): bool
    {
        return $this->invitation_date > 0;
    }

    public function getTotalDuration(?int $processing_time): int
    {
        if (!$processing_time) {
            return 0;
        }

        return $processing_time + $this->extra_time * 60;
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function setSubmitted(?bool $submitted): void
    {
        $this->submitted = $submitted;
    }

    public function getSubmittedTimestamp(): ?int
    {
        return $this->submitted_timestamp;
    }

    public function setSubmittedTimestamp(?int $submitted_timestamp): void
    {
        $this->submitted_timestamp = $submitted_timestamp;
    }

    public function getLastStartedPass(): ?int
    {
        return $this->last_started_pass;
    }

    public function setLastStartedPass(?int $last_started_pass): void
    {
        $this->last_started_pass = $last_started_pass;
    }

    public function getLastFinishedPass(): ?int
    {
        return $this->last_finished_pass;
    }

    public function setLastFinishedPass(?int $last_finished_pass): void
    {
        $this->last_finished_pass = $last_finished_pass;
    }

    public function getTestStartDate(): ?\DateTimeInterface
    {
        return $this->lazy($this->test_start_date);
    }

    public function setTestStartDate(\DateTimeInterface|\Closure|null $test_start_date): void
    {
        $this->test_start_date = $test_start_date;
    }

    public function getTestEndDate(): ?\DateTimeInterface
    {
        return $this->lazy($this->test_end_date);
    }

    public function setTestEndDate(\DateTimeInterface|\Closure|null $test_end_date): void
    {
        $this->test_end_date = $test_end_date;
    }

    public function hasSolutions(): bool
    {
        return $this->lazy($this->has_solutions);
    }

    public function setHasSolutions(bool|\Closure $has_solutions): void
    {
        $this->has_solutions = $has_solutions;
    }

    public function getRemainingDuration(int $processing_time): int
    {
        $remaining = $this->getTotalDuration($processing_time);
        $remaining += $this->getTestStartDate()?->getTimestamp() ?? 0;

        if ($this->isTestFinished()) {
            $remaining -= $this->getTestEndDate()?->getTimestamp() ?? time();
        } else {
            $remaining -= time();
        }

        return max(0, $remaining);
    }

    public function getStatusOfAttempt(): string
    {
        if (!$this->getActiveId()) {
            return self::ATTEMPT_NOT_STARTED;
        }

        if (!$this->isTestFinished()) {
            return self::ATTEMPT_RUNNING;
        }

        return self::ATTEMPT_FINISHED;
    }

    private function lazy(mixed &$value): mixed
    {
        if (is_callable($value)) {
            $callable = $value;
            $value = $callable();
        }

        return $value;
    }
}
