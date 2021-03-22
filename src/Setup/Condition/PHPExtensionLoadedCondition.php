<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class PHPExtensionLoadedCondition extends ExternalConditionObjective
{
    public function __construct($which)
    {
        $ilias_version = ILIAS_VERSION_NUMERIC;

        return parent::__construct(
            "PHP extension \"$which\" loaded",
            function (Setup\Environment $env) use ($which) : bool {
                return in_array($which, get_loaded_extensions());
            },
            "ILIAS $ilias_version requires the PHP extension $which."
        );
    }
}
