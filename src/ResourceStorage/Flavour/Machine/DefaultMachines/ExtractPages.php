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

        $this->high_quality = $for_definition->useMaxQuality();
        $quality = $for_definition->getQuality();

        $img = new \Imagick();
        if ($this->high_quality) {
            $quality = 100;
            $img->setResolution(300, 300); // way better previews for PDFs. Must be set before reading the file
        }

        try {
            $resource = $stream->detach();
            fseek($resource, 0);
            $img->readImageFile($resource);
        } catch (\ImagickException $e) {
            // due to possible security risks, gs disabled access to files, see e.g. https://en.linuxportal.info/tutorials/troubleshooting/how-to-fix-errors-from-imagemagick-imagick-conversion-system-security-policy
            return;
        }

        $original_with = $img->getImageWidth();
        $original_height = $img->getImageHeight();


        $img->setBackgroundColor('white');
        $img->resetIterator();

        $max_size = $for_definition->getMaxSize();

        for ($x = 0; ($x < $for_definition->getMaxPages() && $x < $img->getNumberImages()); $x++) {
            $img->setIteratorIndex($x);
            $img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $img->setImageFormat(self::PREVIEW_IMAGE_FORMAT);
            if (!$this->high_quality) {
                $yield = $img->thumbnailImage($max_size, $max_size, true, $for_definition->isFill());
            } else {
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality($quality);
                $img->stripImage();

                if ($original_with > $original_height) {
                    $columns = $max_size;
                    $rows = (int) ($max_size * $original_height / $original_with);
                    $yield = $yield = $img->resizeImage(
                        $columns,
                        $rows,
                        \Imagick::FILTER_MITCHELL,
                        1
                    );
                } else {
                    $columns = (int) ($max_size * $original_with / $original_height);
                    $rows = $max_size;
                    $yield = $yield = $img->resizeImage(
                        $columns,
                        $rows,
                        \Imagick::FILTER_MITCHELL,
                        1
                    );
                }
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
}
