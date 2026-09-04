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

namespace ILIAS\Refinery\Decode\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use JsonException;

/**
 * This class is a wrapper around `json_decode` which reports undecodable input as a violation
 * instead of returning `null`, which cannot be told apart from the successfully decoded JSON
 * literal `null`.
 *
 * JSON objects are decoded into associative arrays instead of `stdClass`, so that results can be
 * processed further with the `container`, `to` and `kindlyTo` groups and round-trip with the
 * `encode` group.
 *
 * Please see https://www.php.net/manual/en/function.json-decode.php for more information.
 */
final class Json implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    /**
     * PHP does not expose the default of its JSON parser (PHP_JSON_PARSER_DEFAULT_DEPTH) to userland.
     */
    public const int DEFAULT_MAX_DEPTH = 512;
    public const int MAX_DEPTH_LOWER_BOUND = 1;
    public const int MAX_DEPTH_UPPER_BOUND = 2147483647;

    public function __construct(private readonly int $max_depth = self::DEFAULT_MAX_DEPTH)
    {
        if ($max_depth < self::MAX_DEPTH_LOWER_BOUND || $max_depth > self::MAX_DEPTH_UPPER_BOUND) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Maximum depth must be between %d and %d, got %d.',
                    self::MAX_DEPTH_LOWER_BOUND,
                    self::MAX_DEPTH_UPPER_BOUND,
                    $max_depth
                )
            );
        }
    }

    public function transform($from): mixed
    {
        if (!\is_string($from)) {
            throw new ConstraintViolationException(
                \sprintf('The value of type "%s" is not a string and cannot be decoded as JSON.', get_debug_type($from)),
                'not_a_string',
                get_debug_type($from)
            );
        }

        try {
            return json_decode($from, true, $this->max_depth, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            // The value itself is left out of the message, it may be large and carry sensitive data.
            throw new ConstraintViolationException(
                \sprintf('The value cannot be decoded as JSON: %s.', $exception->getMessage()),
                'not_json',
                $exception->getMessage()
            );
        }
    }
}
