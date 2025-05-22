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
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract\SVG;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract\General;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract\PDF;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract\Video;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngineWithOptionalFFMpeg;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ExtractPages extends AbstractMachine implements FlavourMachine
{
    use ImageSizeCalculator;

    public const ID = 'extract_pages';
    private bool $high_quality = false;

    public function getId(): string
    {
        return self::ID;
    }

    public function dependsOnEngine(): ?string
    {
        return ImagickEngineWithOptionalFFMpeg::class;
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

        $extractor = new General();

        $mime_type = $information->getMimeType();
        switch (true) {
            case ($mime_type === 'image/svg+xml' || $mime_type === 'image/svg'):
                $extractor = new SVG();
                break;
            case (str_contains($mime_type, 'video')):
                $extractor = new Video();
                break;
            case ($mime_type === 'application/pdf'):
                $extractor = new PDF();
                break;
        }

        $target_format = $extractor->getTargetFormat();
        $target_background = $extractor->getBackground();
        $alpha_channel = $extractor->getAlphaChannel();
        $remove_color = $extractor->getRemoveColor();
        $resolution = $extractor->getResolution();

        // General Image Settings
        $img->setResolution($resolution, $resolution);
        $img->setBackgroundColor($target_background);

        // Read source image
        try {
            $img = $extractor->readImage($img, $stream, $for_definition);
        } catch (\ImagickException) {
            // due to possible security risks, gs disabled access to files, see e.g. https://en.linuxportal.info/tutorials/troubleshooting/how-to-fix-errors-from-imagemagick-imagick-conversion-system-security-policy
            return;
        }

        // Size Settings
        $max_size = $for_definition->getMaxSize();
        $img->resetIterator();

        // create gif if needed
        $gif = $target_format === 'gif' ? new \Imagick() : null;
        if ($gif !== null) {
            $gif->setFormat('GIF');
        }

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

                $yield = $img->resizeImage(
                    (int) $columns,
                    (int) $rows,
                    \Imagick::FILTER_MITCHELL,
                    1
                );
            }

            if ($yield && $gif === null) {
                yield new Result(
                    $for_definition,
                    Streams::ofString($img->getImageBlob()),
                    $x,
                    $for_definition->persist()
                );
            } elseif ($yield && $gif !== null) {
                $gif->addImage($img->getImage());
                $gif->setImageDelay(50);
            }
        }

        if ($gif !== null) {
            $gif->setImageFormat('gif');
            $gif->setIteratorIndex(0);
            $gif->setImageIterations(0); // 0 means infinite loop
            [$columns, $rows] = $this->calculateWidthHeightFromImage($gif, $max_size);
            $gif->thumbnailImage(
                (int) $columns,
                (int) $rows,
                true,
                $for_definition->isFill()
            );

            yield new Result(
                $for_definition,
                Streams::ofString($gif->getImagesBlob()),
                1,
                $for_definition->persist()
            );
            $gif->destroy();
        }
        $img->destroy();
    }
}
