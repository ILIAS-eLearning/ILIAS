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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\ToGreyScale;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropSquare;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\GdImageToStreamTrait;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\MakeGreyScale;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilUserProfilePictureMachine extends AbstractMachine implements FlavourMachine
{
    use GdImageToStreamTrait;
    public const ID = "a3c77dec93b5303f4340767b9a445ad591f6025a9ca7edbf24fa8ab23a851eae";
    private const FULL_QUALITY_SIZE_THRESHOLD = 100;
    private CropSquare $crop;
    private MakeGreyScale $grey;
    private ?ilUserProfilePictureDefinition $definition = null;
    private ?FileInformation $information = null;

    public function __construct()
    {
        $this->crop = new CropSquare();
        $this->grey = new MakeGreyScale();
    }


    public function getId(): string
    {
        return self::ID;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof ilUserProfilePictureDefinition;
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
        /** @var ilUserProfilePictureDefinition $for_definition */
        $this->definition = $for_definition;
        $this->information = $information;

        $i = 0;
        foreach ($for_definition->getSizes() as $size) {
            yield new Result(
                $for_definition,
                $this->cropImage($stream, $size),
                $i,
                true
            );
            $i++;
        }
    }

    protected function cropImage(
        FileStream $stream,
        int $size
    ) {
        $quality = $size <= self::FULL_QUALITY_SIZE_THRESHOLD
            ? 100 // we take 100% jpeg quality for small resultions
            : $this->definition->getQuality();


        return $this->crop->processStream(
            $this->information,
            $stream,
            new CropToSquare(
                false,
                $size,
                $quality
            )
        )->current()->getStream();
    }

    protected function makeGreyScale(
        FileStream $stream
    ) {
        return $this->grey->processStream(
            $this->information,
            $stream,
            new ToGreyScale(
                false,
                $this->definition->getQuality()
            )
        )->current()->getStream();
    }
}
