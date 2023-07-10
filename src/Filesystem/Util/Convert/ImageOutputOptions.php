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
 */
class ImageOutputOptions
{
    public const FORMAT_JPG = 'jpg';
    public const FORMAT_PNG = 'png';
    public const FORMAT_WEBP = 'webp';
    public const FORMAT_KEEP = 'keep';
    private int $quality = 75;
    private string $format = self::FORMAT_JPG;
    private array $allowed_formats = [
        self::FORMAT_JPG,
        self::FORMAT_PNG,
        self::FORMAT_WEBP,
        self::FORMAT_KEEP,
    ];

    /**
     * @description set the desired output format.
     * @throws \InvalidArgumentException if an invalid format is passed
     */
    public function withFormat(string $format): ImageOutputOptions
    {
        $format = $this->checkFormat($format);
        $clone = clone $this;
        $clone->format = $format;
        return $clone;
    }

    /**
     * @description set the output format to JPG
     */
    public function withJpgOutput(): ImageOutputOptions
    {
        return $this->withFormat(self::FORMAT_JPG);
    }

    /**
     * @description set the output format to PNG
     */
    public function withPngOutput(): ImageOutputOptions
    {
        return $this->withFormat(self::FORMAT_PNG);
    }

    /**
     * @description set the output format to WEBP
     */
    public function withWebPOutput(): ImageOutputOptions
    {
        return $this->withFormat(self::FORMAT_WEBP);
    }

    /**
     * @description set the image compression quality. Depending on the format, this will be ignored or other values are needed.
     * JPG: 0-100
     * WEBP: 0 (loss) or 100 (lossless)
     * PNG: 0-100 (which will be converted to 0-9 internally)
     */
    public function withQuality(int $image_quality): ImageOutputOptions
    {
        $this->checkImageQuality($image_quality);
        $clone = clone $this;
        $clone->quality = $image_quality;
        return $clone;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    private function checkFormat(string $format): string
    {
        $format = strtolower($format);
        if ($format === 'jpeg') {
            $format = self::FORMAT_JPG;
        }

        if (!in_array($format, $this->allowed_formats, true)) {
            throw new \InvalidArgumentException('Format must be either jpg or png, ' . $format . ' given.');
        }
        return $format;
    }

    protected function checkImageQuality(int $image_quality): void
    {
        if ($this->format === self::FORMAT_WEBP) {
            if ($image_quality !== 0 && $image_quality !== 100) {
                throw new \InvalidArgumentException('WebP only supports quality 0 (loss) or 100 (losless)');
            }
        } elseif ($image_quality < 0 || $image_quality > 100) {
            throw new \InvalidArgumentException('Quality must be between 0 and 100');
        }
    }
}
