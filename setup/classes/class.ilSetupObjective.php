<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilSetupObjective implements Setup\Objective
{
    /**
     * @var	Setup\Config
     */
    protected $config;

    public function __construct(Setup\Config $config)
    {
        $this->config = $config;
    }
}
