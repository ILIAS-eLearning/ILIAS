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
use ILIAS\ResourceStorage\Flavour\Definition\CropToRectangle;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author       Stephan Kergomard <webmaster@kergomard.ch>
 * @noinspection AutoloadingIssuesInspection
 */
class CropRectangle extends AbstractMachine implements FlavourMachine
{
    use GdImageToStreamTrait;

    public const ID = 'crop_rectangle';
    public const QUALITY = 30;

    public function getId(): string
    {
        return self::ID;
    }


    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof CropToRectangle;
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
        if (!$for_definition instanceof CropToRectangle) {
            throw new \InvalidArgumentException('Invalid definition');
        }
        $image = $this->from($stream);
        if (!is_resource($image) && !$image instanceof \GdImage) {
            throw new \Exception('Invalid  Image');
        }

        $stream_path = $stream->getMetadata('uri');
        if ($stream_path === 'php://memory') {
            [$source_width, $source_height] = getimagesizefromstring((string)$stream);
        } else {
            [$source_width, $source_height] = getimagesize($stream_path);
        }

        $target_width = $for_definition->getMaxWidth();
        $target_height = (int) ($for_definition->getMaxWidth() / $for_definition->getRatio());

        list($cutout_width, $cutout_height, $x_shift, $y_shift) = $this->calculateCutout(
            (int) $source_width,
            (int) $source_height,
            $for_definition->getRatio()
        );

        $thumb = imagecreatetruecolor(
            $target_width,
            $target_height
        );

        imagecopyresampled(
            $thumb,
            $image,
            0,
            0,
            $x_shift,
            $y_shift,
            $target_width,
            $target_height,
            $cutout_width,
            $cutout_height
        );


        imagedestroy($image);

        $target_stream = $this->to($thumb, $for_definition->getQuality());

        yield new Result(
            $for_definition,
            $target_stream,
            0,
            $for_definition->persist()
        );
    }

    /**
     *
     * @return array<int>
     */
    private function calculateCutout(
        int $source_width,
        int $source_height,
        float $target_ratio
    ): array {
        $cutout_width = $source_width;
        $cutout_height = (int) ($source_width / $target_ratio);
        $x_shift = 0;
        $y_shift = (int) (($source_height - $cutout_height) / 2);

        if ($cutout_height > $source_height) {
            $cutout_height = $source_height;
            $cutout_width = (int) ($cutout_height * $target_ratio);
            $x_shift = (int) ($source_width - $cutout_width) / 2;
            $y_shift = 0;
        }

        return [$cutout_width, $cutout_height, $x_shift, $y_shift];
    }
}
