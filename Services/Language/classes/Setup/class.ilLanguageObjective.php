<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilLanguageObjective implements Setup\Objective
{
    protected $config;

    public function __construct(
        ?\ilLanguageSetupConfig $config
    ) {
        $this->config = $config;
    }
}
