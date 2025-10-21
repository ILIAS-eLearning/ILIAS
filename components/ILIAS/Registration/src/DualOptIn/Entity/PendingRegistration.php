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

use DateTimeImmutable;
use ILIAS\Data\ObjectId;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationId;

final class PendingRegistration
{
    private PendingRegistrationId $id;
    private ObjectId $usr_id;
    private PendingRegistrationHash $hash;
    private DateTimeImmutable $created_at;

    public function __construct(
        PendingRegistrationId $id,
        ObjectId $usr_id,
        PendingRegistrationHash $hash,
        DateTimeImmutable $created_at,
    ) {
        $this->id = $id;
        $this->usr_id = $usr_id;
        $this->hash = $hash;
        $this->created_at = $created_at;
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getUserId(): int
    {
        return $this->usr_id->toInt();
    }

    public function getHashValue(): string
    {
        return $this->hash->toString();
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->created_at;
    }
}
