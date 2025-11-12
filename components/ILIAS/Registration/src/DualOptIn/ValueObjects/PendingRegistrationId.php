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

namespace ILIAS\Registration\DualOptIn\ValueObjects;

use ILIAS\Data\UUID\Factory as UUIDFactory;

final readonly class PendingRegistrationId
{
    private string $uuid;

    public function __construct(string $uuid)
    {
        if (mb_strlen($uuid) !== 36) {
            throw new \InvalidArgumentException('Registration UUID must be 32 characters (UUIDv4).');
        }

        $this->uuid = $uuid;
    }

    public static function create(): self
    {
        return new self((new UUIDFactory())->uuid4AsString());
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->uuid, $other->uuid);
    }

    public function toString(): string
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
