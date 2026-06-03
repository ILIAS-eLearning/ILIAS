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

namespace ILIAS\KeyValueStorage;

/**
 * Logical namespace isolating keys of one consumer from others within the same backend.
 */
final readonly class StorageNamespace implements \Stringable
{
    /**
     * Maximum length for structured namespace identifiers.
     */
    public const int MAX_LENGTH = 128;

    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('Storage namespace must not be empty.');
        }

        if (\strlen($value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'Storage namespace must not exceed ' . self::MAX_LENGTH . ' characters, got '
                . \strlen($value) . '.'
            );
        }

        if (!\preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/', $value)) {
            throw new \InvalidArgumentException(
                'Storage namespace must be a dot-separated lowercase identifier, got "' . $value . '".'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
