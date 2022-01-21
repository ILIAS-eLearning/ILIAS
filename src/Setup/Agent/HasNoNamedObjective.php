<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Config;

trait HasNoNamedObjective
{
    public function getNamedObjectives(?Config $config = null) : array
    {
        return [];
    }
}
