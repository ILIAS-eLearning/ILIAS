<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Transform date format to DateTimeImmutable
 * Please note:
 * - RFC3339 & W3C format output on screen is the same as Atom
 * - RFC850 format output on screen is the same as Cookie
 * - RFC1036, RFC1123, RFC2822 & RSS format output on screen is the same as RFC822
 */
class DateTimeTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritDoc
     */
    public function transform($from) : DateTimeImmutable
    {
        if ($from instanceof DateTimeImmutable) {
            return $from;
        }

        $formats = [
            DateTimeInterface::ATOM,
            DateTimeInterface::COOKIE,
            DateTimeInterface::ISO8601,
            DateTimeInterface::RFC822,
            DateTimeInterface::RFC7231,
            DateTimeInterface::RFC3339_EXTENDED
        ];

        if (is_string($from)) {
            foreach ($formats as $format) {
                $res = DateTimeImmutable::createFromFormat($format, $from);
                if ($res instanceof DateTimeImmutable) {
                    return $res;
                }
            }
        }

        if (is_int($from) || is_float($from)) {
            return new DateTimeImmutable("@" . round($from));
        }

        throw new ConstraintViolationException(
            sprintf('Value "%s" could not be transformed.', var_export($from, true)),
            'no_valid_datetime',
            $from
        );
    }
}
