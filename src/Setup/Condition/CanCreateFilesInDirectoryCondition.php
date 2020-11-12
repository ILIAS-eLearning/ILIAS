<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class CanCreateFilesInDirectoryCondition extends ExternalConditionObjective
{
    const PROBE_NAME = "probe_for_file_creation";

    public function __construct($which)
    {
        return parent::__construct(
            "Can create files in '$which'",
            function (Setup\Environment $env) use ($which) : bool {
                $probe = $which . "/" . self::PROBE_NAME;
                if (!@file_put_contents($probe, self::PROBE_NAME)) {
                    return false;
                }
                $success = @file_get_contents($probe) == self::PROBE_NAME;
                unlink($probe);
                return $success;
            },
            "ILIAS needs to be able to create files in '$which'."
        );
    }
}
