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

namespace ILIAS\Refinery\URI;

use ILIAS\Refinery\Transformation;
use ILIAS\Data\QR\ErrorCorrectionLevel;
use ILIAS\Refinery\URI\Transformation\ToStringTransformation;
use ILIAS\Refinery\URI\Transformation\ToSvgQrCodeTransformation;
use ILIAS\Refinery\URI\Transformation\FromSvgTransformation;

class Group
{
    public function toString(): Transformation
    {
        return new ToStringTransformation();
    }

    public function toSvgQrCode(
        ErrorCorrectionLevel $error_correction_level = ErrorCorrectionLevel::MEDIUM,
        int $size_in_px = 400,
    ): Transformation {
        return new ToSvgQrCodeTransformation($error_correction_level, $size_in_px);
    }

    public function fromSvg(): Transformation
    {
        return new FromSvgTransformation();
    }
}
