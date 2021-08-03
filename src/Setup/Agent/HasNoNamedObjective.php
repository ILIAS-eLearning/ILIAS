<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Agent;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Config;

trait HasNoNamedObjective
{
    /**
     * Get a named objective from this agent.
     *
     * @throw InvalidArgumentException if there is no such objective.
     */
    public function getNamedObjective(string $name, Config $config = null) : Objective
    {
        throw new \InvalidArgumentException(
            "There is no named objective '$name'."
        );
    }
}
