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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ImageSizeCalculator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class SVG implements Extractor
{
    use ImageSizeCalculator;

    private bool $pattern = false;

    public function readImage(\Imagick $img, Stream $stream, PagesToExtract $definition): \Imagick
    {
        $img->readImageBlob(
            $this->prescaleSVG((string) $stream, $definition->getMaxSize())
        );

        // add pattern to background
        if ($this->pattern) {
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
        }

        return $img;
    }

    public function getBackground(): \ImagickPixel
    {
        return new \ImagickPixel('none');
    }

    public function getAlphaChannel(): int
    {
        return \Imagick::ALPHACHANNEL_ACTIVATE;
    }

    public function getRemoveColor(): ?\ImagickPixel
    {
        return new \ImagickPixel('transparent');
    }

    public function getResolution(): int
    {
        return 96;
    }

    public function getTargetFormat(): string
    {
        return $this->pattern ? 'jpg' : 'png64';
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
}
