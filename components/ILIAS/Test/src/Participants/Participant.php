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

use ILIAS\Test\Results\Data\AttemptOverview;

class Participant
{
    private ?AttemptOverview $attempt_overview = null;
    private ?\DateTimeImmutable $running_attempt_start = null;

    public function __construct(
        private readonly int $user_id,
        private readonly ?int $active_id = null,
        private readonly ?int $test_id = null,
        private readonly ?string $anonymous_id = null,
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

    public function getAnonymousId(): ?string
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

    public function withClientIpFrom(?string $ip): self
    {
        if ($ip === '') {
            $ip = null;
        }
        $clone = clone $this;
        $clone->client_ip_from = $ip;
        return $clone;
    }

    public function getClientIpTo(): ?string
    {
        return $this->client_ip_to;
    }

    public function withClientIpTo(?string $ip): self
    {
        if ($ip === '') {
            $ip = null;
        }
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

    public function hasUnfinishedAttempts(): bool
    {
        return $this->unfinished_attempts;
    }

    public function getFirstAccess(): ?\DateTimeImmutable
    {
        return $this->first_access;
    }

    public function getLastAccess(): ?\DateTimeImmutable
    {
        return $this->last_access;
    }

    public function hasAnsweredQuestionsForScoredAttempt(): bool
    {
        return $this->attempt_overview?->hasAnsweredQuestions() ?? false;
    }

    public function getRemainingDuration(
        int $processing_time,
        bool $reset_time_on_new_attempt
    ): int {
        $remaining = $this->getTotalDuration($processing_time);

        $start = $this->getFirstAccess()?->getTimestamp();
        if ($reset_time_on_new_attempt) {
            $start = $this->running_attempt_start?->getTimestamp();
        }

        if ($start === null) {
            return $remaining;
        }

        $remaining += $start;
        if ($this->submitted) {
            $remaining -= $this->getLastAccess()?->getTimestamp() ?? time();
        } else {
            $remaining -= time();
        }

        return max(0, $remaining);
    }

    public function getAttemptOverviewInformation(): ?AttemptOverview
    {
        return $this->attempt_overview;
    }

    public function withAttemptOverviewInformation(?AttemptOverview $attempt_overview): self
    {
        $clone = clone $this;
        $clone->attempt_overview = $attempt_overview;
        return $clone;
    }

    public function withRunningAttemptStart(\DateTimeImmutable $start_date): self
    {
        $clone = clone $this;
        $clone->running_attempt_start = $start_date;
        return $clone;
    }
}
