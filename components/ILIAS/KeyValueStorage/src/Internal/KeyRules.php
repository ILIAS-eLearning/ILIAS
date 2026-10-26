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
 * The rules every storage key has to follow.
 *
 * Keys are deliberately permissive: they are handed in by consumers, often
 * derived from class names or object ids, and this component is not a PSR-16
 * cache that would have to keep its keys interchangeable with one.
 *
 * Only what would break a storage is rejected: the colon, which separates
 * namespace from key when a repository has to compose both into one identifier,
 * and control characters, which cannot be stored or compared reliably.
 *
 * @internal
 */
final readonly class KeyRules
{
    public const int MAX_LENGTH = 255;

    public const string SEPARATOR = ':';

    private const string FORBIDDEN_PATTERN = '/[\x00-\x1F\x7F' . self::SEPARATOR . ']/';

    public function check(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('A storage key must not be empty.');
        }

        if (\mb_strlen($key, 'UTF-8') > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'A storage key must not be longer than ' . self::MAX_LENGTH . ' characters, got '
                . \mb_strlen($key, 'UTF-8') . '.'
            );
        }

        if (\preg_match(self::FORBIDDEN_PATTERN, $key) === 1) {
            throw new \InvalidArgumentException(
                'A storage key must not contain "' . self::SEPARATOR . '" or control characters.'
            );
        }
    }
}
