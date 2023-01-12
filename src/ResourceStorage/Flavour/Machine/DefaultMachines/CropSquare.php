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
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class CropSquare extends AbstractMachine implements FlavourMachine
{
    use GdImageToStreamTrait;

    public const ID = 'crop_square';
    public const QUALITY = 30;

    public function getId(): string
    {
        return self::ID;
    }


    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof CropToSquare;
    }

    public function dependsOnEngine(): ?string
    {
        return GDEngine::class;
    }


    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        if (!$for_definition instanceof \ILIAS\ResourceStorage\Flavour\Definition\CropToSquare) {
            throw new \InvalidArgumentException('Invalid definition');
        }
        $image = $this->from($stream);
        if (!is_resource($image) && !$image instanceof \GdImage) {
            return;
        }

        $stream_path = $stream->getMetadata('uri');
        if ($stream_path === 'php://memory') {
            [$width, $height] = getimagesizefromstring((string)$stream);
        } else {
            [$width, $height] = getimagesize($stream_path);
        }

        if ($width > $height) {
            $y = 0;
            $x = (int) (($width - $height) / 2);
            $smallest_side = (int) $height;
        } else {
            $x = 0;
            $y = (int) (($height - $width) / 2);
            $smallest_side = (int) $width;
        }

        $size = (int) $for_definition->getMaxSize();

        $thumb = imagecreatetruecolor($size, $size);
        imagecopyresampled(
            $thumb,
            $image,
            0,
            0,
            $x,
            $y,
            $size,
            $size,
            $smallest_side,
            $smallest_side
        );


        imagedestroy($image);

        $stream = $this->to($thumb, $for_definition->getQuality());

        yield new Result(
            $for_definition,
            $stream,
            0,
            $for_definition->persist()
        );
    }
}
