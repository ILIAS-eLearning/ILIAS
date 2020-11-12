<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class PHPVersionCondition extends ExternalConditionObjective
{
    public function __construct($which)
    {
        return parent::__construct(
            "PHP version >= $which",
            function (Setup\Environment $env) use ($which) : bool {
                return version_compare(phpversion(), $which, ">=");
            },
            "ILIAS 6 requires PHP $which or later."
        );
    }
}
