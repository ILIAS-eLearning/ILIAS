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
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropSquare;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\GdImageToStreamTrait;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ExtractPages;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\Filesystem\Stream\Stream;

class ilBadgePictureMachine extends AbstractMachine
{
    use GdImageToStreamTrait;

    public const ID = 'badge_image_resize_machine';
    private const FULL_QUALITY_SIZE_THRESHOLD = 100;

    private CropSquare $crop;
    private ?ilBadgePictureDefinition $definition = null;
    private ?FileInformation $information = null;
    private ExtractPages $extract_pages;

    public function __construct()
    {
        parent::__construct();
        $this->extract_pages = new ExtractPages();
        $this->crop = new CropSquare();
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof ilBadgePictureDefinition;
    }

    public function dependsOnEngine(): ?string
    {
        return ImagickEngine::class;
    }

    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): Generator {
        /** @var ilBadgePictureDefinition $for_definition */
        $this->definition = $for_definition;
        $this->information = $information;

        $page_stream = $this->extract_pages->processStream(
            $this->information,
            $stream,
            new PagesToExtract(
                false,
                $this->definition->getWidths()['xl'],
                1,
                false,
                100
            )
        )->current()?->getStream();

        if ($page_stream === null) {
            return;
        }

        $i = 0;
        foreach ($for_definition->getWidths() as $width) {
            yield new Result(
                $for_definition,
                $this->cropImage($page_stream, $width),
                $i,
                true
            );
            $i++;
        }
    }

    protected function cropImage(
        FileStream $stream,
        int $size
    ): ?Stream {
        $quality = $size <= self::FULL_QUALITY_SIZE_THRESHOLD
            ? 100
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
}
