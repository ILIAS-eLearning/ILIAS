<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilSetupObjective implements Setup\Objective
{
    protected Setup\Config $config;

    public function __construct(Setup\Config $config)
    {
        $this->config = $config;
    }
}
