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

namespace ILIAS\Registration\DualOptIn\Entity;

use ILIAS\Data\ObjectId;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationId;

final readonly class PendingRegistration
{
    public function __construct(
        private PendingRegistrationId $id,
        private ObjectId $usr_id,
        private PendingRegistrationHash $hash,
        private \DateTimeImmutable $created_at,
        private PendingRegistrationStatus $status = PendingRegistrationStatus::PENDING
    ) {
    }

    public function id(): PendingRegistrationId
    {
        return $this->id;
    }

    public function userId(): ObjectId
    {
        return $this->usr_id;
    }

    public function hash(): PendingRegistrationHash
    {
        return $this->hash;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function status(): PendingRegistrationStatus
    {
        return $this->status;
    }

    public function isConfirmed(): bool
    {
        return $this->status === PendingRegistrationStatus::CONFIRMED;
    }

    public function isExpired(): bool
    {
        return $this->status === PendingRegistrationStatus::EXPIRED;
    }

    public function isPending(): bool
    {
        return $this->status === PendingRegistrationStatus::PENDING;
    }

    public function withConfirmed(): self
    {
        if ($this->status === PendingRegistrationStatus::EXPIRED) {
            throw new \DomainException('Cannot confirm an expired registration.');
        }

        if ($this->status === PendingRegistrationStatus::CONFIRMED) {
            return $this;
        }

        return new self(
            $this->id,
            $this->usr_id,
            $this->hash,
            $this->created_at,
            PendingRegistrationStatus::CONFIRMED
        );
    }

    public function withExpired(): self
    {
        if ($this->status === PendingRegistrationStatus::CONFIRMED) {
            throw new \DomainException('Cannot expire an already confirmed registration.');
        }

        if ($this->status === PendingRegistrationStatus::EXPIRED) {
            return $this;
        }

        return new self(
            $this->id,
            $this->usr_id,
            $this->hash,
            $this->created_at,
            PendingRegistrationStatus::EXPIRED
        );
    }

    public function hasExpiredAt(\DateTimeImmutable $now, int $validity_in_seconds): bool
    {
        $expiration_date = $this->created_at->modify("+{$validity_in_seconds} seconds");

        return $now >= $expiration_date;
    }

    /**
     * @throws \InvalidArgumentException if the passed validity in seconds is invalid
     */
    public function withEvaluatedState(\DateTimeImmutable $now, ?int $validity_in_seconds): self
    {
        if (\is_int($validity_in_seconds) && $validity_in_seconds < 1) {
            throw new \InvalidArgumentException('Invalid validity_in_seconds value.');
        }

        if ($this->status === PendingRegistrationStatus::CONFIRMED) {
            return $this;
        }

        if (\is_int($validity_in_seconds) && $this->hasExpiredAt($now, $validity_in_seconds)) {
            return $this->withExpired();
        }

        return $this;
    }
}
