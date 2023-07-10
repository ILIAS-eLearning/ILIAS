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

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;

/**
 * @internal
 */
class DummyDefinition implements FlavourDefinition
{
    private string $id;
    private string $machine_id;
    private bool $persists = false;

    public function __construct(string $id, string $machine_id, bool $persists = false)
    {
        $this->id = $id;
        $this->machine_id = $machine_id;
        $this->persists = $persists;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFlavourMachineId(): string
    {
        return $this->machine_id;
    }

    public function getInternalName(): string
    {
        return 'foo';
    }

    public function getVariantName(): ?string
    {
        return null;
    }

    public function persist(): bool
    {
        return $this->persists;
    }
}
