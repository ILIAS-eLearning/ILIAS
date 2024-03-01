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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;

class UTF8 implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private const UTF_8 = 'UTF-8';
    private const ISO_8859_1 = 'ISO-8859-1';

    private bool $detect_encoding = false;
    private bool $check_existing_encoding = false;

    public function transform($from): string
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        if ($this->check_existing_encoding && mb_check_encoding($from, self::UTF_8)) {
            return $from;
        }

        if ($this->detect_encoding) {
            $from_encoding = mb_detect_encoding($from);
        } else {
            $from_encoding = self::ISO_8859_1;
        }
        return mb_convert_encoding($from, self::UTF_8, $from_encoding);
    }
}
