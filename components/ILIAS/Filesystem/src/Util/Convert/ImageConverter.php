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
use ILIAS\Filesystem\Util\MemoryStreamToTempFileStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class ImageConverter
{
    use MemoryStreamToTempFileStream;

    protected const STATUS_OK = 1;
    protected const STATUS_FAILED = 2;
    protected const STATUS_UNKNOWN = 4;

    protected const RESOLUTION = 72;
    protected const RESOLUTION_FACTOR = self::RESOLUTION / 72;

    protected int $status = self::STATUS_UNKNOWN;
    protected ?FileStream $output_stream = null;
    protected ?\Throwable $throwable = null;
    protected ?string $requested_background = null;
    protected \Imagick $image;

    public function __construct(
        protected ImageConversionOptions $conversion_options,
        protected ImageOutputOptions $output_options,
        protected FileStream $stream
    ) {
        $this->image = new \Imagick();
        $this->convert();
    }

    private function convert(): void
    {
        try {
            $this->handleBackgroundColor();
            $this->readInputStream();
            $this->handleFormatAndQuality();
            $this->handleImageDimension();
            $this->buildOutputStream();
        } catch (\Throwable $t) {
            $this->status = self::STATUS_FAILED;
            $this->throwable = $t;
            if ($this->conversion_options->throwOnError()) {
                throw $t;
            }
        }
    }


    protected function handleImageDimension(): void
    {
        $requested_width = $this->conversion_options->getWidth();
        $requested_height = $this->conversion_options->getHeight();
        $original_image_width = $this->image->getImageWidth();
        $original_image_height = $this->image->getImageHeight();

        switch ($this->conversion_options->getDimensionMode()) {
            default:
            case ImageConversionOptions::DIMENSION_MODE_NONE:
                // no resizing
                return;
            case ImageConversionOptions::DIMENSION_MODE_FIT:
                if ($this->conversion_options->hasCrop()) {
                    $final_height = $requested_height;
                    $final_width = $requested_width;
                } else {
                    // this is a special case, where we want to fit the image into the given dimensions and
                    // Imagick knows the thumbnail method for that
                    $this->doThumbnail(
                        $requested_width,
                        $requested_height
                    );
                    return;
                }
                break;
            case ImageConversionOptions::DIMENSTION_MODE_RESIZE_TO_FIXED:
                // by width and height
                if ($requested_width > 0 && $requested_height > 0) {
                    $final_width = $requested_width;
                    $final_height = $requested_height;
                } else {
                    throw new \InvalidArgumentException('Dimension Mode does not match the given width/height');
                }
                break;
            case ImageConversionOptions::DIMENSTION_MODE_RESIZE_BY_HEIGHT:
                // by height
                if ($requested_width === null && $requested_height > 0) {
                    $ratio = $original_image_height / $requested_height;
                    $final_width = intval($original_image_width / $ratio);
                    $final_height = $requested_height;
                    $l = 1;
                } else {
                    throw new \InvalidArgumentException('Dimension Mode does not match the given width/height');
                }
                break;
            case ImageConversionOptions::DIMENSTION_MODE_RESIZE_BY_WIDTH:
                // by width
                if ($requested_width > 0 && $requested_height === null) {
                    $ratio = $original_image_width / $requested_width;
                    $final_width = $requested_width;
                    $final_height = intval($original_image_height / $ratio);
                } else {
                    throw new \InvalidArgumentException('Dimension Mode does not match the given width/height');
                }
                break;
            case ImageConversionOptions::DIMENSION_MODE_KEEP:
                // by none of them
                if ($requested_width === null && $requested_height === null) {
                    $final_width = $original_image_width;
                    $final_height = $original_image_height;
                } else {
                    throw new \InvalidArgumentException('Dimension Mode does not match the given width/height');
                }
                break;
        }

        if ($this->conversion_options->hasCrop()) {
            $this->doCrop(
                $final_width,
                $final_height
            );
        } else {
            $this->doResize(
                $final_width,
                $final_height
            );
        }
    }


    protected function buildOutputStream(): void
    {
        if ($this->conversion_options->getOutputPath() === null) {
            $this->output_stream = Streams::ofString($this->image->getImageBlob());
        } else {
            $this->image->writeImage($this->conversion_options->getOutputPath());
            $this->output_stream = Streams::ofResource(fopen($this->conversion_options->getOutputPath(), 'rb'));
        }

        $this->output_stream->rewind();

        $this->status = self::STATUS_OK;
    }

    protected function handleFormatAndQuality(): void
    {
        $this->image->setImageResolution(
            self::RESOLUTION,
            self::RESOLUTION
        );
        // High density images do not support Resampling, cache to small. we deactivate this
        /*$this->image->resampleImage(
            self::RESOLUTION,
            self::RESOLUTION,
            \Imagick::FILTER_LANCZOS,
            1
        );*/
        $quality = $this->output_options->getQuality();

        // if $this->output_options->getFormat() is 'keep', we map it to the original format
        if ($this->output_options->getFormat() === ImageOutputOptions::FORMAT_KEEP) {
            try {
                $this->output_options = $this->output_options->withFormat(strtolower($this->image->getImageFormat()));
            } catch (\InvalidArgumentException) {
            }
        }

        switch ($this->output_options->getFormat()) {
            case ImageOutputOptions::FORMAT_WEBP:
                $this->image->setImageFormat('webp');
                $this->image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $this->image = $this->image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                if ($quality === 0) {
                    $this->image->setOption('webp:lossless', 'false');
                }
                if ($quality === 100) {
                    $this->image->setOption('webp:lossless', 'true');
                }
                break;
            case ImageOutputOptions::FORMAT_JPG:
                $this->image->setImageFormat('jpeg');
                $this->image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $this->image = $this->image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $this->image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $this->image->setImageCompressionQuality($quality);
                break;
            case ImageOutputOptions::FORMAT_PNG:
                $png_compression_level = round($quality / 100 * 9, 0);
                if ($this->requested_background !== null && $this->requested_background !== 'transparent') {
                    $this->image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                } else {
                    $this->image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
                }
                $this->image->setImageFormat('png');
                $this->image->setOption('png:compression-level', (string) $png_compression_level);
                break;
        }
        $this->image->stripImage();
    }

    protected function handleBackgroundColor(): void
    {
        $this->requested_background = $this->conversion_options->getBackgroundColor();
        if ($this->output_options->getFormat(
        ) === ImageOutputOptions::FORMAT_JPG && $this->requested_background === null) {
            $this->requested_background = '#FFFFFF';
        }
        if ($this->output_options->getFormat(
        ) === ImageOutputOptions::FORMAT_PNG && $this->requested_background === null) {
            $this->requested_background = 'transparent';
        }
        if ($this->requested_background !== null) {
            $this->image->setBackgroundColor(new \ImagickPixel($this->requested_background));
        }
    }

    protected function readInputStream(): void
    {
        if ($this->conversion_options->makeTemporaryFiles()) {
            $this->stream = $this->maybeSafeToTempStream($this->stream);
        }
        $this->stream->rewind();
        $this->image->readImageFile($this->stream->detach());
    }


    public function isOK(): bool
    {
        return $this->status === self::STATUS_OK;
    }

    public function getThrowableIfAny(): ?\Throwable
    {
        return $this->throwable;
    }


    public function getStream(): FileStream
    {
        return $this->output_stream;
    }

    private function factoredResolution(int $initial): int
    {
        return intval(round($initial * self::RESOLUTION_FACTOR, 0));
    }

    protected function doCrop(int $width, int $height): void
    {
        $this->image->setGravity(\Imagick::GRAVITY_CENTER);
        $this->image->cropThumbnailImage(
            $this->factoredResolution($width),
            $this->factoredResolution($height)
        );
    }

    protected function doResize(int $width, int $height): void
    {
        $this->image->resizeImage(
            $this->factoredResolution($width),
            $this->factoredResolution($height),
            \Imagick::FILTER_LANCZOS,
            1
        );
    }

    protected function doThumbnail(int $width, int $height): void
    {
        $this->image->thumbnailImage(
            $this->factoredResolution($width),
            $this->factoredResolution($height),
            true,
            !$this->conversion_options->keepAspectRatio(),
        );
    }
}
