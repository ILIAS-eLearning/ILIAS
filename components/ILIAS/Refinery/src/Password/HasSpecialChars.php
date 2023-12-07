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

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Constraint;
use ILIAS\Data\Password;

class HasSpecialChars implements Constraint
{
    private const ALLOWED_CHARS = '/[,_.\-#\+\*?!%§\(\)\$]/u';

    public function problemWith($value)
    {
        return $this->password($value);
    }

    private function password(Password $value): ?string
    {
        if ((bool) preg_match(self::ALLOWED_CHARS, $value->toString())) {
            return null;
        }
        return "Password must contain special chars.";
    }
}
