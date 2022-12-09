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
use ILIAS\ResourceStorage\Flavour\Definition\FitToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class FitSquare extends AbstractMachine implements FlavourMachine
{
    use GdImageToStreamTrait;

    public const ID = 'fit_square';


    public function getId(): string
    {
        return self::ID;
    }


    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof FitToSquare;
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
        if (!$for_definition instanceof \ILIAS\ResourceStorage\Flavour\Definition\FitToSquare) {
            throw new \InvalidArgumentException('Invalid definition');
        }
        $image = $this->from($stream);
        if (!is_resource($image) && !$image instanceof \GdImage) {
            return;
        }

        $size = $for_definition->getMaxSize();

        $cur_width = imagesx($image);
        $cur_height = imagesy($image);

        if ($cur_width < $size && $cur_height < $size) {
            return;
        }

        $width_ratio = $size / $cur_width;
        $height_ratio = $size / $cur_height;
        $ratio = min($width_ratio, $height_ratio);

        $new_height = (int)floor($cur_height * $ratio);
        $new_width = (int)floor($cur_width * $ratio);
        $resized = imagescale(
            $image,
            $new_width,
            $new_height,
            IMG_BICUBIC
        );
        imagedestroy($image);

        $stream = $this->to(
            $resized,
            $for_definition->getQuality(),
        );

        yield new Result(
            $for_definition,
            $stream,
            0,
            $for_definition->persist()
        );
    }
}
