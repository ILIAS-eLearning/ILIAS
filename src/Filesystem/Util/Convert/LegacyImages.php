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
 * @deprecated ise \ILIAS\Filesystem\Util\Convert\Images instead
 */
class LegacyImages
{
    private Images $image_converters;
    private ImageOutputOptions $output_options;

    public function __construct()
    {
        $this->image_converters = new Images(false);
        $this->output_options = new ImageOutputOptions();
    }
    //
    // Thumbnails
    //

    /**
     * @return string path to the thumbnail
     * @deprecated Use \ILIAS\Filesystem\Util\Convert\Images::thumbnail() instead.
     */
    public function thumbnail(
        string $path_to_original,
        string $path_to_output,
        int $fit_into_size,
        string $output_format = ImageOutputOptions::FORMAT_KEEP,
        int $image_quality = 75
    ): string {
        $converter = $this->image_converters->thumbnail(
            $this->buildStream($path_to_original),
            $fit_into_size,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat($output_format)
        );
        return $this->storeStream($converter, $path_to_output);
    }

    //
    // Cropped Square
    //

    /**
     * @return string path to the thumbnail
     * @deprecated Use \ILIAS\Filesystem\Util\Convert\Images::croppedSquare() instead.
     */
    public function croppedSquare(
        string $path_to_original,
        string $path_to_output,
        int $square_size,
        string $output_format = ImageOutputOptions::FORMAT_KEEP,
        int $image_quality = 75
    ): string {
        $converter = $this->image_converters->croppedSquare(
            $this->buildStream($path_to_original),
            $square_size,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat($output_format)
        );

        return $this->storeStream($converter, $path_to_output);
    }

    //
    // Resize
    //

    /**
     * @return string path to the thumbnail
     * @deprecated Use \ILIAS\Filesystem\Util\Convert\Images::resizeByWidth() instead.
     */
    public function resizeByWidth(
        string $path_to_original,
        string $path_to_output,
        int $width,
        string $output_format = ImageOutputOptions::FORMAT_KEEP,
        int $image_quality = 60
    ): string {
        $converter = $this->image_converters->resizeByWidth(
            $this->buildStream($path_to_original),
            $width,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat($output_format)
        );
        return $this->storeStream($converter, $path_to_output);
    }


    /**
     * @deprecated Use \ILIAS\Filesystem\Util\Convert\Images::resizeByHeight() instead.
     * @return string path to the resized image
     */
    public function resizeByHeight(
        string $path_to_original,
        string $path_to_output,
        int $height,
        string $output_format = ImageOutputOptions::FORMAT_KEEP,
        int $image_quality = 60
    ): string {
        $converter = $this->image_converters->resizeByHeight(
            $this->buildStream($path_to_original),
            $height,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat($output_format)
        );
        return $this->storeStream($converter, $path_to_output);
    }


    /**
     * @return string path to the thumbnail
     * @deprecated Use \ILIAS\Filesystem\Util\Convert\Images::resizeToFixedSize() instead.
     */
    public function resizeToFixedSize(
        string $path_to_original,
        string $path_to_output,
        int $width,
        int $height,
        bool $crop_if_true_and_resize_if_false = true,
        string $output_format = ImageOutputOptions::FORMAT_KEEP,
        int $image_quality = 60
    ): string {
        $converter = $this->image_converters->resizeToFixedSize(
            $this->buildStream($path_to_original),
            $width,
            $height,
            $crop_if_true_and_resize_if_false,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat($output_format)
        );
        return $this->storeStream($converter, $path_to_output);
    }

    public function convertToFormat(
        string $path_to_original,
        string $path_to_output,
        string $output_format,
        ?int $width = null,
        ?int $height = null
    ): string {
        $converter = $this->image_converters->convertToFormat(
            $this->buildStream($path_to_original),
            $output_format,
            $width,
            $height
        );
        return $this->storeStream($converter, $path_to_output);
    }

    private function storeStream(ImageConverter $converter, string $path): string
    {
        if (!$converter->isOK()) {
            throw $converter->getThrowableIfAny() ?? new \RuntimeException('Could not create requested image');
        }

        $stream = $converter->getStream();

        $stream->rewind();
        if (file_put_contents($path, $stream->getContents()) === false) {
            throw new \RuntimeException('Could not store image');
        }
        return $path;
    }

    private function buildStream(string $path_to_original): \ILIAS\Filesystem\Stream\Stream|FileStream
    {
        return Streams::ofResource(fopen($path_to_original, 'rb'));
    }
}
