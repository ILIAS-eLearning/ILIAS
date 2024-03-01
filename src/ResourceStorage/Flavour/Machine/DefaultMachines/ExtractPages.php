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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ExtractPages extends AbstractMachine implements FlavourMachine
{
    public const ID = 'extract_pages';
    public const PREVIEW_IMAGE_FORMAT = 'jpg';
    private bool $high_quality = false;

    public function getId(): string
    {
        return self::ID;
    }

    public function dependsOnEngine(): ?string
    {
        return ImagickEngine::class;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof PagesToExtract;
    }

    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        if (!$for_definition instanceof PagesToExtract) {
            throw new \InvalidArgumentException('Invalid definition');
        }

        if (!class_exists(\Imagick::class)) {
            return;
        }

        // Create target image
        $img = new \Imagick();

        // Quality Settings
        $this->high_quality = $for_definition->useMaxQuality();
        if ($this->high_quality) {
            $quality = 100;
            $img->setResolution(300, 300); // way better previews for PDFs. Must be set before reading the file
        } else {
            $quality = $for_definition->getQuality();
        }

        // Source specific settings
        $target_format = self::PREVIEW_IMAGE_FORMAT;
        $target_background = new \ImagickPixel('white');
        $alpha_channel = \Imagick::ALPHACHANNEL_REMOVE;
        $remove_color = null;
        $resolution = 72;
        $pattern = false;

        switch (true) {
            case ($this->isSVG($information)):
                $target_format = 'png64';
                $target_background = new \ImagickPixel('none');
                $alpha_channel = \Imagick::ALPHACHANNEL_ACTIVATE;
                $resolution = 96;
                $remove_color = new \ImagickPixel('transparent');
                $pattern = true;
                break;
            case ($information->getMimeType() === 'application/pdf'):
                $resolution = 96; // way better previews for PDFs. Must be set before reading the file
                break;
        }

        // General Image Settings
        $img->setResolution($resolution, $resolution);
        $img->setBackgroundColor($target_background);

        // Read source image
        try {
            if ($this->isSVG($information)) {
                $img->readImageBlob(
                    $this->prescaleSVG((string) $stream, $for_definition->getMaxSize())
                );
                if ($pattern) {
                    $pattern = new \Imagick();
                    $x = 10;
                    $pattern->readImageBlob(
                        '<?xml version="1.0" encoding="UTF-8"?><svg id="Ebene_2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '
                        . ($x * 2) . ' ' . ($x * 2) . '"><defs><style>.cls-1{fill:#afafaf;}.cls-1,.cls-2{stroke-width:0px;}.cls-2{fill:#e8e8e8;}</style></defs><g id="Ebene_1-2"><rect class="cls-1" width="'
                        . $x . '" height="' . $x . '"/><rect class="cls-2" y="' . $x . '" width="' . $x . '" height="' . $x
                        . '"/><rect class="cls-1" x="' . $x . '" y="' . $x . '" width="' . $x . '" height="' . $x
                        . '"/><rect class="cls-2" x="' . $x . '" width="' . $x . '" height="' . $x . '"/></g></svg>'
                    );
                    $pattern = $img->textureImage($pattern);
                    $pattern->compositeImage(
                        $img,
                        \Imagick::COMPOSITE_OVER,
                        0,
                        0
                    );
                    $img = $pattern;
                    $target_format = self::PREVIEW_IMAGE_FORMAT;
                }
            } else {
                $resource = $stream->detach();
                fseek($resource, 0);
                $img->readImageFile($resource);
            }
        } catch (\ImagickException $e) {
            // due to possible security risks, gs disabled access to files, see e.g. https://en.linuxportal.info/tutorials/troubleshooting/how-to-fix-errors-from-imagemagick-imagick-conversion-system-security-policy
            return;
        }

        // Size Settings
        $max_size = $for_definition->getMaxSize();
        $img->resetIterator();

        for ($x = 0; ($x < $for_definition->getMaxPages() && $x < $img->getNumberImages()); $x++) {
            $img->setIteratorIndex($x);
            $img->setImageAlphaChannel($alpha_channel);
            $img->setImageBackgroundColor($target_background);
            $img->setImageFormat($target_format);
            if ($remove_color !== null) {
                $img->transparentPaintImage($remove_color, 0, 0, false);
            }

            [$columns, $rows] = $this->calculateWidthHeightFromImage($img, $max_size);

            if (!$this->high_quality) {
                $yield = $img->thumbnailImage(
                    (int) $columns,
                    (int) $rows,
                    true,
                    $for_definition->isFill()
                );
            } else {
                $img->setImageResolution($resolution, $resolution);
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality($quality);
                $img->stripImage();

                $yield = $yield = $img->resizeImage(
                    (int) $columns,
                    (int) $rows,
                    \Imagick::FILTER_MITCHELL,
                    1
                );
            }

            if ($yield) {
                yield new Result(
                    $for_definition,
                    Streams::ofString($img->getImageBlob()),
                    $x,
                    $for_definition->persist()
                );
            }
        }
        $img->destroy();
    }

    private function calculateWidthHeight(float $original_width, float $original_height, int $max_size): array
    {
        if ($original_width === $original_height) {
            return [$max_size, $max_size];
        }

        if ($original_width > $original_height) {
            $columns = $max_size;
            $rows = (float) ($max_size * $original_height / $original_width);
            return [$columns, $rows];
        }

        $columns = (float) ($max_size * $original_width / $original_height);
        $rows = $max_size;
        return [$columns, $rows];
    }

    private function calculateWidthHeightFromImage(\Imagick $original, int $max_size): array
    {
        return $this->calculateWidthHeight(
            $original->getImageWidth(),
            $original->getImageHeight(),
            $max_size
        );
    }

    /**
     * @description SVGs usually become extremely poou in quality when converted, because they start from a much too
     * small original size and are therefore extremely scaled, so we temporarily write the size for the SVG so that it
     * already corresponds to the desired size
     */
    private function prescaleSVG(string $svg_content, int $max_length): string
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($svg_content);
            $svg = $dom->documentElement;

            // Get Viewbox if available
            $viewbox = $svg->getAttribute('viewBox');
            if ($viewbox === '') {
                return $svg_content;
            }
            $viewbox = explode(' ', $viewbox);
            $width = (float) ($viewbox[2] ?? 0);
            $height = (float) ($viewbox[3] ?? 0);

            if ($width === 0 || $height === 0) {
                return $svg_content;
            }

            [$new_width, $new_height] = $this->calculateWidthHeight(
                ceil($width),
                ceil($height),
                $max_length
            );
            $svg->setAttribute('width', (string) $new_width);
            $svg->setAttribute('height', (string) $new_height);

            return $dom->saveXML($svg);
        } catch (\Throwable $e) {
            return $svg_content;
        }
    }

    private function isSVG(FileInformation $information): bool
    {
        return $information->getMimeType() === 'image/svg+xml' || $information->getMimeType() === 'image/svg';
    }

}
