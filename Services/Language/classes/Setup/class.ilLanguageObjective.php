<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilLanguageObjective implements Setup\Objective
{
    /**
     * @var	?\ilLanguageSetupConfig
     */
    protected $config;

    public function __construct(
        ?\ilLanguageSetupConfig $config
    ) {
        $this->config = $config;
    }
}
