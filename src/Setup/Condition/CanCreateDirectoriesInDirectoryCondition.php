<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class CanCreateDirectoriesInDirectoryCondition extends ExternalConditionObjective
{
    const PROBE_NAME = "probe_for_directory_creation";

    public function __construct($which)
    {
        return parent::__construct(
            "Can create directories in '$which'",
            function (Setup\Environment $env) use ($which) : bool {
                $probe = $which . "/" . self::PROBE_NAME;
                if (!@mkdir($probe, 0774)) {
                    return false;
                }
                rmdir($probe);
                return true;
            },
            "ILIAS needs to be able to create directories in '$which'."
        );
    }
}
