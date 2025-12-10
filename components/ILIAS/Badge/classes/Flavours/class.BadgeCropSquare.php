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

use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngineWithOptionalFFMpeg;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;

class BadgeCropSquare extends AbstractMachine
{
    public const string ID = 'badge_crop_square';

    public function getId(): string
    {
        return self::ID;
    }

    public function dependsOnEngine(): ?string
    {
        return ImagickEngineWithOptionalFFMpeg::class;
    }

    /**
     * @throws ImagickException
     */
    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        if (!$for_definition instanceof CropToSquare) {
            throw new \InvalidArgumentException('Invalid definition');
        }

        $stream_path = $stream->getMetadata()['uri'] ?? '';

        try {
            $image = new \Imagick($stream_path);
        } catch (\ImagickException) {
            return;
        }

        $size = $for_definition->getMaxSize();

        $image->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
        $image->setImageBackgroundColor(new \ImagickPixel('transparent'));
        $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_BACKGROUND);
        $image->mergeImageLayers(Imagick::LAYERMETHOD_REMOVEZERO);
        $image->setFormat("png");

        $img_target = Streams::ofString($image->getImageBlob());
        $image->clear();

        yield new Result(
            $for_definition,
            $img_target,
            0,
            $for_definition->persist()
        );
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof CropToSquare;
    }
}
