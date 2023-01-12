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
    private bool $use_thumbnail_implementation = true;

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

        $img = new \Imagick();

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
        $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $img->resetIterator();

        $max_size = $for_definition->getMaxSize();

        for ($x = 0; ($x < $for_definition->getMaxPages() && $x < $img->getNumberImages()); $x++) {
            $img->setIteratorIndex($x);
            $img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $img->setImageFormat(self::PREVIEW_IMAGE_FORMAT);
            if ($this->use_thumbnail_implementation) {
                $yield = $img->thumbnailImage($max_size, $max_size, true, $for_definition->isFill());
            } else {
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality($for_definition->getQuality());
                $img->stripImage();

                if ($original_with > $original_height) {
                    $img->scaleImage($max_size, 0);
                } else {
                    $img->scaleImage(0, $max_size);
                }
                $yield = true;
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
