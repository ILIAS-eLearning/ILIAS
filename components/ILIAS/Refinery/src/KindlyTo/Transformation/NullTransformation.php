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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\Transformable;
use ILIAS\Refinery\ConstraintViolationException;

class NullTransformation implements Transformable
{
    public function transform($from)
    {
        if (is_null($from)) {
            return null;
        }
        if (is_string($from) && trim($from) === '') {
            return null;
        }
        throw new ConstraintViolationException(
            sprintf('The value "%s" could not be transformed into null', var_export($from, true)),
            'not_null',
            $from
        );
    }
}
