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
 */

declare(strict_types=1);

namespace ILIAS\Refinery\URI\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\SVG;

/**
 * The {@see \ILIAS\Data\URI} does not support data URI yet, therefore
 * this transformation currently returns a string.
 */
class FromSvgTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    protected const string SCHEME = 'data:';
    protected const string MIME_TYPE = 'image/svg+xml';
    protected const string ENCODING = 'base64';

    public function transform(mixed $from): string
    {
        if (!$from instanceof SVG) {
            throw new \InvalidArgumentException("Argument must be of type " . SVG::class);
        }

        return self::SCHEME . self::MIME_TYPE . ';' . self::ENCODING . ',' . base64_encode($from->__toString());
    }
}
