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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\CropRectangle;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ExtractPages;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\CropToRectangle;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class FirstPageToTileImageMachine extends AbstractMachine implements FlavourMachine
{
    public const ID = "be03e07e396a61f7d9b31a712f0913fe9c6cd43b009ce69d30aa0f10bd3500bd";
    private const FULL_QUALITY_SIZE_THRESHOLD = 100;
    private ExtractPages $extract_pages;
    private CropRectangle $crop;
    private ?ilObjectTileImageFlavourDefinition $definition = null;
    private ?FileInformation $information = null;

    public function __construct()
    {
        $this->extract_pages = new ExtractPages();
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
        return ImagickEngine::class;
    }

    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
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
