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

namespace ILIAS\Mail\Transformation;

use ilDBConstants;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;

class Utf8Mb4Sanitizer implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    public function transform($from): string
    {
        if (!\is_string($from)) {
            throw new ConstraintViolationException(
                'Value to be transformed must be of type string',
                'not_string'
            );
        }

        return preg_replace(
            '/[\x{10000}-\x{10FFFF}]/u',
            ilDBConstants::MB4_REPLACEMENT,
            $from
        );
    }
}
