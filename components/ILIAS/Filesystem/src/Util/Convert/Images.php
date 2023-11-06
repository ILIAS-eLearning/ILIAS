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

namespace ILIAS\Filesystem\Util\Convert;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Images
{
    protected ImageConversionOptions $conversion_options;
    protected ImageOutputOptions $image_output_options;

    /**
     * @param bool $throw_on_error if there is any error, throw an exception, otherwise one must check with isOK()
     */
    public function __construct(
        bool $throw_on_error = false
    ) {
        // Defaults
        $this->conversion_options = (new ImageConversionOptions())
            ->withMakeTemporaryFiles(false)
            ->withThrowOnError($throw_on_error);
        $this->image_output_options = (new ImageOutputOptions())
            ->withJpgOutput()
            ->withQuality(75);
    }

    /**
     * @description Creates an image from the given stream which fits into the given size and keeps the
     * aspect ratio. Use getStream() to get final image.
     */
    public function thumbnail(
        FileStream $stream,
        int $fit_into_size,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        return new ImageConverter(
            $this->conversion_options
                ->withFitIn($fit_into_size)
                ->withCrop(false)
                ->withKeepAspectRatio(true),
            $this->merge($image_output_options),
            $stream
        );
    }

    /**
     * @description Creates an image from the given stream which fits into the given size, but is cropped to
     * fill the whole square. Use getStream() to get final image.
     */
    public function croppedSquare(
        FileStream $stream,
        int $square_size,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        return new ImageConverter(
            $this->conversion_options
                ->withFitIn($square_size)
                ->withKeepAspectRatio(true)
                ->withCrop(true),
            $this->merge($image_output_options),
            $stream
        );
    }


    /**
     * @description Resizes an image to an image with the given width.
     * The height is calculated to keep the aspect ratio.
     * Use getStream() to get final image.
     */
    public function resizeByWidth(
        FileStream $stream,
        int $width,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        return new ImageConverter(
            $this->conversion_options
                ->withWidth($width)
                ->withKeepAspectRatio(true),
            $this->merge($image_output_options),
            $stream
        );
    }


    /**
     * @description Resizes an image to an image with the given height. The width is calculated to
     * keep the aspect ratio.
     * Use getStream() to get final image.
     */
    public function resizeByHeight(
        FileStream $stream,
        int $height,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        return new ImageConverter(
            $this->conversion_options
                ->withHeight($height)
                ->withKeepAspectRatio(true),
            $this->merge($image_output_options),
            $stream
        );
    }


    /**
     * @description Creates an image from the given stream, resized to width and height given.
     * The original image can be cropped (to keep aspect ratio) or not (which squeezes the original to fit).
     * Use getStream() to get final image.
     */
    public function resizeToFixedSize(
        FileStream $stream,
        int $width,
        int $height,
        bool $crop_or_otherwise_squeeze = true,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        return new ImageConverter(
            $this->conversion_options
                ->withWidth($width)
                ->withHeight($height)
                ->withCrop($crop_or_otherwise_squeeze)
                ->withKeepAspectRatio(true),
            $this->merge($image_output_options),
            $stream
        );
    }

    /**
     * @description Creates an image from the given stream, converted to the desired format.
     * Currently supported target formats are:
     * - ImageOutputOptions::FORMAT_JPG
     * - ImageOutputOptions::FORMAT_PNG
     * - ImageOutputOptions::FORMAT_KEEP (will keep the original format), but makes no sense here
     */
    public function convertToFormat(
        FileStream $stream,
        string $to_format,
        ?int $width = null,
        ?int $height = null,
        ImageOutputOptions $image_output_options = null
    ): ImageConverter {
        $conversion_options = $this->conversion_options
            ->withKeepAspectRatio(true)
            ->withCrop(true);

        if ($height !== null) {
            $conversion_options = $conversion_options->withHeight($height);
        }
        if ($width !== null) {
            $conversion_options = $conversion_options->withWidth($width);
        }
        if ($width === null && $height === null) {
            $conversion_options = $conversion_options->withKeepDimensions(true);
        }
        return new ImageConverter(
            $conversion_options,
            $this->merge($image_output_options)->withFormat($to_format),
            $stream
        );
    }

    private function merge(?ImageOutputOptions $image_output_options): ImageOutputOptions
    {
        if ($image_output_options !== null) {
            return $this->image_output_options
                ->withQuality($image_output_options->getQuality())
                ->withFormat($image_output_options->getFormat());
        }
        return $this->image_output_options;
    }
}
