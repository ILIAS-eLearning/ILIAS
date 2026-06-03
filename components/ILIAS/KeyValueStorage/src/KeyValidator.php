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
 * Validates storage keys using the same rules as PSR-16 (Simple Cache).
 *
 * PSR-16 reserves the characters `{}()/\@:` so keys stay compatible with cache
 * implementations and future key-pattern extensions (for example hierarchical or
 * templated keys). Excluding them here keeps KeyValueStorage aligned with that
 * interoperability contract and avoids ambiguous keys in composed storage paths
 * (such as session key prefixes built from namespace and key segments).
 *
 * Providers should delegate key validation to this class so all backends behave
 * consistently without coupling this component to any specific implementation.
 */
final readonly class KeyValidator
{
    /** @see https://www.php-fig.org/psr/psr-16/ PSR-16 reserved key characters */
    private const string RESERVED_CHARACTERS = '{}()/\@:';

    /**
     * Maximum length for storage keys.
     */
    public const int MAX_LENGTH = 255;

    public function validate(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('Storage key must be a non-empty string.');
        }

        if (\mb_strlen($key, 'UTF-8') > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'Storage key must not exceed ' . self::MAX_LENGTH . ' characters, got '
                . \mb_strlen($key, 'UTF-8') . '.'
            );
        }

        if (\strpbrk($key, self::RESERVED_CHARACTERS) !== false) {
            throw new \InvalidArgumentException(
                'Storage key must not contain reserved characters "' . self::RESERVED_CHARACTERS . '".'
            );
        }
    }
}
