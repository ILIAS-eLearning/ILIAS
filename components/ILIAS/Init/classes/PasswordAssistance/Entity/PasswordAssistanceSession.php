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

namespace ILIAS\Init\PasswordAssitance\Entity;

use ILIAS\Init\PasswordAssitance\ValueObject\PasswordAssistanceHash;
use ILIAS\Data\ObjectId;
use ILIAS\Data\Clock\ClockInterface;

final class PasswordAssistanceSession
{
    public function __construct(
        private readonly PasswordAssistanceHash $hash,
        private readonly ObjectId $usr_id,
        private ?\DateTimeImmutable $creation_datetime = null,
        private ?\DateTimeImmutable $expiration_datetime = null
    ) {
    }

    public function withCreationDateTime(?\DateTimeImmutable $creation_datetime): self
    {
        $clone = clone $this;
        $clone->creation_datetime = $creation_datetime;

        return $clone;
    }

    public function withExpirationDateTime(?\DateTimeImmutable $expiration_datetime): self
    {
        $clone = clone $this;
        $clone->expiration_datetime = $expiration_datetime;

        return $clone;
    }

    public function hash(): PasswordAssistanceHash
    {
        return $this->hash;
    }

    public function usrId(): ObjectId
    {
        return $this->usr_id;
    }

    public function creationDateTime(): ?\DateTimeImmutable
    {
        return $this->creation_datetime;
    }

    public function expirationDateTime(): ?\DateTimeImmutable
    {
        return $this->expiration_datetime;
    }

    public function isExpired(ClockInterface $clock): bool
    {
        return $this->expiration_datetime !== null && $this->expiration_datetime < $clock->now();
    }
}
