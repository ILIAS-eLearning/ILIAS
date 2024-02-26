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

namespace ILIAS\Object\Properties\CoreProperties\TileImage;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\CropToRectangle;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropRectangle;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

class ilObjectTileImageFlavourMachine extends AbstractMachine implements FlavourMachine
{
    public const ID = "4c7e3aaff42a352fa3fd3dfc4d4a994cc3dfdd97e97c2c2c9932e22e2e57357a";
    private const FULL_QUALITY_SIZE_THRESHOLD = 100;
    private CropRectangle $crop;
    private ?ilObjectTileImageFlavourDefinition $definition = null;
    private ?FileInformation $information = null;

    public function __construct()
    {
        $this->crop = new CropRectangle();
    }


    public function getId(): string
    {
        return self::ID;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof ilObjectTileImageFlavourDefinition;
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
        /** @var ilObjectTileImageFlavourDefinition $for_definition */
        $this->definition = $for_definition;
        $this->information = $information;

        $i = 0;
        foreach ($for_definition->getWidths() as $width) {
            yield new Result(
                $for_definition,
                $this->cropImage($stream, $width),
                $i,
                true
            );
            $i++;
        }
    }

    protected function cropImage(
        FileStream $stream,
        int $width
    ) {
        $quality = $width <= self::FULL_QUALITY_SIZE_THRESHOLD
            ? 100 // we take 100% jpeg quality for small resultions
            : $this->definition->getQuality();


        return $this->crop->processStream(
            $this->information,
            $stream,
            new CropToRectangle(
                false,
                $width,
                $this->definition->getRatio(),
                $quality
            )
        )->current()->getStream();
    }
}
