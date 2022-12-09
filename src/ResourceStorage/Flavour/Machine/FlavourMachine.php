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

namespace ILIAS\ResourceStorage\Flavour\Machine;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\Engine;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface FlavourMachine
{
    /**
     * FlavourMachines must be able to be created without further dependencies
     */
    public function __construct();

    /**
     * @return string max. 64 characters, MUST be unique and NOT a class-related magic-constant.
     * E.g. you can generate a random one with
     *   $ php -r"echo hash('sha256', uniqid());" | pbcopy
     * in your shell and paste string in your getId() implementation.
     *
     * If you ever change the ID, FlavourDefinitions may no longer process anything with your
     * machine that previously designated you as the processing machine.
     */
    public function getId(): string;

    /**
     * Check if a corresponding configuration can be processed by this Machine.
     */
    public function canHandleDefinition(FlavourDefinition $definition): bool;

    /**
     * Return the class name of the Engine that is required for this Machine to work. Returning null will
     * result in a NullEngine passed to the Machine.
     */
    public function dependsOnEngine(): ?string;

    /**
     * The demanded Engine will be passed here. If the Machine does not depend on an Engine, a NullEngine
     */
    public function withEngine(Engine $engine): FlavourMachine;

    /**
     * @throws \OutOfBoundsException if the Machine is not yet initialized with an Engine
     */
    public function getEngine(): Engine;

    /**
     * @param FileInformation $information of the original File.
     * @param FileStream $stream of the original File.
     * @param FlavourDefinition $for_definition the definition for which the stream should be processed.
     *
     * @return Result[]|\Generator For each "thing" that the machine generates, a result can be returned.
     * E.g. when extracting 5 images from a PDF, 5 results are returned (Generator).
     */
    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator;
}
