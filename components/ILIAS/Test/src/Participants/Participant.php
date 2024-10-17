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

class Participant
{
    public const ATTEMPT_NOT_STARTED = 'not_started';
    public const ATTEMPT_RUNNING = 'running';
    public const ATTEMPT_FINISHED = 'finished';
    private const ATTEMPT_FINISHED_BY_PARTICIPANT = 'finished_by_participant';
    private const ATTEMPT_FINISHED_BY_ADMINISTRATOR = 'finished_by_administrator';
    private const ATTEMPT_FINISHED_BY_DURATION = 'finished_by_duration';
    private const ATTEMPT_FINISHED_BY_CRONJOB = 'finished_by_cronjob';

    public function __construct(
        private readonly int $user_id,
        private readonly ?int $active_id = null,
        private readonly ?int $test_id = null,
        private readonly ?int $anonymous_id = null,
        private readonly string $firstname = '',
        private readonly string $lastname = '',
        private readonly string $login = '',
        private readonly string $matriculation = '',
        private int $extra_time = 0,
        private readonly int $attempts = 0,
        private ?string $client_ip_from = null,
        private ?string $client_ip_to = null,
        private readonly ?int $invitation_date = null,
        private readonly ?bool $submitted = null,
        private readonly ?int $last_started_attempt = null,
        private readonly ?int $last_finished_attempt = null,
        private readonly bool $unfinished_attempts = false,
        private readonly ?\DateTimeImmutable $first_access = null,
        private readonly ?\DateTimeImmutable $last_access = null
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getActiveId(): ?int
    {
        return $this->active_id;
    }

    public function getTestId(): ?int
    {
        return $this->test_id;
    }

    public function getAnonymousId(): ?int
    {
        return $this->anonymous_id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getMatriculation(): string
    {
        return $this->matriculation;
    }

    public function getExtraTime(): int
    {
        return $this->extra_time;
    }

    public function withAddedExtraTime(int $extra_time): self
    {
        $clone = clone $this;
        $clone->extra_time += $extra_time;
        return $clone;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getClientIpFrom(): ?string
    {
        return $this->client_ip_from;
    }

    public function withClientIpFrom(string $ip): self
    {
        $clone = clone $this;
        $clone->client_ip_from = $ip;
        return $clone;
    }

    public function getClientIpTo(): ?string
    {
        return $this->client_ip_to;
    }

    public function withClientIpTo(string $ip): self
    {
        $clone = clone $this;
        $clone->client_ip_to = $ip;
        return $clone;
    }

    public function isInvitedParticipant(): bool
    {
        return $this->invitation_date > 0;
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function getTotalDuration(?int $processing_time): int
    {
        if (!$processing_time) {
            return 0;
        }

        return $processing_time + $this->extra_time * 60;
    }

    public function getLastStartedAttempt(): ?int
    {
        return $this->last_started_attempt;
    }

    public function getLastFinishedAttempt(): ?int
    {
        return $this->last_finished_attempt;
    }

    public function hasUnfinishedAttempts(): ?bool
    {
        return $this->unfinished_attempts;
    }

    public function getTestStartDate(): ?\DateTimeInterface
    {
        return $this->lazy($this->test_start_date);
    }

    public function getTestEndDate(): ?\DateTimeInterface
    {
        return $this->lazy($this->test_end_date);
    }

    public function hasSolutions(): bool
    {
        return $this->lazy($this->has_solutions);
    }

    public function getRemainingDuration(int $processing_time): int
    {
        $remaining = $this->getTotalDuration($processing_time);
        $remaining += $this->getTestStartDate()?->getTimestamp() ?? 0;

        if ($this->submitted) {
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

        if (!$this->submitted) {
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
