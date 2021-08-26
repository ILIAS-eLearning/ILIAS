<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilDatabaseObjective implements Setup\Objective
{
    protected \ilDatabaseSetupConfig $config;

    public function __construct(\ilDatabaseSetupConfig $config)
    {
        $this->config = $config;
    }
}
