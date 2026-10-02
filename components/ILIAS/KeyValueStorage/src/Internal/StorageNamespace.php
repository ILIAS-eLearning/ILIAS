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

namespace ILIAS\KeyValueStorage\Internal;

/**
 * Isolates the keys of one consumer from every other consumer of a storage.
 *
 * Built from namespace segments so consumers of {@see \ILIAS\KeyValueStorage\Services}
 * never choose the delimiter. Repositories receive this value object on the
 * port boundary.
 *
 * Segments are otherwise permissive: the only characters rejected are those
 * that would break composition — the segment separator, the namespace/key
 * separator used by repositories, and control characters.
 *
 * @internal
 */
final readonly class StorageNamespace implements \Stringable
{
    public const int MAX_LENGTH = 128;

    private const string SEPARATOR = '.';

    /**
     * Characters that must not appear inside a segment: the segment separator,
     * {@see KeyRules::SEPARATOR}, and ASCII control characters.
     */
    private const string FORBIDDEN_PATTERN = '/[\x00-\x1F\x7F' . self::SEPARATOR . KeyRules::SEPARATOR . ']/';

    private string $value;

    /**
     * @param list<string> $segments
     */
    public function __construct(array $segments)
    {
        if ($segments === []) {
            throw new \InvalidArgumentException('A storage namespace must not be empty.');
        }

        foreach ($segments as $index => $segment) {
            if (!\is_string($segment)) {
                throw new \InvalidArgumentException(
                    'A storage namespace segment must be a string, got ' . \get_debug_type($segment)
                    . ' at position ' . $index . '.'
                );
            }

            if ($segment === '') {
                throw new \InvalidArgumentException('A storage namespace segment must not be empty.');
            }

            if (\preg_match(self::FORBIDDEN_PATTERN, $segment) === 1) {
                throw new \InvalidArgumentException(
                    'A storage namespace segment must not contain "' . self::SEPARATOR . '", "'
                    . KeyRules::SEPARATOR . '" or control characters, got "' . $segment . '".'
                );
            }
        }

        $value = \implode(self::SEPARATOR, $segments);

        if (\strlen($value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'A storage namespace must not be longer than ' . self::MAX_LENGTH
                . ' characters, got ' . \strlen($value) . '.'
            );
        }

        $this->value = $value;
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
