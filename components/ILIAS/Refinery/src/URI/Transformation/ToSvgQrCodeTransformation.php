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
use ILIAS\Data\QR\ErrorCorrectionLevel;
use ILIAS\Data\SVG;
use ILIAS\Data\URI;
use BaconQrCode as External;

class ToSvgQrCodeTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    protected const string ENCODING = 'UTF-8';

    public function __construct(
        protected ErrorCorrectionLevel $error_correction_level,
        protected int $size_in_px,
    ) {
        $this->assertIntGreaterThanZero($size_in_px);
    }

    public function transform(mixed $from): SVG
    {
        if (!$from instanceof URI) {
            throw new \InvalidArgumentException("Argument must be of type " . URI::class);
        }

        $writer = new External\Writer(
            new External\Renderer\ImageRenderer(
                new External\Renderer\RendererStyle\RendererStyle($this->size_in_px),
                new External\Renderer\Image\SvgImageBackEnd(),
            ),
        );

        $raw_svg_string = $writer->writeString(
            $from->__toString(),
            self::ENCODING,
            $this->mapErrorCorrectionLevel($this->error_correction_level),
        );

        return new SVG($raw_svg_string);
    }

    protected function mapErrorCorrectionLevel(ErrorCorrectionLevel $level): External\Common\ErrorCorrectionLevel
    {
        return match ($level) {
            ErrorCorrectionLevel::LOW => External\Common\ErrorCorrectionLevel::L(),
            ErrorCorrectionLevel::MEDIUM => External\Common\ErrorCorrectionLevel::M(),
            ErrorCorrectionLevel::QUARTILE => External\Common\ErrorCorrectionLevel::Q(),
            ErrorCorrectionLevel::HIGH => External\Common\ErrorCorrectionLevel::H(),
        };
    }

    protected function assertIntGreaterThanZero(int $number): void
    {
        if (0 >= $number) {
            throw new \InvalidArgumentException("Number must be greater than zero.");
        }
    }
}
