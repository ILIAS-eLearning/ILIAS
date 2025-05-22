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

namespace ILIAS\Init\PasswordAssitance\ValueObject;

final class PasswordAssistanceHash implements \Stringable
{
    private const HASH_LENGTH_IN_BYTES = 64;

    public function __construct(
        private readonly string $hash
    ) {
        if (strlen($hash) !== self::HASH_LENGTH_IN_BYTES) {
            throw new \InvalidArgumentException(sprintf('The hash must be %s bytes long.', self::HASH_LENGTH_IN_BYTES));
        }
    }

    public function value(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
