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

namespace ILIAS\KeyValueStorage\Subject;

/**
 * Opaque storage identity segment supplied by the consumer.
 *
 * KeyValueStorage validates the segment format only. It does not assign
 * meaning (user, role, object, …) to particular prefixes.
 */
final readonly class SubjectId
{
    public const int MAX_LENGTH = 128;

    public function __construct(private string $segment)
    {
        if ($segment === '') {
            throw new \InvalidArgumentException('Subject segment must not be empty.');
        }

        $length = \mb_strlen($segment, 'UTF-8');
        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'Subject segment must not exceed ' . self::MAX_LENGTH . ' characters, got ' . $length . '.'
            );
        }

        if (!\preg_match('/^[a-z][a-z0-9_]*$/', $segment)) {
            throw new \InvalidArgumentException(
                'Subject segment must be a lowercase identifier, got "' . $segment . '".'
            );
        }
    }

    public function storageSegment(): string
    {
        return $this->segment;
    }
}
