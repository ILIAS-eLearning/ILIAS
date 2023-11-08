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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class ImageConversionOptions
{
    public const DIMENSION_MODE_NONE = 0;
    public const DIMENSION_MODE_FIT = 1;
    public const DIMENSTION_MODE_RESIZE_TO_FIXED = 2;
    public const DIMENSTION_MODE_RESIZE_BY_HEIGHT = 3;
    public const DIMENSTION_MODE_RESIZE_BY_WIDTH = 4;
    public const DIMENSION_MODE_KEEP = 5;

    private int $dimension_mode = self::DIMENSION_MODE_NONE;
    private ?int $height = null;
    private ?int $width = null;
    private bool $keep_aspect_ratio = true;
    private ?string $background_color = null;
    private ?string $output_path = null;
    private bool $crop = true;
    private bool $throw_on_error = false;
    private bool $make_temporary_files = false;

    /**
     * @description If there is any throwable during convertion, this will be thworn again. otherwise one
     * must check with isOK()
     */
    public function withThrowOnError(bool $throw_on_error): self
    {
        $clone = clone $this;
        $clone->throw_on_error = $throw_on_error;
        return $clone;
    }

    /**
     * @description if passing a stream from memory, make a temporary file for this. otherwise,
     * the php://memory stream will be used which can cause problems with imagick in some circumstances,
     * but is faster.
     * @internal
     */
    public function withMakeTemporaryFiles(bool $make_temporary_files): self
    {
        $clone = clone $this;
        $clone->make_temporary_files = $make_temporary_files;
        return $clone;
    }

    /**
     * @description Fit the image into the given size. Depending on the withCrop option, the image will be cropped or not.
     */
    public function withFitIn(int $max_size): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->dimension_mode = self::DIMENSION_MODE_FIT;
        $clone->width = $clone->height = $max_size;
        return $clone;
    }

    /**
     * @description Keep the aspect ratio while resizing the image. If used with withCrop, the image will be cropped as well to fit the size.
     */
    public function withKeepAspectRatio(bool $keep_aspect_ratio): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->keep_aspect_ratio = $keep_aspect_ratio;
        return $clone;
    }

    /**
     * @description Crops the final image if needed.
     */
    public function withCrop(bool $crop): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->crop = $crop;
        return $clone;
    }

    /**
     * @description No resizing, the original image dimension will be used.
     */
    public function withKeepDimensions(bool $keep): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->dimension_mode = self::DIMENSION_MODE_KEEP;
        $this->width = $this->height = null;
        return $clone;
    }

    /**
     * @description Resize the image to the given width. Depends on withHeight() if the image will be resized to a fixed size or depending on the width.
     */
    public function withWidth(int $width): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->dimension_mode = ($this->height === null) ? self::DIMENSTION_MODE_RESIZE_BY_WIDTH : self::DIMENSTION_MODE_RESIZE_TO_FIXED;
        $clone->width = $width;
        return $clone;
    }

    /**
     * @description Resize the image to the given height. Depends on withWidth() if the image will be resized to a fixed size or depending on the height.
     */
    public function withHeight(int $height): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->dimension_mode = ($this->width === null) ? self::DIMENSTION_MODE_RESIZE_BY_HEIGHT : self::DIMENSTION_MODE_RESIZE_TO_FIXED;
        $clone->height = $height;
        return $clone;
    }

    /**
     * @description Resizes the Image to a fixed size. Use withCrop() and withKeepAspectRatio() if needed.
     */
    public function withFixedDimensions(int $width, int $height): ImageConversionOptions
    {
        return $this->withHeight($height)->withWidth($width);
    }

    /**
     * @deprecated
     * @description Set an write path for the converted image. If not set, the image will be converted in memory.
     */
    public function withOutputPath(string $output_path): ImageConversionOptions
    {
        $clone = clone $this;
        $clone->output_path = $output_path;
        return $clone;
    }

    /**
     * @description Set a background color for the image. This is used e.g. while converting
     * transparent pngs to jpgs.
     */
    public function withBackgroundColor(string $background_color): ImageConversionOptions
    {
        $this->checkBackgroundColor($background_color);

        $clone = clone $this;
        $clone->background_color = $background_color;
        return $clone;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->background_color;
    }

    public function getOutputPath(): ?string
    {
        return $this->output_path;
    }


    public function getDimensionMode(): int
    {
        return $this->dimension_mode;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }


    public function keepAspectRatio(): bool
    {
        return $this->keep_aspect_ratio;
    }


    public function hasCrop(): bool
    {
        return $this->crop;
    }

    public function throwOnError(): bool
    {
        return $this->throw_on_error;
    }

    public function makeTemporaryFiles(): bool
    {
        return $this->make_temporary_files;
    }

    protected function checkBackgroundColor(string $background_color): void
    {
        if (!preg_match('/^#?([a-f0-9]{6})$/i', $background_color)) {
            throw new \InvalidArgumentException('Invalid background color');
        }
    }
}
