<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * A non-objective, nothing to do to achieve it...
 */
class NullObjective implements Setup\Objective
{
    const LABEL = "Nothing to do.";

    public function getHash() : string
    {
        return "null-objective";
    }

    public function getLabel() : string
    {
        return self::LABEL;
    }

    public function isNotable() : bool
    {
        return false;
    }

    /*
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return false;
    }
}
