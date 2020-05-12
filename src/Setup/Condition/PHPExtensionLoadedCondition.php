<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class PHPExtensionLoadedCondition extends ExternalConditionObjective
{
    public function __construct($which)
    {
        return parent::__construct(
            "PHP extension \"$which\" loaded",
            function (Setup\Environment $env) use ($which) : bool {
                return in_array($which, get_loaded_extensions());
            },
            "ILIAS 6 requires the PHP extension $which."
        );
    }
}
