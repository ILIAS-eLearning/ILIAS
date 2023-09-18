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
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Engine\NoEngine;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\AbstractMachine;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NullMachine extends AbstractMachine implements FlavourMachine
{
    private string $reason = '';


    public function getId(): string
    {
        return 'null_machine';
    }

    public function canHandleDefinition(FlavourDefinition $definition): bool
    {
        return true;
    }

    public function dependsOnEngine(): ?string
    {
        return NoEngine::class;
    }

    public function withReason(string $reason): FlavourMachine
    {
        $clone = clone $this;
        $clone->reason = $reason;
        return $clone;
    }

    public function getReason(): string
    {
        return $this->reason;
    }


    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        yield new NonStoreableResult(
            $for_definition,
            Streams::ofString('empty')
        );
    }
}
