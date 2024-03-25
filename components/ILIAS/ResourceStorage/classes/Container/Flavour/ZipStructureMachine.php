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

use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Engine\Engine;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Flavour\Engine\NoEngine;
use ILIAS\components\ResourceStorage\Container\Wrapper\ZipReader;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
class ZipStructureMachine implements FlavourMachine
{
    public function __construct()
    {
    }

    public function getId(): string
    {
        return 'zip_structure_reader';
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return $definition instanceof ZipStructureDefinition;
    }

    public function dependsOnEngine(): ?string
    {
        return NoEngine::class;
    }

    public function withEngine(Engine $engine): FlavourMachine
    {
        return $this;
    }

    public function getEngine(): Engine
    {
        return new NoEngine();
    }

    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        if (!$for_definition instanceof ZipStructureDefinition) {
            throw new \InvalidArgumentException('Invalid definition');
        }
        $reader = new ZipReader($stream);

        $data = $reader->getStructure();

        yield new Result(
            $for_definition,
            Streams::ofString($for_definition->sleep($data)),
            0,
            $for_definition->persist()
        );
    }

}
