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

namespace ILIAS\Refinery\String\Encoding;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\String\InvalidArgumentException;

class Encoding implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    public const UTF_8 = 'UTF-8';
    public const ISO_8859_1 = 'ISO-8859-1';
    public const US_ASCII = 'US-ASCII';

    // More common names for the encodings
    public const ASCII = self::US_ASCII;
    public const LATIN_1 = self::ISO_8859_1;

    public function __construct(
        private string $from_encoding,
        private string $to_encoding
    ) {
    }

    public function transform($from): string
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        return mb_convert_encoding(
            $from,
            $this->to_encoding,
            $this->from_encoding
        );
    }
}
