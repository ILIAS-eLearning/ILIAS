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

namespace ILIAS\ResourceStorage\Flavours;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\Engine;
use ILIAS\ResourceStorage\Flavour\Engine\NoEngine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\ResourceStorage\Flavour\Machine\NonStoreableResult;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @internal
 */
class DummyMachine extends AbstractMachine implements FlavourMachine
{
    private string $depends_on_engine = NoEngine::class;
    private string $id = self::class;
    private ?string $can_handle_definition_id = null;

    public function load(
        string $id,
        string $can_handle_definition_id = null,
        string $depends_on_engine = NoEngine::class
    ): void {
        $this->id = $id;
        $this->depends_on_engine = $depends_on_engine;
        $this->can_handle_definition_id = $can_handle_definition_id;
    }

    public function __construct()
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        if ($this->can_handle_definition_id === null) {
            return true;
        }
        return $this->can_handle_definition_id === $definition->getId();
    }

    public function dependsOnEngine(): ?string
    {
        return $this->depends_on_engine;
    }


    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        yield new NonStoreableResult($for_definition, $stream);
    }
}
