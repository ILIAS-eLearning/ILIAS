<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class CanCreateFilesInDirectoryCondition extends ExternalConditionObjective
{
    const PROBE_NAME = "probe_for_file_creation";

    public function __construct(string $which)
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
